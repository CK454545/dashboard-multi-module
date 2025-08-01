#!/bin/bash

# Script pour initialiser la base de données avec la structure correcte

PROJECT_DIR="/var/www/dashboard-multi-modules"
DB_FILE="$PROJECT_DIR/database/database.db"

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
print_message "🗄️ INITIALISATION DE LA BASE DE DONNÉES" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

cd "$PROJECT_DIR" || exit

# 1. Créer le dossier database s'il n'existe pas
print_message "📁 Création du dossier database..." "$CYAN"
sudo mkdir -p "$PROJECT_DIR/database"
sudo chmod 777 "$PROJECT_DIR/database"

# 2. Créer la base de données si elle n'existe pas
if [ ! -f "$DB_FILE" ]; then
    print_message "🗄️ Création de la base de données..." "$YELLOW"
    sudo touch "$DB_FILE"
    sudo chmod 666 "$DB_FILE"
    sudo chown ubuntu:www-data "$DB_FILE"
    print_message "✅ Base de données créée" "$GREEN"
else
    print_message "✅ Base de données existante trouvée" "$GREEN"
fi

# 3. Créer la structure de la base de données
print_message "🏗️ Création de la structure de la base de données..." "$CYAN"

# Créer un fichier SQL temporaire avec la structure correcte
cat > /tmp/init_db.sql << 'EOF'
-- Table principale des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    token TEXT PRIMARY KEY,
    discord_id TEXT UNIQUE NOT NULL,
    pseudo TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table flexible pour stocker toutes les données des modules
CREATE TABLE IF NOT EXISTS user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    module TEXT NOT NULL,           -- 'wins', 'timer', 'teams'
    key TEXT NOT NULL,              -- 'count', 'multiplier', 'team1_score', etc.
    value TEXT,                     -- Valeur stockée (tout est stocké en texte)
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(token, module, key),
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Table pour stocker les styles personnalisés
CREATE TABLE IF NOT EXISTS user_styles (
    token TEXT PRIMARY KEY,
    styles TEXT,                    -- JSON avec tous les styles
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token) ON DELETE CASCADE
);

-- Index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_user_data_token ON user_data(token);
CREATE INDEX IF NOT EXISTS idx_user_data_module ON user_data(token, module);
CREATE INDEX IF NOT EXISTS idx_users_discord_id ON users(discord_id);
EOF

# 4. Exécuter le script SQL
print_message "🔄 Application de la structure..." "$YELLOW"
sqlite3 "$DB_FILE" < /tmp/init_db.sql

if [ $? -eq 0 ]; then
    print_message "✅ Structure de la base de données créée avec succès!" "$GREEN"
else
    print_message "❌ Erreur lors de la création de la structure" "$RED"
    exit 1
fi

# 5. Vérifier la structure
print_message "🔍 Vérification de la structure..." "$CYAN"
echo ""
echo "Tables créées :"
sqlite3 "$DB_FILE" ".tables" 2>/dev/null

echo ""
echo "Structure de la table users :"
sqlite3 "$DB_FILE" ".schema users" 2>/dev/null

# 6. Nettoyer le fichier temporaire
rm -f /tmp/init_db.sql

# 7. Afficher les permissions
echo ""
print_message "📋 Permissions de la base de données :" "$CYAN"
ls -la "$DB_FILE"

echo ""
print_message "✅ INITIALISATION TERMINÉE !" "$GREEN"
echo ""
print_message "💡 Vous pouvez maintenant utiliser l'option 20 pour ajouter des utilisateurs" "$CYAN"
echo "" 