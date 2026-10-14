<?php

namespace App\Http\Middleware;

/**
 * Rôle 10 : Particulier.
 * Logique commune : voir RoleGate.
 */
class ParticulierMiddleware extends RoleGate
{
    protected array $allowedRoleIds = [10];
}
