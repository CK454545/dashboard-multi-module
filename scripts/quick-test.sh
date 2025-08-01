#!/bin/bash

# ================================================================
# ⚡ Test Rapide des Permissions
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

print_message "⚡ TEST RAPIDE DES PERMISSIONS" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Test rapide des fichiers critiques
CRITICAL_FILES=(
    "database/database.db"
    "config/config.json"
    "scripts/ubuntu-manager.sh"
    "bot/bot.js"
)

SUCCESS=0
TOTAL=${#CRITICAL_FILES[@]}

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        if [ -r "$file" ] && [ -w "$file" ]; then
            print_message "✅ $file: OK" "$GREEN"
            ((SUCCESS++))
        else
            print_message "❌ $file: problème de permissions" "$RED"
        fi
    else
        print_message "⚠️ $file: introuvable" "$YELLOW"
    fi
done

# Test sqlite3
print_message "📦 Test de sqlite3..." "$CYAN"
if [ -d "bot/node_modules" ]; then
    cd bot
    if npm list sqlite3 >/dev/null 2>&1; then
        print_message "✅ sqlite3 installé" "$GREEN"
        ((SUCCESS++))
    else
        print_message "❌ sqlite3 non installé" "$RED"
    fi
    cd ..
else
    print_message "⚠️ node_modules introuvable" "$YELLOW"
fi

# Test PM2
print_message "📊 Test de PM2..." "$CYAN"
PM2_PROCESSES=$(pm2 jlist 2>/dev/null | jq length 2>/dev/null || echo "0")
if [ "$PM2_PROCESSES" -gt 0 ]; then
    print_message "✅ PM2: $PM2_PROCESSES processus actifs" "$GREEN"
    ((SUCCESS++))
else
    print_message "❌ PM2: aucun processus actif" "$RED"
fi

TOTAL=$((TOTAL + 2))

echo ""
print_message "📊 RÉSULTAT: $SUCCESS/$TOTAL" "$BLUE"

if [ $SUCCESS -eq $TOTAL ]; then
    print_message "🎉 Tout fonctionne parfaitement!" "$GREEN"
else
    print_message "⚠️ Certains problèmes détectés" "$YELLOW"
    print_message "💡 Lancez: ./scripts/ubuntu-manager.sh puis option 14" "$CYAN"
fi 