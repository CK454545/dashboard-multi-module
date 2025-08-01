#!/bin/bash

# Script pour redémarrer le bot sans supprimer les commandes

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
print_message "🤖 REDÉMARRAGE DU BOT (SANS SUPPRIMER LES COMMANDES)" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

cd "$PROJECT_DIR" || exit

# 1. Arrêter le bot
print_message "🛑 Arrêt du bot..." "$YELLOW"
pm2 stop discord-bot

# 2. Redémarrer le bot
print_message "🚀 Redémarrage du bot..." "$YELLOW"
pm2 start bot/bot.js --name discord-bot

# 3. Attendre que le bot démarre
sleep 5

# 4. Vérifier le statut
print_message "📊 Statut du bot :" "$CYAN"
pm2 status discord-bot

# 5. Afficher les logs
print_message "📋 Logs du bot (dernières 15 lignes) :" "$CYAN"
pm2 logs discord-bot --lines 15 --nostream

echo ""
print_message "✅ REDÉMARRAGE TERMINÉ !" "$GREEN"
echo ""
print_message "💡 Le bot ne supprime plus les commandes au démarrage" "$YELLOW"
echo "   Les commandes Discord devraient maintenant rester visibles !"
echo ""
print_message "🔧 Pour voir les logs en temps réel :" "$CYAN"
echo "  pm2 logs discord-bot"
echo "" 