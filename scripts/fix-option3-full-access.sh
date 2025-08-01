#!/bin/bash

# Script pour corriger IMMÉDIATEMENT l'option 3 avec FULL ACCESS

PROJECT_DIR="/var/www/dashboard-multi-modules"

echo "🔧 CORRECTION DE L'OPTION 3 AVEC FULL ACCESS"
echo "============================================"

# 1. D'abord, appliquer FULL PERMISSIONS sur TOUT
echo "🔓 Application des permissions FULL ACCESS..."

sudo chown -R ubuntu:ubuntu "$PROJECT_DIR"
sudo find "$PROJECT_DIR" -type d -exec chmod 777 {} \;
sudo find "$PROJECT_DIR" -type f -exec chmod 666 {} \;
sudo find "$PROJECT_DIR/scripts" -name "*.sh" -exec chmod 777 {} \;

# Spécialement pour .git
sudo chown -R ubuntu:ubuntu "$PROJECT_DIR/.git" 2>/dev/null
sudo chmod -R 777 "$PROJECT_DIR/.git" 2>/dev/null

# Base de données
sudo mkdir -p "$PROJECT_DIR/database"
[ -f "$PROJECT_DIR/database/database.db" ] && sudo chmod 666 "$PROJECT_DIR/database/database.db"
sudo chmod 777 "$PROJECT_DIR/database"

# Config
sudo mkdir -p "$PROJECT_DIR/config"
[ -f "$PROJECT_DIR/config/config.json" ] && sudo chmod 666 "$PROJECT_DIR/config/config.json"
sudo chmod 777 "$PROJECT_DIR/config"

# Backups
sudo mkdir -p "$PROJECT_DIR/backups"
sudo chmod 777 "$PROJECT_DIR/backups"

# Web pour Nginx
sudo chown -R ubuntu:www-data "$PROJECT_DIR/web" 2>/dev/null
sudo chmod -R 777 "$PROJECT_DIR/web" 2>/dev/null

# Ajouter ubuntu au groupe www-data
sudo usermod -a -G www-data ubuntu

echo "✅ Permissions FULL ACCESS appliquées!"
echo ""

# 2. Maintenant, récupérer les mises à jour
echo "📥 Récupération des mises à jour..."
cd "$PROJECT_DIR"

# Forcer la récupération
git fetch --all
git reset --hard origin/main

# Si Git échoue, télécharger directement le script
if [ $? -ne 0 ]; then
    echo "⚠️ Git a échoué, téléchargement direct du script..."
    curl -o "$PROJECT_DIR/scripts/ubuntu-manager.sh" https://raw.githubusercontent.com/CK454545/dashboard-multi-modules/main/scripts/ubuntu-manager.sh
    chmod 777 "$PROJECT_DIR/scripts/ubuntu-manager.sh"
fi

echo ""
echo "✅ CORRECTION TERMINÉE !"
echo ""
echo "📌 L'option 3 appliquera maintenant AUTOMATIQUEMENT les permissions FULL ACCESS"
echo "   AVANT toute opération Git pour éviter TOUS les problèmes de permissions!"
echo ""
echo "🚀 Vous pouvez maintenant relancer : ./scripts/ubuntu-manager.sh"
echo "   et utiliser l'option 3 sans aucun problème !"