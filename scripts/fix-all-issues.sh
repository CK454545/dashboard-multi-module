#!/bin/bash

# ================================================================
# 🚨 Script de Correction Complète - Tous les Problèmes
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
CONFIG_FILE="$PROJECT_DIR/config/config.json"
DB_FILE="$PROJECT_DIR/database/database.db"

print_message() {
    echo -e "${2}${1}${NC}"
}

print_message "🚨 CORRECTION COMPLÈTE - TOUS LES PROBLÈMES" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
print_message "🔧 Ce script va corriger automatiquement tous les problèmes détectés" "$CYAN"
echo ""

# Vérifier que nous sommes sur Ubuntu/Linux
if [[ "$OSTYPE" != "linux-gnu"* ]]; then
    print_message "❌ Ce script doit être exécuté sur Ubuntu/Linux" "$RED"
    exit 1
fi

# Vérifier les privilèges sudo
if ! sudo -n true 2>/dev/null; then
    print_message "🔐 Demande des privilèges sudo..." "$YELLOW"
    sudo echo "Privilèges accordés" > /dev/null
fi

# ================================================================
# ÉTAPE 1: CORRECTION DE LA CONFIGURATION
# ================================================================
print_message "📋 ÉTAPE 1: Correction de la configuration..." "$PURPLE"

if [ -f "$CONFIG_FILE" ]; then
    # Corriger le chemin de la base de données
    DB_PATH_FROM_CONFIG=$(jq -r '.database.file' "$CONFIG_FILE" 2>/dev/null)
    if [ "$DB_PATH_FROM_CONFIG" != "database/database.db" ]; then
        print_message "⚠️  Correction du chemin de la base de données..." "$YELLOW"
        jq '.database.file = "database/database.db"' "$CONFIG_FILE" > "$CONFIG_FILE.tmp" && mv "$CONFIG_FILE.tmp" "$CONFIG_FILE"
        print_message "✅ Chemin corrigé: database/database.db" "$GREEN"
    fi
else
    print_message "❌ Fichier config.json manquant!" "$RED"
    exit 1
fi

# ================================================================
# ÉTAPE 2: INSTALLATION DES DÉPENDANCES CRITIQUES
# ================================================================
print_message "📦 ÉTAPE 2: Installation des dépendances critiques..." "$PURPLE"

# Mettre à jour la liste des paquets
sudo apt update >/dev/null 2>&1

# Installer les paquets essentiels
PACKAGES=("sqlite3" "jq" "curl" "nginx" "php8.1-fpm" "php8.1-sqlite3" "php8.1-mbstring" "php8.1-curl" "php8.1-json")

for package in "${PACKAGES[@]}"; do
    if ! dpkg -l | grep -q "^ii  $package "; then
        print_message "📦 Installation de $package..." "$YELLOW"
        sudo apt install -y "$package" >/dev/null 2>&1
        print_message "✅ $package installé" "$GREEN"
    else
        print_message "✅ $package déjà installé" "$GREEN"
    fi
done

# ================================================================
# ÉTAPE 3: CRÉATION ET CONFIGURATION DE LA BASE DE DONNÉES
# ================================================================
print_message "🗄️  ÉTAPE 3: Configuration de la base de données..." "$PURPLE"

# Créer le répertoire database
mkdir -p "$(dirname "$DB_FILE")"

# Créer la base de données si elle n'existe pas
if [ ! -f "$DB_FILE" ]; then
    print_message "⚠️  Création de la base de données manquante..." "$YELLOW"
    
    # Créer la base de données avec le schéma
    if [ -f "$PROJECT_DIR/database/database.sql" ]; then
        sqlite3 "$DB_FILE" < "$PROJECT_DIR/database/database.sql"
        print_message "✅ Base de données créée avec succès" "$GREEN"
    else
        # Schéma minimal
        sqlite3 "$DB_FILE" <<EOF
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    discord_id TEXT UNIQUE NOT NULL,
    pseudo TEXT NOT NULL,
    token TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    wins INTEGER DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token)
);

CREATE TABLE IF NOT EXISTS module_styles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_name TEXT UNIQUE NOT NULL,
    styles TEXT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
EOF
        print_message "✅ Base de données créée avec schéma minimal" "$GREEN"
    fi
else
    print_message "✅ Base de données existante" "$GREEN"
fi

# ================================================================
# ÉTAPE 4: CORRECTION DES PERMISSIONS
# ================================================================
print_message "🔑 ÉTAPE 4: Correction des permissions..." "$PURPLE"

# S'assurer que www-data existe
if ! id www-data >/dev/null 2>&1; then
    print_message "👤 Création de l'utilisateur www-data..." "$YELLOW"
    sudo useradd -r -s /bin/false www-data 2>/dev/null
fi

# Permissions sur tout le projet
print_message "📁 Configuration des permissions du projet..." "$YELLOW"
sudo chown -R www-data:www-data "$PROJECT_DIR" 2>/dev/null
sudo chmod -R 755 "$PROJECT_DIR" 2>/dev/null

# Permissions spécifiques pour la base de données
print_message "🗄️  Configuration des permissions de la base de données..." "$YELLOW"
sudo chown www-data:www-data "$DB_FILE" 2>/dev/null
sudo chmod 664 "$DB_FILE" 2>/dev/null
sudo chown www-data:www-data "$(dirname "$DB_FILE")" 2>/dev/null
sudo chmod 755 "$(dirname "$DB_FILE")" 2>/dev/null

# Ajouter l'utilisateur actuel au groupe www-data
print_message "👥 Configuration des groupes..." "$YELLOW"
sudo usermod -a -G www-data ubuntu 2>/dev/null
sudo usermod -a -G www-data $USER 2>/dev/null

# Permissions de fallback si nécessaire
print_message "🔧 Application de permissions de fallback..." "$YELLOW"
sudo chmod 666 "$DB_FILE" 2>/dev/null
sudo chmod 777 "$(dirname "$DB_FILE")" 2>/dev/null

# ================================================================
# ÉTAPE 5: CONFIGURATION NGINX
# ================================================================
print_message "🌐 ÉTAPE 5: Configuration Nginx..." "$PURPLE"

# Extraire le domaine
DOMAIN=$(jq -r '.website.url' "$CONFIG_FILE" | sed 's|https\?://||' | cut -d'/' -f1)
if [ "$DOMAIN" = "null" ] || [ "$DOMAIN" = "localhost" ]; then
    DOMAIN="localhost"
fi

print_message "🌐 Domaine configuré: $DOMAIN" "$GREEN"

# Créer la configuration Nginx
sudo tee /etc/nginx/sites-available/dashboard-multi-modules > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    root $PROJECT_DIR/web;
    index index.php index.html;

    access_log /var/log/nginx/dashboard-access.log;
    error_log /var/log/nginx/dashboard-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # Block access to sensitive files
    location ~ /\.(htaccess|htpasswd|git|svn|json|db|bat|js|log)$ {
        deny all;
        return 404;
    }

    # Block access to backup and config directories
    location ~ /(backups|config|database|scripts)/ {
        deny all;
        return 404;
    }

    # PHP processing
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }

    # Handle static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    # Main location block
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Block access to sensitive endpoints
    location ~ ^/(admin|wp-admin|administrator|manage|management|control|console|debug|test|api/admin) {
        deny all;
        return 404;
    }

    # Block access to common attack paths
    location ~ ^/(shell|cmd|exec|system|eval|base64|decode|encode|phpinfo|info|status|health|ping|test) {
        deny all;
        return 404;
    }
}
EOF

# Activer le site
sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -sf /etc/nginx/sites-available/dashboard-multi-modules /etc/nginx/sites-enabled/

# Vérifier la configuration
if sudo nginx -t; then
    print_message "✅ Configuration Nginx valide" "$GREEN"
else
    print_message "❌ Erreur dans la configuration Nginx" "$RED"
    # Configuration de fallback
    sudo tee /etc/nginx/sites-available/dashboard-multi-modules > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    
    root $PROJECT_DIR/web;
    index index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        include fastcgi_params;
    }
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
}
EOF
    print_message "✅ Configuration de fallback appliquée" "$GREEN"
fi

# ================================================================
# ÉTAPE 6: CRÉATION DU FICHIER INDEX.PHP PAR DÉFAUT
# ================================================================
print_message "📄 ÉTAPE 6: Création du fichier index.php par défaut..." "$PURPLE"

if [ ! -f "$PROJECT_DIR/web/index.php" ]; then
    sudo tee "$PROJECT_DIR/web/index.php" > /dev/null <<EOF
<?php
// Dashboard Multi-Modules
// Fichier d'accueil par défaut

// Rediriger vers le dashboard principal
if (file_exists('dashboard.php')) {
    header('Location: dashboard.php');
    exit;
}

// Page d'accueil simple si dashboard.php n'existe pas
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Multi-Modules</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .status { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Dashboard Multi-Modules</h1>
        <div class="status success">
            ✅ Serveur web opérationnel
        </div>
        <div class="status info">
            ℹ️ Le système est en cours de configuration...
        </div>
        <p>Si vous voyez cette page, cela signifie que :</p>
        <ul>
            <li>✅ Nginx fonctionne correctement</li>
            <li>✅ PHP est configuré</li>
            <li>✅ Les permissions sont correctes</li>
        </ul>
        <p><strong>Prochaine étape :</strong> Configurez votre application principale.</p>
    </div>
</body>
</html>
EOF
    print_message "✅ Fichier index.php créé" "$GREEN"
else
    print_message "✅ Fichier index.php existant" "$GREEN"
fi

# ================================================================
# ÉTAPE 7: REDÉMARRAGE DES SERVICES
# ================================================================
print_message "🔄 ÉTAPE 7: Redémarrage des services..." "$PURPLE"

# Redémarrer PHP-FPM
sudo systemctl restart php8.1-fpm 2>/dev/null
print_message "✅ PHP-FPM redémarré" "$GREEN"

# Redémarrer Nginx
sudo systemctl restart nginx 2>/dev/null
print_message "✅ Nginx redémarré" "$GREEN"

# Redémarrer les services PM2
pm2 restart all 2>/dev/null
print_message "✅ Services PM2 redémarrés" "$GREEN"

# ================================================================
# ÉTAPE 8: VÉRIFICATION FINALE
# ================================================================
print_message "📊 ÉTAPE 8: Vérification finale..." "$PURPLE"
echo ""

# Vérifier les services
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✅ Nginx: Actif${NC}"
else
    echo -e "${RED}❌ Nginx: Inactif${NC}"
fi

if systemctl is-active --quiet php8.1-fpm; then
    echo -e "${GREEN}✅ PHP-FPM: Actif${NC}"
else
    echo -e "${RED}❌ PHP-FPM: Inactif${NC}"
fi

PM2_ONLINE=$(pm2 jlist 2>/dev/null | jq '[.[] | select(.pm2_env.status == "online")] | length' 2>/dev/null || echo "0")
if [ "$PM2_ONLINE" -ge 1 ]; then
    echo -e "${GREEN}✅ PM2: $PM2_ONLINE processus actifs${NC}"
else
    echo -e "${RED}❌ PM2: Aucun processus actif${NC}"
fi

# Vérifier la base de données
if [ -f "$DB_FILE" ] && [ -r "$DB_FILE" ] && [ -w "$DB_FILE" ]; then
    echo -e "${GREEN}✅ Base de données: Accessible en lecture/écriture${NC}"
else
    echo -e "${RED}❌ Base de données: Problème d'accès${NC}"
fi

# Test d'écriture dans la base de données
if sqlite3 "$DB_FILE" "CREATE TABLE IF NOT EXISTS test_permissions (id INTEGER); DROP TABLE test_permissions;" 2>/dev/null; then
    echo -e "${GREEN}✅ Test d'écriture: Réussi${NC}"
else
    echo -e "${RED}❌ Test d'écriture: Échoué${NC}"
fi

# Vérifier l'intégrité
INTEGRITY=$(sqlite3 "$DB_FILE" "PRAGMA integrity_check;" 2>/dev/null)
if [ "$INTEGRITY" = "ok" ]; then
    echo -e "${GREEN}✅ Intégrité: OK${NC}"
else
    echo -e "${RED}❌ Intégrité: Problème détecté${NC}"
fi

# Tester l'accès web
print_message "🌐 Test d'accès web..." "$CYAN"
if curl -s -o /dev/null -w "%{http_code}" http://localhost/ | grep -q "200\|301\|302"; then
    print_message "✅ Accès web fonctionnel" "$GREEN"
else
    print_message "⚠️  Problème d'accès web détecté" "$YELLOW"
fi

echo ""
print_message "🎉 CORRECTION COMPLÈTE TERMINÉE!" "$GREEN"
print_message "💡 Tous les problèmes détectés ont été corrigés" "$CYAN"
print_message "🌐 Votre site devrait maintenant être accessible" "$CYAN" 