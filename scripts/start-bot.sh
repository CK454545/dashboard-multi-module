#!/bin/bash

# Script de démarrage automatique du bot Discord avec correction des permissions
# Usage: ./scripts/start-bot.sh

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Fonction pour afficher des messages colorés
print_message() {
    echo -e "${2}${1}${NC}"
}

# Configuration
PROJECT_DIR="/var/www/dashboard-multi-modules"
DB_FILE="$PROJECT_DIR/database/database.db"
BOT_DIR="$PROJECT_DIR/bot"

print_message "🤖 Démarrage automatique du bot Discord..." "$BLUE"
print_message "📁 Répertoire du projet: $PROJECT_DIR" "$CYAN"

# Vérifier que nous sommes dans le bon répertoire
if [ ! -d "$PROJECT_DIR" ]; then
    print_message "❌ Répertoire du projet introuvable: $PROJECT_DIR" "$RED"
    exit 1
fi

cd "$PROJECT_DIR"

# ÉTAPE 1: CORRECTION AUTOMATIQUE DES PERMISSIONS
print_message "🔧 Correction automatique des permissions..." "$YELLOW"

# S'assurer que le fichier existe
if [ ! -f "$DB_FILE" ]; then
    print_message "⚠️  Base de données introuvable, création..." "$YELLOW"
    touch "$DB_FILE"
fi

# Corriger les permissions automatiquement
if sudo chown ubuntu:ubuntu "$DB_FILE" 2>/dev/null; then
    print_message "✅ Propriétaire corrigé: ubuntu:ubuntu" "$GREEN"
else
    print_message "⚠️  Impossible de changer le propriétaire" "$YELLOW"
fi

if sudo chmod 664 "$DB_FILE" 2>/dev/null; then
    print_message "✅ Permissions corrigées: 664" "$GREEN"
else
    print_message "⚠️  Impossible de changer les permissions" "$YELLOW"
fi

# Vérifier que les permissions sont correctes
if [ -r "$DB_FILE" ] && [ -w "$DB_FILE" ]; then
    print_message "✅ Permissions de la base de données OK" "$GREEN"
else
    print_message "❌ Problème de permissions persistants" "$RED"
    print_message "📋 Permissions actuelles: $(ls -la "$DB_FILE")" "$CYAN"
fi

# ÉTAPE 2: VÉRIFICATION DE LA BASE DE DONNÉES
print_message "🔍 Vérification de la base de données..." "$BLUE"

if [ -f "$DB_FILE" ]; then
    DB_SIZE=$(stat -c%s "$DB_FILE" 2>/dev/null || echo "0")
    if [ "$DB_SIZE" -gt 0 ]; then
        USER_COUNT=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")
        print_message "✅ Base de données OK (taille: ${DB_SIZE} bytes, utilisateurs: $USER_COUNT)" "$GREEN"
    else
        print_message "⚠️  Base de données vide" "$YELLOW"
    fi
else
    print_message "❌ Base de données introuvable" "$RED"
fi

# ÉTAPE 3: INSTALLATION DES DÉPENDANCES
print_message "📦 Vérification des dépendances..." "$BLUE"

if [ -d "$BOT_DIR" ]; then
    cd "$BOT_DIR"
    
    if [ ! -d "node_modules" ]; then
        print_message "📦 Installation des dépendances..." "$YELLOW"
        npm install --production
    else
        print_message "✅ Dépendances déjà installées" "$GREEN"
    fi
    
    cd "$PROJECT_DIR"
else
    print_message "❌ Répertoire bot introuvable" "$RED"
    exit 1
fi

# ÉTAPE 4: DÉMARRAGE DU BOT
print_message "🚀 Démarrage du bot Discord..." "$BLUE"

# Arrêter le bot s'il tourne déjà
if pm2 list | grep -q "discord-bot"; then
    print_message "🔄 Arrêt du bot existant..." "$YELLOW"
    pm2 stop discord-bot 2>/dev/null || true
    pm2 delete discord-bot 2>/dev/null || true
fi

# Démarrer le bot
print_message "🎮 Démarrage du bot Discord..." "$GREEN"
pm2 start "$BOT_DIR/bot.js" --name "discord-bot" --cwd "$BOT_DIR"

# Vérifier que le bot démarre correctement
sleep 3
if pm2 list | grep -q "discord-bot.*online"; then
    print_message "✅ Bot Discord démarré avec succès!" "$GREEN"
    print_message "📊 Logs du bot:" "$CYAN"
    pm2 logs discord-bot --lines 10
else
    print_message "❌ Erreur lors du démarrage du bot" "$RED"
    pm2 logs discord-bot --lines 20
    exit 1
fi

print_message "🎉 Bot Discord prêt à l'utilisation!" "$GREEN"
print_message "📝 Pour voir les logs: pm2 logs discord-bot" "$CYAN"
print_message "📝 Pour arrêter: pm2 stop discord-bot" "$CYAN"
print_message "📝 Pour redémarrer: pm2 restart discord-bot" "$CYAN" 