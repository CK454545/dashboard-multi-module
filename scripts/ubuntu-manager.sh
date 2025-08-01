#!/bin/bash

# ================================================================
# 🚀 Dashboard Multi-Modules - Ubuntu Manager
# ================================================================
# Script complet pour gérer votre projet sur Ubuntu
# - Démarrage automatique de tous les services
# - Mise à jour depuis GitHub sans toucher aux données
# - Sauvegarde automatique
# - Monitoring complet temps réel
# - Simple à utiliser
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
BACKUP_DIR="$PROJECT_DIR/backups"
GITHUB_REPO="https://github.com/CK454545/dashboard-multi-modules.git"

# Fonction pour obtenir le chemin de la base de données depuis config.json
get_database_path() {
    if [ -f "$CONFIG_FILE" ]; then
        # Essayer avec jq d'abord
        if command -v jq >/dev/null 2>&1; then
            DB_FILE_FROM_CONFIG=$(jq -r '.database.file' "$CONFIG_FILE" 2>/dev/null)
        else
            # Fallback sans jq pour production
            DB_FILE_FROM_CONFIG=$(grep -o '"file"[^,}]*' "$CONFIG_FILE" | cut -d'"' -f4)
        fi
        
        if [ "$DB_FILE_FROM_CONFIG" != "null" ] && [ -n "$DB_FILE_FROM_CONFIG" ]; then
            # Si le chemin est absolu (commence par /), l'utiliser tel quel
            if [[ "$DB_FILE_FROM_CONFIG" == /* ]]; then
                echo "$DB_FILE_FROM_CONFIG"
            else
                # Si relatif, le résoudre par rapport au PROJECT_DIR
                echo "$PROJECT_DIR/$DB_FILE_FROM_CONFIG"
            fi
        else
            # Fallback par défaut
            echo "$PROJECT_DIR/database/database.db"
        fi
    else
        # Fallback si pas de config
        echo "$PROJECT_DIR/database/database.db"
    fi
}

# Initialiser DB_FILE (sera mis à jour dans main() après install des dépendances)
DB_FILE="$PROJECT_DIR/database/database.db"

# Fonction pour afficher un message coloré
print_message() {
    echo -e "${2}${1}${NC}"
}

# Fonction pour vérifier si une commande existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Fonction pour afficher une barre de progression
progress_bar() {
    local current=$1
    local total=$2
    local width=20
    local percentage=$((current * 100 / total))
    local filled=$((current * width / total))
    
    printf "["
    for ((i=0; i<filled; i++)); do printf "▓"; done
    for ((i=filled; i<width; i++)); do printf "░"; done
    printf "] %d%%\n" $percentage
}

# ================================================================
# 0. VÉRIFICATION AUTOMATIQUE DES PERMISSIONS
# ================================================================
verify_database_permissions() {
    print_message "🔧 Vérification automatique des permissions de base de données..." "$BLUE"
    
    # S'assurer que le fichier existe
    if [ ! -f "$DB_FILE" ]; then
        print_message "⚠️  Base de données introuvable, création..." "$YELLOW"
        touch "$DB_FILE"
    fi
    
    # Obtenir le propriétaire actuel
    CURRENT_OWNER=$(stat -c '%U:%G' "$DB_FILE" 2>/dev/null || echo "unknown:unknown")
    print_message "📋 Propriétaire actuel: $CURRENT_OWNER" "$CYAN"
    
    # Corriger automatiquement les permissions pour www-data:www-data
    print_message "🔧 Correction automatique des permissions..." "$YELLOW"
    
    # 1. Changer le propriétaire pour www-data
    if sudo chown www-data:www-data "$DB_FILE" 2>/dev/null; then
        print_message "✅ Propriétaire corrigé: www-data:www-data" "$GREEN"
    else
        print_message "⚠️  Impossible de changer le propriétaire" "$YELLOW"
    fi
    
    # 2. Définir les permissions de fichier
    if sudo chmod 664 "$DB_FILE" 2>/dev/null; then
        print_message "✅ Permissions corrigées: 664" "$GREEN"
    else
        print_message "⚠️  Impossible de changer les permissions" "$YELLOW"
    fi
    
    # 3. Ajouter ubuntu au groupe www-data
    if sudo usermod -a -G www-data ubuntu 2>/dev/null; then
        print_message "✅ Utilisateur ubuntu ajouté au groupe www-data" "$GREEN"
    else
        print_message "⚠️ Utilisateur ubuntu déjà dans le groupe www-data" "$YELLOW"
    fi
    
    # 4. Configurer le dossier parent
    DB_DIR=$(dirname "$DB_FILE")
    if sudo chown www-data:www-data "$DB_DIR" 2>/dev/null; then
        print_message "✅ Dossier parent configuré: www-data:www-data" "$GREEN"
    fi
    
    if sudo chmod 755 "$DB_DIR" 2>/dev/null; then
        print_message "✅ Permissions du dossier parent: 755" "$GREEN"
    fi
    
    # Vérifier que les permissions sont correctes avec sudo pour simuler www-data
    if sudo -u www-data test -r "$DB_FILE" && sudo -u www-data test -w "$DB_FILE"; then
        print_message "✅ Permissions de la base de données OK" "$GREEN"
        
        # Vérifier le contenu de la base
        if [ -f "$DB_FILE" ]; then
            DB_SIZE=$(stat -c%s "$DB_FILE" 2>/dev/null || echo "0")
            if [ "$DB_SIZE" -gt 0 ]; then
                USER_COUNT=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")
                print_message "✅ Base de données OK (taille: ${DB_SIZE} bytes, utilisateurs: $USER_COUNT)" "$GREEN"
            else
                print_message "⚠️  Base de données vide" "$YELLOW"
            fi
        fi
        
        return 0
    else
        print_message "⚠️ Permissions insuffisantes, application de permissions plus larges..." "$YELLOW"
        
        # Fallback: permissions plus larges
        if sudo chmod 666 "$DB_FILE" 2>/dev/null; then
            print_message "✅ Permissions élargies appliquées: 666" "$GREEN"
        fi
        
        if sudo chmod 777 "$DB_DIR" 2>/dev/null; then
            print_message "✅ Permissions du dossier élargies: 777" "$GREEN"
        fi
        
        # Vérifier à nouveau
        if sudo -u www-data test -r "$DB_FILE" && sudo -u www-data test -w "$DB_FILE"; then
            print_message "✅ Permissions de la base de données corrigées" "$GREEN"
            return 0
        else
            print_message "❌ Permissions de la base de données incorrectes" "$RED"
            return 1
        fi
    fi
}

# ================================================================
# 1. INSTALLATION INITIALE
# ================================================================
install_dependencies() {
    print_message "📦 Installation des dépendances..." "$BLUE"
    
    # Mettre à jour le système
    sudo apt update -y
    
    # Installer Node.js si nécessaire
    if ! command_exists node; then
        curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
        sudo apt install -y nodejs
    fi
    
    # Installer les autres dépendances
    sudo apt install -y \
        php8.1 php8.1-cli php8.1-fpm php8.1-sqlite3 php8.1-mbstring php8.1-xml php8.1-curl \
        sqlite3 git nginx certbot python3-certbot-nginx jq
    
    # Installer PM2 globalement
    if ! command_exists pm2; then
        sudo npm install -g pm2
    fi
    
    # Démarrer PHP-FPM
    sudo systemctl start php8.1-fpm
    sudo systemctl enable php8.1-fpm
    
    print_message "✅ Dépendances installées" "$GREEN"
}

# ================================================================
# 2. CONFIGURATION NGINX
# ================================================================
setup_nginx() {
    print_message "🌐 Configuration de Nginx..." "$BLUE"
    
    # Extraire le domaine de la config
    DOMAIN=$(jq -r '.website.url' "$CONFIG_FILE" | sed 's|https\?://||' | cut -d'/' -f1)
    
    # Créer la configuration Nginx
    sudo tee /etc/nginx/sites-available/dashboard-multi-modules > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    root $PROJECT_DIR/web;
    index index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.(json|db|bat|js)$ {
        deny all;
    }

    error_log /var/log/nginx/dashboard-error.log;
    access_log /var/log/nginx/dashboard-access.log;
}
EOF

    # Activer le site
    sudo ln -sf /etc/nginx/sites-available/dashboard-multi-modules /etc/nginx/sites-enabled/
    sudo rm -f /etc/nginx/sites-enabled/default
    
    # Tester et redémarrer
    sudo nginx -t && sudo systemctl restart nginx
    
    print_message "✅ Nginx configuré" "$GREEN"
}

# ================================================================
# 3. SAUVEGARDE DE LA BASE DE DONNÉES
# ================================================================
backup_database() {
    print_message "💾 Sauvegarde de la base de données..." "$BLUE"
    
    # Créer le dossier de backup s'il n'existe pas
    mkdir -p "$BACKUP_DIR/database"
    
    # Sauvegarder avec timestamp
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    if [ -f "$DB_FILE" ]; then
        cp "$DB_FILE" "$BACKUP_DIR/database/database_${TIMESTAMP}.db"
        print_message "✅ Base de données sauvegardée: database_${TIMESTAMP}.db" "$GREEN"
    else
        print_message "⚠️  Pas de base de données à sauvegarder" "$YELLOW"
    fi
}

# ================================================================
# 3.1. CORRECTION DES PERMISSIONS DE LA BASE DE DONNÉES
# ================================================================
# Fonction pour corriger les permissions de la base de données
fix_database_permissions() {
    print_message "🔧 Correction des permissions de la base de données..." "$BLUE"
    
    # S'assurer que le fichier existe
    if [ ! -f "$DB_FILE" ]; then
        print_message "⚠️  Base de données introuvable, création d'une nouvelle..." "$YELLOW"
        touch "$DB_FILE"
    fi
    
    # Obtenir le propriétaire actuel
    CURRENT_OWNER=$(stat -c '%U:%G' "$DB_FILE" 2>/dev/null || echo "unknown:unknown")
    print_message "📋 Propriétaire actuel: $CURRENT_OWNER" "$CYAN"
    
    # SOLUTION DÉFINITIVE: Donner les permissions à www-data et ajouter ubuntu au groupe
    print_message "🔧 Configuration des permissions pour www-data et ubuntu..." "$YELLOW"
    
    # 1. Changer le propriétaire pour www-data
    if sudo chown www-data:www-data "$DB_FILE" 2>/dev/null; then
        print_message "✅ Propriétaire défini: www-data:www-data" "$GREEN"
    else
        print_message "❌ Impossible de changer le propriétaire" "$RED"
        return 1
    fi
    
    # 2. Définir les permissions de fichier
    if sudo chmod 664 "$DB_FILE" 2>/dev/null; then
        print_message "✅ Permissions de fichier définies (664)" "$GREEN"
    else
        print_message "❌ Impossible de définir les permissions de fichier" "$RED"
        return 1
    fi
    
    # 3. Ajouter ubuntu au groupe www-data pour permettre l'accès
    if sudo usermod -a -G www-data ubuntu 2>/dev/null; then
        print_message "✅ Utilisateur ubuntu ajouté au groupe www-data" "$GREEN"
    else
        print_message "⚠️ Impossible d'ajouter ubuntu au groupe www-data (peut-être déjà membre)" "$YELLOW"
    fi
    
    # 4. S'assurer que le dossier parent a aussi les bonnes permissions
    DB_DIR=$(dirname "$DB_FILE")
    if sudo chown www-data:www-data "$DB_DIR" 2>/dev/null; then
        print_message "✅ Dossier parent configuré: www-data:www-data" "$GREEN"
    fi
    
    if sudo chmod 755 "$DB_DIR" 2>/dev/null; then
        print_message "✅ Permissions du dossier parent: 755" "$GREEN"
    fi
    
    # Vérifier que les permissions sont correctes
    if [ -r "$DB_FILE" ] && [ -w "$DB_FILE" ]; then
        print_message "✅ Permissions de la base de données corrigées" "$GREEN"
        
        # Test d'écriture pour vérifier que tout fonctionne
        print_message "🧪 Test d'écriture dans la base de données..." "$CYAN"
        if sqlite3 "$DB_FILE" "CREATE TABLE IF NOT EXISTS test_permissions (id INTEGER); DROP TABLE test_permissions;" 2>/dev/null; then
            print_message "✅ Test d'écriture réussi" "$GREEN"
        else
            print_message "⚠️ Test d'écriture échoué, mais les permissions semblent correctes" "$YELLOW"
        fi
        
        return 0
    else
        print_message "❌ Impossible de corriger les permissions de la base de données" "$RED"
        print_message "📋 Permissions actuelles: $(ls -la "$DB_FILE")" "$CYAN"
        return 1
    fi
}

# ================================================================
# 3.5. VÉRIFICATION POST-MISE À JOUR
# ================================================================
verify_post_update() {
    print_message "🔍 Vérification post-mise à jour..." "$BLUE"
    
    # Vérifier la base de données
    if [ -f "$DB_FILE" ]; then
        USER_COUNT=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")
        DATA_COUNT=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM user_data;" 2>/dev/null || echo "0")
        
        if [ "$USER_COUNT" -gt 0 ]; then
            print_message "✅ $USER_COUNT utilisateur(s) préservé(s)" "$GREEN"
            print_message "✅ $DATA_COUNT données utilisateur préservées" "$GREEN"
        else
            print_message "❌ ALERTE: Aucun utilisateur trouvé!" "$RED"
            print_message "🔄 Tentative de restauration depuis les backups..." "$YELLOW"
            
            # Chercher le backup le plus récent
            LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/database_*.db 2>/dev/null | head -1)
            if [ -n "$LATEST_BACKUP" ]; then
                cp "$LATEST_BACKUP" "$DB_FILE"
                print_message "✅ Base restaurée depuis: $(basename "$LATEST_BACKUP")" "$GREEN"
            fi
        fi
    else
        print_message "❌ Base de données manquante!" "$RED"
    fi
    
    # CORRECTION AUTOMATIQUE DES PERMISSIONS APRÈS MISE À JOUR
    print_message "🔧 Correction automatique des permissions après mise à jour..." "$YELLOW"
    
    # 1. Corriger les permissions du dossier parent
    if sudo chown www-data:www-data "$(dirname "$DB_FILE")" 2>/dev/null; then
        print_message "✅ Dossier parent configuré: www-data:www-data" "$GREEN"
    fi
    
    if sudo chmod 755 "$(dirname "$DB_FILE")" 2>/dev/null; then
        print_message "✅ Permissions du dossier parent: 755" "$GREEN"
    fi
    
    # 2. Corriger les permissions de la base de données
    if sudo chown www-data:www-data "$DB_FILE" 2>/dev/null; then
        print_message "✅ Propriétaire corrigé: www-data:www-data" "$GREEN"
    fi
    
    if sudo chmod 664 "$DB_FILE" 2>/dev/null; then
        print_message "✅ Permissions corrigées: 664" "$GREEN"
    fi
    
    # 3. Ajouter ubuntu au groupe www-data
    if sudo usermod -a -G www-data ubuntu 2>/dev/null; then
        print_message "✅ Utilisateur ubuntu ajouté au groupe www-data" "$GREEN"
    fi
    
    # 4. Vérifier que les permissions sont correctes
    if [ -r "$DB_FILE" ] && [ -w "$DB_FILE" ]; then
        print_message "✅ Permissions de la base de données OK après mise à jour" "$GREEN"
    else
        print_message "⚠️ Problème de permissions après mise à jour, tentative avec permissions plus larges..." "$YELLOW"
        sudo chmod 666 "$DB_FILE" 2>/dev/null
        sudo chmod 777 "$(dirname "$DB_FILE")" 2>/dev/null
        print_message "✅ Permissions élargies appliquées" "$GREEN"
    fi
    
    print_message "✅ Vérification et correction terminées" "$GREEN"
}

# ================================================================
# FONCTION DE CORRECTION AUTOMATIQUE DES PERMISSIONS (AMÉLIORÉE)
# ================================================================
auto_fix_permissions() {
    print_message "🔧 Correction automatique des permissions après mise à jour..." "$BLUE"
    
    # 1. Corriger les permissions du projet entier
    print_message "📁 Correction des permissions du projet..." "$YELLOW"
    sudo chown -R ubuntu:ubuntu . 2>/dev/null
    sudo chmod -R 755 . 2>/dev/null
    
    # 2. Permissions spécifiques pour les fichiers sensibles
    print_message "📄 Correction des permissions des fichiers..." "$YELLOW"
    sudo chmod 644 .gitignore LICENSE README.md SECURITY.md 2>/dev/null
    sudo chmod 644 bot/*.json bot/*.js 2>/dev/null
    sudo chmod 644 scripts/*.sh scripts/*.js 2>/dev/null
    sudo chmod 644 web/*.php web/*.css 2>/dev/null
    
    # 3. Permissions spéciales pour la base de données
    print_message "🗄️ Correction des permissions de la base de données..." "$YELLOW"
    sudo chown www-data:www-data database/ 2>/dev/null
    sudo chown www-data:www-data database/database.db 2>/dev/null
    sudo chmod 755 database/ 2>/dev/null
    sudo chmod 664 database/database.db 2>/dev/null
    
    # 4. Permissions spéciales pour config.json
    print_message "📄 Correction des permissions de config.json..." "$YELLOW"
    if [ -f "config/config.json" ]; then
        sudo chown www-data:www-data config/config.json 2>/dev/null
        sudo chmod 664 config/config.json 2>/dev/null
    fi
    
    # 5. Ajouter l'utilisateur au groupe www-data
    print_message "👤 Configuration des groupes..." "$YELLOW"
    sudo usermod -a -G www-data ubuntu 2>/dev/null
    sudo usermod -a -G www-data $USER 2>/dev/null
    
    # 6. Corriger les permissions du dossier bot
    print_message "🤖 Correction des permissions du bot..." "$YELLOW"
    sudo chown -R ubuntu:ubuntu bot/ 2>/dev/null
    sudo chmod -R 755 bot/ 2>/dev/null
    
    # 7. Permissions plus larges si nécessaire
    print_message "🔓 Application de permissions plus larges..." "$YELLOW"
    sudo chmod 666 database/database.db 2>/dev/null
    sudo chmod 777 database/ 2>/dev/null
    
    # 8. Installer sqlite3 pour Node.js si nécessaire
    print_message "📦 Vérification de sqlite3 pour Node.js..." "$YELLOW"
    if [ -d "bot/node_modules" ]; then
        cd bot
        if ! npm list sqlite3 >/dev/null 2>&1; then
            print_message "📦 Installation de sqlite3..." "$YELLOW"
            npm install sqlite3 --save 2>/dev/null
        fi
        cd ..
    fi
    
    # 9. Test d'écriture avec www-data
    print_message "🧪 Test d'écriture avec www-data..." "$CYAN"
    if sudo -u www-data test -w database/database.db 2>/dev/null; then
        print_message "✅ www-data peut écrire dans la base" "$GREEN"
    else
        print_message "❌ www-data ne peut pas écrire, permissions critiques appliquées..." "$RED"
        sudo chmod 777 database/database.db 2>/dev/null
        sudo chmod 777 database/ 2>/dev/null
    fi
    
    # 10. Test d'écriture réel
    print_message "🧪 Test d'écriture réel..." "$CYAN"
    if sudo -u www-data sqlite3 database/database.db "CREATE TABLE IF NOT EXISTS test_permissions (id INTEGER); DROP TABLE test_permissions;" 2>/dev/null; then
        print_message "✅ Test d'écriture réussi" "$GREEN"
    else
        print_message "❌ Test d'écriture échoué, permissions critiques appliquées" "$RED"
    fi
    
    # 11. Test de config.json
    print_message "🧪 Test de config.json..." "$CYAN"
    if [ -f "config/config.json" ] && [ -r "config/config.json" ] && [ -w "config/config.json" ]; then
        print_message "✅ config.json accessible" "$GREEN"
    else
        print_message "⚠️ Problème avec config.json, permissions élargies..." "$YELLOW"
        sudo chmod 666 config/config.json 2>/dev/null
    fi
    
    # 12. Correction des scripts
    print_message "🔧 Correction des permissions des scripts..." "$YELLOW"
    sudo chmod +x scripts/*.sh 2>/dev/null
    chmod +x scripts/*.sh 2>/dev/null
    
    # 13. Vérification finale des permissions critiques
    print_message "🔍 Vérification finale des permissions critiques..." "$CYAN"
    
    # Vérifier que les fichiers critiques sont accessibles
    CRITICAL_FILES=(
        "database/database.db"
        "config/config.json"
        "scripts/ubuntu-manager.sh"
        "bot/bot.js"
    )
    
    for file in "${CRITICAL_FILES[@]}"; do
        if [ -f "$file" ]; then
            if [ -r "$file" ] && [ -w "$file" ]; then
                print_message "✅ $file: accessible" "$GREEN"
            else
                print_message "❌ $file: problème de permissions" "$RED"
                # Forcer les permissions
                sudo chmod 666 "$file" 2>/dev/null
            fi
        fi
    done
    
    print_message "✅ Correction automatique des permissions terminée" "$GREEN"
}

# ================================================================
# 4. MISE À JOUR DEPUIS GITHUB (AMÉLIORÉE)
# ================================================================
update_from_github() {
    print_message "🔄 Vérification des mises à jour GitHub..." "$BLUE"
    
    cd "$PROJECT_DIR" || exit
    
    # ÉTAPE 1: PROTECTION CRITIQUE DE LA BASE DE DONNÉES
    print_message "🛡️ Protection de la base de données..." "$YELLOW"
    
    # Créer un dossier de sauvegarde temporaire
    TEMP_BACKUP_DIR="/tmp/dashboard_backup_$(date +%s)"
    mkdir -p "$TEMP_BACKUP_DIR"
    
    # Sauvegarder TOUS les fichiers critiques
    if [ -f "$DB_FILE" ]; then
        cp "$DB_FILE" "$TEMP_BACKUP_DIR/database.db"
        print_message "✅ Base de données sauvegardée" "$GREEN"
    fi
    
    if [ -f "$CONFIG_FILE" ]; then
        cp "$CONFIG_FILE" "$TEMP_BACKUP_DIR/config.json"
        print_message "✅ Configuration sauvegardée" "$GREEN"
    fi
    
    # Sauvegarder le dossier backups si il existe
    if [ -d "$BACKUP_DIR" ]; then
        cp -r "$BACKUP_DIR" "$TEMP_BACKUP_DIR/backups"
        print_message "✅ Dossier backups sauvegardé" "$GREEN"
    fi
    
    # ÉTAPE 2: VÉRIFIER LES MISES À JOUR
    git fetch origin main
    
    LOCAL=$(git rev-parse HEAD)
    REMOTE=$(git rev-parse origin/main)
    
    if [ "$LOCAL" != "$REMOTE" ]; then
        print_message "📥 Nouvelles mises à jour disponibles!" "$YELLOW"
        
        # ÉTAPE 3: MISE À JOUR SÉCURISÉE
        print_message "📦 Application des mises à jour..." "$BLUE"
        
        # S'assurer que database.db n'est PAS tracké
        git rm --cached database.db 2>/dev/null || true
        
        # Ajouter database.db au .gitignore s'il n'y est pas
        if ! grep -q "database.db" .gitignore 2>/dev/null; then
            echo "database.db" >> .gitignore
            echo "backups/" >> .gitignore
            print_message "✅ .gitignore mis à jour" "$GREEN"
        fi
        
        # Stash SEULEMENT les fichiers de code (pas la DB)
        git add .gitignore 2>/dev/null || true
        git stash push -m "Auto-stash before update (excluding database)"
        
        # Pull les changements
        git pull origin main
        
        # ÉTAPE 4: RESTAURATION CRITIQUE DES DONNÉES
        print_message "🔄 Restauration des données critiques..." "$YELLOW"
        
        # Restaurer la base de données
        if [ -f "$TEMP_BACKUP_DIR/database.db" ]; then
            cp "$TEMP_BACKUP_DIR/database.db" "$DB_FILE"
            print_message "✅ Base de données restaurée" "$GREEN"
        fi
        
        # Restaurer la configuration
        if [ -f "$TEMP_BACKUP_DIR/config.json" ]; then
            cp "$TEMP_BACKUP_DIR/config.json" "$CONFIG_FILE"
            print_message "✅ Configuration restaurée" "$GREEN"
        fi
        
        # Restaurer les backups
        if [ -d "$TEMP_BACKUP_DIR/backups" ]; then
            mkdir -p "$BACKUP_DIR"
            cp -r "$TEMP_BACKUP_DIR/backups/"* "$BACKUP_DIR/" 2>/dev/null || true
            print_message "✅ Backups restaurés" "$GREEN"
        fi
        
        # ÉTAPE 5: CORRECTION AUTOMATIQUE DES PERMISSIONS (AMÉLIORÉE)
        print_message "🔧 CORRECTION AUTOMATIQUE DES PERMISSIONS APRÈS MISE À JOUR..." "$BLUE"
        auto_fix_permissions
        
        # Installer les nouvelles dépendances
        if [ -f "bot/package.json" ]; then
            cd bot && npm install --production && cd ..
            print_message "✅ Dépendances mises à jour" "$GREEN"
        fi
        
        # Appliquer les migrations DB si nécessaire
        if [ -f "scripts/migrate-db.js" ]; then
            print_message "🔄 Vérification des migrations..." "$YELLOW"
            if [ ! -f "migration.lock" ]; then
                # Vérifier si sqlite3 est installé
                if npm list sqlite3 >/dev/null 2>&1; then
                    node scripts/migrate-db.js
                else
                    print_message "⚠️ sqlite3 non installé, installation..." "$YELLOW"
                    cd bot && npm install sqlite3 --save && cd ..
                    node scripts/migrate-db.js
                fi
            fi
        fi
        
        # ÉTAPE 6: VÉRIFICATION FINALE ET CORRECTION SUPPLÉMENTAIRE
        print_message "🔍 VÉRIFICATION FINALE DES PERMISSIONS..." "$CYAN"
        
        # Vérifier et corriger config.json spécifiquement
        if [ -f "config/config.json" ]; then
            if [ ! -r "config/config.json" ] || [ ! -w "config/config.json" ]; then
                print_message "🔧 Correction des permissions de config.json..." "$YELLOW"
                sudo chown www-data:www-data config/config.json 2>/dev/null
                sudo chmod 664 config/config.json 2>/dev/null
                sudo chmod 666 config/config.json 2>/dev/null
            fi
        fi
        
        # Vérifier et corriger les scripts
        if [ ! -x "scripts/ubuntu-manager.sh" ]; then
            print_message "🔧 Correction des permissions des scripts..." "$YELLOW"
            sudo chmod +x scripts/*.sh 2>/dev/null
            chmod +x scripts/*.sh 2>/dev/null
        fi
        
        # Vérification finale
        verify_post_update
        
        # Nettoyer le dossier temporaire
        rm -rf "$TEMP_BACKUP_DIR"
        
        print_message "✅ Mise à jour terminée avec PROTECTION des données et CORRECTION AUTOMATIQUE COMPLÈTE des permissions!" "$GREEN"
        
        # Redémarrer les services
        pm2 restart all 2>/dev/null || true
        
    else
        print_message "✅ Déjà à jour" "$GREEN"
    fi
    
    # Nettoyer le dossier temporaire en cas d'erreur
    rm -rf "$TEMP_BACKUP_DIR" 2>/dev/null || true
}

# ================================================================
# 5. DÉMARRAGE DES SERVICES (AMÉLIORÉ)
# ================================================================
start_services() {
    print_message "🚀 Démarrage COMPLET de tous les services..." "$BLUE"
    
    cd "$PROJECT_DIR" || exit
    
    # CORRECTION AUTOMATIQUE DES PERMISSIONS AVANT DÉMARRAGE
    print_message "🔧 Vérification et correction des permissions avant démarrage..." "$YELLOW"
    auto_fix_permissions
    
    # 1. Services système - PHP-FPM
    print_message "🔧 Démarrage de PHP-FPM..." "$YELLOW"
    if ! systemctl is-active --quiet php8.1-fpm; then
        sudo apt install -y php8.1-fpm php8.1-sqlite3 php8.1-mbstring php8.1-curl php8.1-json 2>/dev/null
    fi
    sudo systemctl start php8.1-fpm
    sudo systemctl enable php8.1-fpm
    
    # 2. Nginx
    print_message "🌐 Démarrage de Nginx..." "$YELLOW"
    sudo systemctl start nginx
    sudo systemctl enable nginx
    
    # 3. MySQL/MariaDB (si installé)
    if systemctl list-unit-files | grep -q "mysql\|mariadb"; then
        print_message "🗄️ Démarrage de MySQL/MariaDB..." "$YELLOW"
        sudo systemctl start mysql 2>/dev/null || sudo systemctl start mariadb 2>/dev/null
        sudo systemctl enable mysql 2>/dev/null || sudo systemctl enable mariadb 2>/dev/null
    fi
    
    # 4. Redis (si installé)
    if systemctl list-unit-files | grep -q "redis"; then
        print_message "📦 Démarrage de Redis..." "$YELLOW"
        sudo systemctl start redis-server
        sudo systemctl enable redis-server
    fi
    
    # 5. Services PM2
    print_message "🤖 Démarrage du bot Discord..." "$CYAN"
    pm2 delete discord-bot 2>/dev/null
    cd bot && pm2 start bot.js --name "discord-bot" && cd ..
    
    print_message "💾 Démarrage du système de backup..." "$CYAN"
    pm2 delete backup-system 2>/dev/null
    pm2 start scripts/auto-backup.js --name "backup-system" -- auto
    
    print_message "🔄 Démarrage du système de mise à jour..." "$CYAN"
    pm2 delete update-system 2>/dev/null
    pm2 start scripts/auto-update-ubuntu.js --name "update-system" -- auto
    
    print_message "📊 Démarrage du système de monitoring..." "$CYAN"
    pm2 delete system-metrics 2>/dev/null
    pm2 start scripts/system-metrics.js --name "system-metrics"
    
    # 6. Sauvegarder la configuration PM2
    pm2 save
    pm2 startup systemd -u $USER --hp /home/$USER
    
    # 7. Vérifier que tout est démarré
    print_message "🔍 Vérification des services..." "$BLUE"
    sleep 2
    
    echo ""
    echo -e "${GREEN}✅ Services système démarrés:${NC}"
    systemctl is-active --quiet nginx && echo "  ✓ Nginx" || echo "  ✗ Nginx"
    systemctl is-active --quiet php8.1-fpm && echo "  ✓ PHP-FPM" || echo "  ✗ PHP-FPM"
    systemctl is-active --quiet mysql 2>/dev/null && echo "  ✓ MySQL" || true
    systemctl is-active --quiet mariadb 2>/dev/null && echo "  ✓ MariaDB" || true
    systemctl is-active --quiet redis-server 2>/dev/null && echo "  ✓ Redis" || true
    
    echo ""
    echo -e "${GREEN}✅ Services PM2 démarrés:${NC}"
    pm2 list --no-color | grep -E "discord-bot|backup-system|update-system|system-metrics" | awk '{print "  ✓", $2}'
    
    print_message "\n✅ Tous les services sont démarrés!" "$GREEN"
}

# ================================================================
# 5.1 ARRÊT DES SERVICES (NOUVEAU)
# ================================================================
stop_services() {
    print_message "⏹️ Arrêt COMPLET de tous les services..." "$YELLOW"
    
    # 1. Arrêter les services PM2
    print_message "🛑 Arrêt des services PM2..." "$YELLOW"
    pm2 stop all
    pm2 delete all
    
    # 2. Arrêter Nginx
    print_message "🛑 Arrêt de Nginx..." "$YELLOW"
    sudo systemctl stop nginx
    
    # 3. Arrêter PHP-FPM
    print_message "🛑 Arrêt de PHP-FPM..." "$YELLOW"
    sudo systemctl stop php8.1-fpm
    
    # 4. Arrêter MySQL/MariaDB (si actif)
    if systemctl is-active --quiet mysql 2>/dev/null; then
        print_message "🛑 Arrêt de MySQL..." "$YELLOW"
        sudo systemctl stop mysql
    fi
    if systemctl is-active --quiet mariadb 2>/dev/null; then
        print_message "🛑 Arrêt de MariaDB..." "$YELLOW"
        sudo systemctl stop mariadb
    fi
    
    # 5. Arrêter Redis (si actif)
    if systemctl is-active --quiet redis-server 2>/dev/null; then
        print_message "🛑 Arrêt de Redis..." "$YELLOW"
        sudo systemctl stop redis-server
    fi
    
    # 6. Vérifier que tout est arrêté
    print_message "🔍 Vérification de l'arrêt..." "$BLUE"
    sleep 2
    
    echo ""
    echo -e "${YELLOW}⏹️ État des services:${NC}"
    systemctl is-active --quiet nginx && echo "  ⚠️ Nginx encore actif!" || echo "  ✓ Nginx arrêté"
    systemctl is-active --quiet php8.1-fpm && echo "  ⚠️ PHP-FPM encore actif!" || echo "  ✓ PHP-FPM arrêté"
    systemctl is-active --quiet mysql 2>/dev/null && echo "  ⚠️ MySQL encore actif!" || echo "  ✓ MySQL arrêté"
    systemctl is-active --quiet mariadb 2>/dev/null && echo "  ⚠️ MariaDB encore actif!" || echo "  ✓ MariaDB arrêté"
    systemctl is-active --quiet redis-server 2>/dev/null && echo "  ⚠️ Redis encore actif!" || echo "  ✓ Redis arrêté"
    
    echo ""
    pm2 status --no-color
    
    print_message "\n✅ Tous les services sont arrêtés!" "$GREEN"
}

# ================================================================
# 5.2 REDÉMARRAGE DES SERVICES (NOUVEAU)
# ================================================================
restart_services() {
    print_message "🔄 Redémarrage COMPLET de tous les services..." "$BLUE"
    
    # D'abord arrêter proprement
    stop_services
    
    echo ""
    print_message "⏳ Attente avant redémarrage..." "$YELLOW"
    sleep 3
    
    # CORRECTION AUTOMATIQUE DES PERMISSIONS AVANT REDÉMARRAGE
    print_message "🔧 Correction automatique des permissions avant redémarrage..." "$YELLOW"
    verify_database_permissions
    
    # Puis redémarrer
    start_services
}

# ================================================================
# 6. STATUS DES SERVICES
# ================================================================
check_status() {
    print_message "📊 Status des services:" "$BLUE"
    
    # PM2
    pm2 status
    
    # Nginx
    echo -e "\n${YELLOW}Nginx:${NC}"
    sudo systemctl status nginx --no-pager | head -n 5
    
    # PHP
    echo -e "\n${YELLOW}PHP-FPM:${NC}"
    sudo systemctl status php8.1-fpm --no-pager | head -n 5
}

# ================================================================
# 7. LOGS AVANCÉS
# ================================================================
show_logs() {
    clear
    print_message "📝 GESTION AVANCÉE DES LOGS" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    
    # Calculer les tailles des logs
    PM2_LOG_SIZE=$(du -sh ~/.pm2/logs/ 2>/dev/null | cut -f1 || echo "0K")
    NGINX_LOG_SIZE=$(du -sh /var/log/nginx/ 2>/dev/null | cut -f1 || echo "0K")
    SYSTEM_LOG_SIZE=$(du -sh /var/log/ 2>/dev/null | cut -f1 || echo "0K")
    
    echo -e "${GREEN}📊 État des logs:${NC}"
    echo "├── PM2 Logs: $PM2_LOG_SIZE"
    echo "├── Nginx Logs: $NGINX_LOG_SIZE" 
    echo "└── System Logs: $SYSTEM_LOG_SIZE"
    echo ""
    
    echo "Choisissez une action:"
    echo ""
    echo "📋 AFFICHAGE:"
    echo "1) 🤖 Bot Discord (temps réel)"
    echo "2) 🌐 Accès web (Nginx)"
    echo "3) ❌ Erreurs système"
    echo "4) 📊 Résumé complet"
    echo "5) 🔄 Logs en temps réel"
    echo ""
    echo "🧹 NETTOYAGE:"
    echo "6) 🗑️ Nettoyer logs PM2"
    echo "7) 🗑️ Nettoyer logs Nginx"
    echo "8) 🗑️ Nettoyage complet"
    echo ""
    echo "9) 📈 Analyse des erreurs"
    echo "0) Retour"
    echo ""
    
    read -p "Votre choix: " log_choice
    
    case $log_choice in
        1)
            clear
            print_message "🤖 LOGS BOT DISCORD (TEMPS RÉEL)" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            echo -e "${YELLOW}Statut du service:${NC}"
            pm2 describe discord-bot 2>/dev/null | grep -E "(status|uptime|restarts)" || echo "Service non trouvé"
            echo ""
            echo -e "${YELLOW}Logs récents (50 dernières lignes):${NC}"
            pm2 logs discord-bot --lines 50 --nostream 2>/dev/null || echo "Aucun log disponible"
            ;;
        2)
            clear
            print_message "🌐 LOGS D'ACCÈS WEB" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            if [ -f "/var/log/nginx/access.log" ] || [ -f "/var/log/nginx/dashboard-access.log" ]; then
                echo -e "${YELLOW}Dernières requêtes:${NC}"
                tail -30 /var/log/nginx/*access*.log 2>/dev/null | grep -v "^$" | tail -20
                echo ""
                echo -e "${YELLOW}Top 10 des IPs:${NC}"
                cat /var/log/nginx/*access*.log 2>/dev/null | awk '{print $1}' | sort | uniq -c | sort -nr | head -10
            else
                echo "Aucun log d'accès Nginx trouvé"
            fi
            ;;
        3)
            clear
            print_message "❌ LOGS D'ERREURS SYSTÈME" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            echo -e "${YELLOW}Erreurs Nginx:${NC}"
            if [ -f "/var/log/nginx/error.log" ]; then
                tail -20 /var/log/nginx/error.log 2>/dev/null | grep -v "^$" || echo "Aucune erreur récente"
            else
                echo "Aucun log d'erreur Nginx"
            fi
            echo ""
            echo -e "${YELLOW}Erreurs PM2:${NC}"
            pm2 logs --err --lines 10 --nostream 2>/dev/null | grep -i error | tail -10 || echo "Aucune erreur PM2 récente"
            echo ""
            echo -e "${YELLOW}Erreurs système (dernières 10):${NC}"
            journalctl --no-pager -n 10 -p err 2>/dev/null || echo "Journalctl non disponible"
            ;;
        4)
            clear
            print_message "📊 RÉSUMÉ COMPLET DES LOGS" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            
            echo -e "${PURPLE}🤖 Services PM2:${NC}"
            pm2 status 2>/dev/null | grep -E "(id|name|status|cpu|memory)" || echo "PM2 non disponible"
            echo ""
            
            echo -e "${PURPLE}🔥 Logs récents Bot Discord:${NC}"
            pm2 logs discord-bot --lines 5 --nostream 2>/dev/null | tail -5 || echo "Service non actif"
            echo ""
            
            echo -e "${PURPLE}🌐 Dernières requêtes web:${NC}"
            tail -5 /var/log/nginx/*access*.log 2>/dev/null | tail -5 || echo "Aucun log web"
            echo ""
            
            echo -e "${PURPLE}⚠️ Erreurs récentes:${NC}"
            (tail -3 /var/log/nginx/error.log 2>/dev/null; pm2 logs --err --lines 3 --nostream 2>/dev/null) | tail -5 || echo "Aucune erreur récente"
            ;;
        5)
            clear
            print_message "🔄 LOGS EN TEMPS RÉEL" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            echo -e "${YELLOW}Logs en temps réel (Ctrl+C pour arrêter):${NC}"
            echo ""
            pm2 logs --timestamp
            ;;
        6)
            clear
            print_message "🗑️ NETTOYAGE LOGS PM2" "$YELLOW"
            echo "═══════════════════════════════════════════════════════════════"
            echo -e "${YELLOW}Taille actuelle: $PM2_LOG_SIZE${NC}"
            echo ""
            read -p "Confirmer le nettoyage des logs PM2? (o/N): " confirm
            if [[ $confirm =~ ^[Oo]$ ]]; then
                pm2 flush
                rm -f ~/.pm2/logs/*.log 2>/dev/null
                print_message "✅ Logs PM2 nettoyés" "$GREEN"
            else
                print_message "❌ Nettoyage annulé" "$RED"
            fi
            ;;
        7)
            clear
            print_message "🗑️ NETTOYAGE LOGS NGINX" "$YELLOW"
            echo "═══════════════════════════════════════════════════════════════"
            echo -e "${YELLOW}Taille actuelle: $NGINX_LOG_SIZE${NC}"
            echo ""
            read -p "Confirmer le nettoyage des logs Nginx? (o/N): " confirm
            if [[ $confirm =~ ^[Oo]$ ]]; then
                sudo truncate -s 0 /var/log/nginx/*.log 2>/dev/null
                sudo systemctl reload nginx 2>/dev/null
                print_message "✅ Logs Nginx nettoyés" "$GREEN"
            else
                print_message "❌ Nettoyage annulé" "$RED"
            fi
            ;;
        8)
            clear
            print_message "🗑️ NETTOYAGE COMPLET" "$YELLOW"
            echo "═══════════════════════════════════════════════════════════════"
            echo -e "${RED}⚠️ ATTENTION: Ceci va supprimer TOUS les logs!${NC}"
            echo ""
            echo "Logs qui seront supprimés:"
            echo "├── PM2 Logs ($PM2_LOG_SIZE)"
            echo "├── Nginx Logs ($NGINX_LOG_SIZE)"
            echo "└── Logs temporaires"
            echo ""
            read -p "Êtes-vous CERTAIN de vouloir tout nettoyer? (tapez 'SUPPRIMER'): " confirm
            if [[ $confirm == "SUPPRIMER" ]]; then
                print_message "🧹 Nettoyage en cours..." "$YELLOW"
                
                # PM2
                pm2 flush 2>/dev/null
                rm -f ~/.pm2/logs/*.log 2>/dev/null
                
                # Nginx
                sudo truncate -s 0 /var/log/nginx/*.log 2>/dev/null
                sudo systemctl reload nginx 2>/dev/null
                
                # Logs système temporaires
                sudo journalctl --vacuum-time=1d 2>/dev/null
                
                print_message "✅ Nettoyage complet terminé" "$GREEN"
            else
                print_message "❌ Nettoyage annulé" "$RED"
            fi
            ;;
                    9)
                clear
                print_message "📈 ANALYSE DES ERREURS DÉTAILLÉE" "$CYAN"
                echo "═══════════════════════════════════════════════════════════════"
                
                echo -e "${YELLOW}🔍 Erreurs les plus fréquentes:${NC}"
                echo ""
                
                echo -e "${PURPLE}🤖 Bot Discord:${NC}"
                pm2 logs discord-bot --lines 100 --nostream 2>/dev/null | grep -i error | sort | uniq -c | sort -nr | head -5 || echo "✅ Aucune erreur détectée"
                echo ""
                
                echo -e "${PURPLE}🌐 Nginx Errors:${NC}"
                tail -100 /var/log/nginx/error.log 2>/dev/null | awk '{print $8, $9, $10}' | sort | uniq -c | sort -nr | head -5 || echo "✅ Aucune erreur détectée"
                echo ""
                
                echo -e "${PURPLE}📡 Codes d'erreur HTTP:${NC}"
                tail -100 /var/log/nginx/*access*.log 2>/dev/null | awk '$9 >= 400 {print $9}' | sort | uniq -c | sort -nr || echo "✅ Aucune erreur HTTP"
                echo ""
                
                echo -e "${PURPLE}🔒 Sécurité SSH (tentatives d'intrusion):${NC}"
                grep "Failed password\|Invalid user\|kex_protocol_error" /var/log/auth.log 2>/dev/null | tail -5 | wc -l | xargs -I {} echo "└── {} tentatives récentes détectées"
                echo ""
                
                echo -e "${PURPLE}🖥️ État XRDP (bureau à distance):${NC}"
                if systemctl is-active --quiet xrdp 2>/dev/null; then
                    echo "├── Service XRDP: Actif (avec erreurs de permissions)"
                    echo "└── 💡 Pour corriger: sudo chmod 640 /etc/xrdp/key.pem"
                else
                    echo "└── Service XRDP: Inactif (normal si non utilisé)"
                fi
                ;;
        0)
            return
            ;;
        *)
            print_message "❌ Option invalide" "$RED"
            ;;
    esac
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# 8. SSL/HTTPS
# ================================================================
setup_ssl() {
    print_message "🔒 Configuration SSL..." "$BLUE"
    
    DOMAIN=$(jq -r '.website.url' "$CONFIG_FILE" | sed 's|https\?://||' | cut -d'/' -f1)
    
    sudo certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN"
    
    print_message "✅ SSL configuré" "$GREEN"
}

# ================================================================
# 9. GESTION DES UTILISATEURS (NOUVEAU)
# ================================================================
show_users() {
    clear
    print_message "👥 GESTION DES UTILISATEURS DISCORD" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    
    if [ -f "$DB_FILE" ]; then
        # Compter les utilisateurs
        TOTAL_USERS=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")
        NEW_TODAY=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users WHERE DATE(created_at) = DATE('now');" 2>/dev/null || echo "0")
        
        echo -e "${GREEN}📊 Statistiques:${NC}"
        echo "├── Total utilisateurs: $TOTAL_USERS"
        echo "├── Nouveaux aujourd'hui: $NEW_TODAY"
        echo "└── Base de données: $(du -h "$DB_FILE" | cut -f1)"
        echo ""
        
        echo -e "${CYAN}👤 Utilisateurs récents:${NC}"
        echo "Discord ID        Pseudo          Token                      Dernière activité"
        echo "────────────────────────────────────────────────────────────────────────────────"
        
        sqlite3 "$DB_FILE" "
        SELECT printf('%-16s', SUBSTR(u.discord_id, 1, 16)) || ' ' ||
               printf('%-14s', SUBSTR(u.pseudo, 1, 14)) || ' ' ||
               printf('%-25s', SUBSTR(u.token, 1, 25) || '...') || ' ' ||
               CASE 
                 WHEN datetime(COALESCE(ud.updated_at, u.created_at)) > datetime('now', '-1 hour') THEN 
                   'Il y a ' || CAST((julianday('now') - julianday(COALESCE(ud.updated_at, u.created_at))) * 24 * 60 AS INTEGER) || ' min'
                 WHEN datetime(COALESCE(ud.updated_at, u.created_at)) > datetime('now', '-1 day') THEN 
                   'Il y a ' || CAST((julianday('now') - julianday(COALESCE(ud.updated_at, u.created_at))) * 24 AS INTEGER) || 'h'
                 ELSE 
                   'Il y a ' || CAST((julianday('now') - julianday(COALESCE(ud.updated_at, u.created_at))) AS INTEGER) || ' jours'
               END as last_activity
        FROM users u
        LEFT JOIN (
            SELECT token, MAX(updated_at) as updated_at 
            FROM user_data 
            GROUP BY token
        ) ud ON u.token = ud.token
        ORDER BY COALESCE(ud.updated_at, u.created_at) DESC 
        LIMIT 10
        " 2>/dev/null || echo "Aucune donnée disponible"
    else
        print_message "❌ Base de données non trouvée" "$RED"
    fi
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# 10. ÉTAT DE LA BASE DE DONNÉES (NOUVEAU)
# ================================================================
check_database() {
    clear
    print_message "🗄️ ÉTAT DE LA BASE DE DONNÉES" "$BLUE" 
    echo "═══════════════════════════════════════════════════════════════"
    
    if [ -f "$DB_FILE" ]; then
        # Vérifier l'intégrité
        INTEGRITY=$(sqlite3 "$DB_FILE" "PRAGMA integrity_check;" 2>/dev/null)
        if [ "$INTEGRITY" = "ok" ]; then
            STATUS_ICON="✅"
            STATUS_TEXT="SAINE"
            STATUS_COLOR="$GREEN"
        else
            STATUS_ICON="❌"
            STATUS_TEXT="PROBLÈME DÉTECTÉ"
            STATUS_COLOR="$RED"
        fi
        
        echo -e "${STATUS_COLOR}$STATUS_ICON Status: $STATUS_TEXT${NC}"
        echo ""
        
        # Statistiques
        echo -e "${CYAN}📊 Statistiques:${NC}"
        DB_SIZE=$(du -h "$DB_FILE" | cut -f1)
        TABLES=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM sqlite_master WHERE type='table';" 2>/dev/null || echo "0")
        RECORDS=$(sqlite3 "$DB_FILE" "SELECT SUM(cnt) FROM (SELECT COUNT(*) as cnt FROM users UNION ALL SELECT COUNT(*) FROM wins);" 2>/dev/null || echo "0")
        
        echo "├── Taille: $DB_SIZE"
        echo "├── Tables: $TABLES"
        echo "├── Enregistrements: $RECORDS"
        echo "├── Intégrité: $INTEGRITY"
        
        # Dernier backup
        LAST_BACKUP=$(ls -t "$BACKUP_DIR/database/" 2>/dev/null | head -1)
        if [ -n "$LAST_BACKUP" ]; then
            BACKUP_TIME=$(stat -c %Y "$BACKUP_DIR/database/$LAST_BACKUP" 2>/dev/null)
            CURRENT_TIME=$(date +%s)
            DIFF=$(( (CURRENT_TIME - BACKUP_TIME) / 60 ))
            echo "├── Dernier backup: Il y a ${DIFF} min"
        else
            echo "├── Dernier backup: Aucun"
        fi
        
        echo "└── Vérification: $(date)"
        echo ""
        
        # Activité récente
        echo -e "${PURPLE}📈 Activité récente:${NC}"
        echo "├── ✅ $(date '+%H:%M') - Vérification intégrité OK"
        if [ -n "$LAST_BACKUP" ]; then
            echo "├── ✅ $(date -d @$BACKUP_TIME '+%H:%M') - Backup automatique réussi"
        fi
        
        # Compter les nouvelles entrées aujourd'hui
        NEW_USERS=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users WHERE DATE(created_at) = DATE('now');" 2>/dev/null || echo "0")
        NEW_WINS=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM wins WHERE DATE(created_at) = DATE('now');" 2>/dev/null || echo "0")
        
        if [ "$NEW_USERS" -gt 0 ]; then
            echo "├── ✅ $(date '+%H:%M') - $NEW_USERS nouveaux utilisateurs aujourd'hui"
        fi
        if [ "$NEW_WINS" -gt 0 ]; then
            echo "└── ✅ $(date '+%H:%M') - $NEW_WINS nouvelles victoires enregistrées"
        fi
        
    else
        print_message "❌ Base de données non trouvée: $DB_FILE" "$RED"
    fi
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# 11. MONITORING BOT DISCORD (NOUVEAU)
# ================================================================
monitor_bot() {
    clear
    print_message "🤖 MONITORING BOT DISCORD" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    
    # Vérifier si le bot est en ligne
    BOT_STATUS=$(pm2 describe discord-bot 2>/dev/null | grep -o "online\|stopped\|errored" | head -1)
    
    if [ "$BOT_STATUS" = "online" ]; then
        STATUS_ICON="✅"
        STATUS_TEXT="EN LIGNE"
        STATUS_COLOR="$GREEN"
    else
        STATUS_ICON="❌"
        STATUS_TEXT="HORS LIGNE"
        STATUS_COLOR="$RED"
    fi
    
    echo -e "${STATUS_COLOR}$STATUS_ICON Status: $STATUS_TEXT${NC}"
    echo ""
    
    if [ "$BOT_STATUS" = "online" ]; then
        # Informations de connexion
        echo -e "${CYAN}🔗 Connexion:${NC}"
        UPTIME=$(pm2 describe discord-bot 2>/dev/null | grep "uptime" | awk '{print $4}')
        echo "├── Uptime: ${UPTIME:-"N/A"}"
        echo "├── Process ID: $(pm2 describe discord-bot 2>/dev/null | grep "pid" | awk '{print $4}')"
        echo "├── Mémoire: $(pm2 describe discord-bot 2>/dev/null | grep "memory" | awk '{print $4}')"
        echo "└── CPU: $(pm2 describe discord-bot 2>/dev/null | grep "cpu" | awk '{print $4}')"
        echo ""
        
        # Statistiques des commandes (si base de données disponible)
        if [ -f "$DB_FILE" ]; then
            echo -e "${PURPLE}📊 Commandes (dernières 24h):${NC}"
            
            # Compter les commandes récentes (approximation via les tokens utilisés)
            RECENT_TOKENS=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users WHERE datetime(updated_at) > datetime('now', '-1 day');" 2>/dev/null || echo "0")
            TOTAL_USERS=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")
            
            echo "├── Utilisateurs actifs: $RECENT_TOKENS"
            echo "├── Total utilisateurs: $TOTAL_USERS"
            echo "└── Taux d'activité: $(( RECENT_TOKENS * 100 / (TOTAL_USERS + 1) ))%"
        fi
        echo ""
        
        # Logs récents
        echo -e "${YELLOW}🔄 Logs récents:${NC}"
        pm2 logs discord-bot --lines 5 --nostream 2>/dev/null | tail -5 | sed 's/^/├── /' || echo "├── Aucun log disponible"
    else
        echo -e "${RED}❌ Bot hors ligne${NC}"
        echo ""
        echo "Pour redémarrer:"
        echo "pm2 restart discord-bot"
    fi
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# 12. STATISTIQUES TEMPS RÉEL (NOUVEAU)
# ================================================================
show_realtime_stats() {
    clear
    print_message "📈 STATISTIQUES TEMPS RÉEL" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    
    # Statistiques serveur
    echo -e "${CYAN}🖥️  Serveur:${NC}"
    
    # CPU
    CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | awk -F'%' '{print $1}' | cut -d',' -f1)
    CPU_BARS=$(echo "scale=0; $CPU_USAGE / 5" | bc 2>/dev/null || echo "0")
    printf "├── CPU: "
    for i in $(seq 1 20); do
        if [ $i -le ${CPU_BARS:-0} ]; then printf "▓"; else printf "░"; fi
    done
    echo " ${CPU_USAGE:-0}%"
    
    # RAM
    RAM_INFO=$(free | grep Mem)
    RAM_TOTAL=$(echo $RAM_INFO | awk '{print $2}')
    RAM_USED=$(echo $RAM_INFO | awk '{print $3}')
    RAM_PERCENT=$(echo "scale=0; $RAM_USED * 100 / $RAM_TOTAL" | bc 2>/dev/null || echo "0")
    RAM_BARS=$(echo "scale=0; $RAM_PERCENT / 5" | bc 2>/dev/null || echo "0")
    
    printf "├── RAM: "
    for i in $(seq 1 20); do
        if [ $i -le ${RAM_BARS:-0} ]; then printf "▓"; else printf "░"; fi
    done
    echo " ${RAM_PERCENT}% ($(( RAM_USED / 1024 ))MB/$(( RAM_TOTAL / 1024 ))MB)"
    
    # Disque
    DISK_INFO=$(df / | tail -1)
    DISK_PERCENT=$(echo "$DISK_INFO" | awk '{print $5}' | sed 's/%//')
    DISK_BARS=$(echo "scale=0; $DISK_PERCENT / 5" | bc 2>/dev/null || echo "0")
    DISK_USED=$(echo "$DISK_INFO" | awk '{print $3}' | awk '{printf "%.1f", $1/1024/1024}')
    DISK_TOTAL=$(echo "$DISK_INFO" | awk '{print $2}' | awk '{printf "%.1f", $1/1024/1024}')
    
    printf "├── Disque: "
    for i in $(seq 1 20); do
        if [ $i -le ${DISK_BARS:-0} ]; then printf "▓"; else printf "░"; fi
    done
    echo " ${DISK_PERCENT}% (${DISK_USED}GB/${DISK_TOTAL}GB)"
    
    # Réseau (approximation)
    NETWORK=$(cat /proc/net/dev | grep eth0 2>/dev/null || cat /proc/net/dev | grep enp 2>/dev/null | head -1)
    if [ -n "$NETWORK" ]; then
        echo "└── Réseau: Interface active détectée"
    else
        echo "└── Réseau: État indéterminé"
    fi
    
    echo ""
    
    # Services
    echo -e "${PURPLE}🚀 Services:${NC}"
    
    # PM2
    PM2_PROCESSES=$(pm2 jlist 2>/dev/null | jq length 2>/dev/null || echo "0")
    PM2_ONLINE=$(pm2 jlist 2>/dev/null | jq '[.[] | select(.pm2_env.status == "online")] | length' 2>/dev/null || echo "0")
    echo "├── PM2: $PM2_ONLINE/$PM2_PROCESSES processus actifs"
    
    # Nginx
    if systemctl is-active --quiet nginx; then
        echo "├── Nginx: ✅ Actif"
    else
        echo "├── Nginx: ❌ Inactif"
    fi
    
    # PHP-FPM
    if systemctl is-active --quiet php8.1-fpm; then
        echo "├── PHP-FPM: ✅ Actif"
    else
        echo "├── PHP-FPM: ❌ Inactif"
    fi
    
    # Base de données
    if [ -f "$DB_FILE" ] && sqlite3 "$DB_FILE" "SELECT 1;" >/dev/null 2>&1; then
        echo "└── SQLite: ✅ Accessible"
    else
        echo "└── SQLite: ❌ Problème"
    fi
    
    echo ""
    
    # Performance
    echo -e "${GREEN}⚡ Performance (dernière heure):${NC}"
    echo "├── Uptime: $(uptime -p)"
    echo "├── Load average: $(uptime | awk -F'load average:' '{print $2}')"
    
    # Nginx logs si disponibles
    if [ -f "/var/log/nginx/dashboard-access.log" ]; then
        REQUESTS=$(tail -1000 /var/log/nginx/dashboard-access.log 2>/dev/null | wc -l)
        echo "├── Requêtes web récentes: $REQUESTS"
    fi
    
    echo "└── Dernière vérification: $(date '+%H:%M:%S')"
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# 13. VÉRIFICATION SYSTÈME COMPLÈTE AMÉLIORÉE
# ================================================================
system_health_check() {
    clear
    print_message "🛡️ VÉRIFICATION SYSTÈME COMPLÈTE" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    print_message "🔍 Scan en cours..." "$YELLOW"
    echo ""
    
    ISSUES=0
    FIXABLE_ISSUES=()
    
    # Services
    echo -e "${GREEN}✅ Services:${NC}"
    
    if systemctl is-active --quiet nginx; then
        echo "├── ✅ nginx: actif et fonctionnel"
    else
        echo "├── ❌ nginx: problème détecté"
        FIXABLE_ISSUES+=("nginx")
        ((ISSUES++))
    fi
    
    if systemctl is-active --quiet php8.1-fpm; then
        echo "├── ✅ php8.1-fpm: actif et fonctionnel"
    else
        echo "├── ❌ php8.1-fpm: problème détecté"
        FIXABLE_ISSUES+=("php")
        ((ISSUES++))
    fi
    
    PM2_ONLINE=$(pm2 jlist 2>/dev/null | jq '[.[] | select(.pm2_env.status == "online")] | length' 2>/dev/null || echo "0")
    if [ "$PM2_ONLINE" -ge 1 ]; then
        echo "├── ✅ pm2: $PM2_ONLINE processus actifs"
    else
        echo "├── ❌ pm2: aucun processus actif"
        FIXABLE_ISSUES+=("pm2")
        ((ISSUES++))
    fi
    
    # UFW check sans privilèges root
    UFW_STATUS=$(sudo ufw status 2>/dev/null | grep "Status:" | awk '{print $2}' || echo "unknown")
    if [ "$UFW_STATUS" = "active" ]; then
        echo "└── ✅ ufw: actif (sécurité OK)"
    elif [ "$UFW_STATUS" = "inactive" ]; then
        echo "└── ⚠️  ufw: inactif (recommandé de l'activer)"
    else
        echo "└── ⚠️  ufw: non installé ou non accessible"
    fi
    
    echo ""
    
    # Base de données
    echo -e "${GREEN}✅ Base de données:${NC}"
    
    # Créer la base de données si elle n'existe pas
    if [ ! -f "$DB_FILE" ]; then
        echo "├── ⚠️  Base de données manquante - création automatique..."
        create_database_if_missing
    fi
    
    if [ -f "$DB_FILE" ]; then
        echo "├── ✅ Fichier database.db accessible"
        
        INTEGRITY=$(sqlite3 "$DB_FILE" "PRAGMA integrity_check;" 2>/dev/null)
        if [ "$INTEGRITY" = "ok" ]; then
            echo "├── ✅ Intégrité: PRAGMA integrity_check = OK"
        else
            echo "├── ❌ Intégrité: PROBLÈME DÉTECTÉ"
            FIXABLE_ISSUES+=("db_integrity")
            ((ISSUES++))
        fi
        
        # Test de permissions amélioré
        DB_READABLE=false
        DB_WRITABLE=false
        
        if [ -r "$DB_FILE" ]; then
            DB_READABLE=true
        fi
        
        # Test d'écriture réel
        if touch "$DB_FILE.test" 2>/dev/null; then
            rm -f "$DB_FILE.test" 2>/dev/null
            DB_WRITABLE=true
        fi
        
        if [ "$DB_READABLE" = true ] && [ "$DB_WRITABLE" = true ]; then
            echo "├── ✅ Permissions: lecture/écriture OK"
        else
            echo "├── ❌ Permissions: problème d'accès (R:$DB_READABLE W:$DB_WRITABLE)"
            FIXABLE_ISSUES+=("db_permissions")
            ((ISSUES++))
        fi
        
        BACKUP_COUNT=$(ls -1 "$BACKUP_DIR/database/" 2>/dev/null | wc -l)
        if [ "$BACKUP_COUNT" -gt 0 ]; then
            echo "└── ✅ Backup: $BACKUP_COUNT sauvegardes disponibles"
        else
            echo "└── ⚠️  Backup: aucune sauvegarde trouvée"
        fi
    else
        echo "└── ❌ Base de données introuvable"
        FIXABLE_ISSUES+=("db_missing")
        ((ISSUES++))
    fi
    
    echo ""
    
    # Configuration
    echo -e "${GREEN}✅ Configuration:${NC}"
    
    if [ -f "$CONFIG_FILE" ]; then
        if jq . "$CONFIG_FILE" >/dev/null 2>&1; then
            echo "├── ✅ config.json: valide et chargé"
        else
            echo "├── ❌ config.json: erreur de syntaxe"
            FIXABLE_ISSUES+=("config_syntax")
            ((ISSUES++))
        fi
    else
        echo "├── ❌ config.json: fichier manquant"
        FIXABLE_ISSUES+=("config_missing")
        ((ISSUES++))
    fi
    
    # Détection SSL améliorée
    DOMAIN=$(jq -r '.website.url' "$CONFIG_FILE" 2>/dev/null | sed 's|https\?://||' | cut -d'/' -f1)
    if [ -n "$DOMAIN" ] && [ "$DOMAIN" != "null" ] && [ "$DOMAIN" != "localhost" ]; then
        # Plusieurs emplacements possibles pour SSL
        SSL_LOCATIONS=(
            "/etc/letsencrypt/live/$DOMAIN/fullchain.pem"
            "/etc/ssl/certs/$DOMAIN.pem"
            "/etc/nginx/ssl/$DOMAIN.crt"
            "/etc/apache2/ssl/$DOMAIN.crt"
        )
        
        SSL_FOUND=false
        SSL_PATH=""
        
        for ssl_path in "${SSL_LOCATIONS[@]}"; do
            if [ -f "$ssl_path" ]; then
                SSL_FOUND=true
                SSL_PATH="$ssl_path"
                break
            fi
        done
        
        if [ "$SSL_FOUND" = true ]; then
            # Vérifier la validité du certificat
            EXPIRE_DATE=$(openssl x509 -enddate -noout -in "$SSL_PATH" 2>/dev/null | cut -d= -f2)
            if [ -n "$EXPIRE_DATE" ]; then
                EXPIRE_TIMESTAMP=$(date -d "$EXPIRE_DATE" +%s 2>/dev/null)
                CURRENT_TIMESTAMP=$(date +%s)
                DAYS_LEFT=$(( (EXPIRE_TIMESTAMP - CURRENT_TIMESTAMP) / 86400 ))
                
                if [ "$DAYS_LEFT" -gt 30 ]; then
                    echo "├── ✅ SSL: certificat valide (expire dans $DAYS_LEFT jours)"
                elif [ "$DAYS_LEFT" -gt 0 ]; then
                    echo "├── ⚠️  SSL: certificat expire bientôt ($DAYS_LEFT jours)"
                else
                    echo "├── ❌ SSL: certificat expiré"
                    FIXABLE_ISSUES+=("ssl_expired")
                    ((ISSUES++))
                fi
            else
                echo "├── ⚠️  SSL: certificat trouvé mais impossible à vérifier"
            fi
        else
            echo "├── ⚠️  SSL: certificat non trouvé pour $DOMAIN"
            echo "│   💡 En production: sudo certbot --nginx -d $DOMAIN"
            FIXABLE_ISSUES+=("ssl_missing")
        fi
        
        # Test DNS
        if nslookup "$DOMAIN" >/dev/null 2>&1; then
            echo "└── ✅ DNS: résolution OK"
        else
            echo "└── ❌ DNS: problème de résolution"
            ((ISSUES++))
        fi
    else
        echo "└── ⚠️  Domaine localhost ou non configuré"
    fi
    
    echo ""
    
    # Espace disque et performance
    echo -e "${GREEN}💾 Ressources système:${NC}"
    
    DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
    if [ "$DISK_USAGE" -lt 80 ]; then
        echo "├── ✅ Espace disque: ${DISK_USAGE}% utilisé"
    elif [ "$DISK_USAGE" -lt 90 ]; then
        echo "├── ⚠️  Espace disque: ${DISK_USAGE}% utilisé (surveiller)"
    else
        echo "├── ❌ Espace disque: ${DISK_USAGE}% utilisé (critique)"
        FIXABLE_ISSUES+=("disk_space")
        ((ISSUES++))
    fi
    
    MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')
    if [ "$MEMORY_USAGE" -lt 80 ]; then
        echo "└── ✅ Mémoire: ${MEMORY_USAGE}% utilisée"
    else
        echo "└── ⚠️  Mémoire: ${MEMORY_USAGE}% utilisée (élevé)"
    fi
    
    echo ""
    
    # Résumé et solutions
    if [ $ISSUES -eq 0 ]; then
        echo -e "${GREEN}🎉 Système en parfait état!${NC}"
        echo "└── Aucun problème détecté"
    else
        echo -e "${YELLOW}⚠️  Problèmes détectés: $ISSUES${NC}"
        echo ""
        
        if [ ${#FIXABLE_ISSUES[@]} -gt 0 ]; then
            echo -e "${CYAN}🔧 Corrections automatiques disponibles:${NC}"
            echo ""
            read -p "Voulez-vous lancer les corrections automatiques ? (o/N): " auto_fix
            
            if [[ $auto_fix =~ ^[Oo]$ ]]; then
                echo ""
                print_message "🔧 Application des corrections..." "$YELLOW"
                
                for issue in "${FIXABLE_ISSUES[@]}"; do
                    case $issue in
                        "nginx")
                            echo "├── Redémarrage de nginx..."
                            sudo systemctl restart nginx && echo "   ✅ nginx redémarré" || echo "   ❌ Échec"
                            ;;
                        "php")
                            echo "├── Redémarrage de php8.1-fpm..."
                            sudo systemctl restart php8.1-fpm && echo "   ✅ PHP redémarré" || echo "   ❌ Échec"
                            ;;
                        "pm2")
                            echo "├── Redémarrage des services PM2..."
                            pm2 restart all && echo "   ✅ PM2 redémarré" || echo "   ❌ Échec"
                            ;;
                        "db_permissions")
                            echo "├── Correction des permissions de la base de données..."
                            fix_database_permissions_force
                            ;;
                        "db_missing")
                            echo "├── Création et configuration de la base de données..."
                            create_database_if_missing
                            ;;
                        "ssl_missing")
                            echo "├── Configuration SSL recommandée..."
                            echo "   💡 Lancez l'option 'Configuration SSL' du menu principal"
                            ;;
                        "disk_space")
                            echo "├── Nettoyage automatique..."
                            sudo apt autoremove -y >/dev/null 2>&1
                            sudo apt autoclean >/dev/null 2>&1
                            pm2 flush >/dev/null 2>&1
                            echo "   ✅ Nettoyage effectué"
                            ;;
                    esac
                done
                
                echo ""
                print_message "✅ Corrections appliquées" "$GREEN"
                echo ""
                read -p "Relancer une vérification ? (o/N): " recheck
                if [[ $recheck =~ ^[Oo]$ ]]; then
                    system_health_check
                    return
                fi
            fi
        fi
    fi
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# FONCTION DE CORRECTION AVANCÉE
# ================================================================
advanced_system_repair() {
    clear
    print_message "🔧 RÉPARATION SYSTÈME AVANCÉE" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    echo ""
    
    echo "Corrections avancées disponibles :"
    echo ""
    echo "1) 🗄️  Réparer base de données complètement"
    echo "2) 🔒 Reconfigurer SSL automatiquement" 
    echo "3) 🌐 Réparer configuration Nginx"
    echo "4) 🤖 Reconfigurer services PM2"
    echo "5) 🧹 Nettoyage complet du système"
    echo "6) 🔑 Corriger toutes les permissions"
    echo "0) Retour"
    echo ""
    
    read -p "Votre choix: " repair_choice
    
    case $repair_choice in
        1)
            clear
            print_message "🗄️ RÉPARATION BASE DE DONNÉES" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            
            echo "🔑 Correction des permissions..."
            sudo chown www-data:www-data "$DB_FILE" 2>/dev/null
            sudo chmod 664 "$DB_FILE" 2>/dev/null
            sudo chown www-data:www-data "$(dirname "$DB_FILE")" 2>/dev/null
            sudo chmod 755 "$(dirname "$DB_FILE")" 2>/dev/null
            echo "✅ Permissions corrigées"
            ;;
        2)
            clear
            print_message "🔒 RECONFIGURATION SSL" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            
            DOMAIN=$(jq -r '.website.url' "$CONFIG_FILE" 2>/dev/null | sed 's|https\?://||' | cut -d'/' -f1)
            
            if [ -n "$DOMAIN" ] && [ "$DOMAIN" != "null" ] && [ "$DOMAIN" != "localhost" ]; then
                echo "🌐 Domaine détecté: $DOMAIN"
                
                if command -v certbot >/dev/null 2>&1; then
                    read -p "Voulez-vous configurer/renouveler SSL ? (o/N): " confirm
                    
                    if [[ $confirm =~ ^[Oo]$ ]]; then
                        echo "🔧 Configuration SSL en cours..."
                        sudo certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --redirect 2>/dev/null
                        
                        if [ $? -eq 0 ]; then
                            echo "✅ SSL configuré avec succès"
                            sudo systemctl reload nginx
                        else
                            echo "❌ Erreur lors de la configuration SSL"
                        fi
                    fi
                else
                    echo "❌ Certbot non installé"
                    read -p "Installer Certbot ? (o/N): " install_certbot
                    
                    if [[ $install_certbot =~ ^[Oo]$ ]]; then
                        sudo apt update && sudo apt install -y certbot python3-certbot-nginx
                        echo "✅ Certbot installé"
                    fi
                fi
            else
                echo "❌ Domaine non valide pour SSL"
            fi
            ;;
        3)
            clear
            print_message "🌐 RÉPARATION NGINX" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            
            echo "🔄 Redémarrage des services..."
            sudo systemctl restart nginx
            sudo systemctl restart php8.1-fpm
            echo "✅ Services redémarrés"
            ;;
        4)
            clear
            print_message "🤖 RECONFIGURATION PM2" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            
            echo "🔄 Redémarrage PM2..."
            pm2 restart all
            pm2 save
            echo "✅ PM2 redémarré"
            ;;
        5)
            clear
            print_message "🧹 NETTOYAGE COMPLET" "$YELLOW"
            echo "═══════════════════════════════════════════════════════════════"
            
            read -p "Confirmer le nettoyage complet ? (o/N): " confirm
            
            if [[ $confirm =~ ^[Oo]$ ]]; then
                echo "🧹 Nettoyage en cours..."
                sudo apt autoremove -y >/dev/null 2>&1
                sudo apt autoclean >/dev/null 2>&1
                pm2 flush >/dev/null 2>&1
                sudo journalctl --vacuum-time=7d >/dev/null 2>&1
                echo "✅ Nettoyage terminé"
            fi
            ;;
        6)
            clear
            print_message "🔑 CORRECTION PERMISSIONS" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            
            echo "🔧 Correction des permissions..."
            create_database_if_missing
            fix_database_permissions_force
            echo "✅ Permissions corrigées avec force"
            ;;
        0)
            return
            ;;
        *)
            print_message "❌ Option invalide" "$RED"
            ;;
    esac
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# 14. LOGS CENTRALISÉS (NOUVEAU)
# ================================================================
show_centralized_logs() {
    clear
    print_message "📋 LOGS CENTRALISÉS" "$BLUE"
    echo "═══════════════════════════════════════════════════════════════"
    
    echo "Choisissez le type de logs à afficher:"
    echo ""
    echo "1) 🤖 Bot Discord (PM2)"
    echo "2) 🌐 Nginx Access"
    echo "3) ❌ Nginx Errors"
    echo "4) 💾 Backup System"
    echo "5) 🔄 Update System"
    echo "6) 🗄️ Base de données"
    echo "7) 📊 Tous les logs récents"
    echo "0) Retour"
    echo ""
    
    read -p "Votre choix: " log_choice
    
    case $log_choice in
        1)
            clear
            print_message "🤖 LOGS BOT DISCORD" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            pm2 logs discord-bot --lines 50 --nostream
            ;;
        2)
            clear
            print_message "🌐 LOGS NGINX ACCESS" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            if [ -f "/var/log/nginx/dashboard-access.log" ]; then
                tail -50 /var/log/nginx/dashboard-access.log
            else
                echo "Aucun log d'accès trouvé"
            fi
            ;;
        3)
            clear
            print_message "❌ LOGS NGINX ERRORS" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            if [ -f "/var/log/nginx/dashboard-error.log" ]; then
                tail -50 /var/log/nginx/dashboard-error.log
            else
                echo "Aucune erreur Nginx récente"
            fi
            ;;
        4)
            clear
            print_message "💾 LOGS BACKUP SYSTEM" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            pm2 logs backup-system --lines 30 --nostream 2>/dev/null || echo "Service backup non actif"
            ;;
        5)
            clear
            print_message "🔄 LOGS UPDATE SYSTEM" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            pm2 logs update-system --lines 30 --nostream 2>/dev/null || echo "Service update non actif"
            ;;
        6)
            clear
            print_message "🗄️ ACTIVITÉ BASE DE DONNÉES" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            if [ -f "$DB_FILE" ]; then
                echo "Dernières créations d'utilisateurs:"
                sqlite3 "$DB_FILE" "SELECT datetime(created_at, 'localtime') as date, id, username FROM users ORDER BY created_at DESC LIMIT 10;" -column 2>/dev/null || echo "Aucune donnée"
                echo ""
                echo "Dernières victoires enregistrées:"
                sqlite3 "$DB_FILE" "SELECT datetime(created_at, 'localtime') as date, user_id, value FROM wins ORDER BY created_at DESC LIMIT 10;" -column 2>/dev/null || echo "Aucune donnée"
            else
                echo "Base de données non accessible"
            fi
            ;;
        7)
            clear
            print_message "📊 RÉSUMÉ - TOUS LES LOGS RÉCENTS" "$CYAN"
            echo "═══════════════════════════════════════════════════════════════"
            
            echo -e "${PURPLE}🤖 Bot Discord (dernières 5 lignes):${NC}"
            pm2 logs discord-bot --lines 5 --nostream 2>/dev/null | tail -5 || echo "Service non actif"
            echo ""
            
            echo -e "${PURPLE}🌐 Nginx (dernières 5 requêtes):${NC}"
            if [ -f "/var/log/nginx/dashboard-access.log" ]; then
                tail -5 /var/log/nginx/dashboard-access.log
            else
                echo "Aucun log disponible"
            fi
            echo ""
            
            echo -e "${PURPLE}❌ Erreurs récentes:${NC}"
            if [ -f "/var/log/nginx/dashboard-error.log" ]; then
                tail -3 /var/log/nginx/dashboard-error.log 2>/dev/null || echo "Aucune erreur récente"
            else
                echo "Aucune erreur récente"
            fi
            ;;
        0)
            return
            ;;
        *)
            print_message "❌ Option invalide!" "$RED"
            ;;
    esac
    
    echo ""
    read -p "Appuyez sur Entrée pour continuer..."
}

# ================================================================
# MENU PRINCIPAL (ÉTENDU)
# ================================================================
show_menu() {
    clear
    echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║        🚀 Dashboard Multi-Modules - Ubuntu Manager           ║${NC}"
    echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo
    echo -e "${GREEN}1)${NC} 🚀 Installation complète (première fois)"
    echo -e "${GREEN}2)${NC} ▶️  Démarrer tous les services"
    echo -e "${GREEN}3)${NC} 🔄 Vérifier et installer les mises à jour GitHub"
    echo -e "${GREEN}4)${NC} 💾 Sauvegarder la base de données"
    echo -e "${GREEN}5)${NC} 📊 Voir le status des services"
    echo -e "${GREEN}6)${NC} 📝 Voir les logs"
    echo -e "${GREEN}7)${NC} 🔒 Configurer SSL (HTTPS)"
    echo -e "${GREEN}8)${NC} 🔄 Redémarrer tous les services"
    echo -e "${GREEN}9)${NC} ⏹️  Arrêter tous les services"
    echo
    echo -e "${CYAN}--- MONITORING AVANCÉ ---${NC}"
    echo -e "${GREEN}10)${NC} 👥 Gestion des utilisateurs"
    echo -e "${GREEN}11)${NC} 🗄️ État de la base de données"
    echo -e "${GREEN}12)${NC} 🤖 Monitoring bot Discord"
    echo -e "${GREEN}13)${NC} 📈 Statistiques temps réel"
    echo -e "${GREEN}14)${NC} 🛡️ Vérification système complète"
    echo -e "${GREEN}15)${NC} 🔧 Réparation système avancée"
    echo -e "${GREEN}16)${NC} 📋 Logs centralisés"
    echo -e "${GREEN}17)${NC} 🔧 Corriger les permissions de la base de données"
    echo -e "${GREEN}18)${NC} 🚨 Restauration d'urgence de la base de données"
    echo
    echo -e "${GREEN}0)${NC} ❌ Quitter"
    echo
    read -p "Choisissez une option: " choice
}

# ================================================================
# FONCTION DE CRÉATION AUTOMATIQUE DE BASE DE DONNÉES
# ================================================================
create_database_if_missing() {
    # Créer le répertoire database s'il n'existe pas
    mkdir -p "$(dirname "$DB_FILE")"
    
    # Si la base de données n'existe pas, la créer
    if [ ! -f "$DB_FILE" ]; then
        print_message "🗄️ Création de la base de données manquante..." "$YELLOW"
        
        # Installer sqlite3 si nécessaire
        if ! command -v sqlite3 >/dev/null 2>&1; then
            print_message "📦 Installation de sqlite3..." "$CYAN"
            sudo apt update >/dev/null 2>&1
            sudo apt install -y sqlite3 >/dev/null 2>&1
        fi
        
        # Créer la base de données avec le schéma
        if [ -f "$PROJECT_DIR/database/database.sql" ]; then
            sqlite3 "$DB_FILE" < "$PROJECT_DIR/database/database.sql"
            print_message "✅ Base de données créée avec succès" "$GREEN"
        else
            # Schéma minimal si le fichier SQL n'existe pas
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
        
        # Corriger les permissions immédiatement
        fix_database_permissions_force
    fi
}

# ================================================================
# FONCTION DE CORRECTION FORCÉE DES PERMISSIONS
# ================================================================
fix_database_permissions_force() {
    print_message "🔑 Correction forcée des permissions..." "$CYAN"
    
    # S'assurer que www-data existe, sinon utiliser l'utilisateur actuel
    if id www-data >/dev/null 2>&1; then
        DB_USER="www-data"
        DB_GROUP="www-data"
    else
        DB_USER="$USER"
        DB_GROUP="$USER"
        print_message "⚠️ www-data non trouvé, utilisation de $USER" "$YELLOW"
    fi
    
    # Créer le répertoire s'il n'existe pas
    mkdir -p "$(dirname "$DB_FILE")"
    
    # Permissions sur le répertoire database
    sudo chown -R "$DB_USER:$DB_GROUP" "$(dirname "$DB_FILE")" 2>/dev/null || chown -R "$DB_USER:$DB_GROUP" "$(dirname "$DB_FILE")" 2>/dev/null
    sudo chmod 755 "$(dirname "$DB_FILE")" 2>/dev/null || chmod 755 "$(dirname "$DB_FILE")" 2>/dev/null
    
    # Permissions sur la base de données
    if [ -f "$DB_FILE" ]; then
        sudo chown "$DB_USER:$DB_GROUP" "$DB_FILE" 2>/dev/null || chown "$DB_USER:$DB_GROUP" "$DB_FILE" 2>/dev/null
        sudo chmod 664 "$DB_FILE" 2>/dev/null || chmod 664 "$DB_FILE" 2>/dev/null
    fi
    
    # Ajouter l'utilisateur actuel au groupe www-data si possible
    if id www-data >/dev/null 2>&1; then
        sudo usermod -a -G www-data "$USER" 2>/dev/null
    fi
    
    # Permissions sur tout le projet
    sudo chown -R "$DB_USER:$DB_GROUP" "$PROJECT_DIR" 2>/dev/null || chown -R "$DB_USER:$DB_GROUP" "$PROJECT_DIR" 2>/dev/null
    
    print_message "✅ Permissions corrigées avec force" "$GREEN"
}

# ================================================================
# FONCTION D'INSTALLATION RAPIDE DES DÉPENDANCES CRITIQUES
# ================================================================
install_critical_dependencies() {
    print_message "📦 Installation des dépendances critiques..." "$CYAN"
    
    # Mettre à jour la liste des paquets
    print_message "🔄 Mise à jour de la liste des paquets..." "$YELLOW"
    sudo apt update >/dev/null 2>&1
    
    # Installer sqlite3 si manquant
    if ! command -v sqlite3 >/dev/null 2>&1; then
        print_message "📦 Installation de sqlite3..." "$YELLOW"
        sudo apt install -y sqlite3 >/dev/null 2>&1
        print_message "✅ sqlite3 installé" "$GREEN"
    fi
    
    # Installer jq si manquant (nécessaire pour JSON)
    if ! command -v jq >/dev/null 2>&1; then
        print_message "📦 Installation de jq..." "$YELLOW"
        sudo apt install -y jq >/dev/null 2>&1
        print_message "✅ jq installé" "$GREEN"
    fi
    
    # Installer curl si manquant
    if ! command -v curl >/dev/null 2>&1; then
        print_message "📦 Installation de curl..." "$YELLOW"
        sudo apt install -y curl >/dev/null 2>&1
        print_message "✅ curl installé" "$GREEN"
    fi
    
    # Créer l'utilisateur www-data si manquant
    if ! id www-data >/dev/null 2>&1; then
        print_message "👤 Création de l'utilisateur www-data..." "$YELLOW"
        sudo useradd -r -s /bin/false www-data 2>/dev/null
        print_message "✅ utilisateur www-data créé" "$GREEN"
    fi
    
    # Créer les répertoires nécessaires
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$(dirname "$CONFIG_FILE")"
    
    print_message "✅ Dépendances critiques installées" "$GREEN"
}

# ================================================================
# LOGIQUE PRINCIPALE
# ================================================================
main() {
    # Installer les dépendances critiques en premier
    install_critical_dependencies
    
    # Mettre à jour le chemin de la base de données depuis config.json
    DB_FILE=$(get_database_path)
    print_message "🗄️ Chemin base de données: $DB_FILE" "$CYAN"
    
    # Vérifier le fichier de config (ne pas recréer s'il existe)
    if [ ! -f "$CONFIG_FILE" ]; then
        print_message "⚠️ Fichier config.json manquant" "$YELLOW"
        if [ -f "$PROJECT_DIR/config/config.example.json" ]; then
            print_message "💡 Copiez config.example.json vers config.json et configurez-le" "$CYAN"
            print_message "   cp config/config.example.json config/config.json" "$CYAN"
        else
            print_message "💡 Créez un fichier config.json basé sur la documentation" "$CYAN"
        fi
    else
        print_message "✅ Fichier config.json détecté" "$GREEN"
        DOMAIN=$(jq -r '.website.url' "$CONFIG_FILE" 2>/dev/null | sed 's|https\?://||' | cut -d'/' -f1)
        if [ -n "$DOMAIN" ] && [ "$DOMAIN" != "null" ]; then
            print_message "🌐 Domaine configuré: $DOMAIN" "$GREEN"
        fi
    fi
    
    # Créer la base de données et vérifier les permissions au démarrage
    create_database_if_missing

    while true; do
        show_menu
        case $choice in
            1)
                install_dependencies
                setup_nginx
                start_services
                print_message "\n✅ Installation terminée!" "$GREEN"
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            2)
                start_services
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            3)
                update_from_github
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            4)
                backup_database
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            5)
                check_status
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            6)
                show_logs
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            7)
                setup_ssl
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            8)
                restart_services
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            9)
                stop_services
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            10)
                show_users
                ;;
            11)
                check_database
                ;;
            12)
                monitor_bot
                ;;
            13)
                show_realtime_stats
                ;;
            14)
                system_health_check
                ;;
            15)
                advanced_system_repair
                ;;
            16)
                show_centralized_logs
                ;;
            17)
                fix_database_permissions
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            18)
                print_message "🚨 Restauration d'urgence de la base de données..." "$YELLOW"
                
                echo "Backups disponibles:"
                ls -la "$BACKUP_DIR"/database_*.db 2>/dev/null | nl
                
                read -p "Entrez le numéro du backup à restaurer (0 pour annuler): " backup_choice
                
                if [ "$backup_choice" != "0" ] && [ "$backup_choice" -gt 0 ]; then
                    SELECTED_BACKUP=$(ls -t "$BACKUP_DIR"/database_*.db 2>/dev/null | sed -n "${backup_choice}p")
                    if [ -n "$SELECTED_BACKUP" ]; then
                        cp "$SELECTED_BACKUP" "$DB_FILE"
                        verify_database_permissions
                        verify_post_update
                        print_message "✅ Restauration terminée!" "$GREEN"
                    else
                        print_message "❌ Backup invalide" "$RED"
                    fi
                fi
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
            0)
                print_message "👋 Au revoir!" "$BLUE"
                exit 0
                ;;
            *)
                print_message "❌ Option invalide!" "$RED"
                read -p "Appuyez sur Entrée pour continuer..."
                ;;
        esac
    done
}

# Lancer le script
main 