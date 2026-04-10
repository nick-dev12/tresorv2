<?php
/**
 * Configuration email - PHPMailer / SMTP
 * Copiez ce fichier en config/email.php et modifiez les valeurs
 * NE JAMAIS committer config/email.php (ajoutez-le à .gitignore)
 *
 * SSL/TLS (recommandé) — même serveur pour SMTP :
 *   Host: tresorafricain.com
 *   Port: 465
 *   Authentification: oui
 */

return [
    'method' => 'smtp',

    'smtp' => [
        'host' => 'tresorafricain.com',
        'port' => 465,
        'encryption' => 'ssl',
        'username' => 'service@tresorafricain.com',
        'password' => 'VOTRE_MOT_DE_PASSE_BOITE_MAIL',
        'timeout' => 30,
        'verify_ssl' => false,
    ],

    'from' => [
        'email' => 'service@tresorafricain.com',
        'name' => 'Trésor Africain',
    ],

    'contact_email' => 'service@tresorafricain.com',

    'debug' => false,
];
