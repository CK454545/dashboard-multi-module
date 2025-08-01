#!/bin/bash

# ================================================================
# 🤖 Script de Démarrage des Services PM2
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

print_message "🤖 DÉMARRAGE DES SERVICES PM2" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Vérifier que PM2 est installé
if ! command -v pm2 >/dev/null 2>&1; then
    print_message "📦 Installation de PM2..." "$YELLOW"
    sudo npm install -g pm2
fi

cd "$PROJECT_DIR" || exit

# 1. Démarrer le bot Discord
print_message "🤖 Démarrage du bot Discord..." "$CYAN"

# Vérifier que le dossier bot existe
if [ -d "bot" ] && [ -f "bot/bot.js" ]; then
    # Installer les dépendances si nécessaire
    if [ -f "bot/package.json" ]; then
        print_message "📦 Installation des dépendances du bot..." "$YELLOW"
        cd bot && npm install --production && cd ..
    fi
    
    # Démarrer le bot
    pm2 delete discord-bot 2>/dev/null
    pm2 start bot/bot.js --name "discord-bot"
    print_message "✅ Bot Discord démarré" "$GREEN"
else
    print_message "❌ Dossier bot ou fichier bot.js manquant" "$RED"
fi

# 2. Démarrer le système de backup
print_message "💾 Démarrage du système de backup..." "$CYAN"

if [ -f "scripts/auto-backup.js" ]; then
    pm2 delete backup-system 2>/dev/null
    pm2 start scripts/auto-backup.js --name "backup-system" -- auto
    print_message "✅ Système de backup démarré" "$GREEN"
else
    print_message "⚠️  Script auto-backup.js non trouvé" "$YELLOW"
fi

# 3. Démarrer le système de mise à jour
print_message "🔄 Démarrage du système de mise à jour..." "$CYAN"

if [ -f "scripts/auto-update-ubuntu.js" ]; then
    pm2 delete update-system 2>/dev/null
    pm2 start scripts/auto-update-ubuntu.js --name "update-system" -- auto
    print_message "✅ Système de mise à jour démarré" "$GREEN"
else
    print_message "⚠️  Script auto-update-ubuntu.js non trouvé" "$YELLOW"
fi

# 4. Démarrer le système de monitoring
print_message "📊 Démarrage du système de monitoring..." "$CYAN"

if [ -f "scripts/system-metrics.js" ]; then
    pm2 delete system-metrics 2>/dev/null
    pm2 start scripts/system-metrics.js --name "system-metrics"
    print_message "✅ Système de monitoring démarré" "$GREEN"
else
    print_message "⚠️  Script system-metrics.js non trouvé" "$YELLOW"
fi

# 5. Sauvegarder la configuration PM2
print_message "💾 Sauvegarde de la configuration PM2..." "$CYAN"
pm2 save
pm2 startup systemd -u $USER --hp /home/$USER

# 6. Vérification finale
print_message "📊 Vérification des services PM2..." "$CYAN"
echo ""

# Afficher le statut de tous les processus
pm2 status

# Compter les processus actifs
PM2_ONLINE=$(pm2 jlist 2>/dev/null | jq '[.[] | select(.pm2_env.status == "online")] | length' 2>/dev/null || echo "0")

if [ "$PM2_ONLINE" -ge 1 ]; then
    print_message "✅ $PM2_ONLINE service(s) PM2 actif(s)" "$GREEN"
else
    print_message "❌ Aucun service PM2 actif" "$RED"
fi

# Afficher les logs récents du bot Discord
if pm2 describe discord-bot >/dev/null 2>&1; then
    print_message "📝 Logs récents du bot Discord:" "$CYAN"
    pm2 logs discord-bot --lines 5 --nostream 2>/dev/null | tail -5 || echo "Aucun log disponible"
fi

echo ""
print_message "🎉 Démarrage des services PM2 terminé!" "$GREEN"
print_message "💡 Utilisez 'pm2 logs' pour voir les logs en temps réel" "$CYAN" 