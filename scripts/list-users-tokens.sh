#!/bin/bash

# Script pour lister tous les utilisateurs avec leurs tokens et URLs

PROJECT_DIR="/var/www/dashboard-multi-modules"
DB_FILE="$PROJECT_DIR/database/database.db"
CONFIG_FILE="$PROJECT_DIR/config/config.json"

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

# Vérifier que la base de données existe
if [ ! -f "$DB_FILE" ]; then
    print_message "❌ Base de données introuvable : $DB_FILE" "$RED"
    exit 1
fi

# Obtenir l'URL du site depuis config.json
website_url="https://myfullagency-connect.fr"
if [ -f "$CONFIG_FILE" ]; then
    url_from_config=$(jq -r '.website.url // empty' "$CONFIG_FILE" 2>/dev/null)
    if [ -n "$url_from_config" ]; then
        website_url="$url_from_config"
    fi
fi

clear
print_message "👥 LISTE DES UTILISATEURS ET LEURS TOKENS" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Compter le nombre d'utilisateurs
user_count=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users;" 2>/dev/null)
print_message "📊 Nombre total d'utilisateurs : $user_count" "$CYAN"
echo ""

# Options d'affichage
echo "Options d'affichage :"
echo "1) Liste simple (pseudo et token)"
echo "2) Liste détaillée (avec Discord ID et dates)"
echo "3) URLs de connexion"
echo "4) Exporter en CSV"
echo ""
read -p "Choisissez une option (1-4): " choice

echo ""

case $choice in
    1)
        print_message "📋 LISTE SIMPLE" "$CYAN"
        echo "═══════════════════════════════════════════════════════════════"
        sqlite3 "$DB_FILE" -column -header "SELECT pseudo, token FROM users ORDER BY pseudo;" 2>/dev/null
        ;;
    
    2)
        print_message "📋 LISTE DÉTAILLÉE" "$CYAN"
        echo "═══════════════════════════════════════════════════════════════"
        sqlite3 "$DB_FILE" -column -header "SELECT id, pseudo, discord_id, token, created_at, updated_at FROM users ORDER BY id;" 2>/dev/null
        ;;
    
    3)
        print_message "🔗 URLS DE CONNEXION" "$CYAN"
        echo "═══════════════════════════════════════════════════════════════"
        echo ""
        sqlite3 "$DB_FILE" "SELECT pseudo || ' : ' || '${website_url}/dashboard.php?token=' || token FROM users ORDER BY pseudo;" 2>/dev/null
        ;;
    
    4)
        EXPORT_FILE="export_users_$(date +%Y%m%d_%H%M%S).csv"
        print_message "📄 Export en cours vers : $EXPORT_FILE" "$YELLOW"
        
        # En-tête CSV
        echo "id,pseudo,discord_id,token,url,created_at,updated_at" > "$EXPORT_FILE"
        
        # Données
        sqlite3 "$DB_FILE" -csv "SELECT id, pseudo, discord_id, token, '${website_url}/dashboard.php?token=' || token as url, created_at, updated_at FROM users ORDER BY id;" >> "$EXPORT_FILE" 2>/dev/null
        
        if [ -f "$EXPORT_FILE" ]; then
            print_message "✅ Export terminé : $EXPORT_FILE" "$GREEN"
            echo ""
            print_message "📊 Aperçu du fichier :" "$CYAN"
            head -5 "$EXPORT_FILE"
            echo "..."
        else
            print_message "❌ Erreur lors de l'export" "$RED"
        fi
        ;;
    
    *)
        print_message "❌ Option invalide" "$RED"
        ;;
esac

echo ""
print_message "═══════════════════════════════════════════════════════════════" "$BLUE"

# Statistiques supplémentaires
echo ""
print_message "📊 STATISTIQUES" "$CYAN"
echo ""

# Utilisateurs créés aujourd'hui
today_count=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users WHERE date(created_at) = date('now');" 2>/dev/null)
print_message "✅ Utilisateurs créés aujourd'hui : $today_count" "$GREEN"

# Utilisateurs modifiés aujourd'hui
modified_count=$(sqlite3 "$DB_FILE" "SELECT COUNT(*) FROM users WHERE date(updated_at) = date('now') AND updated_at != created_at;" 2>/dev/null)
print_message "🔄 Utilisateurs modifiés aujourd'hui : $modified_count" "$YELLOW"

# Dernier utilisateur créé
last_user=$(sqlite3 "$DB_FILE" "SELECT pseudo || ' (créé le ' || created_at || ')' FROM users ORDER BY created_at DESC LIMIT 1;" 2>/dev/null)
if [ -n "$last_user" ]; then
    print_message "👤 Dernier utilisateur créé : $last_user" "$CYAN"
fi