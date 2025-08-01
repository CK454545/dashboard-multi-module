#!/bin/bash

# ================================================================
# 🔧 Correction de la Configuration du Bot
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

# Vérifier que la base de données existe au bon endroit
if [ ! -f "database/database.db" ]; then
    print_message "⚠️ Base de données introuvable dans database/database.db" "$YELLOW"
    print_message "💡 Création d'un lien symbolique..." "$CYAN"
    ln -sf database/database.db database.db 2>/dev/null
fi

print_message "🔧 CORRECTION DE LA CONFIGURATION DU BOT" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# 1. Vérifier et corriger config.json
print_message "📄 Vérification de config.json..." "$CYAN"
if [ -f "config/config.json" ]; then
    print_message "✅ config.json trouvé" "$GREEN"
    
    # Vérifier la syntaxe JSON
    if jq empty config/config.json 2>/dev/null; then
        print_message "✅ Syntaxe JSON valide" "$GREEN"
    else
        print_message "❌ Syntaxe JSON invalide" "$RED"
        exit 1
    fi
else
    print_message "❌ config.json introuvable" "$RED"
    exit 1
fi

# 2. Corriger les permissions
print_message "🔧 Correction des permissions..." "$YELLOW"
sudo chown www-data:www-data config/config.json 2>/dev/null
sudo chmod 666 config/config.json 2>/dev/null
sudo chown www-data:www-data config/ 2>/dev/null
sudo chmod 755 config/ 2>/dev/null
print_message "✅ Permissions corrigées" "$GREEN"

# 3. Vérifier que le bot peut lire le fichier
print_message "🧪 Test de lecture par le bot..." "$CYAN"
if [ -r "config/config.json" ]; then
    print_message "✅ config.json lisible" "$GREEN"
else
    print_message "❌ config.json non lisible" "$RED"
    sudo chmod 777 config/config.json 2>/dev/null
    print_message "✅ Permissions élargies appliquées" "$GREEN"
fi

# 4. Redémarrer le bot
print_message "🔄 Redémarrage du bot Discord..." "$YELLOW"
pm2 restart discord-bot 2>/dev/null
print_message "✅ Bot redémarré" "$GREEN"

# 5. Vérifier le statut du bot
print_message "📊 Vérification du statut du bot..." "$CYAN"
sleep 3
BOT_STATUS=$(pm2 jlist 2>/dev/null | jq -r '.[] | select(.name=="discord-bot") | .pm2_env.status' 2>/dev/null)

if [ "$BOT_STATUS" = "online" ]; then
    print_message "✅ Bot Discord en ligne" "$GREEN"
else
    print_message "⚠️ Bot Discord: $BOT_STATUS" "$YELLOW"
    print_message "📋 Logs du bot:" "$CYAN"
    pm2 logs discord-bot --lines 10 --nostream 2>/dev/null
fi

echo ""
print_message "🎯 Correction terminée!" "$BLUE"
print_message "💡 Vérifiez les logs avec: pm2 logs discord-bot" "$CYAN" 