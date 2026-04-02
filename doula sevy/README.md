# Lumière de Vie – Doula Sévy
## Instructions de déploiement sur alwaysdata

### 📁 Structure des fichiers
```
www/
├── index.php          ← Site public
├── admin.php          ← Page de connexion admin
├── dashboard.php      ← Panneau d'administration
├── config.php         ← Configuration (mot de passe, chemins)
├── .htaccess          ← Sécurité
├── logo.png           ← Votre logo (à uploader)
├── data/
│   ├── content.json   ← Tout le contenu du site
│   └── messages.json  ← Messages du formulaire de contact
└── uploads/
    ├── photos/        ← Photos de profil
    └── blog/          ← Images des articles
```

### 🚀 Étapes de déploiement

1. **Connectez-vous** à votre compte alwaysdata
2. **Ouvrez le gestionnaire de fichiers** (ou utilisez FTP/SSH)
3. **Uploadez tous les fichiers** dans le dossier `www/` de votre site
4. **Créez les dossiers** `data/`, `uploads/photos/`, `uploads/blog/` s'ils n'existent pas
5. **Permissions** : assurez-vous que `data/` et `uploads/` sont en écriture (chmod 755 ou 777)

### 🔐 Mot de passe admin

Le mot de passe par défaut est : **doula2025**

**IMPORTANT** : Changez-le avant la mise en ligne !

Pour changer le mot de passe :
1. Ouvrez `config.php`
2. Remplacez la ligne :
   ```php
   define('ADMIN_PASSWORD_HASH', password_hash('doula2025', PASSWORD_DEFAULT));
   ```
   Par :
   ```php
   define('ADMIN_PASSWORD_HASH', password_hash('VOTRE_NOUVEAU_MOT_DE_PASSE', PASSWORD_DEFAULT));
   ```

### 🔑 Accès à l'administration

- **Site public** : `https://votre-domaine.fr/`
- **Admin** : `https://votre-domaine.fr/admin.php`

### ⚙️ Permissions sur alwaysdata

Via SSH ou le gestionnaire de fichiers, assurez-vous que :
```bash
chmod 755 data/
chmod 755 uploads/
chmod 755 uploads/photos/
chmod 755 uploads/blog/
chmod 644 data/content.json
chmod 644 data/messages.json
```

### 📧 Notifications email

Pour recevoir les messages du formulaire par email :
1. Allez dans l'admin → Informations site
2. Renseignez votre adresse email
3. Les messages seront envoyés automatiquement

### 🖼️ Ajouter le logo

Uploadez votre logo dans `www/` sous le nom `logo.png`
(ou utilisez l'admin pour uploader une photo de Sévy)
