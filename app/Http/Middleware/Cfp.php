<?php

namespace App\Http\Middleware;

/**
 * Rôle 3 : CFP.
 * Logique commune : voir RoleGate.
 */
class Cfp extends RoleGate
{
    protected array $allowedRoleIds = [3];
}
