#!/bin/bash

# Script de nettoyage du projet

PROJECT_DIR="/var/www/dashboard-multi-modules"

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
print_message "🧹 NETTOYAGE DU PROJET" "$BLUE"
echo "═══════════════════════════════════════════════════════════════"
echo ""

cd "$PROJECT_DIR" || exit

# 1. Nettoyer les fichiers temporaires
print_message "🗑️ Suppression des fichiers temporaires..." "$YELLOW"

# Fichiers de lock de migration
if [ -f "migration.lock" ]; then
    rm -f migration.lock
    print_message "✅ migration.lock supprimé" "$GREEN"
fi

# Fichiers temporaires dans /tmp
rm -rf /tmp/dashboard_backup_* 2>/dev/null
rm -f /tmp/fix-git.sh 2>/dev/null
rm -f /tmp/init_db.sql 2>/dev/null
print_message "✅ Fichiers temporaires /tmp nettoyés" "$GREEN"

# 2. Nettoyer les logs PM2 anciens
print_message "📋 Nettoyage des logs PM2..." "$YELLOW"
pm2 flush
print_message "✅ Logs PM2 nettoyés" "$GREEN"

# 3. Nettoyer les fichiers de sauvegarde anciens (garder les 5 derniers)
print_message "💾 Nettoyage des anciennes sauvegardes..." "$YELLOW"
if [ -d "$PROJECT_DIR/backups" ]; then
    # Garder seulement les 5 sauvegardes les plus récentes
    cd "$PROJECT_DIR/backups"
    ls -t database_*.db 2>/dev/null | tail -n +6 | xargs -r rm -f
    print_message "✅ Anciennes sauvegardes supprimées (5 dernières conservées)" "$GREEN"
    cd "$PROJECT_DIR"
fi

# 4. Nettoyer les fichiers npm inutiles
print_message "📦 Nettoyage npm..." "$YELLOW"
if [ -d "node_modules" ]; then
    npm cache clean --force 2>/dev/null
    print_message "✅ Cache npm nettoyé" "$GREEN"
fi

# 5. Nettoyer les fichiers de logs anciens
print_message "📄 Nettoyage des logs anciens..." "$YELLOW"
if [ -d "$PROJECT_DIR/logs" ]; then
    find "$PROJECT_DIR/logs" -name "*.log" -mtime +7 -delete 2>/dev/null
    print_message "✅ Logs de plus de 7 jours supprimés" "$GREEN"
fi

# 6. Supprimer les fichiers de rapport anciens
print_message "📊 Nettoyage des rapports anciens..." "$YELLOW"
find "$PROJECT_DIR" -name "rapport_ajout_*.txt" -mtime +30 -delete 2>/dev/null
find "$PROJECT_DIR" -name "export_users_*.csv" -mtime +30 -delete 2>/dev/null
print_message "✅ Rapports de plus de 30 jours supprimés" "$GREEN"

# 7. Nettoyer les fichiers Git inutiles
print_message "📦 Optimisation Git..." "$YELLOW"
git gc --aggressive --prune=now 2>/dev/null
print_message "✅ Dépôt Git optimisé" "$GREEN"

# 8. Supprimer les fichiers d'exemple si non utilisés
print_message "📄 Nettoyage des fichiers d'exemple..." "$YELLOW"
if [ -f "exemple_utilisateurs.csv" ]; then
    read -p "Supprimer le fichier exemple_utilisateurs.csv ? (o/N): " confirm
    if [[ $confirm == [oO] ]]; then
        rm -f exemple_utilisateurs.csv
        print_message "✅ Fichier exemple supprimé" "$GREEN"
    fi
fi

# 9. Vérifier l'espace disque gagné
print_message "💾 Calcul de l'espace libéré..." "$CYAN"
echo ""

# Afficher l'utilisation actuelle
df -h "$PROJECT_DIR" | grep -v Filesystem

echo ""
print_message "═══════════════════════════════════════════════════════════════" "$BLUE"
print_message "✅ NETTOYAGE TERMINÉ !" "$GREEN"
echo ""

# Résumé des actions
print_message "📋 Résumé du nettoyage :" "$CYAN"
echo "  ✅ Fichiers temporaires supprimés"
echo "  ✅ Logs PM2 nettoyés"
echo "  ✅ Anciennes sauvegardes supprimées (5 dernières conservées)"
echo "  ✅ Cache npm nettoyé"
echo "  ✅ Logs de plus de 7 jours supprimés"
echo "  ✅ Rapports de plus de 30 jours supprimés"
echo "  ✅ Dépôt Git optimisé"
echo ""

# Conseils
print_message "💡 Conseils pour maintenir le projet propre :" "$YELLOW"
echo "  • Exécutez ce script régulièrement (1 fois par mois)"
echo "  • Utilisez l'option 4 pour sauvegarder avant le nettoyage"
echo "  • Vérifiez les logs importants avant suppression"
echo ""