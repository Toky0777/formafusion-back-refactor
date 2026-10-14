# FormaFusion back — rapport d'optimisation

Périmètre : refactorisation ciblée et optimisation des requêtes, **sans changement de comportement métier**
(sauf mentions explicites ci-dessous). Toutes les modifications PHP passent `php -l` (PHP 8.3 ; code
compatible 8.1). L'application n'a **pas pu être exécutée** (pas de `vendor/` ni de `bootstrap/` dans l'archive
reçue) : faire une recette fonctionnelle avant mise en production.

## 1. Gains mesurés (SQL)

Banc d'essai : MariaDB 10.11, base de démo multipliée à ~154 000 projets, 246 000 émargements.
Les temps incluent ~6 ms de démarrage du client MySQL. Dans chaque cas, le résultat est vérifié **identique**.

| Requête | Avant | Après |
|---|---|---|
| Projets d'une année (`v_projet_emps`) | 73 ms | 24 ms |
| Projets d'une année (`v_projet_form`) | 73 ms | 26 ms |
| Projets d'une année (`v_projet_cfps`) | 68 ms | 22 ms |
| Projets d'une année (`v_projet_particulier`) | 56 ms | 16 ms |
| Plage de dates sur `projets` | 46 ms | 6 ms |
| `emargements` filtrée par projet | 31 ms | 7 ms |
| `getDropdownItem` (filtres apprenant) | 13 + N requêtes | 7 requêtes |

(N = nombre de formateurs de la plateforme.) Les gains croissent avec le volume : ces requêtes parcouraient
toute la table.

## 2. Modifications

**Requêtes**
- `where('headYear', …)` → `whereDebutYear(…)` (76 usages, 4 vues). `headYear` est calculé par
  `DATE_FORMAT(dateDebut,'%Y')` dans la vue : aucun index possible. Le nouveau filtre travaille sur
  `dateDebut` (plage de dates) et utilise l'index. Macro définie dans `AppServiceProvider`.
  Les 16 usages hors chaîne simple et ceux sur `v_union_projets` n'ont volontairement pas été touchés.
- `FiltreApprenantController::getDropdownItem` : 6 requêtes de périodes fusionnées en 1 ; boucle morte
  supprimée (`$formateurs` n'était jamais renvoyé et lançait une requête par formateur du système).
  Format JSON renvoyé inchangé (vérifié sur 12 combinaisons employé × année).
- `Customer::idCustomer()` & dérivés (≈ 1 900 appels dans le code) : une requête par utilisateur et par
  requête HTTP au lieu d'une par appel (`App\Support\RequestMemo`, enregistré en *scoped* : aucune fuite entre
  requêtes/jobs).
- Composers de vues `'*'` (`AppServiceProvider`, `MenuServiceProvider`) : calcul une fois par requête ;
  villes/communes en cache 24 h. Impact limité ici (l'application sert surtout une API JSON).

**Factorisation**
- 10 middlewares de rôle copiés-collés → `RoleGate` + 10 classes de 12 lignes (mêmes noms, `Kernel`
  inchangé), une requête `role_users` par requête HTTP.
- 3 closures de routes → contrôleurs (`SpaController`, `ClientLookupController`) : **`php artisan route:cache`
  est maintenant possible** (2 416 routes reconstruites à chaque requête sans cache).
- 13 appels `env()` hors config déplacés dans `config/services.php` (mêmes valeurs par défaut) :
  **`php artisan config:cache` est maintenant possible**.

**Nettoyage**
- 3 `dd()` actifs (classes `*Mail`) → exception `RuntimeException`.
- Migration en doublon supprimée (`…password_resets_table copy.php`, identique à l'originale : `migrate` échouerait).
- Supprimés : `dconf.bkup`, `.phpunit.result.cache`. `.env.example` ajouté (sans secret).
- Poids du projet : 394 Mo → ~54 Mo (sans `ml_service/venv`, 341 Mo, et sans `.env`).

## 3. Mise en production

1. Copier les fichiers par-dessus le dépôt (conserver votre `bootstrap/`, `vendor/` et `.env`).
2. `php artisan migrate` — ajoute 4 index (idempotent). Sur de grosses tables, hors heures de pointe.
   Équivalent SQL : `database/optimisation/01_indexes.sql`.
3. `php artisan route:cache && php artisan config:cache && php artisan view:cache`
   (à refaire à chaque déploiement ; `php artisan optimize:clear` pour annuler).
4. `composer install --no-dev --optimize-autoloader`.

## 4. À décider / non modifié

- **Secrets** : le zip reçu contenait un `.env` avec des clés actives. À régénérer. Ce `.env` indique
  `APP_ENV=local`, `APP_DEBUG=true`, `LOG_LEVEL=debug`, `QUEUE_CONNECTION=sync` : à corriger en production.
- **`Cfp` & co (règle d'accès)** : seul le *premier* rôle actif est comparé ; un utilisateur à plusieurs
  rôles peut recevoir un 403 selon l'ordre des lignes. Conservé tel quel (c'est une règle d'autorisation).
- **`getDropdownItem`** : la requête `$months` n'a aucun filtre utilisateur/centre (compte tous les projets).
- `UNION` → `UNION ALL` dans `v_union_projets` : -37 % sur la lecture complète, résultat identique sur le
  jeu de test, mais à valider sur vos données : `database/optimisation/02_union_all_check.sql`.
  Les sidebars ETP/apprenant (vue `UNION`) restent coûteuses (~230 ms à 154 000 projets) : la vraie
  solution est de ne plus passer par cette vue pour ces écrans.
- 135 boucles contenant des requêtes (N+1) restent à traiter, par exemple `AttributionBadgeController`.
- `public/img/modules/66cc761be482a.png` : 15 Mo (3639 × 3639) ; ~3 Mo une fois redimensionnée à 1 600 px.
- `intervention/image` est déclaré `*` (verrouillé en 3.11.6) : fixer `^3.11` puis `composer update --lock`.
- `ini_set('upload_max_filesize'|'post_max_size')` dans le code n'a aucun effet à l'exécution (réglage
  PHP_INI_PERDIR) : à fixer dans la configuration PHP de l'hébergeur.
- Mesures faites sur MariaDB ; les vues `UNION` peuvent se comporter différemment sur MySQL 8.
