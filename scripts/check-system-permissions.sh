#!/bin/bash

# ================================================================
# 🔍 Vérification Complète des Permissions Système
# ================================================================

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

print_message() {
    echo -e "${2}${1}${NC}"
}

# Se placer dans le bon répertoire
cd /var/www/dashboard-multi-modules

print_message "🔍 VÉRIFICATION COMPLÈTE DES PERMISSIONS SYSTÈME" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Test 1: Base de données
print_message "🗄️ Vérification de la base de données..." "$CYAN"
if [ -f "database/database.db" ]; then
    if [ -r "database/database.db" ] && [ -w "database/database.db" ]; then
        print_message "✅ Base de données: lecture/écriture OK" "$GREEN"
    else
        print_message "❌ Base de données: problème de permissions" "$RED"
    fi
    
    if sudo -u www-data test -w database/database.db 2>/dev/null; then
        print_message "✅ www-data peut écrire dans la base" "$GREEN"
    else
        print_message "❌ www-data ne peut pas écrire dans la base" "$RED"
    fi
    
    # Vérifier la taille
    DB_SIZE=$(stat -c%s "database/database.db" 2>/dev/null || echo "0")
    print_message "📊 Taille de la base: ${DB_SIZE} bytes" "$PURPLE"
else
    print_message "❌ Base de données introuvable" "$RED"
fi

# Test 2: config.json
print_message "📄 Vérification de config.json..." "$CYAN"
if [ -f "config/config.json" ]; then
    if [ -r "config/config.json" ] && [ -w "config/config.json" ]; then
        print_message "✅ config.json: lecture/écriture OK" "$GREEN"
    else
        print_message "❌ config.json: problème de permissions" "$RED"
    fi
    
    # Vérifier la validité JSON
    if jq . "config/config.json" >/dev/null 2>&1; then
        print_message "✅ config.json: syntaxe JSON valide" "$GREEN"
    else
        print_message "❌ config.json: erreur de syntaxe JSON" "$RED"
    fi
else
    print_message "❌ config.json introuvable" "$RED"
fi

# Test 3: sqlite3 pour Node.js
print_message "📦 Vérification de sqlite3 pour Node.js..." "$CYAN"
if [ -d "bot/node_modules" ]; then
    cd bot
    if npm list sqlite3 >/dev/null 2>&1; then
        print_message "✅ sqlite3 installé pour Node.js" "$GREEN"
    else
        print_message "❌ sqlite3 non installé pour Node.js" "$RED"
    fi
    cd ..
else
    print_message "⚠️ Dossier node_modules introuvable" "$YELLOW"
fi

# Test 4: Permissions du projet
print_message "📁 Vérification des permissions du projet..." "$CYAN"
if [ -r "." ] && [ -w "." ]; then
    print_message "✅ Projet: lecture/écriture OK" "$GREEN"
else
    print_message "❌ Projet: problème de permissions" "$RED"
fi

# Test 5: Scripts
print_message "📜 Vérification des scripts..." "$CYAN"
if [ -x "scripts/ubuntu-manager.sh" ]; then
    print_message "✅ ubuntu-manager.sh: exécutable" "$GREEN"
else
    print_message "❌ ubuntu-manager.sh: non exécutable" "$RED"
fi

# Test 6: Bot
print_message "🤖 Vérification du bot..." "$CYAN"
if [ -f "bot/bot.js" ] && [ -r "bot/bot.js" ]; then
    print_message "✅ bot.js: accessible" "$GREEN"
else
    print_message "❌ bot.js: inaccessible" "$RED"
fi

# Test 7: Services système
print_message "🔧 Vérification des services système..." "$CYAN"
if systemctl is-active --quiet nginx; then
    print_message "✅ Nginx: actif" "$GREEN"
else
    print_message "❌ Nginx: inactif" "$RED"
fi

if systemctl is-active --quiet php8.1-fpm; then
    print_message "✅ PHP-FPM: actif" "$GREEN"
else
    print_message "❌ PHP-FPM: inactif" "$RED"
fi

# Test 8: PM2
print_message "📊 Vérification de PM2..." "$CYAN"
PM2_PROCESSES=$(pm2 jlist 2>/dev/null | jq length 2>/dev/null || echo "0")
if [ "$PM2_PROCESSES" -gt 0 ]; then
    print_message "✅ PM2: $PM2_PROCESSES processus actifs" "$GREEN"
else
    print_message "❌ PM2: aucun processus actif" "$RED"
fi

# Test 9: Utilisateur et groupes
print_message "👤 Vérification des utilisateurs et groupes..." "$CYAN"
if id www-data >/dev/null 2>&1; then
    print_message "✅ Utilisateur www-data existe" "$GREEN"
else
    print_message "❌ Utilisateur www-data n'existe pas" "$RED"
fi

if groups ubuntu | grep -q www-data; then
    print_message "✅ ubuntu est dans le groupe www-data" "$GREEN"
else
    print_message "❌ ubuntu n'est pas dans le groupe www-data" "$RED"
fi

echo ""
print_message "📊 RÉSUMÉ COMPLET DES VÉRIFICATIONS" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"

# Compter les tests réussis
SUCCESS=0
TOTAL=9

if [ -r "database/database.db" ] && [ -w "database/database.db" ]; then ((SUCCESS++)); fi
if [ -r "config/config.json" ] && [ -w "config/config.json" ]; then ((SUCCESS++)); fi
if [ -d "bot/node_modules" ] && npm list sqlite3 >/dev/null 2>&1; then ((SUCCESS++)); fi
if [ -r "." ] && [ -w "." ]; then ((SUCCESS++)); fi
if [ -x "scripts/ubuntu-manager.sh" ]; then ((SUCCESS++)); fi
if [ -f "bot/bot.js" ] && [ -r "bot/bot.js" ]; then ((SUCCESS++)); fi
if systemctl is-active --quiet nginx; then ((SUCCESS++)); fi
if systemctl is-active --quiet php8.1-fpm; then ((SUCCESS++)); fi
if [ "$PM2_PROCESSES" -gt 0 ]; then ((SUCCESS++)); fi

print_message "✅ Vérifications réussies: $SUCCESS/$TOTAL" "$GREEN"

if [ $SUCCESS -eq $TOTAL ]; then
    print_message "🎉 Système en parfait état!" "$GREEN"
else
    print_message "⚠️ Certaines vérifications ont échoué." "$YELLOW"
    print_message "💡 Lancez la correction automatique avec l'option 14" "$CYAN"
fi

echo ""
print_message "🚀 Actions recommandées:" "$PURPLE"
echo "├── Pour corriger automatiquement: ./scripts/ubuntu-manager.sh puis option 14"
echo "├── Pour démarrer les services: ./scripts/ubuntu-manager.sh puis option 2"
echo "└── Pour vérifier le statut: ./scripts/ubuntu-manager.sh puis option 5" 