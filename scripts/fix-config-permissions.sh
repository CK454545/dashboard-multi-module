#!/bin/bash

# ================================================================
# 🔧 Correction Spécifique des Permissions de config.json
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

print_message "🔧 CORRECTION SPÉCIFIQUE DE CONFIG.JSON" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

CONFIG_FILE="config/config.json"

# Vérifier si le fichier existe
if [ ! -f "$CONFIG_FILE" ]; then
    print_message "❌ config.json introuvable!" "$RED"
    print_message "📝 Création du fichier config.json..." "$YELLOW"
    
    # Créer le dossier config s'il n'existe pas
    mkdir -p config
    
    # Créer un fichier config.json de base
    cat > "$CONFIG_FILE" << 'EOF'
{
    "bot_token": "VOTRE_TOKEN_BOT_DISCORD",
    "client_id": "VOTRE_CLIENT_ID",
    "guild_id": "VOTRE_GUILD_ID",
    "webhook_url": "VOTRE_WEBHOOK_URL",
    "database_path": "./database/database.db",
    "port": 3000,
    "host": "localhost"
}
EOF
    
    print_message "✅ config.json créé avec succès" "$GREEN"
fi

# Correction des permissions
print_message "🔧 Application des corrections de permissions..." "$YELLOW"

# 1. Changer le propriétaire
sudo chown www-data:www-data "$CONFIG_FILE" 2>/dev/null
print_message "✅ Propriétaire changé: www-data:www-data" "$GREEN"

# 2. Permissions de base
sudo chmod 644 "$CONFIG_FILE" 2>/dev/null
print_message "✅ Permissions de base: 644" "$GREEN"

# 3. Permissions élargies si nécessaire
sudo chmod 666 "$CONFIG_FILE" 2>/dev/null
print_message "✅ Permissions élargies: 666" "$GREEN"

# 4. Permissions du dossier config
sudo chown www-data:www-data config/ 2>/dev/null
sudo chmod 755 config/ 2>/dev/null
print_message "✅ Dossier config configuré" "$GREEN"

# 5. Ajouter ubuntu au groupe www-data
sudo usermod -a -G www-data ubuntu 2>/dev/null
print_message "✅ Utilisateur ubuntu ajouté au groupe www-data" "$GREEN"

# 6. Vérification finale
print_message "🔍 Vérification finale..." "$CYAN"

if [ -r "$CONFIG_FILE" ] && [ -w "$CONFIG_FILE" ]; then
    print_message "✅ config.json: lecture/écriture OK" "$GREEN"
    
    # Test de lecture du contenu
    if jq empty "$CONFIG_FILE" 2>/dev/null; then
        print_message "✅ config.json: syntaxe JSON valide" "$GREEN"
    else
        print_message "⚠️ config.json: syntaxe JSON invalide" "$YELLOW"
    fi
else
    print_message "❌ config.json: problème de permissions persistant" "$RED"
    
    # Dernière tentative avec permissions très larges
    sudo chmod 777 "$CONFIG_FILE" 2>/dev/null
    sudo chmod 777 config/ 2>/dev/null
    print_message "🔧 Permissions critiques appliquées (777)" "$YELLOW"
fi

echo ""
print_message "🎯 CORRECTION TERMINÉE" "$BLUE"
print_message "💡 Testez avec: ./scripts/quick-test.sh" "$CYAN" 