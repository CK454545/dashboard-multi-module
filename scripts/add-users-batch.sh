#!/bin/bash

# Script pour ajouter plusieurs utilisateurs en lot
# Format du fichier CSV : pseudo,token,discord_id (discord_id optionnel)

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

# Vérifier le fichier CSV
if [ $# -eq 0 ]; then
    print_message "Usage: $0 fichier_utilisateurs.csv" "$YELLOW"
    echo ""
    echo "Format du fichier CSV (une ligne par utilisateur) :"
    echo "pseudo,token[,discord_id]"
    echo ""
    echo "Exemples :"
    echo "Jean,a1b2c3d4e5f6"
    echo "Marie,g7h8i9j0k1l2,123456789012345678"
    echo ""
    echo "Note: Si discord_id n'est pas fourni, il sera généré automatiquement"
    exit 1
fi

CSV_FILE="$1"

if [ ! -f "$CSV_FILE" ]; then
    print_message "❌ Fichier introuvable : $CSV_FILE" "$RED"
    exit 1
fi

clear
print_message "👥 AJOUT D'UTILISATEURS EN LOT" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Compter les lignes
total_lines=$(wc -l < "$CSV_FILE")
print_message "📄 Fichier : $CSV_FILE" "$CYAN"
print_message "📊 Nombre de lignes : $total_lines" "$CYAN"
echo ""

read -p "Voulez-vous continuer ? (o/N): " confirm
if [[ $confirm != [oO] ]]; then
    print_message "❌ Opération annulée" "$YELLOW"
    exit 0
fi

echo ""
print_message "🔄 Traitement en cours..." "$YELLOW"
echo ""

success_count=0
error_count=0
update_count=0

# Créer un fichier de rapport
REPORT_FILE="rapport_ajout_$(date +%Y%m%d_%H%M%S).txt"
echo "RAPPORT D'AJOUT D'UTILISATEURS - $(date)" > "$REPORT_FILE"
echo "═══════════════════════════════════════════════════════════════" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Lire le fichier CSV ligne par ligne
while IFS=',' read -r pseudo token discord_id || [ -n "$pseudo" ]; do
    # Ignorer les lignes vides
    if [ -z "$pseudo" ] || [ -z "$token" ]; then
        continue
    fi
    
    # Nettoyer les espaces
    pseudo=$(echo "$pseudo" | xargs)
    token=$(echo "$token" | xargs)
    discord_id=$(echo "$discord_id" | xargs)
    
    # Générer un discord_id si nécessaire
    if [ -z "$discord_id" ]; then
        discord_id=$(date +%s)$(shuf -i 10000-99999 -n 1)
    fi
    
    echo -n "👤 $pseudo... "
    
    # Vérifier si le token existe déjà
    existing_user=$(sqlite3 "$DB_FILE" "SELECT pseudo FROM users WHERE token='$token' LIMIT 1;" 2>/dev/null)
    
    if [ -n "$existing_user" ]; then
        # Mettre à jour l'utilisateur existant
        sqlite3 "$DB_FILE" "UPDATE users SET pseudo='$pseudo', discord_id='$discord_id', updated_at=datetime('now') WHERE token='$token';" 2>/dev/null
        if [ $? -eq 0 ]; then
            print_message "✅ Mis à jour (ancien: $existing_user)" "$GREEN"
            echo "✅ MIS À JOUR - $pseudo (token: $token, ancien: $existing_user)" >> "$REPORT_FILE"
            ((update_count++))
        else
            print_message "❌ Erreur de mise à jour" "$RED"
            echo "❌ ERREUR MISE À JOUR - $pseudo (token: $token)" >> "$REPORT_FILE"
            ((error_count++))
        fi
    else
        # Insérer le nouvel utilisateur
        sqlite3 "$DB_FILE" "INSERT INTO users (discord_id, pseudo, token, created_at, updated_at) VALUES ('$discord_id', '$pseudo', '$token', datetime('now'), datetime('now'));" 2>/dev/null
        if [ $? -eq 0 ]; then
            # Créer aussi dans user_data
            sqlite3 "$DB_FILE" "INSERT OR IGNORE INTO user_data (discord_id, pseudo, created_at, updated_at) VALUES ('$discord_id', '$pseudo', datetime('now'), datetime('now'));" 2>/dev/null
            
            print_message "✅ Ajouté" "$GREEN"
            echo "✅ AJOUTÉ - $pseudo (token: $token, discord_id: $discord_id)" >> "$REPORT_FILE"
            echo "   URL: ${website_url}/dashboard.php?token=$token" >> "$REPORT_FILE"
            ((success_count++))
        else
            print_message "❌ Erreur d'ajout" "$RED"
            echo "❌ ERREUR AJOUT - $pseudo (token: $token)" >> "$REPORT_FILE"
            ((error_count++))
        fi
    fi
    
done < "$CSV_FILE"

echo ""
echo "═══════════════════════════════════════════════════════════════" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Afficher le résumé
print_message "═══════════════════════════════════════════════════════════════" "$BLUE"
print_message "📊 RÉSUMÉ" "$CYAN"
echo ""
print_message "✅ Utilisateurs ajoutés : $success_count" "$GREEN"
print_message "🔄 Utilisateurs mis à jour : $update_count" "$YELLOW"
print_message "❌ Erreurs : $error_count" "$RED"
echo ""

echo "RÉSUMÉ :" >> "$REPORT_FILE"
echo "- Utilisateurs ajoutés : $success_count" >> "$REPORT_FILE"
echo "- Utilisateurs mis à jour : $update_count" >> "$REPORT_FILE"
echo "- Erreurs : $error_count" >> "$REPORT_FILE"

print_message "📄 Rapport sauvegardé dans : $REPORT_FILE" "$CYAN"
echo ""

# Afficher quelques exemples d'URLs
if [ $success_count -gt 0 ] || [ $update_count -gt 0 ]; then
    print_message "🔗 Exemples d'URLs de connexion :" "$CYAN"
    sqlite3 "$DB_FILE" "SELECT '  ' || pseudo || ': ${website_url}/dashboard.php?token=' || token FROM users ORDER BY id DESC LIMIT 5;" 2>/dev/null
fi