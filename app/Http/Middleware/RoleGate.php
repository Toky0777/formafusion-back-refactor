<?php

namespace App\Http\Middleware;

use App\Support\RequestMemo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Base commune des middlewares de rôle (Cfp, Employe, Formateur, ...).
 *
 * Avant : 10 classes copiées-collées, chacune relançait la même requête sur role_users.
 * Désormais : une seule logique, une seule requête par requête HTTP (même si plusieurs
 * middlewares de rôle s'enchaînent).
 *
 * Règle d'autorisation conservée à l'identique : c'est le PREMIER rôle actif de l'utilisateur
 * (ordre naturel de role_users) qui est comparé à la liste autorisée.
 * Un utilisateur sans aucun rôle actif reçoit désormais un 403 (avant : réponse vide en 200).
 */
abstract class RoleGate
{
    /** Identifiants de rôles (table roles) autorisés pour ce middleware. @var int[] */
    protected array $allowedRoleIds = [];

    /** Exige en plus role_users.isActive = 1 (SuperAdmin). */
    protected bool $requireActive = false;

    public function handle(Request $request, Closure $next)
    {
        $firstRoleId = $this->firstRoleId((int) Auth::user()->id);

        if ($firstRoleId !== null && in_array($firstRoleId, $this->allowedRoleIds, true)) {
            return $next($request);
        }

        abort(403);
    }

    private function firstRoleId(int $userId): ?int
    {
        $rows = app(RequestMemo::class)->remember(
            'role_users:' . $userId . ':' . (int) $this->requireActive,
            function () use ($userId) {
                $query = DB::table('role_users')
                    ->select('role_id', 'user_id')
                    ->where('user_id', $userId)
                    ->where('hasRole', 1);

                if ($this->requireActive) {
                    $query->where('isActive', 1);
                }

                return $query->get()->all();
            }
        );

        return isset($rows[0]) ? (int) $rows[0]->role_id : null;
    }
}
