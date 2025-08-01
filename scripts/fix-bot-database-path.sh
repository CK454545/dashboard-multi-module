#!/bin/bash

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔧 Correction du chemin de la base de données dans le bot${NC}"
echo "========================================"

# 1. S'assurer que la base de données existe au bon endroit
echo -e "${YELLOW}📁 Vérification de la base de données...${NC}"
DB_PATH="/var/www/dashboard-multi-modules/database/database.db"

if [ ! -f "$DB_PATH" ]; then
    echo -e "${YELLOW}📦 Création de la base de données...${NC}"
    sudo mkdir -p /var/www/dashboard-multi-modules/database
    sudo touch "$DB_PATH"
    
    # Initialiser la base
    sudo sqlite3 "$DB_PATH" << 'EOF'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    discord_id TEXT UNIQUE NOT NULL,
    pseudo TEXT NOT NULL,
    token TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL,
    module TEXT NOT NULL,
    key TEXT NOT NULL,
    value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(token, module, key),
    FOREIGN KEY (token) REFERENCES users(token)
);

CREATE TABLE IF NOT EXISTS user_styles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT UNIQUE NOT NULL,
    styles TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (token) REFERENCES users(token)
);

CREATE TABLE IF NOT EXISTS wins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    value INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS module_styles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_name TEXT UNIQUE NOT NULL,
    styles TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
EOF
fi

echo -e "${GREEN}✅ Base de données vérifiée : $DB_PATH${NC}"

# 2. Corriger les permissions
echo -e "${YELLOW}🔐 Correction des permissions...${NC}"
sudo chown www-data:www-data /var/www/dashboard-multi-modules/database
sudo chmod 755 /var/www/dashboard-multi-modules/database
sudo chown www-data:www-data "$DB_PATH"
sudo chmod 666 "$DB_PATH"

# 3. S'assurer que le fichier config.json a le bon chemin
echo -e "${YELLOW}📝 Vérification de config.json...${NC}"
CONFIG_FILE="/var/www/dashboard-multi-modules/config/config.json"

# Vérifier que le chemin est bien "database/database.db"
if grep -q '"file": "../database/database.db"' "$CONFIG_FILE" 2>/dev/null; then
    echo "Correction du chemin dans config.json..."
    sudo sed -i 's/"file": "\.\.\/database\/database\.db"/"file": "database\/database.db"/' "$CONFIG_FILE"
fi

# 4. Redémarrer le bot
echo -e "${YELLOW}🔄 Redémarrage du bot Discord...${NC}"
pm2 restart discord-bot

# 5. Attendre un peu et vérifier les logs
sleep 3
echo -e "${BLUE}📋 Logs du bot :${NC}"
pm2 logs discord-bot --lines 10 --nostream

echo ""
echo -e "${GREEN}✅ Correction terminée !${NC}"
echo "La base de données est maintenant à : $DB_PATH"