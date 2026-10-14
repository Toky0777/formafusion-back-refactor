<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

/**
 * Sert l'application React (route fallback). Contrôleur à la place d'une closure
 * pour que `php artisan route:cache` fonctionne.
 */
class SpaController extends Controller
{
    public function __invoke()
    {
        $path = public_path('react/index.html');

        if (!File::exists($path)) {
            abort(404, 'React app not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'text/html',
        ]);
    }
}
