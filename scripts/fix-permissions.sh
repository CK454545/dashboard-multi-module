#!/bin/bash

# ================================================================
# 🔧 Script de Correction Automatique des Permissions
# ================================================================

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$PROJECT_DIR/config/config.json"
DB_FILE="$PROJECT_DIR/database/database.db"

print_message() {
    echo -e "${2}${1}${NC}"
}

print_message "🔧 CORRECTION AUTOMATIQUE DES PERMISSIONS" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# 1. Vérifier et corriger la configuration
print_message "📋 Vérification de la configuration..." "$CYAN"

if [ -f "$CONFIG_FILE" ]; then
    # Vérifier que le chemin de la base de données est correct
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

# 2. Créer la base de données si elle n'existe pas
print_message "🗄️  Vérification de la base de données..." "$CYAN"

mkdir -p "$(dirname "$DB_FILE")"

if [ ! -f "$DB_FILE" ]; then
    print_message "⚠️  Création de la base de données manquante..." "$YELLOW"
    
    # Installer sqlite3 si nécessaire
    if ! command -v sqlite3 >/dev/null 2>&1; then
        sudo apt update >/dev/null 2>&1
        sudo apt install -y sqlite3 >/dev/null 2>&1
    fi
    
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

# 3. Corriger les permissions de manière agressive
print_message "🔑 Correction des permissions..." "$CYAN"

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

# 4. Permissions plus larges si nécessaire (fallback)
print_message "🔧 Application de permissions de fallback..." "$YELLOW"
sudo chmod 666 "$DB_FILE" 2>/dev/null
sudo chmod 777 "$(dirname "$DB_FILE")" 2>/dev/null

# 5. Vérifier que les permissions sont correctes
print_message "✅ Vérification finale..." "$CYAN"

if [ -r "$DB_FILE" ] && [ -w "$DB_FILE" ]; then
    print_message "✅ Permissions de lecture/écriture OK" "$GREEN"
else
    print_message "❌ Problème de permissions persistant" "$RED"
    print_message "🔧 Tentative de correction manuelle..." "$YELLOW"
    sudo chmod 777 "$DB_FILE" 2>/dev/null
    sudo chmod 777 "$(dirname "$DB_FILE")" 2>/dev/null
fi

# 6. Test d'écriture dans la base de données
print_message "🧪 Test d'écriture dans la base de données..." "$CYAN"
if sqlite3 "$DB_FILE" "CREATE TABLE IF NOT EXISTS test_permissions (id INTEGER); DROP TABLE test_permissions;" 2>/dev/null; then
    print_message "✅ Test d'écriture réussi" "$GREEN"
else
    print_message "❌ Test d'écriture échoué" "$RED"
    print_message "🔧 Application de permissions d'urgence..." "$YELLOW"
    sudo chmod 777 "$DB_FILE" 2>/dev/null
    sudo chown $USER:$USER "$DB_FILE" 2>/dev/null
fi

# 7. Vérifier l'intégrité de la base de données
print_message "🔍 Vérification de l'intégrité..." "$CYAN"
INTEGRITY=$(sqlite3 "$DB_FILE" "PRAGMA integrity_check;" 2>/dev/null)
if [ "$INTEGRITY" = "ok" ]; then
    print_message "✅ Intégrité de la base de données OK" "$GREEN"
else
    print_message "❌ Problème d'intégrité détecté" "$RED"
fi

# 8. Redémarrer les services
print_message "🔄 Redémarrage des services..." "$CYAN"

# Redémarrer PHP-FPM
sudo systemctl restart php8.1-fpm 2>/dev/null
print_message "✅ PHP-FPM redémarré" "$GREEN"

# Redémarrer Nginx
sudo systemctl restart nginx 2>/dev/null
print_message "✅ Nginx redémarré" "$GREEN"

# Redémarrer les services PM2
pm2 restart all 2>/dev/null
print_message "✅ Services PM2 redémarrés" "$GREEN"

# 9. Vérification finale
print_message "📊 Vérification finale du système..." "$CYAN"
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

echo ""
print_message "🎉 Correction terminée!" "$GREEN"
print_message "💡 Si des problèmes persistent, relancez ce script" "$CYAN" 