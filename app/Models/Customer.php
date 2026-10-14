<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\RequestMemo;
// use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;

class Customer extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'customers';

    protected $primaryKey = 'idCustomer';

    protected $fillable = [
        'customerName',
        'nif',
        'stat',
        'assujetti',
        'rcs',
        'description',
        'siteWeb',
        'logo',
        'customerEmail',
        'customerPhone',
        'customer_addr_lot',
        'customer_addr_quartier',
        'customer_addr_rue',
        'customer_addr_code_postal',
        'customer_slogan',
        'idSecteur',
        'idTypeCustomer',
    ];

    public $timestamps = true;

    protected $casts = [
        'idCustomer' => 'integer',
        'assujetti' => 'boolean',
        'customer_addr_code_postal' => 'integer',
        'idSecteur' => 'integer',
        'idTypeCustomer' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Subscription feature disabled.
    // public function subscriptions()
    // {
    //     return $this->hasMany(config('laravel-subscriptions.models.subscription'));
    // }

    public function entreprise()
    {
        return $this->hasOne(Entreprise::class, 'idCustomer', 'idCustomer');
    }

    public function etp_groupeds()
    {
        return $this->hasOne(Etp_groupeds::class, 'idEntreprise', 'idCustomer');
    }

    /**
     * Ligne employé + type de client de l'utilisateur, lue UNE seule fois par requête HTTP.
     * (Avant : 1 requête SQL à chaque appel, soit ~1 900 appels dans l'application.)
     */
    private static function employeRow($userId): ?object
    {
        return app(RequestMemo::class)->remember("employe_customer:{$userId}", function () use ($userId) {
            return DB::table('employes')
                ->join('customers', 'employes.idCustomer', '=', 'customers.idCustomer')
                ->where('employes.idEmploye', $userId)
                ->select('employes.idEmploye', 'employes.idCustomer', 'customers.idTypeCustomer', 'customers.customerName')
                ->first();
        });
    }

    public static function idCustomer()
    {
        return self::employeRow(Auth::user()->id)->idCustomer;
    }

    public static function idCustomerById($userId)
    {
        return self::employeRow($userId)->idCustomer ?? null;
    }

    public static function customerName($userId)
    {
        return self::employeRow($userId)->customerName ?? null;
    }

    public static function typeCustomer()
    {
        return self::employeRow(Auth::user()->id)->idTypeCustomer;
    }

    public static function getCustomer($idCustomer)
    {
        $customer = DB::table('customers')
            ->select('idCustomer', 'idTypeCustomer', 'customerName AS customer_name', 'customerEmail AS customer_email', 'nif as customer_nif', 'customer_addr_lot')
            ->where('idCustomer', $idCustomer)
            ->first();

        return $customer;
    }
}
