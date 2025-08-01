#!/bin/bash

# ================================================================
# 🔍 Script de Vérification de Configuration Ubuntu
# ================================================================

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║     🔍 Vérification de la Configuration Ubuntu               ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Compteurs
ERRORS=0
WARNINGS=0

# Fonction de vérification
check() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅${NC} $2"
    else
        echo -e "${RED}❌${NC} $2"
        ((ERRORS++))
    fi
}

warn() {
    echo -e "${YELLOW}⚠️${NC} $1"
    ((WARNINGS++))
}

echo "📁 Vérification des fichiers essentiels..."
echo "==========================================="

# Vérifier les scripts principaux
[ -f "scripts/ubuntu-manager.sh" ] && check 0 "ubuntu-manager.sh présent" || check 1 "ubuntu-manager.sh manquant"
[ -f "scripts/auto-update-ubuntu.js" ] && check 0 "auto-update-ubuntu.js présent" || check 1 "auto-update-ubuntu.js manquant"
[ -f "scripts/migrate-db.js" ] && check 0 "migrate-db.js présent" || check 1 "migrate-db.js manquant"

# Vérifier la documentation
[ -f "docs/UBUNTU_DEPLOYMENT_GUIDE.md" ] && check 0 "Guide Ubuntu présent" || check 1 "Guide Ubuntu manquant"
[ -f "migrations/README.md" ] && check 0 "Guide migrations présent" || check 1 "Guide migrations manquant"

# Vérifier les exemples
[ -f "config/config.example.json" ] && check 0 "config.example.json présent" || check 1 "config.example.json manquant"

echo ""
echo "🔧 Vérification de la configuration..."
echo "======================================"

# Vérifier si config.json existe pour afficher le domaine
if [ -f "config/config.json" ]; then
    DOMAIN=$(grep -o '"url"[[:space:]]*:[[:space:]]*"[^"]*"' config/config.json | cut -d'"' -f4 | sed 's|https\?://||' | cut -d'/' -f1)
    if [ ! -z "$DOMAIN" ]; then
        check 0 "Domaine configuré: $DOMAIN"
    else
        warn "Domaine non configuré dans config.json"
    fi
else
    warn "config/config.json n'existe pas (sera créé sur le serveur)"
fi

echo ""
echo "📂 Structure des dossiers..."
echo "============================"

# Vérifier les dossiers importants
[ -d "web" ] && check 0 "Dossier web/ présent" || check 1 "Dossier web/ manquant"
[ -d "bot" ] && check 0 "Dossier bot/ présent" || check 1 "Dossier bot/ manquant"
[ -d "scripts" ] && check 0 "Dossier scripts/ présent" || check 1 "Dossier scripts/ manquant"
[ -d "docs" ] && check 0 "Dossier docs/ présent" || check 1 "Dossier docs/ manquant"

echo ""
echo "🔐 Vérification des permissions..."
echo "=================================="

# Vérifier que les scripts sont exécutables
if [ -f "scripts/ubuntu-manager.sh" ]; then
    [ -x "scripts/ubuntu-manager.sh" ] && check 0 "ubuntu-manager.sh exécutable" || warn "ubuntu-manager.sh non exécutable (sera corrigé sur le serveur)"
fi

echo ""
echo "📝 Vérification Git..."
echo "======================"

# Vérifier le status git
if command -v git &> /dev/null; then
    MODIFIED=$(git status --porcelain | wc -l)
    if [ $MODIFIED -gt 0 ]; then
        warn "$MODIFIED fichiers modifiés non commités"
        echo "  Utilisez: git add . && git commit -m 'message' && git push"
    else
        check 0 "Tous les fichiers sont commités"
    fi
    
    # Vérifier la branche
    BRANCH=$(git branch --show-current)
    [ "$BRANCH" = "main" ] && check 0 "Sur la branche main" || warn "Sur la branche $BRANCH (pas main)"
else
    warn "Git non installé localement"
fi

echo ""
echo "📋 Résumé"
echo "========="
echo -e "Erreurs: ${RED}$ERRORS${NC}"
echo -e "Avertissements: ${YELLOW}$WARNINGS${NC}"

if [ $ERRORS -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ Tout est prêt pour le déploiement Ubuntu !${NC}"
    echo ""
    echo "Prochaines étapes:"
    echo "1. git add . && git commit -m 'Ajout système Ubuntu' && git push"
    echo "2. Sur votre VPS: cd /var/www/dashboard-multi-modules"
    echo "3. git pull"
    echo "4. chmod +x scripts/ubuntu-manager.sh"
    echo "5. ./scripts/ubuntu-manager.sh (Option 1 pour installation complète)"
else
    echo ""
    echo -e "${RED}❌ Des erreurs doivent être corrigées avant le déploiement${NC}"
fi 