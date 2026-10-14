<?php

namespace App\Http\Middleware;

/**
 * Rôle 6 : Référent entreprise.
 * Logique commune : voir RoleGate.
 */
class Entreprise extends RoleGate
{
    protected array $allowedRoleIds = [6];
}
