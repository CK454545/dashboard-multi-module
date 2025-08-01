#!/bin/bash

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=================================================${NC}"
echo -e "${BLUE}🔧 RÉPARATION DES COMMANDES DISCORD${NC}"
echo -e "${BLUE}=================================================${NC}\n"

# Vérifier qu'on est dans le bon répertoire
if [ ! -f "bot/bot.js" ]; then
    echo -e "${RED}❌ Erreur : Ce script doit être exécuté depuis la racine du projet${NC}"
    exit 1
fi

# Menu de sélection
echo -e "${YELLOW}Choisissez une option :${NC}"
echo -e "1️⃣  ${BLUE}Diagnostic complet (recommandé)${NC}"
echo -e "2️⃣  ${BLUE}Enregistrement global des commandes${NC}"
echo -e "3️⃣  ${BLUE}Réparation par serveur${NC}"
echo -e "4️⃣  ${BLUE}Vérification des permissions uniquement${NC}"
echo -e ""
read -p "Votre choix (1-4) : " choice

case $choice in
    1)
        echo -e "\n${GREEN}🔍 DIAGNOSTIC COMPLET${NC}\n"
        
        # Étape 1 : Arrêter le bot s'il est en cours d'exécution
        echo -e "${YELLOW}📋 Étape 1 : Arrêt du bot si nécessaire...${NC}"
        if pgrep -f "node bot/bot.js" > /dev/null; then
            echo -e "${YELLOW}   Arrêt du bot en cours...${NC}"
            pkill -f "node bot/bot.js"
            sleep 2
            echo -e "${GREEN}   ✅ Bot arrêté${NC}"
        else
            echo -e "${GREEN}   ✅ Le bot n'est pas en cours d'exécution${NC}"
        fi

        echo ""

        # Étape 2 : Diagnostic détaillé
        echo -e "${YELLOW}📋 Étape 2 : Diagnostic détaillé...${NC}"
        cd bot
        node debug-commands.js
        cd ..

        echo ""
        echo -e "${YELLOW}Appuyez sur Entrée pour continuer...${NC}"
        read

        # Étape 3 : Essayer l'enregistrement global
        echo -e "${YELLOW}📋 Étape 3 : Tentative d'enregistrement global...${NC}"
        cd bot
        node register-global-commands.js
        cd ..

        echo ""
        echo -e "${GREEN}=================================================${NC}"
        echo -e "${GREEN}✅ DIAGNOSTIC TERMINÉ !${NC}"
        echo -e "${GREEN}=================================================${NC}\n"
        ;;
        
    2)
        echo -e "\n${GREEN}🌐 ENREGISTREMENT GLOBAL${NC}\n"
        
        # Arrêter le bot
        echo -e "${YELLOW}📋 Arrêt du bot...${NC}"
        if pgrep -f "node bot/bot.js" > /dev/null; then
            pkill -f "node bot/bot.js"
            sleep 2
            echo -e "${GREEN}   ✅ Bot arrêté${NC}"
        fi

        echo ""

        # Enregistrement global
        echo -e "${YELLOW}📋 Enregistrement global des commandes...${NC}"
        cd bot
        node register-global-commands.js
        cd ..

        echo ""
        echo -e "${GREEN}=================================================${NC}"
        echo -e "${GREEN}✅ ENREGISTREMENT GLOBAL TERMINÉ !${NC}"
        echo -e "${GREEN}=================================================${NC}\n"
        ;;
        
    3)
        echo -e "\n${GREEN}🔧 RÉPARATION PAR SERVEUR${NC}\n"
        
        # Étape 1 : Arrêter le bot
        echo -e "${YELLOW}📋 Étape 1 : Arrêt du bot...${NC}"
        if pgrep -f "node bot/bot.js" > /dev/null; then
            pkill -f "node bot/bot.js"
            sleep 2
            echo -e "${GREEN}   ✅ Bot arrêté${NC}"
        fi

        echo ""

        # Étape 2 : Vérifier les permissions
        echo -e "${YELLOW}📋 Étape 2 : Vérification des permissions...${NC}"
        cd bot
        node check-bot-permissions.js
        cd ..

        echo ""
        echo -e "${YELLOW}Appuyez sur Entrée pour continuer...${NC}"
        read

        # Étape 3 : Réparer les commandes
        echo -e "${YELLOW}📋 Étape 3 : Réparation des commandes...${NC}"
        cd bot
        node fix-discord-commands.js
        cd ..

        echo ""
        echo -e "${GREEN}=================================================${NC}"
        echo -e "${GREEN}✅ RÉPARATION TERMINÉE !${NC}"
        echo -e "${GREEN}=================================================${NC}\n"
        ;;
        
    4)
        echo -e "\n${GREEN}🔍 VÉRIFICATION DES PERMISSIONS${NC}\n"
        
        cd bot
        node check-bot-permissions.js
        cd ..

        echo ""
        echo -e "${GREEN}=================================================${NC}"
        echo -e "${GREEN}✅ VÉRIFICATION TERMINÉE !${NC}"
        echo -e "${GREEN}=================================================${NC}\n"
        ;;
        
    *)
        echo -e "${RED}❌ Choix invalide${NC}"
        exit 1
        ;;
esac

echo -e "${BLUE}📌 INSTRUCTIONS IMPORTANTES :${NC}\n"
echo -e "1️⃣  ${YELLOW}Fermez complètement Discord${NC} (pas juste la fenêtre)"
echo -e "2️⃣  ${YELLOW}Rouvrez Discord${NC}"
echo -e "3️⃣  ${YELLOW}Allez dans un salon texte${NC}"
echo -e "4️⃣  ${YELLOW}Tapez /${NC} et attendez quelques secondes"
echo -e "5️⃣  ${YELLOW}Les 9 commandes devraient apparaître !${NC}\n"

echo -e "${BLUE}🎯 Les commandes disponibles sont :${NC}"
echo -e "   • /mfa - Lance une session MFA ouverte à tous"
echo -e "   • /start-mfa - Démarre ton onboarding personnel"
echo -e "   • /mfa-list - Liste tous les comptes MFA"
echo -e "   • /voc - Message pour rejoindre le vocal"
echo -e "   • /end - Clôture une demande"
echo -e "   • /supptoken - Supprime un token MFA"
echo -e "   • /carte - Génère ta carte MFA avec QR code"
echo -e "   • /wait - Informe qu'un agent va répondre"
echo -e "   • /infos - Affiche tes informations MFA\n"

echo -e "${YELLOW}Voulez-vous redémarrer le bot maintenant ? (o/n)${NC}"
read -r response

if [[ "$response" =~ ^[Oo]$ ]]; then
    echo -e "\n${GREEN}Redémarrage du bot...${NC}"
    cd bot
    node bot.js
fi