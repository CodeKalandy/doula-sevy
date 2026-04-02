<?php
require_once 'config.php';

// Charger le contenu
$content = file_exists(CONTENT_FILE) ? json_decode(file_get_contents(CONTENT_FILE), true) : [];
$s   = $content['site']       ?? [];
$a   = $content['apropos']    ?? [];
$srv = array_filter($content['Accompagnements']    ?? [], fn($x)=>$x['actif']??true);
$tar = $content['tarifs']     ?? [];
$tem = array_filter($content['temoignages'] ?? [], fn($x)=>$x['actif']??true);
$blg = array_filter($content['blog']        ?? [], fn($x)=>$x['actif']??true);
$faq = array_filter($content['faq']         ?? [], fn($x)=>$x['actif']??true);

function h(string $str): string { return htmlspecialchars($str, ENT_QUOTES); }

// Traitement formulaire de contact
$msg_ok = false; $msg_err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $messages = file_exists(MESSAGES_FILE) ? json_decode(file_get_contents(MESSAGES_FILE), true) : [];
    $new = [
        'id'        => empty($messages) ? 1 : max(array_column($messages,'id'))+1,
        'prenom'    => trim(strip_tags($_POST['prenom']??'')),
        'nom'       => trim(strip_tags($_POST['nom']??'')),
        'email'     => trim(strip_tags($_POST['email']??'')),
        'telephone' => trim(strip_tags($_POST['telephone']??'')),
        'terme'     => trim(strip_tags($_POST['terme']??'')),
        'service'   => trim(strip_tags($_POST['service']??'')),
        'message'   => trim(strip_tags($_POST['message']??'')),
        'date'      => date('d/m/Y H:i'),
        'lu'        => false,
    ];
    if (empty($new['prenom']) || empty($new['email']) || empty($new['message'])) {
        $msg_err = 'Veuillez remplir au minimum votre prénom, email et message.';
    } else {
        $messages[] = $new;
        file_put_contents(MESSAGES_FILE, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        // Optionnel : envoyer un email de notification
        if (!empty($s['email'])) {
            $subject = "Nouveau message de {$new['prenom']} {$new['nom']}";
            $body    = "Prénom : {$new['prenom']} {$new['nom']}\nEmail : {$new['email']}\nTél : {$new['telephone']}\nTerme : {$new['terme']}\nService : {$new['service']}\n\nMessage :\n{$new['message']}";
            @mail($s['email'], $subject, $body, "From: noreply@lumiere-de-vie-doula.fr");
        }
        $msg_ok = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($s['nom'] ?? 'Lumière de Vie – Doula Sévy') ?></title>
  <meta name="description" content="<?= h($s['description'] ?? '') ?>"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
  <style>
    :root{--taupe:#a08878;--taupe-light:#c4aa9a;--taupe-dark:#7a6156;--cream:#f5efe8;--cream-dark:#ede3d8;--red:#c0392b;--white:#fdfaf6;--text:#4a3a32;--text-mid:#7a6558;--gold:#c9a96e;--sidebar:260px;}
    *{box-sizing:border-box;margin:0;padding:0;}
    html{scroll-behavior:smooth;}
    body{font-family:'Jost',sans-serif;background:var(--white);color:var(--text);overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");pointer-events:none;z-index:9999;opacity:.4;}
    nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:1.2rem 4rem;background:rgba(253,250,246,.88);backdrop-filter:blur(12px);border-bottom:1px solid rgba(160,136,120,.15);transition:box-shadow .3s;}
    nav.scrolled{box-shadow:0 4px 30px rgba(120,90,70,.08);}
    .nav-logo{font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:400;font-style:italic;color:var(--taupe-dark);letter-spacing:.04em;text-decoration:none;}
    .nav-logo span{color:var(--red);}
    .nav-links{display:flex;gap:2.4rem;list-style:none;}
    .nav-links a{font-size:.78rem;font-weight:400;letter-spacing:.12em;text-transform:uppercase;color:var(--text-mid);text-decoration:none;position:relative;transition:color .2s;}
    .nav-links a::after{content:'';position:absolute;bottom:-3px;left:0;width:0;height:1px;background:var(--taupe);transition:width .3s;}
    .nav-links a:hover{color:var(--taupe-dark);}
    .nav-links a:hover::after{width:100%;}
    .nav-cta{background:var(--taupe);color:var(--white)!important;padding:.55rem 1.4rem;border-radius:50px;transition:background .2s!important;}
    .nav-cta:hover{background:var(--taupe-dark)!important;}
    .nav-cta::after{display:none!important;}
    .burger{display:none;flex-direction:column;gap:5px;cursor:pointer;}
    .burger span{display:block;width:24px;height:1.5px;background:var(--taupe-dark);transition:.3s;}
    section{padding:7rem 4rem;}
    .section-inner{max-width:1100px;margin:0 auto;}
    .section-tag{display:inline-block;font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;color:var(--taupe);margin-bottom:.8rem;font-weight:500;}
    .section-title{font-family:'Cormorant Garamond',serif;font-size:clamp(2rem,3.5vw,3rem);font-weight:300;line-height:1.2;color:var(--text);margin-bottom:1.5rem;}
    .section-title em{font-style:italic;color:var(--taupe);}
    .divider{width:60px;height:1px;background:var(--taupe-light);margin:1.5rem 0;}
    .divider.center{margin:1.5rem auto;}
    /* HERO */
    #accueil{min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:8rem 4rem 6rem;}
    .hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse 80% 70% at 65% 50%,#d4bfb0 0%,#c4a898 30%,#a08878 70%,#7a6156 100%);}
    .hero-bg::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 30% 50%,rgba(253,250,246,.25) 0%,transparent 70%);}
    .floral-tl,.floral-br{position:absolute;opacity:.25;pointer-events:none;}
    .floral-tl{top:60px;left:60px;width:220px;transform:rotate(-10deg);}
    .floral-br{bottom:60px;right:60px;width:200px;transform:rotate(15deg) scaleX(-1);}
    .hero-content{position:relative;z-index:2;display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:center;max-width:1100px;margin:0 auto;}
    .hero-text h1{font-family:'Cormorant Garamond',serif;font-size:clamp(2.8rem,5vw,4.5rem);font-weight:300;line-height:1.1;color:var(--white);margin-bottom:1rem;animation:fadeUp .9s ease both;}
    .hero-text h1 em{font-style:italic;color:rgba(255,255,255,.75);font-size:.75em;display:block;letter-spacing:.06em;}
    .hero-text p{color:rgba(253,250,246,.82);font-size:1.05rem;font-weight:300;line-height:1.75;max-width:440px;margin-bottom:2rem;animation:fadeUp .9s .15s ease both;}
    .hero-btns{display:flex;gap:1rem;flex-wrap:wrap;animation:fadeUp .9s .3s ease both;}
    .btn-primary{background:var(--white);color:var(--taupe-dark);padding:.85rem 2rem;border-radius:50px;font-size:.82rem;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;font-weight:500;transition:all .25s;box-shadow:0 4px 20px rgba(0,0,0,.12);}
    .btn-primary:hover{background:var(--cream);transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.16);}
    .btn-outline{border:1px solid rgba(255,255,255,.6);color:var(--white);padding:.85rem 2rem;border-radius:50px;font-size:.82rem;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;font-weight:400;transition:all .25s;}
    .btn-outline:hover{background:rgba(255,255,255,.15);}
    .hero-logo-wrap{display:flex;justify-content:center;align-items:center;animation:fadeIn 1.1s .2s ease both;}
    .hero-logo-circle{width:min(380px,44vw);aspect-ratio:1;border-radius:50%;background:radial-gradient(ellipse at 40% 35%,#c9b09e 0%,#a08878 50%,#7a6156 100%);box-shadow:0 20px 60px rgba(80,50,35,.35);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
    .hero-logo-circle img{width:92%;height:92%;object-fit:cover;border-radius:50%;}
    /* APROPOS */
    #apropos{background:var(--cream);}
    .apropos-grid{display:grid;grid-template-columns:1fr 1.4fr;gap:5rem;align-items:center;}
    .apropos-img-bg{width:100%;aspect-ratio:3/4;background:linear-gradient(135deg,var(--taupe-light) 0%,var(--taupe) 100%);border-radius:2px 80px 2px 80px;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .apropos-img-bg img{width:100%;height:100%;object-fit:cover;}
    .apropos-img-placeholder{font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-style:italic;color:rgba(255,255,255,.7);text-align:center;padding:2rem;}
    .apropos-img-wrap{position:relative;}
    .apropos-badge{position:absolute;bottom:-20px;right:-20px;background:var(--white);border-radius:50%;width:110px;height:110px;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:0 8px 30px rgba(0,0,0,.1);font-family:'Cormorant Garamond',serif;}
    .apropos-badge strong{font-size:2rem;color:var(--taupe-dark);line-height:1;}
    .apropos-badge span{font-size:.65rem;letter-spacing:.08em;text-transform:uppercase;color:var(--text-mid);}
    .apropos-text p{font-size:1rem;line-height:1.9;color:var(--text-mid);margin-bottom:1.2rem;font-weight:300;}
    .apropos-text p strong{color:var(--text);font-weight:500;}
    .apropos-values{display:flex;gap:1rem;margin-top:2rem;flex-wrap:wrap;}
    .value-pill{display:flex;align-items:center;gap:.5rem;background:var(--white);border:1px solid var(--taupe-light);border-radius:50px;padding:.5rem 1.2rem;font-size:.8rem;color:var(--taupe-dark);}
    .value-pill::before{content:'✦';color:var(--taupe-light);font-size:.6rem;}
    /* Accompagnements */
    #Accompagnements{background:var(--white);}
    .Accompagnements-header{text-align:center;margin-bottom:4rem;}
    .Accompagnements-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;}
    .service-card{background:var(--cream);border-radius:4px 40px 4px 40px;padding:2.5rem 2rem;position:relative;overflow:hidden;transition:transform .3s,box-shadow .3s;}
    .service-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(120,90,70,.1);}
    .service-card::before{content:'';position:absolute;top:0;left:0;width:3px;height:0;background:var(--taupe);transition:height .4s;}
    .service-card:hover::before{height:100%;}
    .service-icon{font-size:2rem;margin-bottom:1rem;display:block;}
    .service-card h3{font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:400;color:var(--text);margin-bottom:.75rem;}
    .service-card p{font-size:.88rem;line-height:1.75;color:var(--text-mid);font-weight:300;}
    /* TARIFS */
    #tarifs{background:var(--cream-dark);}
    .tarifs-header{text-align:center;margin-bottom:3.5rem;}
    .tarifs-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;align-items:start;}
    .tarif-card{background:var(--white);border-radius:2px 50px 2px 2px;padding:2.5rem 2rem;text-align:center;transition:transform .3s,box-shadow .3s;}
    .tarif-card.featured{background:var(--taupe);transform:scale(1.04);}
    .tarif-card.featured h3,.tarif-card.featured .price,.tarif-card.featured p,.tarif-card.featured li{color:var(--white)!important;}
    .tarif-card.featured .price-note{color:rgba(255,255,255,.7)!important;}
    .tarif-card:hover{box-shadow:0 16px 40px rgba(120,90,70,.12);}
    .tarif-card h3{font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:400;color:var(--text);margin-bottom:1rem;}
    .price{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:300;color:var(--taupe-dark);line-height:1;}
    .price-note{font-size:.75rem;color:var(--text-mid);display:block;margin-bottom:1.5rem;font-style:italic;}
    .tarif-features{list-style:none;margin-bottom:2rem;}
    .tarif-features li{font-size:.85rem;color:var(--text-mid);padding:.45rem 0;border-bottom:1px solid rgba(160,136,120,.15);font-weight:300;}
    .tarif-features li:last-child{border-bottom:none;}
    .tarif-features li::before{content:'✦ ';color:var(--taupe-light);font-size:.6rem;}
    .btn-tarif{display:inline-block;width:100%;background:var(--taupe);color:var(--white);padding:.8rem;border-radius:50px;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;transition:background .2s;font-weight:500;}
    .btn-tarif:hover{background:var(--taupe-dark);}
    .tarif-card.featured .btn-tarif{background:var(--white);color:var(--taupe-dark);}
    .tarif-card.featured .btn-tarif:hover{background:var(--cream);}
    .tarif-note{text-align:center;margin-top:2rem;font-size:.83rem;color:var(--text-mid);font-style:italic;}
    /* TÉMOIGNAGES */
    #temoignages{background:var(--white);}
    .temoignages-header{text-align:center;margin-bottom:3.5rem;}
    .temoignages-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;}
    .temoignage-card{background:var(--cream);border-radius:40px 4px 40px 4px;padding:2.5rem 2rem;position:relative;}
    .quote-mark{font-family:'Cormorant Garamond',serif;font-size:5rem;line-height:.8;color:var(--taupe-light);opacity:.4;position:absolute;top:1rem;left:1.5rem;}
    .temoignage-card p{font-size:.93rem;line-height:1.8;color:var(--text-mid);font-weight:300;margin-top:1rem;font-style:italic;}
    .temoignage-author{display:flex;align-items:center;gap:.8rem;margin-top:1.5rem;}
    .author-avatar{width:38px;height:38px;border-radius:50%;background:var(--taupe-light);display:flex;align-items:center;justify-content:center;font-size:1rem;}
    .author-info strong{display:block;font-size:.85rem;color:var(--text);}
    .author-info span{font-size:.75rem;color:var(--taupe);}
    .stars{color:var(--gold);font-size:.85rem;margin-bottom:.5rem;}
    /* BLOG */
    #blog{background:var(--cream);}
    .blog-header{margin-bottom:3rem;display:flex;align-items:flex-end;justify-content:space-between;}
    .blog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;}
    .blog-card{background:var(--white);border-radius:2px;overflow:hidden;transition:transform .3s,box-shadow .3s;}
    .blog-card:hover{transform:translateY(-5px);box-shadow:0 12px 35px rgba(120,90,70,.1);}
    .blog-img{height:180px;background:linear-gradient(135deg,var(--taupe-light) 0%,var(--taupe) 100%);display:flex;align-items:center;justify-content:center;font-size:2.5rem;overflow:hidden;}
    .blog-img img{width:100%;height:100%;object-fit:cover;}
    .blog-body{padding:1.5rem;}
    .blog-tag{font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:var(--taupe);margin-bottom:.5rem;display:block;}
    .blog-card h3{font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:400;color:var(--text);margin-bottom:.5rem;line-height:1.3;}
    .blog-card p{font-size:.83rem;line-height:1.7;color:var(--text-mid);font-weight:300;margin-bottom:1rem;}
    .blog-link{font-size:.75rem;letter-spacing:.1em;text-transform:uppercase;color:var(--taupe);text-decoration:none;font-weight:500;transition:color .2s;}
    .blog-link:hover{color:var(--taupe-dark);}
    /* FAQ */
    #faq{background:var(--white);}
    .faq-header{text-align:center;margin-bottom:3.5rem;}
    .faq-list{max-width:760px;margin:0 auto;}
    .faq-item{border-bottom:1px solid rgba(160,136,120,.2);padding:1.5rem 0;}
    .faq-question{display:flex;justify-content:space-between;align-items:center;cursor:pointer;gap:1rem;}
    .faq-question h4{font-family:'Cormorant Garamond',serif;font-size:1.15rem;font-weight:400;color:var(--text);}
    .faq-toggle{width:28px;height:28px;border-radius:50%;border:1px solid var(--taupe-light);display:flex;align-items:center;justify-content:center;color:var(--taupe);font-size:1.2rem;flex-shrink:0;transition:all .25s;}
    .faq-item.open .faq-toggle{background:var(--taupe);color:var(--white);transform:rotate(45deg);}
    .faq-answer{max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s;}
    .faq-answer p{padding-top:1rem;font-size:.9rem;line-height:1.8;color:var(--text-mid);font-weight:300;}
    .faq-item.open .faq-answer{max-height:300px;}
    /* CONTACT */
    #contact{background:var(--taupe);}
    .contact-grid{display:grid;grid-template-columns:1fr 1.2fr;gap:5rem;align-items:start;}
    .contact-info .section-title{color:var(--white);}
    .contact-info p{color:rgba(255,255,255,.8);font-size:.95rem;line-height:1.8;font-weight:300;margin-bottom:2rem;}
    .contact-details{display:flex;flex-direction:column;gap:1rem;}
    .contact-detail{display:flex;align-items:center;gap:1rem;color:rgba(255,255,255,.85);font-size:.9rem;}
    .contact-detail-icon{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
    .contact-social{display:flex;gap:1rem;margin-top:2rem;}
    .social-btn{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);display:flex;align-items:center;justify-content:center;color:var(--white);text-decoration:none;font-size:1rem;transition:background .2s;}
    .social-btn:hover{background:rgba(255,255,255,.28);}
    .contact-form{background:var(--white);border-radius:4px 60px 4px 4px;padding:3rem 2.5rem;}
    .form-group{margin-bottom:1.5rem;}
    label{display:block;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--text-mid);margin-bottom:.5rem;font-weight:500;}
    input[type=text],input[type=email],input[type=tel],textarea,select{width:100%;border:1px solid rgba(160,136,120,.3);border-radius:4px;padding:.85rem 1rem;font-family:'Jost',sans-serif;font-size:.9rem;color:var(--text);background:var(--cream);outline:none;transition:border-color .2s;appearance:none;}
    input:focus,textarea:focus,select:focus{border-color:var(--taupe);}
    textarea{resize:vertical;min-height:120px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    .btn-submit{width:100%;background:var(--taupe);color:var(--white);border:none;padding:1rem;border-radius:50px;font-family:'Jost',sans-serif;font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;font-weight:500;cursor:pointer;transition:background .25s;}
    .btn-submit:hover{background:var(--taupe-dark);}
    .ok-banner{background:#e8f5e9;border:1px solid #c8e6c9;color:#2e7d32;padding:.85rem 1.2rem;border-radius:4px;margin-bottom:1.5rem;font-size:.9rem;}
    .err-banner{background:#fde8e8;border:1px solid #f5c6c6;color:var(--red);padding:.85rem 1.2rem;border-radius:4px;margin-bottom:1.5rem;font-size:.9rem;}
    /* FOOTER */
    footer{background:var(--text);padding:3rem 4rem;}
    .footer-inner{max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;}
    .footer-logo{font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-style:italic;color:var(--taupe-light);text-decoration:none;}
    .footer-copy{font-size:.78rem;color:rgba(255,255,255,.3);}
    .footer-links{display:flex;gap:2rem;}
    .footer-links a{font-size:.78rem;color:rgba(255,255,255,.4);text-decoration:none;transition:color .2s;}
    .footer-links a:hover{color:var(--taupe-light);}
    .heart-float{position:fixed;bottom:2rem;right:2rem;z-index:200;width:50px;height:50px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:1.4rem;cursor:pointer;box-shadow:0 4px 20px rgba(192,57,43,.4);text-decoration:none;color:white;transition:transform .2s,box-shadow .2s;animation:pulse 2.5s ease-in-out infinite;}
    .heart-float:hover{transform:scale(1.12);box-shadow:0 8px 30px rgba(192,57,43,.5);}
    @keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
    @keyframes fadeIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
    @keyframes pulse{0%,100%{box-shadow:0 4px 20px rgba(192,57,43,.4)}50%{box-shadow:0 4px 30px rgba(192,57,43,.65)}}
    .reveal{opacity:0;transform:translateY(30px);transition:opacity .7s ease,transform .7s ease;}
    .reveal.visible{opacity:1;transform:translateY(0);}
    @media(max-width:900px){
      nav{padding:1rem 1.5rem;}
      .nav-links{display:none;flex-direction:column;position:fixed;top:64px;left:0;right:0;background:var(--white);padding:2rem;gap:1.5rem;box-shadow:0 8px 30px rgba(0,0,0,.08);}
      .nav-links.open{display:flex;}
      .burger{display:flex;}
      section{padding:5rem 1.5rem;}
      #accueil{padding:7rem 1.5rem 4rem;}
      .hero-content{grid-template-columns:1fr;gap:3rem;}
      .hero-logo-wrap{order:-1;}
      .hero-logo-circle{width:240px;}
      .hero-text h1,.hero-text p{text-align:center;}
      .hero-text p{margin:0 auto 2rem;}
      .hero-btns{justify-content:center;}
      .apropos-grid,.Accompagnements-grid,.tarifs-grid,.temoignages-grid,.blog-grid,.contact-grid{grid-template-columns:1fr;}
      .tarif-card.featured{transform:none;}
      .blog-header{flex-direction:column;gap:1rem;}
      footer{padding:2rem 1.5rem;}
      .footer-inner{flex-direction:column;text-align:center;}
    }
  </style>
</head>
<body>

<nav id="navbar">
  <a href="#accueil" class="nav-logo"><?= h($s['nom'] ?? 'Lumière de Vie') ?> <span>♥</span></a>
  <ul class="nav-links" id="navLinks">
    <li><a href="#apropos">À propos</a></li>
    <li><a href="#Accompagnements">Accompagnements</a></li>
    <li><a href="#tarifs">Tarifs</a></li>
    <li><a href="#temoignages">Témoignages</a></li>
    <li><a href="#blog">Blog</a></li>
    <li><a href="#faq">FAQ</a></li>
    <li><a href="#contact" class="nav-cta">Prendre RDV</a></li>
  </ul>
  <div class="burger" id="burger"><span></span><span></span><span></span></div>
</nav>

<!-- HERO -->
<section id="accueil">
  <div class="hero-bg"></div>
  <svg class="floral-tl" viewBox="0 0 200 200" fill="none"><circle cx="80" cy="80" r="35" stroke="white" stroke-width="1.5"/><circle cx="80" cy="80" r="18" stroke="white" stroke-width="1"/><line x1="80" y1="45" x2="80" y2="115" stroke="white" stroke-width="1"/><line x1="45" y1="80" x2="115" y2="80" stroke="white" stroke-width="1"/><ellipse cx="80" cy="57" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="80" cy="103" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="57" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/><ellipse cx="103" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/></svg>
  <svg class="floral-br" viewBox="0 0 200 200" fill="none"><circle cx="80" cy="80" r="35" stroke="white" stroke-width="1.5"/><circle cx="80" cy="80" r="18" stroke="white" stroke-width="1"/><line x1="80" y1="45" x2="80" y2="115" stroke="white" stroke-width="1"/><line x1="45" y1="80" x2="115" y2="80" stroke="white" stroke-width="1"/><ellipse cx="80" cy="57" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="80" cy="103" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="57" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/><ellipse cx="103" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/></svg>
  <div class="hero-content">
    <div class="hero-text">
      <h1><em>Doula Sévy</em><?= h($s['nom'] ?? 'Lumière de Vie') ?></h1>
      <p><?= h($s['description'] ?? '') ?></p>
      <div class="hero-btns">
        <a href="#contact" class="btn-primary">Prendre rendez-vous</a>
        <a href="#Accompagnements" class="btn-outline">Découvrir mes Accompagnements</a>
      </div>
    </div>
    <div class="hero-logo-wrap">
      <div class="hero-logo-circle">
        <?php if(!empty($s['logo2.png'])): ?>
          <img src="<?= h($s['logo2.png']) ?>" alt="Logo"/>
        <?php else: ?>
          <img src="logo2.png" alt="Logo" onerror="this.style.display='none';this.parentElement.innerHTML+='<div style=\'font-family:Cormorant Garamond,serif;font-size:1.1rem;font-style:italic;color:rgba(255,255,255,.8);text-align:center;padding:2rem;\'>Lumière de Vie<br><span style=\'font-size:2rem;\'>♥</span></div>'"/>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- À PROPOS -->
<section id="apropos">
  <div class="section-inner">
    <div class="apropos-grid reveal">
      <div class="apropos-img-wrap">
        <div class="apropos-img-bg">
          <?php if(!empty($s['photo_sevy'])): ?>
            <img src="<?= h($s['photo_sevy']) ?>" alt="Sévy"/>
          <?php else: ?>
            <div class="apropos-img-placeholder">🌸<br><br><em>Photo de Sévy</em></div>
          <?php endif; ?>
        </div>
        <div class="apropos-badge"><strong>♥</strong><span>Doula<br>certifiée</span></div>
      </div>
      <div class="apropos-text">
        <span class="section-tag">À propos</span>
        <h2 class="section-title"><?= h($a['titre'] ?? '') ?></h2>
        <div class="divider"></div>
        <?php foreach(['texte1','texte2','texte3'] as $k): if(!empty($a[$k])): ?>
        <p><?= h($a[$k]) ?></p>
        <?php endif; endforeach; ?>
        <div class="apropos-values">
          <?php foreach($a['valeurs']??[] as $v): ?>
          <span class="value-pill"><?= h($v) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Accompagnements -->
<section id="Accompagnements">
  <div class="section-inner">
    <div class="Accompagnements-header reveal">
      <span class="section-tag">Mes prestations</span>
      <h2 class="section-title">Des Accompagnements <em>sur mesure</em><br>pour votre famille</h2>
      <div class="divider center"></div>
    </div>
    <div class="Accompagnements-grid">
      <?php foreach($srv as $sv): ?>
      <div class="service-card reveal">
        <span class="service-icon"><?= h($sv['icone']??'🌸') ?></span>
        <h3><?= h($sv['titre']??'') ?></h3>
        <p><?= h($sv['description']??'') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TARIFS -->
<section id="tarifs">
  <div class="section-inner">
    <div class="tarifs-header reveal">
      <span class="section-tag">Investissement</span>
      <h2 class="section-title">Des tarifs <em>transparents</em></h2>
      <div class="divider center"></div>
    </div>
    <div class="tarifs-grid reveal">
      <?php foreach($tar as $t): ?>
      <div class="tarif-card <?= ($t['featured']??false)?'featured':'' ?>">
        <h3><?= h($t['titre']??'') ?></h3>
        <div class="price"><?= h($t['prix']??'') ?></div>
        <span class="price-note"><?= h($t['note']??'') ?></span>
        <ul class="tarif-features">
          <?php foreach($t['features']??[] as $f): ?>
          <li><?= h($f) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="#contact" class="btn-tarif">Me contacter</a>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="tarif-note">💬 Un entretien découverte gratuit et sans engagement est offert à toutes les familles.</p>
  </div>
</section>

<!-- TÉMOIGNAGES -->
<section id="temoignages">
  <div class="section-inner">
    <div class="temoignages-header reveal">
      <span class="section-tag">Témoignages</span>
      <h2 class="section-title">Elles me font <em>confiance</em></h2>
      <div class="divider center"></div>
    </div>
    <div class="temoignages-grid">
      <?php foreach($tem as $t): ?>
      <div class="temoignage-card reveal">
        <span class="quote-mark">"</span>
        <div class="stars">★★★★★</div>
        <p><?= h($t['texte']??'') ?></p>
        <div class="temoignage-author">
          <div class="author-avatar"><?= h($t['icone']??'🌸') ?></div>
          <div class="author-info">
            <strong><?= h($t['auteur']??'') ?></strong>
            <span><?= h($t['detail']??'') ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- BLOG -->
<section id="blog">
  <div class="section-inner">
    <div class="blog-header reveal">
      <div>
        <span class="section-tag">Le blog</span>
        <h2 class="section-title">Ressources & <em>inspirations</em></h2>
      </div>
    </div>
    <div class="blog-grid">
      <?php foreach(array_slice($blg, 0, 3) as $b): ?>
      <div class="blog-card reveal">
        <div class="blog-img">
          <?php if(!empty($b['photo'])): ?>
            <img src="<?= h($b['photo']) ?>" alt="<?= h($b['titre']??'') ?>"/>
          <?php else: ?>
            <?= h($b['icone']??'🌸') ?>
          <?php endif; ?>
        </div>
        <div class="blog-body">
          <span class="blog-tag"><?= h($b['tag']??'') ?></span>
          <h3><?= h($b['titre']??'') ?></h3>
          <p><?= h($b['resume']??'') ?></p>
          <a href="#" class="blog-link">Lire la suite →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq">
  <div class="section-inner">
    <div class="faq-header reveal">
      <span class="section-tag">Questions fréquentes</span>
      <h2 class="section-title">Vous vous <em>posez des questions ?</em></h2>
      <div class="divider center"></div>
    </div>
    <div class="faq-list">
      <?php foreach($faq as $f): ?>
      <div class="faq-item reveal">
        <div class="faq-question">
          <h4><?= h($f['question']??'') ?></h4>
          <span class="faq-toggle">+</span>
        </div>
        <div class="faq-answer"><p><?= h($f['reponse']??'') ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact">
  <div class="section-inner">
    <div class="contact-grid">
      <div class="contact-info reveal">
        <span class="section-tag" style="color:rgba(255,255,255,.6)">Contact</span>
        <h2 class="section-title" style="color:var(--white)">Commençons<br><em style="color:rgba(255,255,255,.7)">votre histoire</em></h2>
        <div class="divider" style="background:rgba(255,255,255,.3)"></div>
        <p>Je serais ravie de vous rencontrer pour un premier échange, sans engagement. Ensemble, nous verrons comment je peux vous accompagner au mieux dans ce voyage unique qu'est la maternité.</p>
        <div class="contact-details">
          <div class="contact-detail"><div class="contact-detail-icon">📍</div><span><?= h($s['ville']??'') ?></span></div>
          <?php if(!empty($s['telephone'])): ?>
          <div class="contact-detail"><div class="contact-detail-icon">📞</div><span><?= h($s['telephone']) ?></span></div>
          <?php endif; ?>
          <?php if(!empty($s['email'])): ?>
          <div class="contact-detail"><div class="contact-detail-icon">✉️</div><span><?= h($s['email']) ?></span></div>
          <?php endif; ?>
          <?php if(!empty($s['calendly'])): ?>
          <div class="contact-detail"><div class="contact-detail-icon">📅</div><a href="<?= h($s['calendly']) ?>" target="_blank" style="color:rgba(255,255,255,.85);text-decoration:none;">Prendre RDV en ligne →</a></div>
          <?php endif; ?>
        </div>
        <div class="contact-social">
          <?php if(!empty($s['instagram'])): ?><a href="<?= h($s['instagram']) ?>" class="social-btn" target="_blank">📸</a><?php endif; ?>
          <?php if(!empty($s['facebook'])): ?><a href="<?= h($s['facebook']) ?>" class="social-btn" target="_blank">📘</a><?php endif; ?>
        </div>
      </div>
      <div class="contact-form reveal">
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:400;color:var(--text);margin-bottom:1.5rem;">Envoyez-moi un message</h3>
        <?php if($msg_ok): ?>
          <div class="ok-banner">✅ Merci ! Votre message a bien été envoyé. Je vous répondrai très vite. 🌸</div>
        <?php elseif($msg_err): ?>
          <div class="err-banner">⚠️ <?= h($msg_err) ?></div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="contact_submit" value="1"/>
          <div class="form-row">
            <div class="form-group"><label>Prénom</label><input type="text" name="prenom" placeholder="Votre prénom" required/></div>
            <div class="form-group"><label>Nom</label><input type="text" name="nom" placeholder="Votre nom"/></div>
          </div>
          <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="votre@email.fr" required/></div>
          <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" placeholder="06 XX XX XX XX"/></div>
          <div class="form-group"><label>Terme approximatif</label><input type="text" name="terme" placeholder="Ex : Juin 2025"/></div>
          <div class="form-group">
            <label>Ce que vous recherchez</label>
            <select name="service">
              <option value="">Sélectionnez un service</option>
              <?php foreach($srv as $sv): ?>
              <option><?= h($sv['titre']??'') ?></option>
              <?php endforeach; ?>
              <option>Accompagnement complet</option>
              <option>Autre</option>
            </select>
          </div>
          <div class="form-group"><label>Votre message</label><textarea name="message" placeholder="Parlez-moi de vous..." required></textarea></div>
          <button type="submit" class="btn-submit">Envoyer mon message ♥</button>
        </form>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="footer-inner">
    <a href="#accueil" class="footer-logo"><?= h($s['nom'] ?? 'Lumière de Vie – Doula Sévy') ?></a>
    <div class="footer-links">
      <a href="#apropos">À propos</a><a href="#Accompagnements">Accompagnements</a><a href="#contact">Contact</a><a href="mentions-legales.php">Mentions légales</a>
    </div>
    <span class="footer-copy">© <?= date('Y') ?> <?= h($s['nom'] ?? 'Lumière de Vie – Doula Sévy') ?> · <?= h($s['ville']??'') ?></span>
  </div>
</footer>

<a href="#contact" class="heart-float">♥</a>

<script>
  const navbar=document.getElementById('navbar');
  window.addEventListener('scroll',()=>navbar.classList.toggle('scrolled',scrollY>40));
  const burger=document.getElementById('burger'),nav=document.getElementById('navLinks');
  burger.addEventListener('click',()=>nav.classList.toggle('open'));
  nav.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>nav.classList.remove('open')));
  const obs=new IntersectionObserver((e)=>e.forEach((en,i)=>{if(en.isIntersecting)setTimeout(()=>en.target.classList.add('visible'),i*80);}),{threshold:.12});
  document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
  document.querySelectorAll('.faq-item').forEach(item=>{
    item.querySelector('.faq-question').addEventListener('click',()=>{
      const o=item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));
      if(!o)item.classList.add('open');
    });
  });
</script>
</body>
</html>
