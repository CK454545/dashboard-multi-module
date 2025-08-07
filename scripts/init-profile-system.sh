#!/bin/bash

# Script d'initialisation du système de profil MFA CONNECT
# Ce script met à jour la base de données avec les nouvelles tables de profil

echo "🚀 Initialisation du système de profil MFA CONNECT..."

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "database/database.sql" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet"
    exit 1
fi

# Vérifier que SQLite3 est installé
if ! command -v sqlite3 &> /dev/null; then
    echo "❌ Erreur: SQLite3 n'est pas installé"
    echo "Installez-le avec: sudo apt-get install sqlite3"
    exit 1
fi

# Chemin vers la base de données
DB_PATH="database/database.sqlite"

echo "📊 Mise à jour de la base de données..."

# Créer la base de données si elle n'existe pas
if [ ! -f "$DB_PATH" ]; then
    echo "📁 Création de la base de données..."
    sqlite3 "$DB_PATH" < database/database.sql
else
    echo "🔄 Mise à jour de la base de données existante..."
    
    # Sauvegarder l'ancienne base de données
    BACKUP_PATH="database/database_backup_$(date +%Y%m%d_%H%M%S).sqlite"
    cp "$DB_PATH" "$BACKUP_PATH"
    echo "💾 Sauvegarde créée: $BACKUP_PATH"
    
    # Appliquer les nouvelles tables
    sqlite3 "$DB_PATH" < database/database.sql
fi

# Vérifier que les tables ont été créées
echo "🔍 Vérification des tables..."

TABLES=$(sqlite3 "$DB_PATH" ".tables")

if echo "$TABLES" | grep -q "user_profiles"; then
    echo "✅ Table user_profiles créée"
else
    echo "❌ Erreur: Table user_profiles non trouvée"
fi

if echo "$TABLES" | grep -q "user_stats"; then
    echo "✅ Table user_stats créée"
else
    echo "❌ Erreur: Table user_stats non trouvée"
fi

if echo "$TABLES" | grep -q "user_preferences"; then
    echo "✅ Table user_preferences créée"
else
    echo "❌ Erreur: Table user_preferences non trouvée"
fi

if echo "$TABLES" | grep -q "user_activity_log"; then
    echo "✅ Table user_activity_log créée"
else
    echo "❌ Erreur: Table user_activity_log non trouvée"
fi

if echo "$TABLES" | grep -q "user_sessions"; then
    echo "✅ Table user_sessions créée"
else
    echo "❌ Erreur: Table user_sessions non trouvée"
fi

if echo "$TABLES" | grep -q "user_notifications"; then
    echo "✅ Table user_notifications créée"
else
    echo "❌ Erreur: Table user_notifications non trouvée"
fi

# Afficher les permissions
echo "🔐 Configuration des permissions..."
chmod 644 "$DB_PATH"
chmod 755 database/

echo ""
echo "🎉 Système de profil MFA CONNECT initialisé avec succès !"
echo ""
echo "📋 Fonctionnalités disponibles:"
echo "   • Profils utilisateurs avec avatars et bios"
echo "   • Statistiques de streaming détaillées"
echo "   • Préférences personnalisables"
echo "   • Historique des activités"
echo "   • Système de notifications"
echo "   • Gestion des sessions"
echo ""
echo "🔗 Pour utiliser le système:"
echo "   1. Le dropdown de profil s'affiche en cliquant sur le nom d'utilisateur"
echo "   2. Les données se chargent automatiquement"
echo "   3. Les statistiques se mettent à jour en temps réel"
echo ""
echo "💡 Prochaines étapes:"
echo "   • Personnaliser les thèmes de couleurs"
echo "   • Ajouter des avatars personnalisés"
echo "   • Créer des badges et achievements"
echo "   • Implémenter un système de rangs" 