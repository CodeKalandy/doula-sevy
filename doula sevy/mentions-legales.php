<?php
require_once 'config.php';
$content  = file_exists(CONTENT_FILE) ? json_decode(file_get_contents(CONTENT_FILE), true) : [];
$s        = $content['site'] ?? [];
$site_nom = $s['nom'] ?? "Lumière de Vie Doula Sévy";
$site_url = "https://www.lumiere-de-vie-doula.fr";
function h(string $str): string { return htmlspecialchars($str, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mentions légales – <?= h($site_nom) ?></title>
  <meta name="robots" content="noindex"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    :root{
      --taupe:#a08878;--taupe-light:#c4aa9a;--taupe-dark:#7a6156;
      --cream:#f5efe8;--cream-dark:#ede3d8;--grey:#f4f4f2;
      --red:#c0392b;--white:#fdfaf6;--text:#4a3a32;--text-mid:#7a6558;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    html{scroll-behavior:smooth;}
    body{font-family:'Jost',sans-serif;background:var(--white);color:var(--text);overflow-x:hidden;}
    body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");pointer-events:none;z-index:9999;opacity:.4;}

    /* ── NAV (identique au site) ── */
    nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:1.1rem 4rem;background:rgba(253,250,246,.93);backdrop-filter:blur(12px);border-bottom:1px solid rgba(160,136,120,.15);}
    .nav-logo{font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:400;font-style:italic;color:var(--taupe-dark);letter-spacing:.04em;text-decoration:none;}
    .nav-logo span{color:var(--red);}
    .nav-back{font-size:.76rem;letter-spacing:.1em;text-transform:uppercase;color:var(--text-mid);text-decoration:none;display:flex;align-items:center;gap:.5rem;transition:color .2s;}
    .nav-back:hover{color:var(--taupe-dark);}

    /* ── HERO MINI ── */
    .page-hero{
      padding:9rem 4rem 5rem;
      background:radial-gradient(ellipse 100% 100% at 60% 50%, #d4bfb0 0%, #c4a898 40%, #a08878 100%);
      position:relative;overflow:hidden;text-align:center;
    }
    .page-hero::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 50% 80% at 30% 50%,rgba(253,250,246,.18) 0%,transparent 70%);}
    .floral{position:absolute;opacity:.2;pointer-events:none;}
    .floral.left{top:40px;left:60px;width:170px;transform:rotate(-10deg);}
    .floral.right{bottom:20px;right:60px;width:155px;transform:rotate(12deg) scaleX(-1);}
    .hero-inner{position:relative;z-index:2;}
    .hero-label{font-size:.7rem;letter-spacing:.22em;text-transform:uppercase;color:rgba(255,255,255,.65);margin-bottom:.8rem;display:block;}
    .hero-h1{font-family:'Cormorant Garamond',serif;font-size:clamp(2rem,4vw,3.2rem);font-weight:300;color:var(--white);line-height:1.15;}
    .hero-h1 em{font-style:italic;color:rgba(255,255,255,.75);}

    /* ── CONTENT ── */
    .content-wrap{max-width:860px;margin:0 auto;padding:5rem 4rem 7rem;}

    /* Sommaire */
    .sommaire{background:var(--cream);border-radius:4px 40px 4px 4px;padding:2rem 2.5rem;margin-bottom:3.5rem;border-left:3px solid var(--taupe-light);}
    .sommaire h2{font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:400;color:var(--text);margin-bottom:1rem;}
    .sommaire ol{padding-left:1.2rem;display:flex;flex-direction:column;gap:.4rem;}
    .sommaire li{font-size:.85rem;color:var(--text-mid);}
    .sommaire a{color:var(--taupe);text-decoration:none;transition:color .2s;}
    .sommaire a:hover{color:var(--taupe-dark);}

    /* Sections */
    .ml-section{margin-bottom:3rem;scroll-margin-top:90px;}
    .ml-section h2{
      font-family:'Cormorant Garamond',serif;font-size:1.45rem;font-weight:400;color:var(--text);
      margin-bottom:1rem;padding-bottom:.6rem;border-bottom:1px solid var(--cream-dark);
      display:flex;align-items:center;gap:.75rem;
    }
    .num{
      display:inline-flex;align-items:center;justify-content:center;
      width:28px;height:28px;border-radius:50%;
      background:var(--taupe-light);color:var(--white);
      font-size:.76rem;flex-shrink:0;font-style:normal;
    }
    .ml-section p{font-size:.91rem;line-height:1.85;color:var(--text-mid);font-weight:300;margin-bottom:.9rem;}
    .ml-section p strong{color:var(--text);font-weight:500;}
    .ml-section ul{padding-left:1.4rem;margin-bottom:.9rem;display:flex;flex-direction:column;gap:.4rem;}
    .ml-section li{font-size:.9rem;line-height:1.75;color:var(--text-mid);font-weight:300;}
    .ml-section li::marker{color:var(--taupe-light);}
    .ml-section a{color:var(--taupe);text-decoration:none;}
    .ml-section a:hover{color:var(--taupe-dark);}

    /* Info box */
    .info-box{background:var(--cream);border-radius:4px;padding:1.5rem 2rem;margin-bottom:1.2rem;border-left:3px solid var(--taupe);}
    .info-box p{font-size:.9rem;line-height:1.75;color:var(--text-mid);font-weight:300;margin-bottom:.4rem;}
    .info-box p:last-child{margin-bottom:0;}
    .info-box strong{color:var(--taupe-dark);}

    /* Droits grid */
    .droits-grid{display:grid;grid-template-columns:1fr 2fr;gap:.7rem;margin-bottom:1rem;}
    .droit-key{background:var(--cream);padding:.65rem 1rem;border-radius:4px;font-size:.82rem;font-weight:500;color:var(--taupe-dark);display:flex;align-items:center;}
    .droit-val{background:var(--cream-dark);padding:.65rem 1rem;border-radius:4px;font-size:.82rem;color:var(--text-mid);font-weight:300;display:flex;align-items:center;}

    /* Divider */
    .divider{width:50px;height:1px;background:var(--taupe-light);margin:2.5rem 0;}

    /* Footer */
    .page-footer{background:var(--text);padding:2.5rem 4rem;text-align:center;}
    .page-footer a{font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-style:italic;color:var(--taupe-light);text-decoration:none;}
    .page-footer p{font-size:.74rem;color:rgba(255,255,255,.3);margin-top:.5rem;}

    @media(max-width:768px){
      nav{padding:1rem 1.5rem;}
      .page-hero{padding:7rem 1.5rem 4rem;}
      .content-wrap{padding:3rem 1.5rem 5rem;}
      .droits-grid{grid-template-columns:1fr;}
      .floral{display:none;}
      .page-footer{padding:2rem 1.5rem;}
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo"><?= h($s['nom'] ?? 'Lumière de Vie') ?> <span>♥</span></a>
  <a href="index.php" class="nav-back">← Retour au site</a>
</nav>

<!-- HERO -->
<div class="page-hero">
  <svg class="floral left" viewBox="0 0 200 200" fill="none"><circle cx="80" cy="80" r="35" stroke="white" stroke-width="1.5"/><circle cx="80" cy="80" r="18" stroke="white" stroke-width="1"/><line x1="80" y1="45" x2="80" y2="115" stroke="white" stroke-width="1"/><line x1="45" y1="80" x2="115" y2="80" stroke="white" stroke-width="1"/><ellipse cx="80" cy="57" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="80" cy="103" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="57" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/><ellipse cx="103" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/></svg>
  <svg class="floral right" viewBox="0 0 200 200" fill="none"><circle cx="80" cy="80" r="35" stroke="white" stroke-width="1.5"/><circle cx="80" cy="80" r="18" stroke="white" stroke-width="1"/><line x1="80" y1="45" x2="80" y2="115" stroke="white" stroke-width="1"/><line x1="45" y1="80" x2="115" y2="80" stroke="white" stroke-width="1"/><ellipse cx="80" cy="57" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="80" cy="103" rx="10" ry="14" stroke="white" stroke-width="1"/><ellipse cx="57" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/><ellipse cx="103" cy="80" rx="14" ry="10" stroke="white" stroke-width="1"/></svg>
  <div class="hero-inner">
    <span class="hero-label">Informations légales</span>
    <h1 class="hero-h1">Mentions <em>légales</em></h1>
  </div>
</div>

<!-- CONTENT -->
<div class="content-wrap">

  <!-- SOMMAIRE -->
  <nav class="sommaire">
    <h2>Sommaire</h2>
    <ol>
      <li><a href="#preambule">Préambule</a></li>
      <li><a href="#mentions">Mentions légales &amp; éditeur</a></li>
      <li><a href="#acces">Accès au site</a></li>
      <li><a href="#contenu">Contenu du site</a></li>
      <li><a href="#gestion">Gestion du site</a></li>
      <li><a href="#responsabilites">Responsabilités</a></li>
      <li><a href="#liens">Liens hypertextes</a></li>
      <li><a href="#donnees">Collecte et protection des données</a></li>
      <li><a href="#droits">Droits d'accès et de rectification</a></li>
      <li><a href="#utilisation">Utilisation des données</a></li>
      <li><a href="#conservation">Conservation des données</a></li>
      <li><a href="#partage">Partage des données</a></li>
      <li><a href="#cookies">Cookies</a></li>
      <li><a href="#loi">Loi applicable</a></li>
    </ol>
  </nav>

  <!-- 1 -->
  <div class="ml-section" id="preambule">
    <h2><em class="num">1</em>Préambule</h2>
    <p>Les présentes Conditions Générales d'Utilisation déterminent les règles d'accès au présent site et ses conditions d'utilisation que tout utilisateur reconnaît accepter, sans réserve, du seul fait de sa connexion au site.</p>
  </div>

  <!-- 2 -->
  <div class="ml-section" id="mentions">
    <h2><em class="num">2</em>Mentions légales &amp; éditeur</h2>
    <p>Conformément aux dispositions de l'article 6 III-1 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique, nous vous informons que le site est édité par :</p>
    <div class="info-box">
      <p><strong>Dénomination :</strong> Lumière de Vie Doula Sévy</p>
      <p><strong>Forme juridique :</strong> Association déclarée (Loi 1901)</p>
      <p><strong>N° RNA :</strong> W513 010 915</p>
      <p><strong>SIREN :</strong> 999 846 363</p>
      <p><strong>SIRET :</strong> 999 846 363 00011</p>
      <p><strong>N° TVA intracommunautaire :</strong> FR50 999 846 363</p>
      <p><strong>Code NAF/APE :</strong> 94.99Z – Autres organisations fonctionnant par adhésion volontaire</p>
      <p><strong>Date de création :</strong> 28 décembre 2025</p>
      <p><strong>Siège social :</strong> 3 rue des Maraîchers, 51370 Saint-Brice-Courcelles</p>
      <p><strong>Email de contact :</strong> <a href="mailto:lumieredevie.doulasevy@gmail.com">lumieredevie.doulasevy@gmail.com</a></p>
    </div>
    <p>Cette association est inscrite au Répertoire National des Associations (RNA) et à l'INSEE. Elle est reconnue comme appartenant à l'<strong>Économie Sociale et Solidaire (ESS)</strong>.</p>
  </div>

  <div class="divider"></div>

  <!-- 3 -->
  <div class="ml-section" id="acces">
    <h2><em class="num">3</em>Accès au site</h2>
    <p>L'accès au site et son utilisation sont réservés à un usage strictement personnel. Vous vous engagez à ne pas utiliser ce site et les informations ou données qui y figurent à des fins commerciales, politiques, publicitaires et pour toute forme de sollicitation commerciale et notamment l'envoi de courriers électroniques non sollicités.</p>
  </div>

  <!-- 4 -->
  <div class="ml-section" id="contenu">
    <h2><em class="num">4</em>Contenu du site</h2>
    <p>Toutes les marques, photographies, textes, commentaires, illustrations, images animées ou non, séquences vidéos, sons, ainsi que toutes les applications informatiques qui pourraient être utilisées pour faire fonctionner ce site et plus généralement tous les éléments reproduits ou utilisés sur le site sont protégés par les lois en vigueur au titre de la propriété intellectuelle.</p>
    <p>Ils sont la propriété pleine et entière de l'éditeur ou de ses partenaires. <strong>Toute reproduction, représentation, utilisation ou adaptation, sous quelque forme que ce soit, de tout ou partie de ces éléments, sans l'accord préalable et écrit de l'éditeur, est strictement interdite.</strong></p>
  </div>

  <!-- 5 -->
  <div class="ml-section" id="gestion">
    <h2><em class="num">5</em>Gestion du site</h2>
    <p>Pour la bonne gestion du site, l'éditeur pourra à tout moment :</p>
    <ul>
      <li>Suspendre, interrompre ou limiter l'accès à tout ou partie du site ;</li>
      <li>Supprimer toute information pouvant en perturber le fonctionnement ou entrant en contravention avec les lois nationales ou internationales ;</li>
      <li>Suspendre le site afin de procéder à des mises à jour.</li>
    </ul>
  </div>

  <!-- 6 -->
  <div class="ml-section" id="responsabilites">
    <h2><em class="num">6</em>Responsabilités</h2>
    <p>La responsabilité de l'éditeur ne peut être engagée en cas de défaillance, panne, difficulté ou interruption de fonctionnement, empêchant l'accès au site ou à une de ses fonctionnalités.</p>
    <p>Le matériel de connexion au site que vous utilisez est sous votre entière responsabilité. Vous devez prendre toutes les mesures appropriées pour protéger votre matériel et vos propres données notamment d'attaques virales par Internet.</p>
    <p>L'éditeur ne pourra être tenu pour responsable en cas de poursuites judiciaires à votre encontre du fait de l'usage du site ou du non-respect des présentes conditions générales.</p>
  </div>

  <div class="divider"></div>

  <!-- 7 -->
  <div class="ml-section" id="liens">
    <h2><em class="num">7</em>Liens hypertextes</h2>
    <p>Tout lien avec le site doit faire l'objet d'une autorisation écrite et préalable de l'association. L'éditeur se réserve le droit de mettre fin à cette autorisation à tout moment.</p>
    <p>Les sites présentant un lien hypertexte avec le présent site ne sont pas sous contrôle de l'association, qui décline toute responsabilité quant à leur contenu.</p>
  </div>

  <!-- 8 -->
  <div class="ml-section" id="donnees">
    <h2><em class="num">8</em>Collecte et protection des données</h2>
    <p>Vos données sont collectées par l'association <strong>Lumière de Vie Doula Sévy</strong>. Une donnée à caractère personnel désigne toute information concernant une personne physique identifiée ou identifiable.</p>
    <p>Les informations personnelles pouvant être recueillies sur le site sont principalement utilisées pour la gestion des relations avec vous. Les données personnelles récoltées sont les suivantes :</p>
    <ul>
      <li>Nom et prénom</li>
      <li>Adresse email</li>
      <li>Numéro de téléphone</li>
      <li>Terme de grossesse / informations de maternité (le cas échéant)</li>
    </ul>
  </div>

  <!-- 9 -->
  <div class="ml-section" id="droits">
    <h2><em class="num">9</em>Droits d'accès, de rectification et de déréférencement</h2>
    <p>En application de la réglementation applicable aux données à caractère personnel (RGPD), vous disposez des droits suivants :</p>
    <div class="droits-grid">
      <div class="droit-key">Droit d'accès</div>
      <div class="droit-val">Connaître les données personnelles vous concernant</div>
      <div class="droit-key">Droit de rectification</div>
      <div class="droit-val">Demander la mise à jour d'informations inexactes</div>
      <div class="droit-key">Droit à l'effacement</div>
      <div class="droit-val">Demander la suppression de vos données personnelles</div>
      <div class="droit-key">Droit à la limitation</div>
      <div class="droit-val">Limiter le traitement de vos données</div>
      <div class="droit-key">Droit d'opposition</div>
      <div class="droit-val">S'opposer au traitement de vos données</div>
      <div class="droit-key">Droit à la portabilité</div>
      <div class="droit-val">Récupérer vos données pour les transmettre à un tiers</div>
    </div>
    <p>Pour exercer ces droits, contactez-nous à : <a href="mailto:lumieredevie.doulasevy@gmail.com">lumieredevie.doulasevy@gmail.com</a><br/>
    Toute demande doit être accompagnée d'une copie d'un titre d'identité en cours de validité. La réponse sera adressée dans un délai d'un mois.</p>
  </div>

  <!-- 10 -->
  <div class="ml-section" id="utilisation">
    <h2><em class="num">10</em>Utilisation des données</h2>
    <p>Les données personnelles collectées ont pour objectif la mise à disposition des services du site, leur amélioration et le maintien d'un environnement sécurisé. Plus précisément :</p>
    <ul>
      <li>Accès et utilisation du site par l'utilisateur</li>
      <li>Gestion du fonctionnement et optimisation du site</li>
      <li>Mise en œuvre d'une assistance utilisateurs</li>
      <li>Vérification, identification et authentification des données transmises</li>
      <li>Prévention et détection des fraudes et incidents de sécurité</li>
      <li>Gestion des éventuels litiges</li>
    </ul>
  </div>

  <!-- 11 -->
  <div class="ml-section" id="conservation">
    <h2><em class="num">11</em>Conservation des données</h2>
    <p>Le site conserve vos données pour une durée nécessaire dans le but de vous fournir ses services et son assistance. Dans la mesure raisonnablement requise pour satisfaire aux obligations légales ou réglementaires, régler des litiges ou empêcher les fraudes, certaines informations peuvent être conservées au-delà de cette période.</p>
  </div>

  <!-- 12 -->
  <div class="ml-section" id="partage">
    <h2><em class="num">12</em>Partage des données personnelles</h2>
    <p>Les données personnelles peuvent être partagées avec des sociétés tierces exclusivement dans l'Union Européenne, dans les cas suivants :</p>
    <ul>
      <li>Lorsque l'utilisateur publie des informations accessibles au public ;</li>
      <li>Quand l'utilisateur autorise un site tiers à accéder à ses données ;</li>
      <li>Quand le site recourt à des prestataires pour l'assistance utilisateurs (accès limité et encadré contractuellement) ;</li>
      <li>Si la loi l'exige, pour donner suite à des réclamations ou procédures administratives et judiciaires.</li>
    </ul>
  </div>

  <div class="divider"></div>

  <!-- 13 -->
  <div class="ml-section" id="cookies">
    <h2><em class="num">13</em>Cookies</h2>
    <p>L'utilisateur est informé que, lors de ses visites sur le site, un cookie peut s'installer automatiquement sur son logiciel de navigation.</p>
    <p>Le cookie est un bloc de données qui ne permet pas d'identifier l'utilisateur mais sert à enregistrer des informations relatives à la navigation de celui-ci sur le site. Le paramétrage du logiciel de navigation permet d'être informé de la présence de cookies et éventuellement de les refuser.</p>
    <p>Les informations collectées ne seront utilisées que pour suivre le volume, le type et la configuration du trafic utilisant ce site, pour en développer la conception et l'agencement, et plus généralement pour améliorer le service offert.</p>
  </div>

  <!-- 14 -->
  <div class="ml-section" id="loi">
    <h2><em class="num">14</em>Loi applicable et juridiction</h2>
    <p>Les présentes Conditions Générales d'Utilisation sont régies par le <strong>droit français</strong>. En cas de litige relatif à la validité, l'interprétation ou l'exécution des présentes, les <strong>Tribunaux français</strong> seront seuls compétents.</p>
    <p>Pour toute question relative au site ou à vos données personnelles, vous pouvez nous contacter à :<br/>
    <a href="mailto:lumieredevie.doulasevy@gmail.com" style="font-weight:500;">lumieredevie.doulasevy@gmail.com</a></p>
  </div>

  <p style="font-size:.76rem;color:var(--taupe-light);text-align:right;margin-top:3rem;font-style:italic;">
    Dernière mise à jour : <?= date('d/m/Y') ?>
  </p>

</div>

<!-- FOOTER -->
<div class="page-footer">
  <a href="index.php"><?= h($site_nom) ?> ♥</a>
  <p>3 rue des Maraîchers, 51370 Saint-Brice-Courcelles · SIREN 999 846 363</p>
</div>

</body>
</html>