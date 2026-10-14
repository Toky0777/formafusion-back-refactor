<?php

namespace App\Http\Controllers;

/**
 * Listes statiques utilisées par l'écran clients du CFP.
 * (Contrôleur à la place de closures pour permettre `php artisan route:cache`.)
 */
class ClientLookupController extends Controller
{
    public function cities()
    {
        return response()->json(['villes' => [
            'idVille' => 1,
            'ville_name' => 'Paris',
        ]]);
    }

    public function typeCustomers()
    {
        return response()->json(['typeCustomers' => [
            ['idTypeCustomer' => 1, 'typeCustomerDesc' => 'Entreprise'],
            ['idTypeCustomer' => 2, 'typeCustomerDesc' => 'Particulier'],
        ]]);
    }
}
