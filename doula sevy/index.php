<?php
require_once 'config.php';

$content = file_exists(CONTENT_FILE) ? json_decode(file_get_contents(CONTENT_FILE), true) : [];
$s    = $content['site']          ?? [];
$a    = $content['apropos']       ?? [];
$phi  = $content['philosophie']   ?? [];
$acc  = array_filter($content['Accompagnements'] ?? [], fn($x)=>$x['actif']??true);
$tar  = $content['tarifs']        ?? [];
$tem  = array_filter($content['temoignages'] ?? [], fn($x)=>$x['actif']??true);
$blg  = array_filter($content['blog']        ?? [], fn($x)=>$x['actif']??true);
$faq  = array_filter($content['faq']         ?? [], fn($x)=>$x['actif']??true);

function h(string $str): string { return htmlspecialchars($str, ENT_QUOTES); }

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

        // Email admin — toujours envoyé à l'adresse fixe + celle du site si différente
        $admin_email = 'lumieredevie.doulasevy@gmail.com';
        $site_email  = !empty($s['email']) ? trim($s['email']) : '';

        $subject = "=?UTF-8?B?".base64_encode("💌 Nouveau message de {$new['prenom']} {$new['nom']}")."?=";

        $body  = "Bonjour,\r\n\r\n";
        $body .= "Vous avez reçu un nouveau message depuis votre site Lumière de Vie Doula Sévy.\r\n\r\n";
        $body .= "────────────────────────────\r\n";
        $body .= "COORDONNÉES\r\n";
        $body .= "────────────────────────────\r\n";
        $body .= "Prénom / Nom : {$new['prenom']} {$new['nom']}\r\n";
        $body .= "Email        : {$new['email']}\r\n";
        if (!empty($new['telephone'])) $body .= "Téléphone    : {$new['telephone']}\r\n";
        if (!empty($new['terme']))     $body .= "Terme        : {$new['terme']}\r\n";
        if (!empty($new['service']))   $body .= "Service      : {$new['service']}\r\n";
        $body .= "\r\n────────────────────────────\r\n";
        $body .= "MESSAGE\r\n";
        $body .= "────────────────────────────\r\n";
        $body .= "{$new['message']}\r\n\r\n";
        $body .= "────────────────────────────\r\n";
        $body .= "Reçu le : {$new['date']}\r\n";
        $body .= "Répondre à : {$new['email']}\r\n\r\n";
        $body .= "Ce message a été enregistré dans votre espace admin.\r\n";
        $body .= "https://www.lumiere-de-vie-doula.fr/dashboard.php?section=messages\r\n";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        $headers .= "From: =?UTF-8?B?".base64_encode("Lumière de Vie – Site")."?= <noreply@lumiere-de-vie-doula.fr>\r\n";
        $headers .= "Reply-To: {$new['prenom']} {$new['nom']} <{$new['email']}>\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $body_encoded = base64_encode($body);

        // Envoi à l'adresse admin fixe
        @mail($admin_email, $subject, $body_encoded, $headers);

        // Envoi aussi à l'email du site si différent
        if ($site_email && $site_email !== $admin_email) {
            @mail($site_email, $subject, $body_encoded, $headers);
        }

        $msg_ok = true;
    }
}

// ── Fallbacks mot pour mot depuis les captures ──────────────
// IMAGE 1 — Hero
$hero_slogan = $s['slogan'] ?? "Votre voyage parental, une présence authentique";
$hero_desc   = $s['description'] ?? "Chez Lumière de vie Doula Sévy, chaque accompagnement est unique. Fort d'une écoute profonde et d'une riche expérience de vie en tant qu'assistante maternelle, mère de six enfants et grand-mère, j'offre un soutien sans jugement, respectueux de votre histoire. Découvrez mon approche personnalisée pour naviguer les passages de la parentalité.";

// IMAGE 8 — À propos titre
$ap_titre  = $a['titre']  ?? "Mon chemin vers l'accompagnement : Lumière de vie Doula Sévy";
// IMAGE 8 — texte 1
$ap_t1     = $a['texte1'] ?? "Je suis  Doula Sévy, accompagnante en périnatalité et parentalité à St Brice Courcelles (51) France. Mon parcours est tissé de moments de vie intenses, d'expériences riches en tant que femme, mère, assistante maternelle, et aussi grand-mère. Ces expériences ont façonné mon désir profond d'accompagner les familles, du desir d'enfant  au post-partum, en passant par les défis de la parentalité. Lumière de vie Doula Sévy est née de cette évidence : offrir une présence, une écoute bienveillante, et un soutien authentique à chaque étape de votre vie de parent.";
// IMAGE 8 — texte 2
$ap_t2     = $a['texte2'] ?? "Dans le cadre individuel mais surtout associatif.";
// IMAGES 10/11 — Pourquoi l'appel de la doula
$ap_t3     = $a['texte3'] ?? "L'envie de devenir doula s'est construite au fil de ma vie, bien avant la formation. J'ai accompagné des familles pendant de nombreuses années, dans le quotidien, l'allaitement, le sommeil, les débuts parfois fragiles de la parentalité. J'ai été témoin de leurs joies, de leurs doutes, et parfois de leurs épuisements. J'ai compris que ce qui manquait le plus n'était pas des conseils supplémentaires, mais une présence, une écoute, un espace où déposer sans jugement. Mes propres maternités, de transformation du corps et de passages de vie intenses, m'ont profondément reliée à ce que traversent les familles. Devenir doula est devenu une évidence : mettre mon expérience, ma sensibilité et mon engagement au service des familles, avec douceur, justesse et humanité, dans les moments où tout peut vaciller. Aujourd'hui, je suis là pour éclairer sans diriger, et soutenir les familles afin qu'elles trouvent leurs propres repères.";
$ap_vals   = $a['valeurs'] ?? ['Bienveillance','Écoute','Douceur','Respect','Authenticité'];

// IMAGE 9 — bloc gauche
$phi_t_approche = $phi['titre_approche'] ?? "Une approche humaine et personnalisée";
$phi_approche   = $phi['texte_approche'] ?? "Ce qui rend mes accompagnements uniques, c'est l'écoute profonde . Je vous offre une présence engagée, fiable et sincère, qui soutient sans diriger et croit en vos ressources.";
// IMAGE 9 — bloc droite
$phi_t_passages = $phi['titre_passages'] ?? "Soutenir vos grands passages de vie";
$phi_passages   = $phi['texte_passages'] ?? "J accompagne les parents et futurs parents à travers les moments clés : désir d'enfant, grossesse, accouchement, post-partum, deuils périnataux, et passages de vie féminins. Mes services s'adressent à toutes les familles en quête d'écoute et de soutien émotionnel.";
// IMAGE 6 — La confiance
$phi_t_conf  = $phi['titre_confiance']   ?? "La confiance, votre plus belle ressource";
$phi_conf    = $phi['texte_confiance']   ?? "Mon objectif est de vous aider à retrouver confiance en vos ressentis, en vos capacités parentales. Après mes accompagnements, vous vous sentirez légitimes, écoutés et respectés, prêts à avancer à votre rythme, sans pression ni jugement. C'est le cœur de Lumière de vie Doula Sévy.";
// IMAGES 12/13 — Philosophie
$phi_t_philo = $phi['titre_philosophie'] ?? "Ma philosophie d'accompagnement bienveillant";
$phi_philo   = $phi['texte_philosophie'] ?? "Ma manière d'accompagner repose avant tout sur une conviction simple : chaque parent, chaque enfant, chaque famille porte déjà en elle ses propres ressources. Mon rôle n'est pas de dire quoi faire, mais d'être présente, d'écouter, de soutenir, et d'offrir un espace sécurisant où chacun peut déposer ce qu'il traverse. J'accompagne avec respect du rythme, sans injonctions, sans modèle unique, en m'adaptant à l'histoire, aux valeurs et au vécu de chaque famille. Mon approche est globale et humaine : elle prend en compte les émotions, le corps, les passages de vie, et la réalité du quotidien parental. Je crois profondément à l'importance de la relation, à la force de la parole, et à la présence silencieuse quand les mots manquent. Accompagner, pour moi, c'est marcher aux côtés, sans jamais prendre la place, en éclairant le chemin sans le diriger.";
// IMAGES 14/15 — Engagement
$phi_t_eng   = $phi['titre_engagement'] ?? "Confiance et sécurité : mon engagement pour vous";
$phi_eng     = $phi['texte_engagement'] ?? "Ce qui me tient le plus à cœur, c'est d'offrir aux familles un espace de sécurité. Un espace où elles peuvent être pleinement elles-mêmes, sans masque, sans peur d'être jugées, sans avoir à correspondre à ce que l'on attend d'elles. Je souhaite avant tout transmettre la confiance : la confiance en leurs ressentis, en leurs intuitions, en leur capacité à faire au mieux pour leur enfant et pour eux-mêmes. J'ai à cœur de rappeler aux parents qu'ils ont le droit de douter, de tâtonner, de ne pas tout savoir, et que cela ne fait pas d'eux de « mauvais » parents. Ce que je souhaite laisser, c'est la sensation de ne plus être seuls, d'avoir été entendus dans leur histoire singulière, et d'avoir trouvé un soutien sincère dans les passages parfois fragiles de la parentalité. À travers mes accompagnements (incluant divers ateliers comme le portage, le massage bébé et enfants, le yoga bébé et enfant, la réflexologie, ainsi que des cercles de femmes enceintes ou en post-partum), je veux transmettre de la douceur, du respect, et cette idée essentielle : vous êtes déjà suffisamment compétents et légitimes.";

// IMAGES 2,3,4,5 — Accompagnements
$acc_list = array_values($acc);
$acc_intro_txt = "";
$acc_cartes = [];
foreach ($acc_list as $item) {
    if (($item['id']??0) == 1) { $acc_intro_txt = $item['description'] ?? ""; }
    else $acc_cartes[] = $item;
}
if (empty($acc_intro_txt)) $acc_intro_txt = "Je propose une gamme variée d'accompagnements pensés pour répondre à vos besoins spécifiques. Qu'il s'agisse de partager, d'apprendre ou de recevoir un soutien individuel, mes services sont conçus pour vous apporter sérénité et confiance.";
if (empty($acc_cartes)) $acc_cartes = [
    ['id'=>2,'icone'=>'🌸','titre'=>'Soutien individuel',   'description'=>"Bénéficiez d'un accompagnement personnalisé pour le désir d'enfant, la grossesse, le post-partum ou les deuils périnataux.",'actif'=>true],
    ['id'=>3,'icone'=>'🧘','titre'=>'Ateliers pratiques',   'description'=>"Participez à mes ateliers thématiques : portage, massage bébé, yoga bébé/enfants et réflexologie. Des moments d'apprentissage et de partage essentiels.",'actif'=>true],
    ['id'=>4,'icone'=>'👥','titre'=>'Cercles de partage',   'description'=>"Rejoignez mes cercles de femmes enceintes ou en post-partum. Un espace sécurisant pour déposer ce que vous vivez et vous sentir moins seule.",'actif'=>true],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= h($s['nom'] ?? 'Lumière de Vie – Doula Sévy') ?></title>
  <meta name="description" content="<?= h($hero_desc) ?>"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    :root{
      --taupe:#a08878;--taupe-light:#c4aa9a;--taupe-dark:#7a6156;
      --cream:#f5efe8;--cream-dark:#ede3d8;--grey:#f4f4f2;
      --red:#c0392b;--white:#fdfaf6;--text:#4a3a32;--text-mid:#7a6558;--gold:#c9a96e;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    html{scroll-behavior:smooth;}
    body{font-family:'Jost',sans-serif;background:var(--white);color:var(--text);overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");pointer-events:none;z-index:9999;opacity:.4;}

    /* NAV */
    nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:1.1rem 4rem;background:rgba(253,250,246,.93);backdrop-filter:blur(12px);border-bottom:1px solid rgba(160,136,120,.15);transition:box-shadow .3s;}
    nav.scrolled{box-shadow:0 4px 30px rgba(120,90,70,.08);}
    .nav-logo{font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:400;font-style:italic;color:var(--taupe-dark);letter-spacing:.04em;text-decoration:none;}
    .nav-logo span{color:var(--red);}
    .nav-links{display:flex;gap:2rem;list-style:none;}
    .nav-links a{font-size:.76rem;font-weight:400;letter-spacing:.1em;text-transform:uppercase;color:var(--text-mid);text-decoration:none;transition:color .2s;}
    .nav-links a:hover{color:var(--taupe-dark);}
    .nav-cta{background:var(--taupe);color:var(--white)!important;padding:.5rem 1.3rem;border-radius:50px;}
    .nav-cta:hover{background:var(--taupe-dark)!important;}
    .burger{display:none;flex-direction:column;gap:5px;cursor:pointer;}
    .burger span{display:block;width:24px;height:1.5px;background:var(--taupe-dark);}

    /* HERO — image 1 */
    #accueil{min-height:100vh;display:flex;align-items:center;position:relative;overflow:hidden;padding:9rem 4rem 5rem;}
    .hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse 80% 70% at 60% 50%,#d4bfb0 0%,#c4a898 30%,#a08878 70%,#7a6156 100%);}
    .floral{position:absolute;opacity:.22;pointer-events:none;}
    .floral.tl{top:60px;left:60px;width:200px;transform:rotate(-10deg);}
    .floral.br{bottom:60px;right:60px;width:185px;transform:rotate(15deg) scaleX(-1);}
    .hero-inner{position:relative;z-index:2;max-width:760px;width:100%;}
    .hero-inner h1{font-family:'Cormorant Garamond',serif;font-size:clamp(2.4rem,5vw,4rem);font-weight:300;line-height:1.1;color:var(--white);margin-bottom:1.2rem;animation:fadeUp .9s ease both;}
    .hero-inner p{color:rgba(253,250,246,.88);font-size:1rem;font-weight:300;line-height:1.85;margin-bottom:2.2rem;animation:fadeUp .9s .15s ease both;}
    .hero-btns{display:flex;gap:1rem;flex-wrap:wrap;animation:fadeUp .9s .3s ease both;}

    /* BOUTONS */
    .btn{display:inline-block;padding:.82rem 2rem;border-radius:4px;font-size:.84rem;letter-spacing:.07em;text-transform:uppercase;text-decoration:none;font-weight:500;transition:all .22s;cursor:pointer;border:none;font-family:'Jost',sans-serif;}
    .btn-taupe{background:var(--taupe);color:var(--white);}
    .btn-taupe:hover{background:var(--taupe-dark);}
    .btn-outline-dark{border:1.5px solid rgba(255,255,255,.7);color:var(--white);background:transparent;}
    .btn-outline-dark:hover{background:rgba(255,255,255,.12);}
    .btn-outline-taupe{border:1.5px solid var(--taupe);color:var(--taupe);background:transparent;}
    .btn-outline-taupe:hover{background:var(--cream);}

    /* SECTIONS */
    .sec{padding:6rem 4rem;}
    .sec-inner{max-width:1100px;margin:0 auto;}
    .sec-label{display:block;font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;color:var(--taupe);margin-bottom:.7rem;font-weight:500;}
    .sec-h2{font-family:'Cormorant Garamond',serif;font-size:clamp(1.7rem,3vw,2.6rem);font-weight:400;line-height:1.2;color:var(--text);margin-bottom:1rem;}
    .sec-h2 em{font-style:italic;color:var(--taupe);}
    .sec-h2.white{color:var(--white);}
    .sec-body{font-size:.97rem;line-height:1.9;color:var(--text-mid);font-weight:300;margin-bottom:1.3rem;}
    .divider{width:55px;height:1px;background:var(--taupe-light);margin:1.2rem 0;}
    .divider.center{margin-left:auto;margin-right:auto;}

    /* À PROPOS — images 8, 10/11 */
    .apropos-grid{display:grid;grid-template-columns:1fr 1.35fr;gap:5rem;align-items:start;}
    .apropos-img-box{width:100%;aspect-ratio:3/4;background:linear-gradient(135deg,var(--taupe-light),var(--taupe));border-radius:2px 80px 2px 80px;overflow:hidden;position:relative;}
    .apropos-img-box img{width:100%;height:100%;object-fit:cover;}
    .apropos-badge{position:absolute;bottom:-15px;right:-15px;background:var(--white);border-radius:50%;width:96px;height:96px;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,.1);}
    .apropos-badge strong{font-family:'Cormorant Garamond',serif;font-size:1.7rem;color:var(--taupe-dark);line-height:1;}
    .apropos-badge small{font-size:.58rem;letter-spacing:.08em;text-transform:uppercase;color:var(--text-mid);text-align:center;}
    .apropos-col p{font-size:.97rem;line-height:1.9;color:var(--text-mid);font-weight:300;margin-bottom:1rem;}
    .apropos-col h3{font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:400;color:var(--taupe-dark);margin:1.8rem 0 .9rem;}
    .values-row{display:flex;gap:.7rem;flex-wrap:wrap;margin-top:1.8rem;}
    .value-pill{background:var(--cream);border:1px solid var(--taupe-light);border-radius:50px;padding:.42rem 1rem;font-size:.77rem;color:var(--taupe-dark);}

    /* PHILOSOPHIE — images 9, 6, 12/13, 14/15 */
    .philo-2col{display:grid;grid-template-columns:1fr 1fr;gap:3.5rem;padding:5rem 4rem;}
    .philo-2col-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:3.5rem;}
    .philo-col h2{font-family:'Cormorant Garamond',serif;font-size:clamp(1.5rem,2.3vw,2rem);font-weight:400;color:var(--taupe-dark);margin-bottom:.9rem;line-height:1.25;}
    .philo-col p{font-size:.95rem;line-height:1.9;color:var(--text-mid);font-weight:300;}

    .philo-imgtext{display:grid;grid-template-columns:1fr 1fr;align-items:stretch;}
    .philo-imgtext-text{padding:5rem 4rem;}
    .philo-imgtext-text h2{font-family:'Cormorant Garamond',serif;font-size:clamp(1.6rem,2.5vw,2.2rem);font-weight:400;color:var(--taupe-dark);margin-bottom:1.1rem;line-height:1.25;}
    .philo-imgtext-text.white h2{color:var(--white);}
    .philo-imgtext-text p{font-size:.95rem;line-height:1.9;color:var(--text-mid);font-weight:300;margin-bottom:1.4rem;}
    .philo-imgtext-text.white p{color:rgba(255,255,255,.82);}
    .philo-imgtext-text.center-content{text-align:center;}
    .philo-imgtext-text.center-content h2{color:var(--taupe-dark);}
    .philo-imgbox{min-height:380px;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:5rem;}
    .philo-imgbox.bg1{background:linear-gradient(135deg,#c4b09e,#a08878);}
    .philo-imgbox.bg2{background:linear-gradient(135deg,#8a7060,#6a5048);}
    .philo-imgbox.bg3{background:linear-gradient(135deg,var(--taupe-light),var(--taupe));}
    .philo-imgbox img{width:100%;height:100%;object-fit:cover;}

    /* ACCOMPAGNEMENTS — images 2, 3, 4, 5 */
    .acc-header p{font-size:1rem;line-height:1.85;color:var(--text-mid);font-weight:300;max-width:720px;margin-top:.8rem;}
    .acc-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;margin-top:2.5rem;}
    .acc-card{border:1px dashed rgba(160,136,120,.4);padding:2.5rem 2rem;}
    .acc-card h3{font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:400;color:var(--taupe-dark);margin-bottom:.85rem;}
    .acc-card p{font-size:.92rem;line-height:1.8;color:var(--text-mid);font-weight:300;margin-bottom:1.5rem;}

    /* TARIFS */
    .tarifs-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;align-items:start;}
    .tarif-card{background:var(--white);padding:2.5rem 2rem;text-align:center;}
    .tarif-card.featured{background:var(--taupe);}
    .tarif-card h3{font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:400;color:var(--text);margin-bottom:.8rem;}
    .tarif-card.featured h3,.tarif-card.featured .price,.tarif-card.featured li,.tarif-card.featured .price-note{color:var(--white)!important;}
    .price{font-family:'Cormorant Garamond',serif;font-size:2.7rem;font-weight:300;color:var(--taupe-dark);line-height:1;}
    .price-note{font-size:.74rem;color:var(--text-mid);display:block;margin-bottom:1.5rem;font-style:italic;}
    .tarif-features{list-style:none;margin-bottom:1.8rem;text-align:left;}
    .tarif-features li{font-size:.84rem;color:var(--text-mid);padding:.38rem 0;border-bottom:1px solid rgba(160,136,120,.15);font-weight:300;}
    .tarif-features li::before{content:'✦ ';color:var(--taupe-light);font-size:.58rem;}
    .tarif-card.featured .btn-tarif{background:var(--white);color:var(--taupe-dark);}
    .btn-tarif{display:block;width:100%;background:var(--taupe);color:var(--white);padding:.8rem;border-radius:50px;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;text-align:center;font-weight:500;transition:background .2s;}
    .btn-tarif:hover{background:var(--taupe-dark);}

    /* TÉMOIGNAGES */
    .tem-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;}
    .tem-card{background:var(--cream);padding:2.5rem 2rem;position:relative;}
    .tem-quote{font-family:'Cormorant Garamond',serif;font-size:4.5rem;line-height:.8;color:var(--taupe-light);opacity:.4;position:absolute;top:.8rem;left:1.2rem;}
    .tem-stars{color:var(--gold);font-size:.8rem;margin-bottom:.5rem;}
    .tem-text{font-size:.92rem;line-height:1.85;color:var(--text-mid);font-weight:300;font-style:italic;margin-top:.8rem;}
    .tem-author{display:flex;align-items:center;gap:.8rem;margin-top:1.5rem;}
    .tem-avatar{width:36px;height:36px;border-radius:50%;background:var(--taupe-light);display:flex;align-items:center;justify-content:center;font-size:.95rem;}
    .tem-author strong{display:block;font-size:.82rem;color:var(--text);}
    .tem-author span{font-size:.72rem;color:var(--taupe);}

    /* BLOG */
    .blog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;}
    .blog-card{background:var(--white);overflow:hidden;}
    .blog-img{height:195px;background:linear-gradient(135deg,var(--taupe-light),var(--taupe));display:flex;align-items:center;justify-content:center;font-size:2.5rem;overflow:hidden;}
    .blog-img img{width:100%;height:100%;object-fit:cover;}
    .blog-body{padding:1.5rem;}
    .blog-tag{font-size:.67rem;letter-spacing:.15em;text-transform:uppercase;color:var(--taupe);margin-bottom:.4rem;display:block;}
    .blog-card h3{font-family:'Cormorant Garamond',serif;font-size:1.18rem;font-weight:400;color:var(--text);margin-bottom:.5rem;}
    .blog-card p{font-size:.82rem;line-height:1.7;color:var(--text-mid);font-weight:300;margin-bottom:1rem;}
    .blog-link{font-size:.74rem;letter-spacing:.1em;text-transform:uppercase;color:var(--taupe);text-decoration:none;font-weight:500;}
    .blog-link:hover{color:var(--taupe-dark);}

    /* FAQ */
    .faq-list{max-width:760px;margin:0 auto;}
    .faq-item{border-bottom:1px solid rgba(160,136,120,.2);padding:1.35rem 0;}
    .faq-q{display:flex;justify-content:space-between;align-items:center;cursor:pointer;gap:1rem;}
    .faq-q h4{font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:400;color:var(--text);}
    .faq-toggle{width:26px;height:26px;border-radius:50%;border:1px solid var(--taupe-light);display:flex;align-items:center;justify-content:center;color:var(--taupe);font-size:1.1rem;flex-shrink:0;transition:all .25s;}
    .faq-item.open .faq-toggle{background:var(--taupe);color:#fff;transform:rotate(45deg);}
    .faq-answer{max-height:0;overflow:hidden;transition:max-height .4s ease;}
    .faq-answer p{padding-top:.85rem;font-size:.9rem;line-height:1.8;color:var(--text-mid);font-weight:300;}
    .faq-item.open .faq-answer{max-height:300px;}

    /* CONTACT */
    .contact-grid{display:grid;grid-template-columns:1fr 1.2fr;gap:5rem;align-items:start;}
    .contact-info-txt{color:rgba(255,255,255,.82);font-size:.95rem;line-height:1.85;font-weight:300;margin-bottom:2rem;}
    .contact-details{display:flex;flex-direction:column;gap:1rem;margin-bottom:2rem;}
    .contact-detail{display:flex;align-items:center;gap:1rem;color:rgba(255,255,255,.85);font-size:.9rem;}
    .c-icon{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
    .social-row{display:flex;gap:.8rem;}
    .social-btn{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:1rem;transition:background .2s;}
    .social-btn:hover{background:rgba(255,255,255,.25);}
    .contact-form-box{background:var(--white);border-radius:2px 50px 2px 2px;padding:3rem 2.5rem;}
    .fg{margin-bottom:1.3rem;}
    label{display:block;font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:var(--text-mid);margin-bottom:.4rem;font-weight:500;}
    input[type=text],input[type=email],input[type=tel],textarea,select{width:100%;border:1px solid rgba(160,136,120,.3);border-radius:2px;padding:.8rem 1rem;font-family:'Jost',sans-serif;font-size:.9rem;color:var(--text);background:var(--cream);outline:none;transition:border-color .2s;appearance:none;}
    input:focus,textarea:focus,select:focus{border-color:var(--taupe);}
    textarea{resize:vertical;min-height:110px;}
    .fr2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    .btn-submit{width:100%;background:var(--taupe);color:#fff;border:none;padding:1rem;border-radius:50px;font-family:'Jost',sans-serif;font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;font-weight:500;cursor:pointer;transition:background .25s;}
    .btn-submit:hover{background:var(--taupe-dark);}
    .ok-banner{background:#e8f5e9;border:1px solid #c8e6c9;color:#2e7d32;padding:.8rem 1rem;border-radius:2px;margin-bottom:1.2rem;font-size:.88rem;}
    .err-banner{background:#fde8e8;border:1px solid #f5c6c6;color:var(--red);padding:.8rem 1rem;border-radius:2px;margin-bottom:1.2rem;font-size:.88rem;}

    /* FOOTER */
    footer{background:var(--text);padding:2.5rem 4rem;}
    .foot-inner{max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;}
    .foot-logo{font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-style:italic;color:var(--taupe-light);text-decoration:none;}
    .foot-copy{font-size:.74rem;color:rgba(255,255,255,.3);}
    .foot-links{display:flex;gap:1.8rem;}
    .foot-links a{font-size:.74rem;color:rgba(255,255,255,.4);text-decoration:none;transition:color .2s;}
    .foot-links a:hover{color:var(--taupe-light);}
    .heart-float{position:fixed;bottom:2rem;right:2rem;z-index:200;width:48px;height:48px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:1.3rem;text-decoration:none;color:#fff;box-shadow:0 4px 20px rgba(192,57,43,.4);animation:pulse 2.5s ease-in-out infinite;}
    .heart-float:hover{transform:scale(1.1);}

    @keyframes fadeUp{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:translateY(0)}}
    @keyframes pulse{0%,100%{box-shadow:0 4px 20px rgba(192,57,43,.4)}50%{box-shadow:0 4px 30px rgba(192,57,43,.65)}}
    .reveal{opacity:0;transform:translateY(26px);transition:opacity .7s ease,transform .7s ease;}
    .reveal.visible{opacity:1;transform:translateY(0);}

    @media(max-width:900px){
      nav{padding:1rem 1.5rem;}
      .nav-links{display:none;flex-direction:column;position:fixed;top:64px;left:0;right:0;background:var(--white);padding:2rem;gap:1.5rem;box-shadow:0 8px 30px rgba(0,0,0,.08);}
      .nav-links.open{display:flex;}
      .burger{display:flex;}
      .sec{padding:4rem 1.5rem;}
      #accueil{padding:7rem 1.5rem 4rem;}
      .apropos-grid,.contact-grid,.philo-2col-inner,.philo-imgtext,.fr2{grid-template-columns:1fr;}
      .philo-imgtext-text,.philo-imgtext-text.center-content{padding:3rem 1.5rem;text-align:left;}
      .philo-2col,.philo-imgtext-text{padding:3.5rem 1.5rem;}
      .philo-imgbox{min-height:250px;}
      .tarifs-grid,.blog-grid{grid-template-columns:1fr;}
      footer{padding:2rem 1.5rem;}
      .foot-inner{flex-direction:column;text-align:center;}
      .foot-links{flex-wrap:wrap;justify-content:center;}
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav id="navbar">
  <a href="#accueil" class="nav-logo"><?= h($s['nom'] ?? 'Lumière de Vie') ?> <span>♥</span></a>
  <ul class="nav-links" id="navLinks">
    <li><a href="#apropos">À propos</a></li>
    <li><a href="#philosophie">Ma philosophie</a></li>
    <li><a href="#Accompagnements">Accompagnements</a></li>
    <li><a href="#tarifs">Tarifs</a></li>
    <li><a href="#temoignages">Témoignages</a></li>
    <li><a href="#blog">Blog</a></li>
    <li><a href="#faq">FAQ</a></li>
    <li><a href="#contact" class="nav-cta">Prendre RDV</a></li>
  </ul>
  <div class="burger" id="burger"><span></span><span></span><span></span></div>
</nav>

<!-- ══════════════════════════════════════════════════ IMAGE 1 — HERO -->
<section id="accueil">
  <div class="hero-bg"></div>
  <svg class="floral tl" viewBox="0 0 200 200" fill="none"><circle cx="80" cy="80" r="35" stroke="white" stroke-width="1.5"/><circle cx="80" cy="80" r="18" stroke="white" stroke-width="1"/><line x1="80" y1="45" x2="80" y2="115" stroke="white" stroke-width="1"/><line x1="45" y1="80" x2="115" y2="80" stroke="white" stroke-width="1"/><ellipse cx="80" cy="57" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="80" cy="103" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="57" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/><ellipse cx="103" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/></svg>
  <svg class="floral br" viewBox="0 0 200 200" fill="none"><circle cx="80" cy="80" r="35" stroke="white" stroke-width="1.5"/><circle cx="80" cy="80" r="18" stroke="white" stroke-width="1"/><line x1="80" y1="45" x2="80" y2="115" stroke="white" stroke-width="1"/><line x1="45" y1="80" x2="115" y2="80" stroke="white" stroke-width="1"/><ellipse cx="80" cy="57" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="80" cy="103" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="57" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/><ellipse cx="103" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/></svg>
  <div class="hero-inner">
    <h1><?= h($hero_slogan) ?></h1>
    <p><?= h($hero_desc) ?></p>
    <div class="hero-btns">
      <a href="#Accompagnements" class="btn btn-outline-dark">Découvrir nos accompagnements</a>
    </div>
  </div>
</section>

<!-- ════════════════════════════ IMAGES 8, 10/11 — À PROPOS + POURQUOI -->
<div class="sec" id="apropos" style="background:var(--white);">
  <div class="sec-inner">
    <div class="apropos-grid reveal">
      <div style="position:relative;">
        <div class="apropos-img-box">
          <?php if(!empty($s['photo_sevy'])): ?>
            <img src="<?= h($s['photo_sevy']) ?>" alt="Doula Sévy"/>
          <?php else: ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:'Cormorant Garamond',serif;font-style:italic;color:rgba(255,255,255,.75);font-size:1rem;text-align:center;padding:2rem;">🌸<br><br>Photo de Sévy</div>
          <?php endif; ?>
        </div>
        <div class="apropos-badge"><strong>♥</strong><small>Doula<br>certifiée</small></div>
      </div>
      <div class="apropos-col">
        <span class="sec-label">À propos</span>
        <!-- Image 8 -->
        <h2 class="sec-h2"><?= h($ap_titre) ?></h2>
        <div class="divider"></div>
        <p><?= h($ap_t1) ?></p>
        <p><?= h($ap_t2) ?></p>
        <!-- Images 10/11 -->
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:400;color:var(--taupe-dark);margin:1.8rem 0 .9rem;">Pourquoi l'appel de la doula ?</h3>
        <p><?= h($ap_t3) ?></p>
        <a href="#Accompagnements" class="btn btn-taupe" style="margin-top:1rem;display:inline-block;">Découvrez nos accompagnements</a>
        <div class="values-row">
          <?php foreach($ap_vals as $v): ?><span class="value-pill"><?= h($v) ?></span><?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════ PHILOSOPHIE — images 9, 6, 12/13, 14/15 -->
<div id="philosophie">

  <!-- IMAGE 9 — 2 colonnes : "Une approche humaine" + "Soutenir vos grands passages" -->
  <div style="background:var(--grey);padding:5rem 4rem;">
    <div class="philo-2col-inner reveal">
      <div class="philo-col">
        <h2><?= h($phi_t_approche) ?></h2>
        <p><?= h($phi_approche) ?></p>
      </div>
      <div class="philo-col">
        <h2><?= h($phi_t_passages) ?></h2>
        <p><?= h($phi_passages) ?></p>
      </div>
    </div>
  </div>

  <!-- IMAGE 6 — "La confiance, votre plus belle ressource" + photo -->
  <div class="philo-imgtext reveal" style="background:var(--cream-dark);">
    <div class="philo-imgtext-text center-content" style="padding:5rem 4rem;">
      <h2><?= h($phi_t_conf) ?></h2>
      <p><?= h($phi_conf) ?></p>
      <a href="#Accompagnements" class="btn btn-taupe">Découvrir nos accompagnements</a>
    </div>
    <div class="philo-imgbox bg1">🤱</div>
  </div>

  <!-- IMAGES 12/13 — "Ma philosophie d'accompagnement bienveillant" + photo mains -->
  <div class="philo-imgtext reveal" style="background:var(--white);">
    <div class="philo-imgbox bg2">🤝</div>
    <div class="philo-imgtext-text" style="padding:5rem 4rem;">
      <h2><?= h($phi_t_philo) ?></h2>
      <p><?= h($phi_philo) ?></p>
      <a href="#contact" class="btn btn-taupe">En savoir plus sur ma vision</a>
    </div>
  </div>

  <!-- IMAGES 14/15 — "Confiance et sécurité : mon engagement" + photo -->
  <div class="philo-imgtext reveal" style="background:var(--grey);">
    <div class="philo-imgtext-text" style="padding:5rem 4rem;">
      <h2><?= h($phi_t_eng) ?></h2>
      <p><?= h($phi_eng) ?></p>
      <a href="#Accompagnements" class="btn btn-taupe">Découvrez nos ateliers et cercles</a>
    </div>
    <div class="philo-imgbox bg3">🌸</div>
  </div>

</div>

<!-- ══════════════════ IMAGES 2,3,4,5 — ACCOMPAGNEMENTS -->
<div class="sec" id="Accompagnements" style="background:var(--white);">
  <div class="sec-inner">
    <div class="acc-header reveal">
      <span class="sec-label">Mes prestations</span>
      <h2 class="sec-h2">Mes accompagnements <em>pour chaque étape</em></h2>
      <!-- Image 2 — intro -->
      <p><?= h($acc_intro_txt) ?></p>
    </div>
    <div class="acc-grid">
      <?php foreach($acc_cartes as $c): ?>
      <div class="acc-card reveal">
        <h3><?= h($c['titre']) ?></h3>
        <p><?= h($c['description']) ?></p>
        <?php if(stripos($c['titre'],'soutien individuel') !== false): ?>
          <!-- Image 5 — bouton "Prendre un rendez-vous" -->
          <a href="#contact" class="btn btn-taupe">Prendre un rendez-vous</a>
        <?php else: ?>
          <a href="#contact" class="btn btn-outline-taupe">En savoir plus</a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════ TARIFS -->
<div class="sec" id="tarifs" style="background:var(--cream-dark);">
  <div class="sec-inner">
    <div class="reveal" style="text-align:center;margin-bottom:3rem;">
      <span class="sec-label">Investissement</span>
      <h2 class="sec-h2">Des tarifs <em>transparents</em></h2>
      <div class="divider center"></div>
    </div>
    <div class="tarifs-grid reveal">
      <?php foreach($tar as $t): ?>
      <div class="tarif-card <?= ($t['featured']??false)?'featured':'' ?>">
        <h3><?= h($t['titre']??'') ?></h3>
        <div class="price"><?= h($t['prix']??'') ?></div>
        <span class="price-note"><?= h($t['note']??'') ?></span>
        <ul class="tarif-features">
          <?php foreach($t['features']??[] as $f): ?><li><?= h($f) ?></li><?php endforeach; ?>
        </ul>
        <a href="#contact" class="btn-tarif">Me contacter</a>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:2rem;font-size:.83rem;color:var(--text-mid);font-style:italic;">💬 Un entretien découverte gratuit et sans engagement est offert à toutes les familles.</p>
  </div>
</div>

<!-- ════════════════════════════════ TÉMOIGNAGES -->
<div class="sec" id="temoignages" style="background:var(--white);">
  <div class="sec-inner">
    <div class="reveal" style="text-align:center;margin-bottom:3rem;">
      <span class="sec-label">Témoignages</span>
      <h2 class="sec-h2">Elles me font <em>confiance</em></h2>
      <div class="divider center"></div>
    </div>
    <div class="tem-grid">
      <?php foreach($tem as $t): ?>
      <div class="tem-card reveal">
        <span class="tem-quote">"</span>
        <div class="tem-stars">★★★★★</div>
        <p class="tem-text"><?= h($t['texte']??'') ?></p>
        <div class="tem-author">
          <div class="tem-avatar"><?= h($t['icone']??'🌸') ?></div>
          <div>
            <strong><?= h($t['auteur']??'') ?></strong>
            <?php if(!empty($t['detail'])): ?><span><?= h($t['detail']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════ BLOG -->
<div class="sec" id="blog" style="background:var(--cream);">
  <div class="sec-inner">
    <div class="reveal" style="margin-bottom:2.5rem;">
      <span class="sec-label">Le blog</span>
      <h2 class="sec-h2">Ressources & <em>inspirations</em></h2>
    </div>
    <div class="blog-grid">
      <?php foreach(array_slice(array_values($blg),0,3) as $b): ?>
      <div class="blog-card reveal">
        <div class="blog-img">
          <?php if(!empty($b['photo'])): ?><img src="<?= h($b['photo']) ?>" alt=""/><?php else: ?><?= h($b['icone']??'🌸') ?><?php endif; ?>
        </div>
        <div class="blog-body">
          <span class="blog-tag"><?= h($b['tag']??'') ?></span>
          <h3><?= h($b['titre']??'') ?></h3>
          <p><?= h($b['resume']??'') ?></p>
          <a href="article.php?id=<?= (int)($b['id']??0) ?>" class="blog-link">Lire la suite →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════ FAQ -->
<div class="sec" id="faq" style="background:var(--white);">
  <div class="sec-inner">
    <div class="reveal" style="text-align:center;margin-bottom:3rem;">
      <span class="sec-label">Questions fréquentes</span>
      <h2 class="sec-h2">Vous vous <em>posez des questions ?</em></h2>
      <div class="divider center"></div>
    </div>
    <div class="faq-list">
      <?php foreach($faq as $f): ?>
      <div class="faq-item reveal">
        <div class="faq-q">
          <h4><?= h($f['question']??'') ?></h4>
          <span class="faq-toggle">+</span>
        </div>
        <div class="faq-answer"><p><?= h($f['reponse']??'') ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════ CONTACT -->
<div class="sec" id="contact" style="background:var(--taupe);">
  <div class="sec-inner">
    <div class="contact-grid">
      <div class="reveal">
        <span class="sec-label" style="color:rgba(255,255,255,.6);">Contact</span>
        <h2 class="sec-h2 white">Commençons<br><em style="color:rgba(255,255,255,.7);">votre histoire</em></h2>
        <div class="divider" style="background:rgba(255,255,255,.3);"></div>
        <p class="contact-info-txt">Je serais ravie de vous rencontrer pour un premier échange, sans engagement. Ensemble, nous verrons comment je peux vous accompagner au mieux dans ce voyage unique qu'est la maternité.</p>
        <div class="contact-details">
          <?php if(!empty($s['ville'])): ?>
          <div class="contact-detail"><div class="c-icon">📍</div><span><?= h($s['ville']) ?></span></div>
          <?php endif; ?>
          <?php if(!empty($s['telephone'])): ?>
          <div class="contact-detail"><div class="c-icon">📞</div><span><?= h($s['telephone']) ?></span></div>
          <?php endif; ?>
          <?php if(!empty($s['email'])): ?>
          <div class="contact-detail"><div class="c-icon">✉️</div><span><?= h($s['email']) ?></span></div>
          <?php endif; ?>
          <?php if(!empty($s['calendly'])): ?>
          <div class="contact-detail"><div class="c-icon">📅</div><a href="<?= h($s['calendly']) ?>" target="_blank" style="color:rgba(255,255,255,.85);text-decoration:none;">Prendre RDV en ligne →</a></div>
          <?php endif; ?>
        </div>
        <div class="social-row">
          <?php if(!empty($s['instagram'])): ?><a href="<?= h($s['instagram']) ?>" class="social-btn" target="_blank">📸</a><?php endif; ?>
          <?php if(!empty($s['facebook'])): ?><a href="<?= h($s['facebook']) ?>" class="social-btn" target="_blank">📘</a><?php endif; ?>
        </div>
      </div>
      <div class="contact-form-box reveal">
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:400;color:var(--text);margin-bottom:1.4rem;">Envoyez-moi un message</h3>
        <?php if($msg_ok): ?><div class="ok-banner">✅ Merci ! Votre message a bien été envoyé. Je vous répondrai très vite. 🌸</div><?php endif; ?>
        <?php if($msg_err): ?><div class="err-banner">⚠️ <?= h($msg_err) ?></div><?php endif; ?>
        <form method="POST">
          <input type="hidden" name="contact_submit" value="1"/>
          <div class="fr2">
            <div class="fg"><label>Prénom</label><input type="text" name="prenom" placeholder="Votre prénom" required/></div>
            <div class="fg"><label>Nom</label><input type="text" name="nom" placeholder="Votre nom"/></div>
          </div>
          <div class="fg"><label>Email</label><input type="email" name="email" placeholder="votre@email.fr" required/></div>
          <div class="fg"><label>Téléphone</label><input type="tel" name="telephone" placeholder="06 XX XX XX XX"/></div>
          <div class="fg"><label>Terme approximatif</label><input type="text" name="terme" placeholder="Ex : Juin 2025"/></div>
          <div class="fg">
            <label>Ce que vous recherchez</label>
            <select name="service">
              <option value="">Sélectionnez un service</option>
              <?php foreach($acc_cartes as $c): ?><option><?= h($c['titre']??'') ?></option><?php endforeach; ?>
              <option>Accompagnement complet</option>
              <option>Autre</option>
            </select>
          </div>
          <div class="fg"><label>Votre message</label><textarea name="message" placeholder="Parlez-moi de vous..." required></textarea></div>
          <button type="submit" class="btn-submit">Envoyer mon message ♥</button>
        </form>
      </div>
    </div>
  </div>
</div>

<footer>
  <div class="foot-inner">
    <a href="#accueil" class="foot-logo"><?= h($s['nom'] ?? 'Lumière de Vie – Doula Sévy') ?></a>
    <div class="foot-links">
      <a href="#apropos">À propos</a>
      <a href="#philosophie">Ma philosophie</a>
      <a href="#Accompagnements">Accompagnements</a>
      <a href="#contact">Contact</a>
      <a href="mentions-legales.php">Mentions légales</a>
    </div>
    <span class="foot-copy">© <?= date('Y') ?> <?= h($s['nom'] ?? 'Lumière de Vie – Doula Sévy') ?> · <?= h($s['ville']??'') ?></span>
  </div>
</footer>

<a href="#contact" class="heart-float">♥</a>

<script>
  const navbar=document.getElementById('navbar');
  window.addEventListener('scroll',()=>navbar.classList.toggle('scrolled',scrollY>40));
  const burger=document.getElementById('burger'),nav=document.getElementById('navLinks');
  burger.addEventListener('click',()=>nav.classList.toggle('open'));
  nav.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>nav.classList.remove('open')));
  const obs=new IntersectionObserver(entries=>{
    entries.forEach((en,i)=>{if(en.isIntersecting)setTimeout(()=>en.target.classList.add('visible'),i*80);});
  },{threshold:.1});
  document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
  document.querySelectorAll('.faq-item').forEach(item=>{
    item.querySelector('.faq-q').addEventListener('click',()=>{
      const o=item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));
      if(!o)item.classList.add('open');
    });
  });
</script>
</body>
</html>