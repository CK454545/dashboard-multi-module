#!/bin/bash

# Script pour corriger les commandes Discord

PROJECT_DIR="/var/www/dashboard-multi-modules"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

print_message() {
    local message=$1
    local color=$2
    echo -e "${color}${message}${NC}"
}

clear
print_message "🤖 CORRECTION DES COMMANDES DISCORD" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

cd "$PROJECT_DIR" || exit

# 1. Redémarrer le bot
print_message "🔄 Redémarrage du bot Discord..." "$YELLOW"
pm2 restart discord-bot

# Attendre que le bot démarre
sleep 5

# 2. Vérifier les logs
print_message "📋 Logs du bot après redémarrage :" "$CYAN"
pm2 logs discord-bot --lines 20 --nostream

echo ""
print_message "💡 VÉRIFICATIONS À FAIRE :" "$YELLOW"
echo ""
echo "1️⃣ **Permissions du bot sur Discord :**"
echo "   • Allez sur https://discord.com/developers/applications"
echo "   • Sélectionnez votre bot"
echo "   • Onglet 'OAuth2' > 'URL Generator'"
echo "   • Cochez : bot + applications.commands"
echo "   • Permissions : Administrator (ou au minimum : Send Messages, Use Slash Commands)"
echo ""
echo "2️⃣ **Réinviter le bot avec le bon lien :**"
echo "   • Utilisez le lien généré avec les bonnes permissions"
echo "   • Format : https://discord.com/api/oauth2/authorize?client_id=BOT_ID&permissions=8&scope=bot%20applications.commands"
echo ""
echo "3️⃣ **Attendre 1-2 minutes**"
echo "   • Les commandes slash peuvent prendre du temps à apparaître"
echo "   • Essayez de taper / dans un canal où le bot a accès"
echo ""
echo "4️⃣ **Vérifier que le bot est bien en ligne**"
echo "   • Le bot doit avoir un indicateur vert sur Discord"
echo "   • S'il est hors ligne, vérifiez le token dans config/config.json"
echo ""

# 3. Afficher le statut PM2
print_message "📊 Statut actuel du bot :" "$CYAN"
pm2 status discord-bot

echo ""
print_message "🔧 COMMANDES UTILES :" "$CYAN"
echo "• Voir les logs en temps réel : pm2 logs discord-bot"
echo "• Redémarrer le bot : pm2 restart discord-bot"
echo "• Arrêter le bot : pm2 stop discord-bot"
echo "• Démarrer le bot : pm2 start discord-bot"
echo ""

# 4. Vérifier si le bot a le bon token
print_message "🔑 Vérification du token..." "$YELLOW"
if [ -f "$PROJECT_DIR/config/config.json" ]; then
    token_length=$(jq -r '.discord.token' "$PROJECT_DIR/config/config.json" | wc -c)
    if [ $token_length -gt 50 ]; then
        print_message "✅ Token présent (longueur: $token_length caractères)" "$GREEN"
    else
        print_message "❌ Token manquant ou invalide!" "$RED"
    fi
fi

echo ""
print_message "═══════════════════════════════════════════════════════════════" "$BLUE"