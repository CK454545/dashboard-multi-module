#!/bin/bash

# Script pour vérifier et corriger les permissions Discord

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
print_message "🔍 DIAGNOSTIC DES PERMISSIONS DISCORD" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

cd "$PROJECT_DIR" || exit

# 1. Vérifier le token du bot
print_message "🔑 Vérification du token..." "$CYAN"
if [ -f "$PROJECT_DIR/config/config.json" ]; then
    token_length=$(jq -r '.discord.token' "$PROJECT_DIR/config/config.json" | wc -c)
    if [ $token_length -gt 50 ]; then
        print_message "✅ Token présent (longueur: $token_length caractères)" "$GREEN"
    else
        print_message "❌ Token manquant ou invalide!" "$RED"
    fi
else
    print_message "❌ Fichier config.json introuvable!" "$RED"
fi

# 2. Afficher les informations importantes
echo ""
print_message "📋 INFORMATIONS IMPORTANTES :" "$YELLOW"
echo ""
echo "🆔 Bot ID: 1396574688028655686"
echo "🤖 Nom du bot: CreatorHub#3769"
echo ""
echo "🔗 LIEN D'INVITATION CORRECT :"
echo "https://discord.com/api/oauth2/authorize?client_id=1396574688028655686&permissions=8&scope=bot%20applications.commands"
echo ""

# 3. Instructions détaillées
print_message "📝 INSTRUCTIONS POUR CORRIGER :" "$CYAN"
echo ""
echo "1️⃣ **Réinvitez le bot avec le bon lien :**"
echo "   • Copiez le lien ci-dessus"
echo "   • Ouvrez-le dans votre navigateur"
echo "   • Sélectionnez votre serveur"
echo "   • Cliquez sur 'Autoriser'"
echo ""
echo "2️⃣ **Vérifiez les permissions du bot sur Discord :**"
echo "   • Allez dans les paramètres du serveur"
echo "   • Rôles → Cherchez le rôle du bot"
echo "   • Vérifiez qu'il a ces permissions :"
echo "     ✅ Utiliser les commandes slash"
echo "     ✅ Envoyer des messages"
echo "     ✅ Intégrer des liens"
echo "     ✅ Lire l'historique des messages"
echo ""
echo "3️⃣ **Vérifiez que le bot est en ligne :**"
echo "   • Le bot doit avoir un indicateur vert"
echo "   • S'il est hors ligne, vérifiez le token"
echo ""

# 4. Redémarrer le bot avec forçage
print_message "🚀 REDÉMARRAGE AVEC FORÇAGE..." "$YELLOW"
echo ""

pm2 stop discord-bot
sleep 2
pm2 start bot/bot.js --name discord-bot
sleep 5

print_message "📊 Statut du bot :" "$CYAN"
pm2 status discord-bot

echo ""
print_message "📋 Logs récents :" "$CYAN"
pm2 logs discord-bot --lines 10 --nostream

echo ""
print_message "💡 APRÈS LE REDÉMARRAGE :" "$GREEN"
echo "  1. Allez sur Discord"
echo "  2. Rafraîchissez la page (Ctrl+R)"
echo "  3. Attendez 1-2 minutes"
echo "  4. Tapez / dans un canal"
echo "  5. Les commandes devraient apparaître"
echo ""
print_message "🔧 Si ça ne marche toujours pas :" "$RED"
echo "  • Le problème vient des permissions Discord"
echo "  • Réinvitez le bot avec le lien fourni"
echo "  • Vérifiez que le bot a les bonnes permissions"
echo "" 