<?php

namespace App\Support;

use Closure;

/**
 * Mémoire à durée de vie d'une requête (enregistrée en "scoped" dans AppServiceProvider).
 *
 * Évite d'exécuter plusieurs fois la même requête SQL au cours d'une même requête HTTP
 * (ex. Customer::idCustomer() appelé des centaines de fois, composers de vues "*").
 * Le conteneur Laravel la réinitialise entre deux requêtes / jobs : aucune fuite de données
 * d'un utilisateur à l'autre (PHP-FPM, Octane, queue workers).
 *
 * Règle : une valeur null n'est JAMAIS mémorisée, ce qui permet de relire une donnée
 * qui n'existait pas encore (ex. ligne créée plus tôt dans la même requête).
 */
class RequestMemo
{
    /** @var array<string, mixed> */
    private array $store = [];

    public function remember(string $key, Closure $resolver): mixed
    {
        if (isset($this->store[$key])) {
            return $this->store[$key];
        }

        $value = $resolver();

        if ($value !== null) {
            $this->store[$key] = $value;
        }

        return $value;
    }

    public function forget(string $prefix = ''): void
    {
        if ($prefix === '') {
            $this->store = [];
            return;
        }

        foreach (array_keys($this->store) as $key) {
            if (str_starts_with($key, $prefix)) {
                unset($this->store[$key]);
            }
        }
    }
}
