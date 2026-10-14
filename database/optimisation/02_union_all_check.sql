-- TEST DE SÉCURITÉ avant de remplacer UNION par UNION ALL dans v_union_projets.
-- UNION supprime les lignes strictement identiques ; UNION ALL non. Le résultat n'est équivalent
-- que si aucune des 3 branches ne produit de doublons exacts.
-- Mesuré sur le jeu de test (154 000 projets) : résultat identique et lecture complète -37 %
-- (4,2 s -> 2,6 s). A VALIDER SUR LES DONNEES REELLES : exécuter ce script ; si "ecart" = 0,
-- le remplacement est sans risque pour les données actuelles (aucune ligne ne disparaît).
-- Cette vue de test n'altère pas v_union_projets.

CREATE OR REPLACE VIEW v_union_projets_all AS
SELECT
	idProjet, idDossier, referenceEtp,'--' as idCfp_intra,idTypeprojet, idModule, idVilleCoded, idVille,'--'as idPaiement,'--' as paiement, p_id_periode,module_name,module_description,etp_name,ville,project_status,project_type, project_name, dateDebut, dateFin, project_reference, project_title, project_description, project_is_reserved, project_is_active, project_is_trashed,module_image, etp_logo, etp_initial_name,salle_name,salle_rue, salle_quartier,salle_code_postal, project_is_cancelled,project_is_repported, '--' as project_price_pedagogique,'--' as project_price_annexe ,idEtp, idCfp_inter,idEtp_inter,idModalite,modalite,headDate,headMonthDebut,headMonthFin,headYear,headDayDebut,headDayFin, total_ht,total_ttc,total_ht_etp,total_ttc_etp, total_ht_sub_contractor
FROM v_projet_internes
UNION ALL
SELECT
	idProjet, idDossier,referenceEtp,idCfp_intra, idTypeprojet, idModule, idVilleCoded, idVille,idPaiement,paiement,p_id_periode,module_name,module_description,etp_name,ville,project_status,project_type,project_name,dateDebut, dateFin, project_reference, project_title, project_description, project_is_reserved, project_is_active,project_is_trashed,module_image, etp_logo,etp_initial_name,salle_name,salle_rue, salle_quartier,salle_code_postal,project_is_cancelled,project_is_repported,project_price_pedagogique,project_price_annexe, idEtp,idCfp_inter,idEtp_inter,idModalite,modalite, headDate, headMonthDebut, headMonthFin, headYear, headDayDebut,headDayFin, total_ht,total_ttc,total_ht_etp,total_ttc_etp, total_ht_sub_contractor 
FROM v_projet_inters
UNION ALL
SELECT
	idProjet, idDossier, referenceEtp,idCfp_intra, idTypeprojet,idModule, idVilleCoded, idVille,idPaiement,paiement,p_id_periode,module_name,module_description,etp_name,ville,project_status,project_type,  project_name, dateDebut, dateFin, project_reference, project_title, project_description, project_is_reserved, project_is_active, project_is_trashed,module_image, etp_logo,etp_initial_name,salle_name,salle_rue, salle_quartier,salle_code_postal,  project_is_cancelled, project_is_repported, project_price_pedagogique, project_price_annexe, idEtp, idCfp_inter,idEtp_inter,idModalite,modalite,headDate,headMonthDebut,headMonthFin,headYear,headDayDebut,headDayFin, total_ht,total_ttc,total_ht_etp,total_ttc_etp, total_ht_sub_contractor 
FROM v_projet_intras;

SELECT (SELECT COUNT(*) FROM v_union_projets)     AS avec_union,
       (SELECT COUNT(*) FROM v_union_projets_all) AS avec_union_all,
       (SELECT COUNT(*) FROM v_union_projets_all) - (SELECT COUNT(*) FROM v_union_projets) AS ecart;

-- Nettoyage : DROP VIEW v_union_projets_all;
