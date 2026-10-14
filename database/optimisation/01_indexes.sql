-- Index de performance (équivalent SQL de la migration 2026_10_09_000001_add_performance_indexes).
-- Syntaxe MariaDB (IF NOT EXISTS). Sur MySQL 8, exécuter chaque ligne séparément : une erreur
-- "Duplicate key name" signifie simplement que l'index existe déjà.
CREATE INDEX IF NOT EXISTS idx_projets_dateDebut     ON projets(dateDebut);
CREATE INDEX IF NOT EXISTS idx_emargements_projet    ON emargements(idProjet);
CREATE INDEX IF NOT EXISTS idx_eval_apprenant_projet ON eval_apprenant(idProjet, idEmploye);
CREATE INDEX IF NOT EXISTS idx_intras_etp            ON intras(idEtp);
