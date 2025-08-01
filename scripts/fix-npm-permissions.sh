#!/bin/bash

# ================================================================
# 📦 Script de Correction des Permissions NPM
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

print_message() {
    echo -e "${2}${1}${NC}"
}

print_message "📦 CORRECTION DES PERMISSIONS NPM" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Détecter l'utilisateur actuel
CURRENT_USER=$(whoami)
print_message "👤 Utilisateur actuel: $CURRENT_USER" "$CYAN"

# 1. Corriger les permissions du dossier bot
print_message "🔧 Correction des permissions du dossier bot..." "$YELLOW"

# Donner les permissions à l'utilisateur actuel pour le dossier bot
sudo chown -R $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/bot" 2>/dev/null
sudo chmod -R 755 "$PROJECT_DIR/bot" 2>/dev/null

# Supprimer le package-lock.json problématique
if [ -f "$PROJECT_DIR/bot/package-lock.json" ]; then
    print_message "🗑️  Suppression du package-lock.json problématique..." "$YELLOW"
    sudo rm -f "$PROJECT_DIR/bot/package-lock.json"
fi

# Supprimer node_modules s'il existe
if [ -d "$PROJECT_DIR/bot/node_modules" ]; then
    print_message "🗑️  Suppression du dossier node_modules..." "$YELLOW"
    sudo rm -rf "$PROJECT_DIR/bot/node_modules"
fi

print_message "✅ Permissions du dossier bot corrigées" "$GREEN"

# 2. Installer les dépendances
print_message "📦 Installation des dépendances..." "$CYAN"

cd "$PROJECT_DIR/bot"

# Vérifier que package.json existe
if [ -f "package.json" ]; then
    print_message "📋 Fichier package.json trouvé" "$GREEN"
    
    # Installer les dépendances
    print_message "🔄 Installation des dépendances avec npm..." "$YELLOW"
    npm install --omit=dev
    
    if [ $? -eq 0 ]; then
        print_message "✅ Dépendances installées avec succès" "$GREEN"
    else
        print_message "❌ Erreur lors de l'installation des dépendances" "$RED"
        print_message "🔧 Tentative avec sudo..." "$YELLOW"
        sudo npm install --omit=dev
    fi
else
    print_message "❌ Fichier package.json manquant" "$RED"
    print_message "📄 Création d'un package.json minimal..." "$YELLOW"
    
    # Créer un package.json minimal
    cat > package.json <<EOF
{
  "name": "discord-bot",
  "version": "1.0.0",
  "description": "Discord Bot for Dashboard Multi-Modules",
  "main": "bot.js",
  "scripts": {
    "start": "node bot.js"
  },
  "dependencies": {
    "discord.js": "^14.0.0",
    "sqlite3": "^5.0.0"
  },
  "engines": {
    "node": ">=16.0.0"
  }
}
EOF
    
    print_message "✅ package.json créé" "$GREEN"
    npm install --omit=dev
fi

cd ..

# 3. Corriger les permissions pour PM2
print_message "🔧 Configuration des permissions pour PM2..." "$YELLOW"

# Donner les permissions à www-data pour la base de données et les fichiers critiques
sudo chown www-data:www-data "$PROJECT_DIR/database/database.db" 2>/dev/null
sudo chmod 664 "$PROJECT_DIR/database/database.db" 2>/dev/null

# Donner les permissions à l'utilisateur actuel pour les fichiers de développement
sudo chown -R $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/bot" 2>/dev/null
sudo chown -R $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/scripts" 2>/dev/null

# Ajouter l'utilisateur au groupe www-data
sudo usermod -a -G www-data $CURRENT_USER 2>/dev/null

print_message "✅ Permissions configurées pour PM2" "$GREEN"

# 4. Démarrer les services PM2
print_message "🤖 Démarrage des services PM2..." "$CYAN"

# Démarrer le bot Discord
if [ -f "bot/bot.js" ]; then
    pm2 delete discord-bot 2>/dev/null
    pm2 start bot/bot.js --name "discord-bot"
    print_message "✅ Bot Discord démarré" "$GREEN"
else
    print_message "❌ Fichier bot.js manquant" "$RED"
fi

# Démarrer les autres services
if [ -f "scripts/auto-backup.js" ]; then
    pm2 delete backup-system 2>/dev/null
    pm2 start scripts/auto-backup.js --name "backup-system" -- auto
    print_message "✅ Système de backup démarré" "$GREEN"
fi

if [ -f "scripts/auto-update-ubuntu.js" ]; then
    pm2 delete update-system 2>/dev/null
    pm2 start scripts/auto-update-ubuntu.js --name "update-system" -- auto
    print_message "✅ Système de mise à jour démarré" "$GREEN"
fi

if [ -f "scripts/system-metrics.js" ]; then
    pm2 delete system-metrics 2>/dev/null
    pm2 start scripts/system-metrics.js --name "system-metrics"
    print_message "✅ Système de monitoring démarré" "$GREEN"
fi

# Sauvegarder la configuration PM2
pm2 save
pm2 startup systemd -u $CURRENT_USER --hp /home/$CURRENT_USER

# 5. Vérification finale
print_message "📊 Vérification finale..." "$CYAN"
echo ""

# Afficher le statut PM2
pm2 status

# Compter les processus actifs
PM2_ONLINE=$(pm2 jlist 2>/dev/null | jq '[.[] | select(.pm2_env.status == "online")] | length' 2>/dev/null || echo "0")

if [ "$PM2_ONLINE" -ge 1 ]; then
    print_message "✅ $PM2_ONLINE service(s) PM2 actif(s)" "$GREEN"
else
    print_message "❌ Aucun service PM2 actif" "$RED"
fi

# Vérifier les permissions
print_message "🔍 Vérification des permissions..." "$CYAN"
echo "Permissions du dossier bot:"
ls -la "$PROJECT_DIR/bot/" | head -5

echo ""
echo "Permissions de la base de données:"
ls -la "$PROJECT_DIR/database/"

echo ""
print_message "🎉 Correction des permissions NPM terminée!" "$GREEN"
print_message "💡 Les services PM2 devraient maintenant fonctionner" "$CYAN" 