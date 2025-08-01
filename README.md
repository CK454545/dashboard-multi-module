# 🎛️ Dashboard Multi-Modules - Plateforme de Widgets Streaming

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Node.js](https://img.shields.io/badge/Node.js-18+-green.svg)](https://nodejs.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net/)
[![Discord.js](https://img.shields.io/badge/Discord.js-14+-purple.svg)](https://discord.js.org/)

Une plateforme complète de widgets pour streaming avec dashboard moderne, modules personnalisables, bot Discord intégré, et système de mise à jour automatique.

## ✨ Fonctionnalités

### 🎮 Interface Web
- **Dashboard moderne** avec aperçu temps réel
- **Modules personnalisables** (Wins Counter, etc.)
- **Configuration avancée** des styles avec onglets
- **Widgets pour OBS/streaming** intégrés
- **Système multi-modules** extensible
- **Thèmes et couleurs** personnalisables

### 🤖 Bot Discord
- **Commandes intuitives** pour gérer les modules
- **Système de tokens** personnalisés par utilisateur
- **Intégration webhook** pour notifications
- **Support multi-serveurs**
- **Gestion automatique** des permissions

### 🔧 Administration
- **Auto-sauvegarde** de la base de données
- **Mise à jour automatique** depuis GitHub
- **Monitoring intégré** des services
- **Interface de configuration** simplifiée

### 🚀 Production Ready
- **Support Docker** complet
- **Scripts de déploiement** automatisés
- **SSL/HTTPS** configuré
- **Backup et rollback** automatiques

## 📋 Prérequis

- **Node.js** 18+ 
- **PHP** 8.1+ avec SQLite
- **Git** pour les mises à jour
- **Serveur web** (Nginx/Apache) pour la production

## 🚀 Installation Rapide

### 1. Cloner le repository
```bash
git clone https://github.com/VOTRE-USERNAME/dashboard-multi-modules.git
cd dashboard-multi-modules
```

### 2. Configuration initiale
```bash
# Windows
DEMARRER.bat

# Ou Linux/Mac
./scripts/start.sh
```

### 3. Configuration sécurisée
```bash
# Copier le fichier d'exemple
cp config/config.example.json config/config.json

# Éditer avec vos vrais secrets
nano config/config.json
```

### 4. Configuration Discord
1. Créer une application Discord sur [Discord Developer Portal](https://discord.com/developers/applications)
2. Copier le token et client ID
3. Lancer la configuration : `DEMARRER.bat` → Option 3

**🔒 IMPORTANT :** Ne jamais commiter `config/config.json` ! Utilisez toujours `config/config.example.json` comme modèle.

### 4. Premier démarrage
```bash
# Démarrer tous les services
DEMARRER.bat → Option 1
```

## 🔧 Configuration

### Configuration de base
Le fichier `config/config.example.json` contient toutes les options disponibles :

```json
{
    "website": {
        "url": "https://votre-domaine.com",
        "port": 80
    },
    "discord": {
        "token": "VOTRE_TOKEN_DISCORD_ICI",
        "support_url": "https://discord.gg/VOTRE_SERVEUR"
    },
    "database": {
        "file": "database/database.db"
    },
    "app": {
        "name": "StreamPro Studio",
        "description": "Solutions Professionnelles pour Créateurs de Contenu",
        "version": "1.0.0",
        "copyright": "© Copyright 2025/2026 MFA & Développement/Design by CK"
    }
}
```

**🔒 SÉCURITÉ :** Copiez `config.example.json` vers `config.json` et remplacez les valeurs par vos vrais secrets.

### Configuration production
Pour la production, utilisez le configurateur intégré :
```bash
DEMARRER.bat → Option P (Configuration Production)
```

## 📖 Utilisation

### Interface Web
- **Dashboard** : `https://myfullagency-connect.fr/dashboard.php`
- **Widget** : `https://myfullagency-connect.fr/modules/win.php`
- **Configuration** : `https://myfullagency-connect.fr/modules/wins-config.php`

### Commandes Discord
- `/token` - Obtenir le token d'API
- `/wins add 5` - Ajouter 5 wins
- `/wins reset` - Reset le compteur

### API REST
```bash
# Ajouter des wins
GET /api.php?token=TOKEN&module=wins&action=add-wins&value=1

# Reset
GET /api.php?token=TOKEN&module=wins&action=reset-wins

# Multiplicateur
GET /api.php?token=TOKEN&module=wins&action=add-multi&value=10
```

## 🎨 Personnalisation

### Styles
L'interface de configuration permet de personnaliser :
- **Couleurs** (avec sélecteur et input manuel)
- **Tailles** (avec slider et input numérique)
- **Polices** (Google Fonts intégrées)
- **Positions** et espacements
- **Effets** (ombres, contours)

### Thèmes
- Mode sombre/clair automatique
- Couleurs selon valeur (rouge/vert)
- Transparence pour OBS
- Multiplicateur masquable

## 🔄 Mise à Jour

### Automatique
Le système vérifie automatiquement les mises à jour toutes les heures :
```bash
# Voir les logs
pm2 logs wins-updater
```

### Manuelle
```bash
# Via l'interface
DEMARRER.bat → Option 9

# Ou directement
node scripts/auto-update.js update
```

## 💾 Sauvegarde

### Automatique
- Backup toutes les 6 heures
- Conservation 30 jours
- Backup avant chaque mise à jour

### Manuelle
```bash
# Interface de backup
DEMARRER.bat → Option 8

# Ou directement
node scripts/auto-backup.js create
```

## 🚀 Déploiement Production

### VPS Recommandés
- **Contabo** : 5€/mois (recommandé)
- **Hetzner** : 4€/mois 
- **OVH** : 6€/mois

### Guide complet
- **Windows** : `docs/HEBERGEMENT_GUIDE.md`
- **Ubuntu** : `docs/UBUNTU_DEPLOYMENT_GUIDE.md` 🆕

#### 🚀 Script Ubuntu All-in-One
```bash
# Installation et gestion complète
./scripts/ubuntu-manager.sh
```

Fonctionnalités :
- ✅ Installation automatique
- ✅ Mises à jour depuis GitHub
- ✅ Préservation des données
- ✅ Migrations automatiques
- ✅ **Monitoring temps réel complet**
- ✅ Gestion utilisateurs Discord
- ✅ Surveillance base de données
- ✅ Logs centralisés
- ✅ Interface simple

### Docker
```bash
# Build et démarrage
docker-compose up -d

# Logs
docker-compose logs -f
```

## 📊 Monitoring

### Services
```bash
# Status des services
pm2 status

# Logs en temps réel
pm2 logs

# Monitoring web
pm2 monit
```

### Métriques
- Uptime des services
- Usage CPU/RAM
- Taille base de données
- Fréquence des backups

## 🛡️ Sécurité

### Fonctionnalités
- **Tokens API** sécurisés
- **Validation** des entrées
- **Rate limiting** intégré
- **Logs d'audit** complets

### Bonnes pratiques
- Changez les tokens par défaut
- Utilisez HTTPS en production
- Limitez l'accès aux fichiers sensibles
- Monitorer les logs d'erreur

## 🤝 Contribution

### Structure du projet
```
dashboard-multi-modules/
├── web/                 # Interface web PHP
├── bot/                 # Bot Discord Node.js
├── scripts/             # Scripts d'administration
├── config/              # Fichiers de configuration
├── database/            # Base de données SQLite
├── docs/                # Documentation
└── backups/             # Sauvegardes automatiques
```

### Développement
1. Fork le repository
2. Créer une branche feature
3. Commit vos changements
4. Push et créer une Pull Request

## 📝 License

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🆘 Support

### Documentation
- [Guide d'hébergement](docs/HEBERGEMENT_GUIDE.md)
- [Configuration Discord](docs/BOT_DISCORD_GUIDE.md)
- [API Reference](docs/README.md)

### Issues
Pour signaler un bug ou demander une fonctionnalité, créez une [issue](https://github.com/VOTRE-USERNAME/dashboard-multi-modules/issues).

## 🎯 Roadmap

- [ ] Interface mobile responsive
- [ ] Support multi-langues
- [ ] Intégration Twitch
- [ ] API GraphQL
- [ ] Thèmes personnalisés avancés
- [ ] Analytics détaillées

---

**Développé avec ❤️ pour la communauté streaming** 