#!/bin/bash

# Script pour appliquer IMMÉDIATEMENT les permissions FULL ACCESS
# AUCUN PROBLÈME DE PERMISSIONS POSSIBLE APRÈS ÇA !

PROJECT_DIR="/var/www/dashboard-multi-modules"

echo "🔧 APPLICATION DES PERMISSIONS FULL ACCESS"
echo "=========================================="

# 1. TOUT donner à ubuntu
echo "📁 Changement du propriétaire..."
sudo chown -R ubuntu:ubuntu "$PROJECT_DIR"

# 2. FULL ACCESS sur TOUT
echo "🔓 Application des permissions 777/666..."
sudo find "$PROJECT_DIR" -type d -exec chmod 777 {} \;
sudo find "$PROJECT_DIR" -type f -exec chmod 666 {} \;

# 3. Scripts exécutables
echo "🔧 Scripts exécutables..."
sudo find "$PROJECT_DIR/scripts" -type f -name "*.sh" -exec chmod 777 {} \;

# 4. Base de données
echo "🗄️ Configuration de la base de données..."
sudo mkdir -p "$PROJECT_DIR/database"
sudo touch "$PROJECT_DIR/database/database.db"
sudo chmod 777 "$PROJECT_DIR/database"
sudo chmod 666 "$PROJECT_DIR/database/database.db"

# 5. Dossiers spécifiques avec groupe www-data
echo "🌐 Configuration pour Nginx..."
sudo chown -R ubuntu:www-data "$PROJECT_DIR/web" 2>/dev/null
sudo chown -R ubuntu:www-data "$PROJECT_DIR/config" 2>/dev/null
sudo chown -R ubuntu:www-data "$PROJECT_DIR/database" 2>/dev/null

# 6. Git reste à ubuntu seul
echo "📦 Configuration Git..."
sudo chown -R ubuntu:ubuntu "$PROJECT_DIR/.git" 2>/dev/null

# 7. Ajouter ubuntu au groupe www-data
echo "👤 Configuration des groupes..."
sudo usermod -a -G www-data ubuntu

# 8. Créer tous les dossiers manquants
echo "📁 Création des dossiers manquants..."
for dir in backups logs cache temp config database web bot scripts; do
    sudo mkdir -p "$PROJECT_DIR/$dir"
    sudo chmod 777 "$PROJECT_DIR/$dir"
done

echo ""
echo "✅ PERMISSIONS FULL ACCESS APPLIQUÉES !"
echo ""
echo "📋 Résumé :"
echo "  • Propriétaire : ubuntu:ubuntu"
echo "  • Dossiers : 777 (rwxrwxrwx)"
echo "  • Fichiers : 666 (rw-rw-rw-)"
echo "  • Scripts : 777 (rwxrwxrwx)"
echo "  • Base de données : OK"
echo "  • Git : OK"
echo ""
echo "🚀 PLUS AUCUN PROBLÈME DE PERMISSIONS POSSIBLE !"