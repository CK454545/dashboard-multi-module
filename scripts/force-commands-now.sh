#!/bin/bash

# Script pour forcer IMMÉDIATEMENT l'affichage des commandes

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
print_message "🚀 FORÇAGE IMMÉDIAT DES COMMANDES DISCORD" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

cd "$PROJECT_DIR" || exit

# 1. Vérifier que le script existe
if [ ! -f "$PROJECT_DIR/bot/force-commands-now.js" ]; then
    print_message "❌ Script force-commands-now.js introuvable!" "$RED"
    exit 1
fi

# 2. Arrêter temporairement le bot principal
print_message "🛑 Arrêt temporaire du bot principal..." "$YELLOW"
pm2 stop discord-bot

# 3. Exécuter le script de forçage
print_message "🚀 Exécution du forçage immédiat..." "$CYAN"
cd "$PROJECT_DIR/bot"
node force-commands-now.js

# 4. Redémarrer le bot principal
print_message "🔄 Redémarrage du bot principal..." "$YELLOW"
pm2 start bot.js --name discord-bot

# 5. Attendre et vérifier
sleep 3
print_message "📊 Statut du bot :" "$CYAN"
pm2 status discord-bot

echo ""
print_message "✅ FORÇAGE TERMINÉ !" "$GREEN"
echo ""
print_message "💡 Les commandes devraient apparaître IMMÉDIATEMENT :" "$YELLOW"
echo "  1. Allez sur Discord MAINTENANT"
echo "  2. Rafraîchissez la page (Ctrl+R)"
echo "  3. Tapez / dans un canal"
echo "  4. Les commandes devraient être visibles immédiatement"
echo ""
print_message "📋 Commandes forcées :" "$CYAN"
echo "  • /wait"
echo "  • /mfa"
echo "  • /start-mfa"
echo "  • /mfa-list"
echo "  • /voc"
echo "  • /end"
echo "  • /supptoken"
echo "  • /carte"
echo "  • /infos"
echo ""
print_message "🔧 Si les commandes n'apparaissent toujours pas :" "$RED"
echo "  • Vérifiez les permissions du bot sur Discord"
echo "  • Le bot doit avoir 'Use Slash Commands'"
echo "  • Réinvitez le bot avec le bon lien d'invitation"
echo "" 