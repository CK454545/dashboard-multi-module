#!/bin/bash

# ================================================================
# 🔧 Script de Correction des Permissions Après Mise à Jour
# ================================================================

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

print_message() {
    echo -e "${2}${1}${NC}"
}

# Se placer dans le bon répertoire
cd /var/www/dashboard-multi-modules

print_message "🔧 Correction des permissions après mise à jour..." "$BLUE"

# 1. Corriger les permissions de tout le projet
print_message "📁 Correction des permissions du projet..." "$YELLOW"
sudo chown -R ubuntu:ubuntu .
sudo chmod -R 755 .

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

# 4. Ajouter l'utilisateur au groupe www-data
print_message "👤 Configuration des groupes..." "$YELLOW"
sudo usermod -a -G www-data ubuntu 2>/dev/null

# 5. Corriger les permissions du dossier bot
print_message "🤖 Correction des permissions du bot..." "$YELLOW"
sudo chown -R ubuntu:ubuntu bot/ 2>/dev/null
sudo chmod -R 755 bot/ 2>/dev/null

# 6. Réinstaller les dépendances npm
print_message "📦 Réinstallation des dépendances npm..." "$YELLOW"
cd bot
npm install --production 2>/dev/null
cd ..

# 7. Vérifier que tout fonctionne
print_message "🔍 Vérification finale..." "$CYAN"

# Vérifier git
if git status >/dev/null 2>&1; then
    print_message "✅ Git fonctionne correctement" "$GREEN"
else
    print_message "❌ Problème avec Git" "$RED"
fi

# Vérifier la base de données
if [ -r database/database.db ] && [ -w database/database.db ]; then
    print_message "✅ Base de données accessible" "$GREEN"
else
    print_message "❌ Problème avec la base de données" "$RED"
fi

# Vérifier npm
if [ -d bot/node_modules ]; then
    print_message "✅ Dépendances npm installées" "$GREEN"
else
    print_message "❌ Problème avec les dépendances npm" "$RED"
fi

print_message "✅ Correction des permissions terminée!" "$GREEN"
print_message "💡 Vous pouvez maintenant relancer la vérification système" "$CYAN" 