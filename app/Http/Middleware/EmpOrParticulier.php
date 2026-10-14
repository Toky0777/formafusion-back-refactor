<?php

namespace App\Http\Middleware;

/**
 * Rôles 4, 9 ou 10 : employé, employé entreprise ou particulier.
 * Logique commune : voir RoleGate.
 */
class EmpOrParticulier extends RoleGate
{
    protected array $allowedRoleIds = [4, 9, 10];
}
