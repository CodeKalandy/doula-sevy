<?php
// ============================================================
//  CONFIGURATION ADMIN — À MODIFIER AVANT MISE EN LIGNE
// ============================================================

// Mot de passe admin : changez "doula2025" par votre mot de passe
define('ADMIN_PASSWORD_HASH', password_hash('doula2025', PASSWORD_DEFAULT));

// Clé secrète de session (changez cette valeur)
define('SESSION_SECRET', 'lumiere_de_vie_secret_2025_changez_moi');

// Durée de session en secondes (3600 = 1 heure)
define('SESSION_DURATION', 3600);

// Chemins absolus — __DIR__ pointe TOUJOURS vers le dossier de config.php (www/)
// Ainsi, peu importe depuis quel script on inclut config.php, les chemins sont corrects.
define('DATA_DIR',     __DIR__ . '/data/');
define('UPLOADS_DIR',  __DIR__ . '/uploads/');
define('CONTENT_FILE', __DIR__ . '/data/content.json');
define('MESSAGES_FILE',__DIR__ . '/data/messages.json');

// Chemin web relatif pour afficher les images dans les balises <img>
define('UPLOADS_WEB', 'uploads/');
