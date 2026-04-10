-- =============================================================================
-- Tables concernées : `commandes` · `categories` · `produits`
--
-- Les relations en base sont :
--   • `commandes` — une commande regroupe des lignes (détails dans `commande_produits`).
--   • `produits` — chaque produit appartient à une `categories` (colonne categorie_id).
--   • Lien commande ↔ produit : table de liaison `commande_produits`
--     (commande_id → commandes.id , produit_id → produits.id).
--
-- Objectif des ALTER ci-dessous :
--   1) Pouvoir supprimer un PRODUIT sans bloquer à cause des anciennes COMMANDES
--      (on assouplit la FK sur `commande_produits`.`produit_id`).
--   2) Pouvoir supprimer une CATEGORIE sans être bloqué par les PRODUITS
--      (FK `produits`.`categorie_id` → ON DELETE SET NULL).
--
-- La table `commandes` elle-même n’a pas de colonne produit_id : ne pas l’ALTER ici.
--
-- Avant exécution : sauvegarde complète de la base.
-- Si un DROP FOREIGN KEY échoue : voir la requête de diagnostic en fin de fichier.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1) Lien COMMANDES ↔ PRODUITS : table `commande_produits`
--    (c’est ici que la commande référence les produits)
-- -----------------------------------------------------------------------------

ALTER TABLE `commande_produits` DROP FOREIGN KEY `fk_commande_produits_produit`;

ALTER TABLE `commande_produits`
  MODIFY `produit_id` INT(11) NULL DEFAULT NULL;

ALTER TABLE `commande_produits`
  ADD CONSTRAINT `fk_commande_produits_produit`
  FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- -----------------------------------------------------------------------------
-- 2) Lien PRODUITS ↔ CATEGORIES : colonne `produits`.`categorie_id`
-- -----------------------------------------------------------------------------

ALTER TABLE `produits` DROP FOREIGN KEY `fk_produits_categorie`;

ALTER TABLE `produits`
  MODIFY `categorie_id` INT(11) NULL DEFAULT NULL;

ALTER TABLE `produits`
  ADD CONSTRAINT `fk_produits_categorie`
  FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- =============================================================================
-- Diagnostic : noms réels des contraintes (si besoin)
-- =============================================================================
-- SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
-- FROM information_schema.KEY_COLUMN_USAGE
-- WHERE TABLE_SCHEMA = DATABASE()
--   AND (
--     (TABLE_NAME = 'commande_produits' AND COLUMN_NAME = 'produit_id' AND REFERENCED_TABLE_NAME = 'produits')
--     OR (TABLE_NAME = 'produits' AND COLUMN_NAME = 'categorie_id' AND REFERENCED_TABLE_NAME = 'categories')
--   );
