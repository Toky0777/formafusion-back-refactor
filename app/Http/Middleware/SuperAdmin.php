<?php

namespace App\Http\Middleware;

/**
 * Rôle 1 : Super admin (rôle actif exigé).
 * Logique commune : voir RoleGate.
 */
class SuperAdmin extends RoleGate
{
    protected array $allowedRoleIds = [1];

    protected bool $requireActive = true;
}
