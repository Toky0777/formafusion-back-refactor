<?php

namespace App\Http\Middleware;

/**
 * Rôle 5 : Formateur.
 * Logique commune : voir RoleGate.
 */
class Formateur extends RoleGate
{
    protected array $allowedRoleIds = [5];
}
