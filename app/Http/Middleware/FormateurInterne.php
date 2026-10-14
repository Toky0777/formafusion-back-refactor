<?php

namespace App\Http\Middleware;

/**
 * Rôle 7 : Formateur interne.
 * Logique commune : voir RoleGate.
 */
class FormateurInterne extends RoleGate
{
    protected array $allowedRoleIds = [7];
}
