<?php
require_once 'config.php';
session_start();

// Auth check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php'); exit;
}
if (time() - ($_SESSION['admin_time'] ?? 0) > SESSION_DURATION) {
    session_destroy(); header('Location: admin.php?expired=1'); exit;
}
$_SESSION['admin_time'] = time();

// Déconnexion
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

// ─── Helpers ───────────────────────────────────────────────
function loadContent(): array {
    $f = CONTENT_FILE;
    return file_exists($f) ? json_decode(file_get_contents($f), true) : [];
}
function saveContent(array $data): void {
    file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function loadMessages(): array {
    $f = MESSAGES_FILE;
    return file_exists($f) ? json_decode(file_get_contents($f), true) : [];
}
function saveMessages(array $data): void {
    file_put_contents(MESSAGES_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function nextId(array $items): int {
    if (empty($items)) return 1;
    return max(array_column($items, 'id')) + 1;
}
function uploadImage(string $key, string $subdir = ''): string {
    if (empty($_FILES[$key]['name'])) return '';
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    $mime = mime_content_type($_FILES[$key]['tmp_name']);
    if (!in_array($mime, $allowed)) return '';
    $ext  = pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
    $name = uniqid('img_').'.'.$ext;
    $dest = UPLOADS_DIR . ($subdir ? $subdir.'/' : '') . $name;
    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
    move_uploaded_file($_FILES[$key]['tmp_name'], $dest);
    return UPLOADS_WEB . ($subdir ? $subdir.'/' : '') . $name;
}

// ─── Actions POST ──────────────────────────────────────────
$content = loadContent();
$messages = loadMessages();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$section = $_GET['section'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // SITE GÉNÉRAL
    if ($action === 'save_site') {
        foreach (['nom','slogan','description','ville','telephone','email','calendly','instagram','facebook'] as $k)
            $content['site'][$k] = trim($_POST[$k] ?? '');
        $photo = uploadImage('photo_sevy', 'photos');
        if ($photo) $content['site']['photo_sevy'] = $photo;
        saveContent($content);
        header('Location: dashboard.php?section=site&ok=1'); exit;
    }

    // À PROPOS
    if ($action === 'save_apropos') {
        foreach (['titre','texte1','texte2','texte3'] as $k)
            $content['apropos'][$k] = trim($_POST[$k] ?? '');
        $valeurs = array_filter(array_map('trim', explode("\n", $_POST['valeurs'] ?? '')));
        $content['apropos']['valeurs'] = array_values($valeurs);
        saveContent($content);
        header('Location: dashboard.php?section=apropos&ok=1'); exit;
    }

    // SERVICES
    if ($action === 'save_service') {
        $id = (int)($_POST['id'] ?? 0);
        $data = ['icone'=>trim($_POST['icone']),'titre'=>trim($_POST['titre']),'description'=>trim($_POST['description']),'actif'=>isset($_POST['actif'])];
        if ($id === 0) {
            $data['id'] = nextId($content['services']);
            $content['services'][] = $data;
        } else {
            foreach ($content['services'] as &$s) if ($s['id']==$id) { $data['id']=$id; $s=$data; break; }
        }
        saveContent($content);
        header('Location: dashboard.php?section=services&ok=1'); exit;
    }
    if ($action === 'delete_service') {
        $id = (int)($_POST['id'] ?? 0);
        $content['services'] = array_values(array_filter($content['services'], fn($s)=>$s['id']!=$id));
        saveContent($content);
        header('Location: dashboard.php?section=services&ok=1'); exit;
    }

    // TARIFS
    if ($action === 'save_tarif') {
        $id = (int)($_POST['id'] ?? 0);
        $features = array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')));
        $data = ['titre'=>trim($_POST['titre']),'prix'=>trim($_POST['prix']),'note'=>trim($_POST['note']),'featured'=>isset($_POST['featured']),'features'=>array_values($features)];
        if ($id === 0) {
            $data['id'] = nextId($content['tarifs']);
            $content['tarifs'][] = $data;
        } else {
            foreach ($content['tarifs'] as &$t) if ($t['id']==$id) { $data['id']=$id; $t=$data; break; }
        }
        saveContent($content);
        header('Location: dashboard.php?section=tarifs&ok=1'); exit;
    }
    if ($action === 'delete_tarif') {
        $id = (int)($_POST['id'] ?? 0);
        $content['tarifs'] = array_values(array_filter($content['tarifs'], fn($t)=>$t['id']!=$id));
        saveContent($content);
        header('Location: dashboard.php?section=tarifs&ok=1'); exit;
    }

    // TÉMOIGNAGES
    if ($action === 'save_temoignage') {
        $id = (int)($_POST['id'] ?? 0);
        $data = ['texte'=>trim($_POST['texte']),'auteur'=>trim($_POST['auteur']),'detail'=>trim($_POST['detail']),'icone'=>trim($_POST['icone']),'actif'=>isset($_POST['actif'])];
        if ($id === 0) {
            $data['id'] = nextId($content['temoignages']);
            $content['temoignages'][] = $data;
        } else {
            foreach ($content['temoignages'] as &$t) if ($t['id']==$id) { $data['id']=$id; $t=$data; break; }
        }
        saveContent($content);
        header('Location: dashboard.php?section=temoignages&ok=1'); exit;
    }
    if ($action === 'delete_temoignage') {
        $id = (int)($_POST['id'] ?? 0);
        $content['temoignages'] = array_values(array_filter($content['temoignages'], fn($t)=>$t['id']!=$id));
        saveContent($content);
        header('Location: dashboard.php?section=temoignages&ok=1'); exit;
    }

    // BLOG
    if ($action === 'save_article') {
        $id = (int)($_POST['id'] ?? 0);
        $photo = uploadImage('photo_blog', 'blog');
        $data = ['tag'=>trim($_POST['tag']),'titre'=>trim($_POST['titre']),'resume'=>trim($_POST['resume']),'contenu'=>trim($_POST['contenu']),'date'=>trim($_POST['date']),'actif'=>isset($_POST['actif']),'icone'=>trim($_POST['icone'])];
        if ($photo) $data['photo'] = $photo;
        if ($id === 0) {
            $data['id'] = nextId($content['blog']);
            if (!$photo && isset($_POST['photo_existing'])) $data['photo'] = $_POST['photo_existing'];
            $content['blog'][] = $data;
        } else {
            foreach ($content['blog'] as &$b) if ($b['id']==$id) {
                $data['id']=$id;
                if (!$photo) $data['photo'] = $b['photo'] ?? '';
                $b=$data; break;
            }
        }
        saveContent($content);
        header('Location: dashboard.php?section=blog&ok=1'); exit;
    }
    if ($action === 'delete_article') {
        $id = (int)($_POST['id'] ?? 0);
        $content['blog'] = array_values(array_filter($content['blog'], fn($b)=>$b['id']!=$id));
        saveContent($content);
        header('Location: dashboard.php?section=blog&ok=1'); exit;
    }

    // FAQ
    if ($action === 'save_faq') {
        $id = (int)($_POST['id'] ?? 0);
        $data = ['question'=>trim($_POST['question']),'reponse'=>trim($_POST['reponse']),'actif'=>isset($_POST['actif'])];
        if ($id === 0) {
            $data['id'] = nextId($content['faq']);
            $content['faq'][] = $data;
        } else {
            foreach ($content['faq'] as &$f) if ($f['id']==$id) { $data['id']=$id; $f=$data; break; }
        }
        saveContent($content);
        header('Location: dashboard.php?section=faq&ok=1'); exit;
    }
    if ($action === 'delete_faq') {
        $id = (int)($_POST['id'] ?? 0);
        $content['faq'] = array_values(array_filter($content['faq'], fn($f)=>$f['id']!=$id));
        saveContent($content);
        header('Location: dashboard.php?section=faq&ok=1'); exit;
    }

    // MESSAGES — marquer lu / supprimer
    if ($action === 'delete_message') {
        $id = (int)($_POST['id'] ?? 0);
        $messages = array_values(array_filter($messages, fn($m)=>$m['id']!=$id));
        saveMessages($messages);
        header('Location: dashboard.php?section=messages&ok=1'); exit;
    }
    if ($action === 'mark_read') {
        $id = (int)($_POST['id'] ?? 0);
        foreach ($messages as &$m) if ($m['id']==$id) { $m['lu']=true; break; }
        saveMessages($messages);
        header('Location: dashboard.php?section=messages&ok=1'); exit;
    }
}

// ─── Stats rapides ──────────────────────────────────────────
$unread = count(array_filter($messages, fn($m)=>!($m['lu']??false)));
$edit_id = (int)($_GET['edit'] ?? 0);

// ─── Helpers HTML ───────────────────────────────────────────
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES); }
function val(array $data, string $key): string { return h($data[$key] ?? ''); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin – Lumière de Vie</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    :root{--taupe:#a08878;--taupe-light:#c4aa9a;--taupe-dark:#7a6156;--cream:#f5efe8;--cream-dark:#ede3d8;--white:#fdfaf6;--text:#4a3a32;--text-mid:#7a6558;--red:#c0392b;--green:#27ae60;--sidebar:260px;}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Jost',sans-serif;background:var(--cream);color:var(--text);display:flex;min-height:100vh;}

    /* SIDEBAR */
    .sidebar{width:var(--sidebar);background:var(--taupe-dark);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;z-index:100;}
    .sidebar-logo{padding:2rem 1.5rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.1);}
    .sidebar-logo .name{font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-style:italic;color:#fff;}
    .sidebar-logo .role{font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-top:.2rem;}
    nav.sidebar-nav{flex:1;padding:1.5rem 0;}
    .nav-section{font-size:.62rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);padding:.8rem 1.5rem .4rem;}
    .nav-link{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.5rem;color:rgba(255,255,255,.7);text-decoration:none;font-size:.85rem;transition:all .2s;position:relative;}
    .nav-link:hover,.nav-link.active{background:rgba(255,255,255,.1);color:#fff;}
    .nav-link.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--taupe-light);}
    .nav-link .icon{font-size:1rem;width:20px;text-align:center;}
    .badge{background:var(--red);color:#fff;border-radius:50px;font-size:.65rem;padding:.15rem .5rem;margin-left:auto;}
    .sidebar-bottom{padding:1.5rem;border-top:1px solid rgba(255,255,255,.1);}
    .btn-logout{display:block;text-align:center;background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);padding:.6rem;border-radius:50px;text-decoration:none;font-size:.75rem;letter-spacing:.1em;text-transform:uppercase;transition:all .2s;}
    .btn-logout:hover{background:rgba(255,255,255,.2);color:#fff;}

    /* MAIN */
    .main{margin-left:var(--sidebar);flex:1;padding:2.5rem;min-height:100vh;}
    .page-header{margin-bottom:2rem;}
    .page-header h1{font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:300;color:var(--text);}
    .page-header p{color:var(--text-mid);font-size:.88rem;margin-top:.3rem;font-weight:300;}
    .ok-banner{background:#e8f5e9;border:1px solid #c8e6c9;color:#2e7d32;padding:.85rem 1.2rem;border-radius:4px;margin-bottom:1.5rem;font-size:.88rem;}

    /* CARDS */
    .card{background:var(--white);border-radius:8px;padding:2rem;margin-bottom:1.5rem;box-shadow:0 2px 12px rgba(120,90,70,.06);}
    .card-title{font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:400;color:var(--text);margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid var(--cream-dark);}

    /* STATS */
    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;}
    .stat-card{background:var(--white);border-radius:8px;padding:1.5rem;text-align:center;box-shadow:0 2px 12px rgba(120,90,70,.06);}
    .stat-num{font-family:'Cormorant Garamond',serif;font-size:2.5rem;color:var(--taupe-dark);line-height:1;}
    .stat-label{font-size:.75rem;letter-spacing:.1em;text-transform:uppercase;color:var(--text-mid);margin-top:.4rem;}

    /* FORMS */
    .form-group{margin-bottom:1.2rem;}
    label{display:block;font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;color:var(--text-mid);margin-bottom:.4rem;font-weight:500;}
    input[type=text],input[type=email],input[type=tel],input[type=date],input[type=url],textarea,select{width:100%;border:1px solid rgba(160,136,120,.3);border-radius:4px;padding:.75rem 1rem;font-family:'Jost',sans-serif;font-size:.9rem;color:var(--text);background:var(--cream);outline:none;transition:border-color .2s;}
    input:focus,textarea:focus,select:focus{border-color:var(--taupe);}
    textarea{resize:vertical;min-height:100px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    .form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;}
    .checkbox-group{display:flex;align-items:center;gap:.6rem;}
    .checkbox-group input[type=checkbox]{width:auto;}
    .checkbox-group label{margin:0;text-transform:none;font-size:.88rem;letter-spacing:0;}
    .hint{font-size:.75rem;color:var(--text-mid);margin-top:.3rem;font-style:italic;}

    /* BUTTONS */
    .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.3rem;border-radius:50px;font-family:'Jost',sans-serif;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:all .2s;}
    .btn-primary{background:var(--taupe);color:#fff;}
    .btn-primary:hover{background:var(--taupe-dark);}
    .btn-danger{background:#fde8e8;color:var(--red);}
    .btn-danger:hover{background:#f5c6c6;}
    .btn-sm{padding:.4rem .9rem;font-size:.72rem;}
    .btn-outline{background:transparent;border:1px solid var(--taupe-light);color:var(--taupe);}
    .btn-outline:hover{background:var(--cream);}

    /* TABLES / LISTS */
    .items-list{display:flex;flex-direction:column;gap:.8rem;}
    .item-row{background:var(--cream);border-radius:6px;padding:1rem 1.2rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;}
    .item-row.inactive{opacity:.5;}
    .item-main{flex:1;}
    .item-title{font-weight:500;color:var(--text);margin-bottom:.2rem;}
    .item-desc{font-size:.83rem;color:var(--text-mid);font-weight:300;line-height:1.5;}
    .item-actions{display:flex;gap:.5rem;flex-shrink:0;}
    .tag{display:inline-block;background:var(--taupe-light);color:#fff;border-radius:50px;font-size:.65rem;padding:.15rem .6rem;letter-spacing:.08em;text-transform:uppercase;margin-right:.4rem;}
    .tag.green{background:var(--green);}
    .tag.red{background:var(--red);}

    /* MESSAGES */
    .msg-card{background:var(--cream);border-radius:6px;padding:1.2rem 1.5rem;margin-bottom:1rem;border-left:3px solid var(--taupe);}
    .msg-card.unread{border-left-color:var(--red);background:#fff9f9;}
    .msg-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem;gap:1rem;}
    .msg-from{font-weight:500;color:var(--text);}
    .msg-date{font-size:.75rem;color:var(--text-mid);}
    .msg-subject{font-size:.85rem;color:var(--taupe-dark);margin-bottom:.5rem;}
    .msg-body{font-size:.88rem;color:var(--text-mid);font-weight:300;line-height:1.7;margin-bottom:.8rem;}
    .msg-actions{display:flex;gap:.5rem;}

    /* MODAL / INLINE FORM */
    .inline-form{background:var(--cream-dark);border-radius:8px;padding:1.5rem;margin-top:1rem;border:1px solid rgba(160,136,120,.2);}
    .inline-form .card-title{font-size:1.1rem;margin-bottom:1rem;}

    /* RESPONSIVE */
    @media(max-width:900px){
      .sidebar{transform:translateX(-100%);transition:.3s;}
      .sidebar.open{transform:none;}
      .main{margin-left:0;}
      .stats-grid{grid-template-columns:1fr 1fr;}
      .form-row,.form-row-3{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="name">Lumière de Vie ♥</div>
    <div class="role">Administration</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Navigation</div>
    <a href="dashboard.php?section=dashboard" class="nav-link <?= $section==='dashboard'?'active':'' ?>"><span class="icon">🏠</span> Tableau de bord</a>
    <div class="nav-section">Contenu</div>
    <a href="dashboard.php?section=site"        class="nav-link <?= $section==='site'?'active':'' ?>"><span class="icon">⚙️</span> Informations site</a>
    <a href="dashboard.php?section=apropos"     class="nav-link <?= $section==='apropos'?'active':'' ?>"><span class="icon">👩</span> À propos</a>
    <a href="dashboard.php?section=services"    class="nav-link <?= $section==='services'?'active':'' ?>"><span class="icon">🌸</span> Services</a>
    <a href="dashboard.php?section=tarifs"      class="nav-link <?= $section==='tarifs'?'active':'' ?>"><span class="icon">💶</span> Tarifs</a>
    <a href="dashboard.php?section=temoignages" class="nav-link <?= $section==='temoignages'?'active':'' ?>"><span class="icon">💬</span> Témoignages</a>
    <a href="dashboard.php?section=blog"        class="nav-link <?= $section==='blog'?'active':'' ?>"><span class="icon">📝</span> Blog</a>
    <a href="dashboard.php?section=faq"         class="nav-link <?= $section==='faq'?'active':'' ?>"><span class="icon">❓</span> FAQ</a>
    <div class="nav-section">Communications</div>
    <a href="dashboard.php?section=messages" class="nav-link <?= $section==='messages'?'active':'' ?>">
      <span class="icon">✉️</span> Messages
      <?php if($unread>0): ?><span class="badge"><?= $unread ?></span><?php endif; ?>
    </a>
    <div class="nav-section">Site</div>
    <a href="index.php" target="_blank" class="nav-link"><span class="icon">👁️</span> Voir le site</a>
  </nav>
  <div class="sidebar-bottom">
    <a href="dashboard.php?logout=1" class="btn-logout">Se déconnecter</a>
  </div>
</aside>

<!-- MAIN -->
<main class="main">
<?php if(isset($_GET['ok'])): ?>
<div class="ok-banner">✅ Modifications enregistrées avec succès !</div>
<?php endif; ?>

<?php
// ════════════════════════════════════════════════════════════
// DASHBOARD
// ════════════════════════════════════════════════════════════
if ($section === 'dashboard'):
?>
<div class="page-header">
  <h1>Tableau de bord</h1>
  <p>Bienvenue dans votre espace d'administration.</p>
</div>
<div class="stats-grid">
  <div class="stat-card"><div class="stat-num"><?= count($content['services']??[]) ?></div><div class="stat-label">Services</div></div>
  <div class="stat-card"><div class="stat-num"><?= count($content['temoignages']??[]) ?></div><div class="stat-label">Témoignages</div></div>
  <div class="stat-card"><div class="stat-num"><?= count($content['blog']??[]) ?></div><div class="stat-label">Articles</div></div>
  <div class="stat-card"><div class="stat-num" style="color:<?= $unread>0?'var(--red)':'var(--taupe-dark)' ?>"><?= $unread ?></div><div class="stat-label">Messages non lus</div></div>
</div>
<div class="card">
  <div class="card-title">Accès rapides</div>
  <div style="display:flex;gap:.8rem;flex-wrap:wrap;">
    <a href="dashboard.php?section=site" class="btn btn-outline">⚙️ Modifier les infos</a>
    <a href="dashboard.php?section=blog&new=1" class="btn btn-primary">✏️ Nouvel article</a>
    <a href="dashboard.php?section=temoignages&new=1" class="btn btn-primary">💬 Ajouter un témoignage</a>
    <a href="dashboard.php?section=messages" class="btn btn-outline">✉️ Voir les messages</a>
    <a href="index.php" target="_blank" class="btn btn-outline">👁️ Voir le site</a>
  </div>
</div>

<?php
// ════════════════════════════════════════════════════════════
// SITE GENERAL
// ════════════════════════════════════════════════════════════
elseif ($section === 'site'):
  $s = $content['site'] ?? [];
?>
<div class="page-header"><h1>Informations du site</h1><p>Coordonnées, réseaux sociaux et photo de profil.</p></div>
<div class="card">
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_site"/>
    <div class="form-row">
      <div class="form-group"><label>Nom du site</label><input type="text" name="nom" value="<?= val($s,'nom') ?>"/></div>
      <div class="form-group"><label>Slogan</label><input type="text" name="slogan" value="<?= val($s,'slogan') ?>"/></div>
    </div>
    <div class="form-group"><label>Description (hero)</label><textarea name="description"><?= val($s,'description') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Ville / Zone</label><input type="text" name="ville" value="<?= val($s,'ville') ?>"/></div>
      <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" value="<?= val($s,'telephone') ?>"/></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= val($s,'email') ?>"/></div>
      <div class="form-group"><label>Lien Calendly</label><input type="url" name="calendly" value="<?= val($s,'calendly') ?>"/></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Instagram (URL)</label><input type="url" name="instagram" value="<?= val($s,'instagram') ?>"/></div>
      <div class="form-group"><label>Facebook (URL)</label><input type="url" name="facebook" value="<?= val($s,'facebook') ?>"/></div>
    </div>
    <div class="form-group">
      <label>Photo de Sévy</label>
      <?php if(!empty($s['photo_sevy'])): ?>
        <img src="<?= h($s['photo_sevy']) ?>" style="height:80px;border-radius:4px;margin-bottom:.5rem;display:block;"/>
      <?php endif; ?>
      <input type="file" name="photo_sevy" accept="image/*"/>
      <div class="hint">JPG, PNG, WEBP — max 5 Mo</div>
    </div>
    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
  </form>
</div>

<?php
// ════════════════════════════════════════════════════════════
// À PROPOS
// ════════════════════════════════════════════════════════════
elseif ($section === 'apropos'):
  $a = $content['apropos'] ?? [];
?>
<div class="page-header"><h1>À propos</h1><p>Votre présentation et vos valeurs.</p></div>
<div class="card">
  <form method="POST">
    <input type="hidden" name="action" value="save_apropos"/>
    <div class="form-group"><label>Titre de la section</label><input type="text" name="titre" value="<?= val($a,'titre') ?>"/></div>
    <div class="form-group"><label>Paragraphe 1</label><textarea name="texte1"><?= val($a,'texte1') ?></textarea></div>
    <div class="form-group"><label>Paragraphe 2</label><textarea name="texte2"><?= val($a,'texte2') ?></textarea></div>
    <div class="form-group"><label>Paragraphe 3</label><textarea name="texte3"><?= val($a,'texte3') ?></textarea></div>
    <div class="form-group">
      <label>Valeurs (une par ligne)</label>
      <textarea name="valeurs" style="min-height:120px"><?= h(implode("\n", $a['valeurs']??[])) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
  </form>
</div>

<?php
// ════════════════════════════════════════════════════════════
// SERVICES
// ════════════════════════════════════════════════════════════
elseif ($section === 'services'):
  $show_new = isset($_GET['new']) || $edit_id > 0;
  $edit_item = $edit_id > 0 ? current(array_filter($content['services']??[], fn($s)=>$s['id']==$edit_id)) : null;
?>
<div class="page-header"><h1>Services</h1><p>Gérez les prestations proposées.</p></div>
<div style="margin-bottom:1rem;"><a href="dashboard.php?section=services&new=1" class="btn btn-primary">+ Ajouter un service</a></div>

<?php if($show_new): ?>
<div class="card">
  <div class="card-title"><?= $edit_item ? 'Modifier le service' : 'Nouveau service' ?></div>
  <form method="POST">
    <input type="hidden" name="action" value="save_service"/>
    <input type="hidden" name="id" value="<?= $edit_item['id']??0 ?>"/>
    <div class="form-row">
      <div class="form-group"><label>Icône (emoji)</label><input type="text" name="icone" value="<?= val($edit_item??[],'icone') ?>"/></div>
      <div class="form-group"><label>Titre</label><input type="text" name="titre" value="<?= val($edit_item??[],'titre') ?>"/></div>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description"><?= val($edit_item??[],'description') ?></textarea></div>
    <div class="checkbox-group form-group"><input type="checkbox" name="actif" id="actif_s" <?= ($edit_item['actif']??true)?'checked':'' ?>/><label for="actif_s">Visible sur le site</label></div>
    <div style="display:flex;gap:.8rem;">
      <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      <a href="dashboard.php?section=services" class="btn btn-outline">Annuler</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Services (<?= count($content['services']??[]) ?>)</div>
  <div class="items-list">
    <?php foreach($content['services']??[] as $s): ?>
    <div class="item-row <?= !($s['actif']??true)?'inactive':'' ?>">
      <div class="item-main">
        <div class="item-title"><?= h($s['icone']??'') ?> <?= h($s['titre']??'') ?> <?= !($s['actif']??true)?'<span class="tag red">Masqué</span>':'' ?></div>
        <div class="item-desc"><?= h(mb_substr($s['description']??'',0,100)) ?>...</div>
      </div>
      <div class="item-actions">
        <a href="dashboard.php?section=services&edit=<?= $s['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
        <form method="POST" onsubmit="return confirm('Supprimer ce service ?')">
          <input type="hidden" name="action" value="delete_service"/>
          <input type="hidden" name="id" value="<?= $s['id'] ?>"/>
          <button class="btn btn-sm btn-danger">🗑️</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
// ════════════════════════════════════════════════════════════
// TARIFS
// ════════════════════════════════════════════════════════════
elseif ($section === 'tarifs'):
  $show_new = isset($_GET['new']) || $edit_id > 0;
  $edit_item = $edit_id > 0 ? current(array_filter($content['tarifs']??[], fn($t)=>$t['id']==$edit_id)) : null;
?>
<div class="page-header"><h1>Tarifs</h1><p>Gérez vos offres et prix.</p></div>
<div style="margin-bottom:1rem;"><a href="dashboard.php?section=tarifs&new=1" class="btn btn-primary">+ Ajouter un tarif</a></div>

<?php if($show_new): ?>
<div class="card">
  <div class="card-title"><?= $edit_item ? 'Modifier le tarif' : 'Nouveau tarif' ?></div>
  <form method="POST">
    <input type="hidden" name="action" value="save_tarif"/>
    <input type="hidden" name="id" value="<?= $edit_item['id']??0 ?>"/>
    <div class="form-row">
      <div class="form-group"><label>Titre</label><input type="text" name="titre" value="<?= val($edit_item??[],'titre') ?>"/></div>
      <div class="form-group"><label>Prix (ex: 250€ ou Sur devis)</label><input type="text" name="prix" value="<?= val($edit_item??[],'prix') ?>"/></div>
    </div>
    <div class="form-group"><label>Note sous le prix</label><input type="text" name="note" value="<?= val($edit_item??[],'note') ?>"/></div>
    <div class="form-group">
      <label>Inclusions (une par ligne)</label>
      <textarea name="features" style="min-height:120px"><?= h(implode("\n", $edit_item['features']??[])) ?></textarea>
    </div>
    <div class="checkbox-group form-group"><input type="checkbox" name="featured" id="feat" <?= ($edit_item['featured']??false)?'checked':'' ?>/><label for="feat">Mettre en avant (colonne centrale)</label></div>
    <div style="display:flex;gap:.8rem;">
      <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      <a href="dashboard.php?section=tarifs" class="btn btn-outline">Annuler</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Tarifs (<?= count($content['tarifs']??[]) ?>)</div>
  <div class="items-list">
    <?php foreach($content['tarifs']??[] as $t): ?>
    <div class="item-row">
      <div class="item-main">
        <div class="item-title"><?= h($t['titre']??'') ?> — <strong><?= h($t['prix']??'') ?></strong> <?= ($t['featured']??false)?'<span class="tag green">Mis en avant</span>':'' ?></div>
        <div class="item-desc"><?= h($t['note']??'') ?></div>
      </div>
      <div class="item-actions">
        <a href="dashboard.php?section=tarifs&edit=<?= $t['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
        <form method="POST" onsubmit="return confirm('Supprimer ?')">
          <input type="hidden" name="action" value="delete_tarif"/>
          <input type="hidden" name="id" value="<?= $t['id'] ?>"/>
          <button class="btn btn-sm btn-danger">🗑️</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
// ════════════════════════════════════════════════════════════
// TÉMOIGNAGES
// ════════════════════════════════════════════════════════════
elseif ($section === 'temoignages'):
  $show_new = isset($_GET['new']) || $edit_id > 0;
  $edit_item = $edit_id > 0 ? current(array_filter($content['temoignages']??[], fn($t)=>$t['id']==$edit_id)) : null;
?>
<div class="page-header"><h1>Témoignages</h1><p>Gérez les avis de vos clientes.</p></div>
<div style="margin-bottom:1rem;"><a href="dashboard.php?section=temoignages&new=1" class="btn btn-primary">+ Ajouter un témoignage</a></div>

<?php if($show_new): ?>
<div class="card">
  <div class="card-title"><?= $edit_item ? 'Modifier' : 'Nouveau témoignage' ?></div>
  <form method="POST">
    <input type="hidden" name="action" value="save_temoignage"/>
    <input type="hidden" name="id" value="<?= $edit_item['id']??0 ?>"/>
    <div class="form-row">
      <div class="form-group"><label>Prénom / Nom</label><input type="text" name="auteur" value="<?= val($edit_item??[],'auteur') ?>"/></div>
      <div class="form-group"><label>Détail (ex: maman de 2 enfants)</label><input type="text" name="detail" value="<?= val($edit_item??[],'detail') ?>"/></div>
    </div>
    <div class="form-group"><label>Emoji avatar</label><input type="text" name="icone" value="<?= val($edit_item??[],'icone') ?: '🌸' ?>"/></div>
    <div class="form-group"><label>Témoignage</label><textarea name="texte" style="min-height:120px"><?= val($edit_item??[],'texte') ?></textarea></div>
    <div class="checkbox-group form-group"><input type="checkbox" name="actif" id="actif_t" <?= ($edit_item['actif']??true)?'checked':'' ?>/><label for="actif_t">Visible sur le site</label></div>
    <div style="display:flex;gap:.8rem;">
      <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      <a href="dashboard.php?section=temoignages" class="btn btn-outline">Annuler</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Témoignages (<?= count($content['temoignages']??[]) ?>)</div>
  <div class="items-list">
    <?php foreach($content['temoignages']??[] as $t): ?>
    <div class="item-row <?= !($t['actif']??true)?'inactive':'' ?>">
      <div class="item-main">
        <div class="item-title"><?= h($t['icone']??'') ?> <?= h($t['auteur']??'') ?> <?= !($t['actif']??true)?'<span class="tag red">Masqué</span>':'' ?></div>
        <div class="item-desc">"<?= h(mb_substr($t['texte']??'',0,100)) ?>..."</div>
      </div>
      <div class="item-actions">
        <a href="dashboard.php?section=temoignages&edit=<?= $t['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
        <form method="POST" onsubmit="return confirm('Supprimer ?')">
          <input type="hidden" name="action" value="delete_temoignage"/>
          <input type="hidden" name="id" value="<?= $t['id'] ?>"/>
          <button class="btn btn-sm btn-danger">🗑️</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
// ════════════════════════════════════════════════════════════
// BLOG
// ════════════════════════════════════════════════════════════
elseif ($section === 'blog'):
  $show_new = isset($_GET['new']) || $edit_id > 0;
  $edit_item = $edit_id > 0 ? current(array_filter($content['blog']??[], fn($b)=>$b['id']==$edit_id)) : null;
?>
<div class="page-header"><h1>Blog</h1><p>Rédigez et gérez vos articles.</p></div>
<div style="margin-bottom:1rem;"><a href="dashboard.php?section=blog&new=1" class="btn btn-primary">+ Nouvel article</a></div>

<?php if($show_new): ?>
<div class="card">
  <div class="card-title"><?= $edit_item ? 'Modifier l\'article' : 'Nouvel article' ?></div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_article"/>
    <input type="hidden" name="id" value="<?= $edit_item['id']??0 ?>"/>
    <input type="hidden" name="photo_existing" value="<?= val($edit_item??[],'photo') ?>"/>
    <div class="form-row">
      <div class="form-group"><label>Titre</label><input type="text" name="titre" value="<?= val($edit_item??[],'titre') ?>"/></div>
      <div class="form-group"><label>Catégorie / Tag</label><input type="text" name="tag" value="<?= val($edit_item??[],'tag') ?>" placeholder="Ex: Grossesse, Naissance..."/></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Emoji (si pas de photo)</label><input type="text" name="icone" value="<?= val($edit_item??[],'icone') ?: '🌸' ?>"/></div>
      <div class="form-group"><label>Date de publication</label><input type="date" name="date" value="<?= val($edit_item??[],'date') ?>"/></div>
    </div>
    <div class="form-group"><label>Résumé (affiché sur la liste)</label><textarea name="resume"><?= val($edit_item??[],'resume') ?></textarea></div>
    <div class="form-group"><label>Contenu complet</label><textarea name="contenu" style="min-height:200px"><?= val($edit_item??[],'contenu') ?></textarea></div>
    <div class="form-group">
      <label>Photo de l'article</label>
      <?php if(!empty($edit_item['photo'])): ?>
        <img src="<?= h($edit_item['photo']) ?>" style="height:80px;border-radius:4px;margin-bottom:.5rem;display:block;"/>
      <?php endif; ?>
      <input type="file" name="photo_blog" accept="image/*"/>
    </div>
    <div class="checkbox-group form-group"><input type="checkbox" name="actif" id="actif_b" <?= ($edit_item['actif']??true)?'checked':'' ?>/><label for="actif_b">Publié</label></div>
    <div style="display:flex;gap:.8rem;">
      <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      <a href="dashboard.php?section=blog" class="btn btn-outline">Annuler</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Articles (<?= count($content['blog']??[]) ?>)</div>
  <div class="items-list">
    <?php foreach($content['blog']??[] as $b): ?>
    <div class="item-row <?= !($b['actif']??true)?'inactive':'' ?>">
      <div class="item-main">
        <div class="item-title"><?= h($b['icone']??'') ?> <?= h($b['titre']??'') ?> <span class="tag"><?= h($b['tag']??'') ?></span> <?= !($b['actif']??true)?'<span class="tag red">Brouillon</span>':'' ?></div>
        <div class="item-desc"><?= h(mb_substr($b['resume']??'',0,100)) ?>...</div>
      </div>
      <div class="item-actions">
        <a href="dashboard.php?section=blog&edit=<?= $b['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
        <form method="POST" onsubmit="return confirm('Supprimer cet article ?')">
          <input type="hidden" name="action" value="delete_article"/>
          <input type="hidden" name="id" value="<?= $b['id'] ?>"/>
          <button class="btn btn-sm btn-danger">🗑️</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
// ════════════════════════════════════════════════════════════
// FAQ
// ════════════════════════════════════════════════════════════
elseif ($section === 'faq'):
  $show_new = isset($_GET['new']) || $edit_id > 0;
  $edit_item = $edit_id > 0 ? current(array_filter($content['faq']??[], fn($f)=>$f['id']==$edit_id)) : null;
?>
<div class="page-header"><h1>FAQ</h1><p>Questions et réponses fréquentes.</p></div>
<div style="margin-bottom:1rem;"><a href="dashboard.php?section=faq&new=1" class="btn btn-primary">+ Ajouter une question</a></div>

<?php if($show_new): ?>
<div class="card">
  <div class="card-title"><?= $edit_item ? 'Modifier' : 'Nouvelle question' ?></div>
  <form method="POST">
    <input type="hidden" name="action" value="save_faq"/>
    <input type="hidden" name="id" value="<?= $edit_item['id']??0 ?>"/>
    <div class="form-group"><label>Question</label><input type="text" name="question" value="<?= val($edit_item??[],'question') ?>"/></div>
    <div class="form-group"><label>Réponse</label><textarea name="reponse" style="min-height:120px"><?= val($edit_item??[],'reponse') ?></textarea></div>
    <div class="checkbox-group form-group"><input type="checkbox" name="actif" id="actif_f" <?= ($edit_item['actif']??true)?'checked':'' ?>/><label for="actif_f">Visible sur le site</label></div>
    <div style="display:flex;gap:.8rem;">
      <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      <a href="dashboard.php?section=faq" class="btn btn-outline">Annuler</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">FAQ (<?= count($content['faq']??[]) ?> questions)</div>
  <div class="items-list">
    <?php foreach($content['faq']??[] as $f): ?>
    <div class="item-row <?= !($f['actif']??true)?'inactive':'' ?>">
      <div class="item-main">
        <div class="item-title"><?= h($f['question']??'') ?></div>
        <div class="item-desc"><?= h(mb_substr($f['reponse']??'',0,100)) ?>...</div>
      </div>
      <div class="item-actions">
        <a href="dashboard.php?section=faq&edit=<?= $f['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
        <form method="POST" onsubmit="return confirm('Supprimer ?')">
          <input type="hidden" name="action" value="delete_faq"/>
          <input type="hidden" name="id" value="<?= $f['id'] ?>"/>
          <button class="btn btn-sm btn-danger">🗑️</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
// ════════════════════════════════════════════════════════════
// MESSAGES
// ════════════════════════════════════════════════════════════
elseif ($section === 'messages'):
?>
<div class="page-header"><h1>Messages reçus</h1><p><?= $unread ?> message<?= $unread>1?'s':'' ?> non lu<?= $unread>1?'s':'' ?>.</p></div>
<?php if(empty($messages)): ?>
  <div class="card"><p style="color:var(--text-mid);text-align:center;padding:2rem;">Aucun message pour le moment.</p></div>
<?php else: ?>
  <?php foreach(array_reverse($messages) as $m): ?>
  <div class="msg-card <?= !($m['lu']??false)?'unread':'' ?>">
    <div class="msg-header">
      <div>
        <div class="msg-from"><?= h($m['prenom']??'') ?> <?= h($m['nom']??'') ?> <?= !($m['lu']??false)?'<span class="tag red">Nouveau</span>':'' ?></div>
        <div class="msg-date"><?= h($m['date']??'') ?> — <?= h($m['email']??'') ?> <?= !empty($m['telephone'])?'· '.h($m['telephone']):'' ?></div>
      </div>
    </div>
    <?php if(!empty($m['service'])): ?><div class="msg-subject">Service : <?= h($m['service']) ?><?= !empty($m['terme'])?' · Terme : '.h($m['terme']):'' ?></div><?php endif; ?>
    <div class="msg-body"><?= nl2br(h($m['message']??'')) ?></div>
    <div class="msg-actions">
      <?php if(!($m['lu']??false)): ?>
      <form method="POST"><input type="hidden" name="action" value="mark_read"/><input type="hidden" name="id" value="<?= $m['id'] ?>"/><button class="btn btn-sm btn-outline">✅ Marquer lu</button></form>
      <?php endif; ?>
      <a href="mailto:<?= h($m['email']??'') ?>" class="btn btn-sm btn-primary">✉️ Répondre</a>
      <form method="POST" onsubmit="return confirm('Supprimer ce message ?')"><input type="hidden" name="action" value="delete_message"/><input type="hidden" name="id" value="<?= $m['id'] ?>"/><button class="btn btn-sm btn-danger">🗑️</button></form>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>
</main>
</body>
</html>
