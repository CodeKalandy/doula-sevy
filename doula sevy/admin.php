<?php
require_once 'config.php';
session_start();

// Déjà connecté → dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mdp = $_POST['password'] ?? '';
    if (password_verify($mdp, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_time']      = time();
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin – Lumière de Vie</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    :root{--taupe:#a08878;--taupe-dark:#7a6156;--cream:#f5efe8;--white:#fdfaf6;--text:#4a3a32;--red:#c0392b;}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Jost',sans-serif;background:var(--cream);min-height:100vh;display:flex;align-items:center;justify-content:center;}
    .card{background:var(--white);border-radius:4px 60px 4px 4px;padding:3.5rem 3rem;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(120,90,70,.12);}
    .logo{font-family:'Cormorant Garamond',serif;font-size:1.6rem;font-style:italic;color:var(--taupe-dark);text-align:center;margin-bottom:.3rem;}
    .logo span{color:var(--red);}
    .subtitle{text-align:center;font-size:.78rem;letter-spacing:.12em;text-transform:uppercase;color:var(--taupe);margin-bottom:2.5rem;}
    label{display:block;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--taupe);margin-bottom:.5rem;font-weight:500;}
    input[type=password]{width:100%;border:1px solid rgba(160,136,120,.3);border-radius:4px;padding:.9rem 1rem;font-family:'Jost',sans-serif;font-size:.95rem;color:var(--text);background:var(--cream);outline:none;transition:border-color .2s;}
    input[type=password]:focus{border-color:var(--taupe);}
    .form-group{margin-bottom:1.5rem;}
    button{width:100%;background:var(--taupe);color:#fff;border:none;padding:1rem;border-radius:50px;font-family:'Jost',sans-serif;font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;font-weight:500;cursor:pointer;transition:background .2s;margin-top:.5rem;}
    button:hover{background:var(--taupe-dark);}
    .error{background:#fde8e8;border:1px solid #f5c6c6;color:#c0392b;padding:.75rem 1rem;border-radius:4px;font-size:.85rem;margin-bottom:1.5rem;text-align:center;}
    .back{display:block;text-align:center;margin-top:1.5rem;font-size:.78rem;color:var(--taupe);text-decoration:none;}
    .back:hover{color:var(--taupe-dark);}
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">Lumière de Vie <span>♥</span></div>
    <div class="subtitle">Espace Administration</div>
    <?php if($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Mot de passe</label>
        <input type="password" name="password" placeholder="••••••••••" autofocus required/>
      </div>
      <button type="submit">Se connecter</button>
    </form>
    <a href="index.php" class="back">← Retour au site</a>
  </div>
</body>
</html>
