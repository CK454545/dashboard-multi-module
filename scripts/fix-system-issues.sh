#!/bin/bash

# ================================================================
# 🔧 Script de Correction Automatique des Problèmes Système
# ================================================================
# Ce script corrige automatiquement les problèmes détectés par
# la vérification système complète
# ================================================================

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Fonction pour afficher un message coloré
print_message() {
    echo -e "${2}${1}${NC}"
}

# Fonction pour vérifier si une commande existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# ================================================================
# 1. CORRECTION DU PROBLÈME NGINX
# ================================================================
fix_nginx() {
    print_message "🌐 Correction du problème Nginx..." "$BLUE"
    
    # Vérifier si nginx est installé
    if ! command_exists nginx; then
        print_message "📦 Installation de Nginx..." "$YELLOW"
        sudo apt update >/dev/null 2>&1
        sudo apt install -y nginx >/dev/null 2>&1
    fi
    
    # Vérifier la configuration nginx
    if sudo nginx -t 2>/dev/null; then
        print_message "✅ Configuration Nginx valide" "$GREEN"
    else
        print_message "❌ Configuration Nginx invalide, création d'une configuration par défaut..." "$YELLOW"
        
        # Créer une configuration basique
        sudo tee /etc/nginx/sites-available/default > /dev/null <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    
    root /var/www/html;
    index index.html index.htm index.nginx-debian.html index.php;
    
    server_name _;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
EOF
        sudo ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/
    fi
    
    # Démarrer et activer nginx
    sudo systemctl start nginx 2>/dev/null
    sudo systemctl enable nginx 2>/dev/null
    
    # Vérifier le statut
    if systemctl is-active --quiet nginx; then
        print_message "✅ Nginx démarré et actif" "$GREEN"
    else
        print_message "❌ Impossible de démarrer Nginx" "$RED"
        print_message "💡 Vérifiez les logs: sudo journalctl -u nginx" "$YELLOW"
    fi
}

# ================================================================
# 2. CORRECTION DES PERMISSIONS DE LA BASE DE DONNÉES
# ================================================================
fix_database_permissions() {
    print_message "🗄️ Correction des permissions de la base de données..." "$BLUE"
    
    # Créer le dossier database s'il n'existe pas
    mkdir -p "$PROJECT_DIR/database"
    
    # Définir le chemin de la base de données
    DB_FILE="$PROJECT_DIR/database/database.db"
    
    # Créer la base de données si elle n'existe pas
    if [ ! -f "$DB_FILE" ]; then
        print_message "📦 Création de la base de données..." "$YELLOW"
        
        # Installer sqlite3 si nécessaire
        if ! command_exists sqlite3; then
            sudo apt update >/dev/null 2>&1
            sudo apt install -y sqlite3 >/dev/null 2>&1
        fi
        
        # Créer la base avec le schéma
        sqlite3 "$DB_FILE" <<'EOF'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    discord_id TEXT UNIQUE NOT NULL,
    pseudo TEXT NOT NULL,
    token TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    data TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token)
);

CREATE TABLE IF NOT EXISTS wins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    value INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS module_styles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_name TEXT UNIQUE NOT NULL,
    styles TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
EOF
        print_message "✅ Base de données créée" "$GREEN"
    fi
    
    # Vérifier si www-data existe
    if ! id www-data >/dev/null 2>&1; then
        print_message "👤 Création de l'utilisateur www-data..." "$YELLOW"
        sudo useradd -r -s /bin/false www-data 2>/dev/null
    fi
    
    # Corriger les permissions
    print_message "🔧 Application des permissions correctes..." "$YELLOW"
    
    # Permissions sur le dossier
    sudo chown -R www-data:www-data "$PROJECT_DIR/database" 2>/dev/null
    sudo chmod 755 "$PROJECT_DIR/database" 2>/dev/null
    
    # Permissions sur la base de données
    sudo chown www-data:www-data "$DB_FILE" 2>/dev/null
    sudo chmod 664 "$DB_FILE" 2>/dev/null
    
    # Ajouter l'utilisateur actuel au groupe www-data
    sudo usermod -a -G www-data "$USER" 2>/dev/null
    
    # Test de lecture/écriture
    if sudo -u www-data test -r "$DB_FILE" && sudo -u www-data test -w "$DB_FILE"; then
        print_message "✅ Permissions corrigées avec succès" "$GREEN"
    else
        print_message "⚠️ Application de permissions plus larges..." "$YELLOW"
        sudo chmod 666 "$DB_FILE" 2>/dev/null
        sudo chmod 777 "$PROJECT_DIR/database" 2>/dev/null
        print_message "✅ Permissions élargies appliquées" "$GREEN"
    fi
}

# ================================================================
# 3. CRÉATION DU FICHIER CONFIG.JSON
# ================================================================
create_config_file() {
    print_message "📄 Création du fichier de configuration..." "$BLUE"
    
    # Créer le dossier config
    mkdir -p "$PROJECT_DIR/config"
    
    CONFIG_FILE="$PROJECT_DIR/config/config.json"
    
    if [ ! -f "$CONFIG_FILE" ]; then
        print_message "📝 Création de config.json avec configuration par défaut..." "$YELLOW"
        
        # Créer un fichier de configuration par défaut
        cat > "$CONFIG_FILE" <<'EOF'
{
    "bot": {
        "token": "YOUR_BOT_TOKEN_HERE",
        "clientId": "YOUR_CLIENT_ID_HERE",
        "guildId": "YOUR_GUILD_ID_HERE"
    },
    "website": {
        "url": "http://localhost",
        "port": 80
    },
    "database": {
        "type": "sqlite",
        "file": "database/database.db"
    },
    "modules": {
        "timer": {
            "enabled": true,
            "defaultDuration": 300
        },
        "wins": {
            "enabled": true
        },
        "teams": {
            "enabled": true
        }
    },
    "backup": {
        "enabled": true,
        "interval": "0 3 * * *",
        "maxBackups": 7
    },
    "update": {
        "enabled": true,
        "checkInterval": "0 */6 * * *"
    }
}
EOF
        
        # Corriger les permissions
        sudo chown www-data:www-data "$CONFIG_FILE" 2>/dev/null
        sudo chmod 664 "$CONFIG_FILE" 2>/dev/null
        
        print_message "✅ Fichier config.json créé" "$GREEN"
        print_message "⚠️ N'oubliez pas de configurer votre token Discord et autres paramètres!" "$YELLOW"
    else
        print_message "✅ Fichier config.json déjà existant" "$GREEN"
    fi
}

# ================================================================
# 4. INSTALLATION ET CONFIGURATION D'UFW (PARE-FEU)
# ================================================================
setup_ufw() {
    print_message "🔒 Configuration du pare-feu UFW..." "$BLUE"
    
    # Installer UFW si nécessaire
    if ! command_exists ufw; then
        print_message "📦 Installation d'UFW..." "$YELLOW"
        sudo apt update >/dev/null 2>&1
        sudo apt install -y ufw >/dev/null 2>&1
    fi
    
    # Configurer les règles de base
    print_message "🔧 Configuration des règles de pare-feu..." "$YELLOW"
    
    # Autoriser SSH (important!)
    sudo ufw allow 22/tcp comment 'SSH' 2>/dev/null
    
    # Autoriser HTTP et HTTPS
    sudo ufw allow 80/tcp comment 'HTTP' 2>/dev/null
    sudo ufw allow 443/tcp comment 'HTTPS' 2>/dev/null
    
    # Politique par défaut
    sudo ufw default deny incoming 2>/dev/null
    sudo ufw default allow outgoing 2>/dev/null
    
    # Ne pas activer UFW automatiquement (risque de perdre l'accès SSH)
    print_message "⚠️ UFW configuré mais non activé" "$YELLOW"
    print_message "💡 Pour activer UFW, exécutez: sudo ufw enable" "$CYAN"
    print_message "⚠️ Assurez-vous que le port SSH est bien ouvert avant!" "$RED"
}

# ================================================================
# 5. CRÉATION D'UN BACKUP INITIAL
# ================================================================
create_initial_backup() {
    print_message "💾 Création d'un backup initial..." "$BLUE"
    
    # Créer le dossier de backup
    BACKUP_DIR="$PROJECT_DIR/backups"
    mkdir -p "$BACKUP_DIR/database"
    
    # Sauvegarder la base de données si elle existe
    DB_FILE="$PROJECT_DIR/database/database.db"
    if [ -f "$DB_FILE" ]; then
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        cp "$DB_FILE" "$BACKUP_DIR/database/database_${TIMESTAMP}.db"
        print_message "✅ Backup créé: database_${TIMESTAMP}.db" "$GREEN"
    else
        print_message "⚠️ Pas de base de données à sauvegarder" "$YELLOW"
    fi
}

# ================================================================
# 6. VÉRIFICATION ET INSTALLATION DES DÉPENDANCES
# ================================================================
install_dependencies() {
    print_message "📦 Vérification des dépendances..." "$BLUE"
    
    # Mettre à jour la liste des paquets
    print_message "🔄 Mise à jour de la liste des paquets..." "$YELLOW"
    sudo apt update >/dev/null 2>&1
    
    # Liste des paquets nécessaires
    PACKAGES=(
        "nginx"
        "php8.1-fpm"
        "php8.1-sqlite3"
        "php8.1-mbstring"
        "php8.1-curl"
        "php8.1-json"
        "sqlite3"
        "jq"
        "curl"
        "git"
    )
    
    # Installer les paquets manquants
    for package in "${PACKAGES[@]}"; do
        if ! dpkg -l | grep -q "^ii  $package"; then
            print_message "📦 Installation de $package..." "$YELLOW"
            sudo apt install -y "$package" >/dev/null 2>&1
        fi
    done
    
    # Installer Node.js et npm si nécessaire
    if ! command_exists node; then
        print_message "📦 Installation de Node.js..." "$YELLOW"
        curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash - >/dev/null 2>&1
        sudo apt install -y nodejs >/dev/null 2>&1
    fi
    
    # Installer PM2 si nécessaire
    if ! command_exists pm2; then
        print_message "📦 Installation de PM2..." "$YELLOW"
        sudo npm install -g pm2 >/dev/null 2>&1
    fi
    
    print_message "✅ Toutes les dépendances sont installées" "$GREEN"
}

# ================================================================
# FONCTION PRINCIPALE
# ================================================================
main() {
    clear
    print_message "🔧 CORRECTION AUTOMATIQUE DES PROBLÈMES SYSTÈME" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    echo ""
    
    print_message "Ce script va corriger automatiquement les problèmes suivants :" "$CYAN"
    echo "  • Problème avec Nginx"
    echo "  • Permissions de la base de données"
    echo "  • Fichier config.json manquant"
    echo "  • UFW non installé"
    echo "  • Absence de backups"
    echo ""
    
    read -p "Voulez-vous continuer ? (o/N): " confirm
    
    if [[ ! $confirm =~ ^[Oo]$ ]]; then
        print_message "❌ Correction annulée" "$RED"
        exit 0
    fi
    
    echo ""
    
    # Exécuter les corrections
    install_dependencies
    echo ""
    
    fix_nginx
    echo ""
    
    fix_database_permissions
    echo ""
    
    create_config_file
    echo ""
    
    setup_ufw
    echo ""
    
    create_initial_backup
    echo ""
    
    # Résumé final
    print_message "═══════════════════════════════════════════════════════════════" "$BLUE"
    print_message "✅ CORRECTIONS TERMINÉES" "$GREEN"
    echo ""
    
    print_message "📋 Résumé des actions effectuées :" "$CYAN"
    
    # Vérifier Nginx
    if systemctl is-active --quiet nginx; then
        echo "  ✅ Nginx : actif et fonctionnel"
    else
        echo "  ❌ Nginx : nécessite une intervention manuelle"
    fi
    
    # Vérifier la base de données
    DB_FILE="$PROJECT_DIR/database/database.db"
    if [ -f "$DB_FILE" ] && [ -r "$DB_FILE" ] && [ -w "$DB_FILE" ]; then
        echo "  ✅ Base de données : créée avec permissions correctes"
    else
        echo "  ⚠️ Base de données : vérifiez les permissions"
    fi
    
    # Vérifier config.json
    if [ -f "$PROJECT_DIR/config/config.json" ]; then
        echo "  ✅ config.json : créé (à configurer)"
    else
        echo "  ❌ config.json : échec de création"
    fi
    
    # UFW
    if command_exists ufw; then
        echo "  ✅ UFW : installé et configuré (non activé)"
    else
        echo "  ❌ UFW : installation échouée"
    fi
    
    # Backup
    if [ -d "$PROJECT_DIR/backups/database" ] && [ "$(ls -A $PROJECT_DIR/backups/database 2>/dev/null)" ]; then
        echo "  ✅ Backup : créé avec succès"
    else
        echo "  ⚠️ Backup : aucun backup créé"
    fi
    
    echo ""
    print_message "💡 Prochaines étapes :" "$YELLOW"
    echo "  1. Éditez $PROJECT_DIR/config/config.json avec vos paramètres"
    echo "  2. Lancez le script ubuntu-manager.sh pour démarrer les services"
    echo "  3. Activez UFW si nécessaire : sudo ufw enable"
    echo ""
    
    print_message "🔄 Pour vérifier à nouveau le système :" "$CYAN"
    echo "  ./scripts/ubuntu-manager.sh"
    echo "  Puis choisissez l'option 14 (Vérification système complète)"
    echo ""
}

# Lancer le script
main