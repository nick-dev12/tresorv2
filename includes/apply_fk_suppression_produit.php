<?php
/**
 * Tables métier concernées : commandes, categories, produits.
 *
 * En base, le lien commandes ↔ produits passe par `commande_produits` (pas de produit_id sur `commandes`).
 *
 * 1) commande_produits.produit_id → produits : ON DELETE SET NULL (suppression produit, historique lignes)
 * 2) devis_produits.produit_id → produits : idem si la table existe
 * 3) produits.categorie_id → categories : ON DELETE SET NULL (suppression catégorie)
 *
 * Idempotent : ne refait les ALTER que si nécessaire.
 *
 * @param PDO $db
 * @return bool True si la configuration est OK en fin d'appel
 */
function ensure_fk_suppression_produit(PDO $db)
{
    $targetsProduitId = [
        ['table' => 'commande_produits', 'constraint' => 'fk_commande_produits_produit'],
        ['table' => 'devis_produits', 'constraint' => 'fk_devis_produits_produit'],
    ];

    foreach ($targetsProduitId as $t) {
        $table = $t['table'];
        $defaultName = $t['constraint'];

        $chk = $db->query('SHOW TABLES LIKE ' . $db->quote($table));
        if (!$chk || !$chk->fetch()) {
            continue;
        }

        $stmt = $db->prepare('
            SELECT IS_NULLABLE FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = \'produit_id\'
        ');
        $stmt->execute([$table]);
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($col && strtoupper($col['IS_NULLABLE'] ?? '') === 'YES') {
            $stmt2 = $db->prepare('
                SELECT rc.DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS rc
                INNER JOIN information_schema.KEY_COLUMN_USAGE kcu
                  ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
                WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
                  AND kcu.TABLE_NAME = ?
                  AND kcu.COLUMN_NAME = \'produit_id\'
                  AND kcu.REFERENCED_TABLE_NAME = \'produits\'
                LIMIT 1
            ');
            $stmt2->execute([$table]);
            $rule = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($rule && strtoupper($rule['DELETE_RULE'] ?? '') === 'SET NULL') {
                continue;
            }
        }

        $stmt = $db->prepare('
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = \'produit_id\'
              AND REFERENCED_TABLE_NAME = \'produits\'
            LIMIT 1
        ');
        $stmt->execute([$table]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['CONSTRAINT_NAME'])) {
            $name = $row['CONSTRAINT_NAME'];
            $db->exec('ALTER TABLE `' . str_replace('`', '``', $table) . '` DROP FOREIGN KEY `' . str_replace('`', '``', $name) . '`');
        }

        $db->exec('ALTER TABLE `' . str_replace('`', '``', $table) . '` MODIFY `produit_id` INT(11) NULL DEFAULT NULL');

        $cname = $defaultName;
        $db->exec('
            ALTER TABLE `' . str_replace('`', '``', $table) . '`
            ADD CONSTRAINT `' . str_replace('`', '``', $cname) . '`
            FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`)
            ON DELETE SET NULL ON UPDATE CASCADE
        ');
    }

    // --- produits.categorie_id → categories ---
    $chkP = $db->query("SHOW TABLES LIKE 'produits'");
    if (!$chkP || !$chkP->fetch()) {
        return true;
    }
    $chkC = $db->query("SHOW TABLES LIKE 'categories'");
    if (!$chkC || !$chkC->fetch()) {
        return true;
    }

    $stmt = $db->prepare('
        SELECT IS_NULLABLE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = \'produits\' AND COLUMN_NAME = \'categorie_id\'
    ');
    $stmt->execute();
    $colCat = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($colCat && strtoupper($colCat['IS_NULLABLE'] ?? '') === 'YES') {
        $stmt2 = $db->prepare('
            SELECT rc.DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS rc
            INNER JOIN information_schema.KEY_COLUMN_USAGE kcu
              ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
            WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
              AND kcu.TABLE_NAME = \'produits\'
              AND kcu.COLUMN_NAME = \'categorie_id\'
              AND kcu.REFERENCED_TABLE_NAME = \'categories\'
            LIMIT 1
        ');
        $stmt2->execute();
        $ruleCat = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($ruleCat && strtoupper($ruleCat['DELETE_RULE'] ?? '') === 'SET NULL') {
            return true;
        }
    }

    $stmt = $db->prepare('
        SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = \'produits\'
          AND COLUMN_NAME = \'categorie_id\'
          AND REFERENCED_TABLE_NAME = \'categories\'
        LIMIT 1
    ');
    $stmt->execute();
    $rowCat = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rowCat && !empty($rowCat['CONSTRAINT_NAME'])) {
        $n = $rowCat['CONSTRAINT_NAME'];
        $db->exec('ALTER TABLE `produits` DROP FOREIGN KEY `' . str_replace('`', '``', $n) . '`');
    }

    $db->exec('ALTER TABLE `produits` MODIFY `categorie_id` INT(11) NULL DEFAULT NULL');

    $db->exec('
        ALTER TABLE `produits`
        ADD CONSTRAINT `fk_produits_categorie`
        FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
    ');

    return true;
}
