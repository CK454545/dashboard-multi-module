#!/bin/bash

# ================================================================
# 🔧 Script de Correction Définitive des Permissions Base de Données
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

print_message "🔧 CORRECTION DÉFINITIVE DES PERMISSIONS BASE DE DONNÉES" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# 1. Vérifier l'état actuel
print_message "📋 État actuel des permissions..." "$CYAN"
ls -la database/
echo ""

# 2. Créer le dossier database s'il n'existe pas
if [ ! -d "database" ]; then
    print_message "📁 Création du dossier database..." "$YELLOW"
    mkdir -p database
fi

# 3. Créer la base de données si elle n'existe pas
if [ ! -f "database/database.db" ]; then
    print_message "🗄️ Création de la base de données..." "$YELLOW"
    touch database/database.db
fi

# 4. Corriger les permissions avec FORCE
print_message "🔧 Application des permissions avec FORCE..." "$YELLOW"

# Permissions sur le dossier
sudo chown -R www-data:www-data database/ 2>/dev/null
sudo chmod -R 755 database/ 2>/dev/null

# Permissions sur la base de données
sudo chown www-data:www-data database/database.db 2>/dev/null
sudo chmod 664 database/database.db 2>/dev/null

# 5. Ajouter l'utilisateur au groupe www-data
print_message "👤 Configuration des groupes..." "$YELLOW"
sudo usermod -a -G www-data ubuntu 2>/dev/null
sudo usermod -a -G www-data $USER 2>/dev/null

# 6. Permissions plus larges si nécessaire
print_message "🔓 Application de permissions plus larges..." "$YELLOW"
sudo chmod 666 database/database.db 2>/dev/null
sudo chmod 777 database/ 2>/dev/null

# 7. Vérifier que www-data peut écrire
print_message "🧪 Test d'écriture avec www-data..." "$CYAN"
if sudo -u www-data test -w database/database.db 2>/dev/null; then
    print_message "✅ www-data peut écrire dans la base" "$GREEN"
else
    print_message "❌ www-data ne peut pas écrire, application de permissions critiques..." "$RED"
    sudo chmod 777 database/database.db 2>/dev/null
    sudo chmod 777 database/ 2>/dev/null
fi

# 8. Test d'écriture réel
print_message "🧪 Test d'écriture réel..." "$CYAN"
if sudo -u www-data sqlite3 database/database.db "CREATE TABLE IF NOT EXISTS test_permissions (id INTEGER); DROP TABLE test_permissions;" 2>/dev/null; then
    print_message "✅ Test d'écriture réussi" "$GREEN"
else
    print_message "❌ Test d'écriture échoué, permissions critiques appliquées" "$RED"
fi

# 9. Vérification finale
print_message "📋 État final des permissions..." "$CYAN"
ls -la database/
echo ""

# 10. Test complet
print_message "🔍 Test complet de la base de données..." "$CYAN"

# Test de lecture
if [ -r database/database.db ]; then
    print_message "✅ Lecture: OK" "$GREEN"
else
    print_message "❌ Lecture: ÉCHEC" "$RED"
fi

# Test d'écriture
if [ -w database/database.db ]; then
    print_message "✅ Écriture: OK" "$GREEN"
else
    print_message "❌ Écriture: ÉCHEC" "$RED"
fi

# Test avec sqlite3
if sqlite3 database/database.db "SELECT 1;" >/dev/null 2>&1; then
    print_message "✅ SQLite3: OK" "$GREEN"
else
    print_message "❌ SQLite3: ÉCHEC" "$RED"
fi

# Test avec www-data
if sudo -u www-data sqlite3 database/database.db "SELECT 1;" >/dev/null 2>&1; then
    print_message "✅ www-data SQLite3: OK" "$GREEN"
else
    print_message "❌ www-data SQLite3: ÉCHEC" "$RED"
fi

echo ""
print_message "✅ CORRECTION TERMINÉE" "$GREEN"
print_message "💡 Relancez la vérification système pour confirmer" "$CYAN" 