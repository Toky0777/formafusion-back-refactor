<?php

namespace App\Http\Middleware;

/**
 * Rôle 4 : Employé.
 * Logique commune : voir RoleGate.
 */
class Employe extends RoleGate
{
    protected array $allowedRoleIds = [4];
}
