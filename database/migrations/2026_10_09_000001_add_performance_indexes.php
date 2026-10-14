<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index manquants sur des colonnes très filtrées / jointes qui n'ont PAS de clé étrangère
 * (MySQL n'indexe automatiquement que les colonnes déclarées en FOREIGN KEY).
 *
 * Mesures (MariaDB, jeu de test ~154 000 projets / 246 000 émargements) :
 *  - projets(dateDebut)        : filtre par année/plage  46 ms -> 6 ms ; sidebar CFP 69 ms -> 15 ms
 *  - emargements(idProjet)     : 31 ms -> 7 ms
 *  - eval_apprenant(...)       : jointures/filtres par projet (gain non mesurable à 16 000 lignes)
 *  - intras(idEtp)             : colonne de jointure des vues v_projet_* (gain non mesuré :
 *                                jeu de test avec seulement 2 entreprises, donc peu sélectif)
 *
 * Idempotente : n'ajoute un index que s'il n'existe pas déjà et si la table existe.
 */
return new class extends Migration
{
    /** [table, nom d'index, colonnes] */
    private array $indexes = [
        ['projets',        'idx_projets_dateDebut',        ['dateDebut']],
        ['emargements',    'idx_emargements_projet',       ['idProjet']],
        ['eval_apprenant', 'idx_eval_apprenant_projet',    ['idProjet', 'idEmploye']],
        ['intras',         'idx_intras_etp',               ['idEtp']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$table, $name, $columns]) {
            if (!Schema::hasTable($table) || !Schema::hasColumns($table, $columns) || $this->indexExists($table, $name)) {
                continue;
            }

            Schema::table($table, function ($blueprint) use ($columns, $name) {
                $blueprint->index($columns, $name);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as [$table, $name]) {
            if (Schema::hasTable($table) && $this->indexExists($table, $name)) {
                Schema::table($table, function ($blueprint) use ($name) {
                    $blueprint->dropIndex($name);
                });
            }
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name])) > 0;
    }
};
