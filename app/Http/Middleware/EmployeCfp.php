<?php

namespace App\Http\Middleware;

/**
 * Rôles 8 (employé CFP) ou 3 (CFP).
 * Logique commune : voir RoleGate.
 */
class EmployeCfp extends RoleGate
{
    protected array $allowedRoleIds = [8, 3];
}
