<?php

namespace App\Http\Middleware;

/**
 * Rôles 8, 3 ou 5 : employé CFP, CFP ou formateur.
 * Logique commune : voir RoleGate.
 */
class FormateurOrCfpOrEmployeCfp extends RoleGate
{
    protected array $allowedRoleIds = [8, 3, 5];
}
