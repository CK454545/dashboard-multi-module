#!/bin/bash

# ================================================================
# 🔧 Script de Correction des Permissions Git et Accès
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

print_message "🔧 CORRECTION DES PERMISSIONS GIT ET ACCÈS" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Détecter l'utilisateur actuel
CURRENT_USER=$(whoami)
print_message "👤 Utilisateur actuel: $CURRENT_USER" "$CYAN"

print_message "🔧 Ce script va corriger les permissions pour permettre l'accès Git et le développement" "$YELLOW"
echo ""

# 1. Corriger les permissions du projet entier
print_message "📁 Correction des permissions du projet..." "$CYAN"

# Donner les permissions à l'utilisateur actuel pour tout le projet
sudo chown -R $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR" 2>/dev/null
sudo chmod -R 755 "$PROJECT_DIR" 2>/dev/null

print_message "✅ Permissions du projet corrigées" "$GREEN"

# 2. Permissions spéciales pour les fichiers critiques
print_message "🔑 Configuration des permissions spéciales..." "$CYAN"

# Base de données - accessible par www-data ET l'utilisateur actuel
sudo chown $CURRENT_USER:www-data "$PROJECT_DIR/database/database.db" 2>/dev/null
sudo chmod 664 "$PROJECT_DIR/database/database.db" 2>/dev/null
sudo chown $CURRENT_USER:www-data "$PROJECT_DIR/database" 2>/dev/null
sudo chmod 775 "$PROJECT_DIR/database" 2>/dev/null

# Dossier web - accessible par www-data ET l'utilisateur actuel
sudo chown $CURRENT_USER:www-data "$PROJECT_DIR/web" 2>/dev/null
sudo chmod 775 "$PROJECT_DIR/web" 2>/dev/null

# Dossier config - accessible par l'utilisateur actuel
sudo chown $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/config" 2>/dev/null
sudo chmod 755 "$PROJECT_DIR/config" 2>/dev/null

# Dossier scripts - accessible par l'utilisateur actuel
sudo chown $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/scripts" 2>/dev/null
sudo chmod 755 "$PROJECT_DIR/scripts" 2>/dev/null

# Dossier bot - accessible par l'utilisateur actuel
sudo chown $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/bot" 2>/dev/null
sudo chmod 755 "$PROJECT_DIR/bot" 2>/dev/null

# Dossier backups - accessible par l'utilisateur actuel
sudo chown $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/backups" 2>/dev/null
sudo chmod 755 "$PROJECT_DIR/backups" 2>/dev/null

# Git - accessible par l'utilisateur actuel
sudo chown -R $CURRENT_USER:$CURRENT_USER "$PROJECT_DIR/.git" 2>/dev/null
sudo chmod -R 755 "$PROJECT_DIR/.git" 2>/dev/null

print_message "✅ Permissions spéciales configurées" "$GREEN"

# 3. Ajouter l'utilisateur au groupe www-data
print_message "👥 Configuration des groupes..." "$CYAN"

sudo usermod -a -G www-data $CURRENT_USER 2>/dev/null
print_message "✅ Utilisateur $CURRENT_USER ajouté au groupe www-data" "$GREEN"

# 4. Vérifier que Git fonctionne
print_message "🔍 Test de Git..." "$CYAN"

cd "$PROJECT_DIR"

# Test de lecture Git
if git status >/dev/null 2>&1; then
    print_message "✅ Git fonctionne correctement" "$GREEN"
else
    print_message "❌ Problème avec Git" "$RED"
    print_message "🔧 Tentative de correction..." "$YELLOW"
    sudo chown -R $CURRENT_USER:$CURRENT_USER .git/
    sudo chmod -R 755 .git/
fi

# 5. Test d'écriture dans la base de données
print_message "🗄️ Test de la base de données..." "$CYAN"

if [ -f "database/database.db" ]; then
    if sqlite3 "database/database.db" "CREATE TABLE IF NOT EXISTS test_permissions (id INTEGER); DROP TABLE test_permissions;" 2>/dev/null; then
        print_message "✅ Base de données accessible en écriture" "$GREEN"
    else
        print_message "❌ Problème d'écriture dans la base de données" "$RED"
        print_message "🔧 Correction des permissions de la base de données..." "$YELLOW"
        sudo chmod 666 "database/database.db"
    fi
else
    print_message "⚠️ Base de données non trouvée" "$YELLOW"
fi

# 6. Vérifier les services PM2
print_message "🤖 Vérification des services PM2..." "$CYAN"

PM2_ONLINE=$(pm2 jlist 2>/dev/null | jq '[.[] | select(.pm2_env.status == "online")] | length' 2>/dev/null || echo "0")

if [ "$PM2_ONLINE" -ge 1 ]; then
    print_message "✅ $PM2_ONLINE service(s) PM2 actif(s)" "$GREEN"
else
    print_message "⚠️ Aucun service PM2 actif" "$YELLOW"
    print_message "💡 Utilisez le script start-pm2-services.sh pour les démarrer" "$CYAN"
fi

# 7. Test de l'accès web
print_message "🌐 Test de l'accès web..." "$CYAN"

if curl -s -o /dev/null -w "%{http_code}" http://localhost/ | grep -q "200\|301\|302"; then
    print_message "✅ Accès web fonctionnel" "$GREEN"
else
    print_message "⚠️ Problème d'accès web détecté" "$YELLOW"
fi

# 8. Affichage des permissions finales
print_message "📊 Résumé des permissions..." "$CYAN"
echo ""

echo "Permissions du projet:"
ls -la "$PROJECT_DIR/" | head -5

echo ""
echo "Permissions de la base de données:"
ls -la "$PROJECT_DIR/database/"

echo ""
echo "Permissions Git:"
ls -la "$PROJECT_DIR/.git/" | head -3

echo ""
echo "Permissions du dossier web:"
ls -la "$PROJECT_DIR/web/" | head -3

echo ""
print_message "🎉 Correction des permissions terminée!" "$GREEN"
print_message "💡 Vous devriez maintenant pouvoir utiliser Git et tous les scripts" "$CYAN"
print_message "🌐 Votre site web devrait toujours être accessible" "$CYAN" 