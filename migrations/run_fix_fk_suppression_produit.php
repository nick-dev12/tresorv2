<?php
/**
 * Met à jour les clés étrangères pour permettre la suppression des produits
 * (commande_produits / devis_produits : produit_id NULL + ON DELETE SET NULL)
 *
 * Exécution : php migrations/run_fix_fk_suppression_produit.php
 * (La même logique est aussi appliquée automatiquement au premier delete_produit.)
 */
require_once __DIR__ . '/../conn/conn.php';
require_once __DIR__ . '/../includes/apply_fk_suppression_produit.php';

if (!$db) {
    fwrite(STDERR, "Connexion BDD impossible.\n");
    exit(1);
}

ensure_fk_suppression_produit($db);
echo "Terminé.\n";
