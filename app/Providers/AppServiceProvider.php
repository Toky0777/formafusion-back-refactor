<?php

namespace App\Providers;

use App\Interfaces\ApprenantInterface;
use App\Interfaces\CustomerOther\EmployeInterface;
use App\Interfaces\FormateurInterface;
use App\Interfaces\InvitationInterface as InvitationInterfaceClean;
use App\Interfaces\LieuInterface;
use App\Interfaces\UserRegisterInterface;
use App\Services\ApprenantService;
use App\Services\CfpService;
use App\Services\CustomerOther\Employe\StoreService;
use App\Services\DossierService;
use App\Services\EntrepriseService;
use App\Services\FormateurService;
use App\Services\LieuService;
use App\Services\ParticulierService;
use App\Support\RequestMemo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Mémoire à durée de vie d'une requête (réinitialisée automatiquement par le conteneur).
        $this->app->scoped(RequestMemo::class);

        $this->app->singleton(UserRegisterInterface::class, ParticulierService::class);
        $this->app->singleton(UserRegisterInterface::class, CfpService::class);
        $this->app->singleton(LieuInterface::class, LieuService::class);
        $this->app->singleton(EmployeInterface::class, StoreService::class);
        $this->app->singleton(ApprenantInterface::class, ApprenantService::class);
        $this->app->singleton(InvitationInterfaceClean::class, EntrepriseService::class);
        $this->app->singleton(InvitationInterfaceClean::class, CfpService::class);
        $this->app->singleton(FormateurInterface::class, FormateurService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('id', '[0-9]+');
        Paginator::useBootstrapFive();

        $this->registerQueryMacros();
        $this->registerSharedViewData();
    }

    /**
     * Filtre "année de début" SARGABLE : exploite l'index sur projets.dateDebut.
     * Remplace where('headYear', $y) qui filtrait sur DATE_FORMAT(dateDebut, '%Y') (valeur calculée
     * par la vue => lecture complète de la table à chaque appel).
     *
     *   DB::table('v_projet_cfps')->whereDebutYear(2026)
     */
    private function registerQueryMacros(): void
    {
        QueryBuilder::macro('whereDebutYear', function ($year, string $column = 'dateDebut') {
            /** @var QueryBuilder $this */
            $year = (int) $year;

            return $this->whereBetween($column, ["{$year}-01-01", "{$year}-12-31"]);
        });
    }

    /**
     * Données partagées avec TOUTES les vues.
     *
     * Avant : le composer '*' s'exécutait pour chaque vue/partiel rendu et relançait ~9 requêtes
     * (dont le chargement complet des villes, communes et lieux). Désormais :
     *  - calcul une seule fois par requête HTTP (RequestMemo) ;
     *  - référentiels géographiques quasi statiques mis en cache 24 h.
     */
    private function registerSharedViewData(): void
    {
        View::composer('*', function ($view) {
            $shared = app(RequestMemo::class)->remember('view_shared_data:' . (Auth::id() ?? 'guest'), function () {
                return $this->buildSharedViewData();
            });

            $view->with($shared);
        });
    }

    private function buildSharedViewData(): array
    {
        if (!Auth::check()) {
            return [
                'sub' => null,
                'infoProfilCfp' => null,
                'bucket' => null,
                'endpoint' => null,
                'digitalOcean' => null,
                'checkEtp' => null,
                'isEtpGrouped' => null,
                'ville' => null,
                'ville_addSalle' => null,
                'lieux_addSalle' => null,
            ];
        }

        $authenticatedUser = Auth::user();
        $userId = $authenticatedUser->id;

        $endpoint = config('filesystems.disks.do.url_cdn_digital');
        $bucket = config('filesystems.disks.do.bucket');

        return [
            // Subscription feature disabled.
            'sub' => null,
            'infoProfilCfp' => DB::table('customers')
                ->select('idCustomer', 'customerName', 'nif', 'stat', 'logo', 'customerEmail', 'idTypeCustomer')
                ->where('idCustomer', $userId)
                ->first(),
            'bucket' => $bucket,
            'endpoint' => $endpoint,
            'digitalOcean' => $endpoint . '/' . $bucket,
            'checkEtp' => DB::table('entreprises')->where('idCustomer', $userId)->first(),
            'isEtpGrouped' => DB::table('etp_groupes')->where('idEntreprise', $userId)->exists(),
            // Référentiels géographiques : changent très rarement.
            'ville' => Cache::remember('ref:ville_codeds', now()->addDay(), function () {
                return DB::table('ville_codeds as vc')->orderBy('vc.vi_code_postal', 'asc')->get();
            }),
            'ville_addSalle' => Cache::remember('ref:villes', now()->addDay(), function () {
                return DB::table('villes')->select('idVille', 'ville')->orderBy('ville', 'asc')->get();
            }),
            // Les lieux sont créés par les utilisateurs : pas de cache inter-requêtes.
            'lieux_addSalle' => DB::table('lieux')
                ->select('idLieu', 'li_name', 'ville_name', 'vi_code_postal')
                ->where('li_name', 'NOT LIKE', '%Default%')
                ->join('ville_codeds', 'ville_codeds.id', 'lieux.idVilleCoded')
                ->orderBy('li_name', 'asc')
                ->get(),
        ];
    }
}
