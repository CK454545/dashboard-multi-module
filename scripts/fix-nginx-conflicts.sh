#!/bin/bash

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔧 Résolution des conflits Nginx${NC}"
echo "========================================"

# 1. Lister tous les sites activés
echo -e "${YELLOW}📋 Sites Nginx actuellement activés :${NC}"
ls -la /etc/nginx/sites-enabled/

# 2. Sauvegarder les configurations actuelles
echo -e "${YELLOW}💾 Sauvegarde des configurations...${NC}"
sudo mkdir -p /etc/nginx/backup-configs
sudo cp -r /etc/nginx/sites-enabled/* /etc/nginx/backup-configs/ 2>/dev/null

# 3. Désactiver tous les sites sauf le default
echo -e "${YELLOW}🔄 Nettoyage des configurations en conflit...${NC}"
sudo rm -f /etc/nginx/sites-enabled/*

# 4. Créer une configuration unique et propre
echo -e "${YELLOW}📝 Création de la configuration principale...${NC}"
sudo tee /etc/nginx/sites-available/dashboard-main > /dev/null <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    
    server_name myfullagency-connect.fr www.myfullagency-connect.fr;
    
    root /var/www/dashboard-multi-modules/web;
    index dashboard.php index.php index.html;
    
    # Configuration PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Sécurité - Bloquer l'accès aux fichiers sensibles
    location ~ /\.(ht|git|json|db|sql) {
        deny all;
        return 404;
    }
    
    # Bloquer les tentatives d'intrusion
    location ~* (shell|cmd|sh|bash|wget|curl) {
        deny all;
        return 404;
    }
    
    location / {
        try_files $uri $uri/ /dashboard.php?$query_string;
    }
    
    # Logs
    access_log /var/log/nginx/dashboard-access.log;
    error_log /var/log/nginx/dashboard-error.log;
}

# Redirection HTTP vers HTTPS (si certificat SSL installé)
# server {
#     listen 80;
#     listen [::]:80;
#     server_name myfullagency-connect.fr www.myfullagency-connect.fr;
#     return 301 https://$server_name$request_uri;
# }
EOF

# 5. Activer uniquement cette configuration
echo -e "${YELLOW}🔗 Activation de la configuration unique...${NC}"
sudo ln -sf /etc/nginx/sites-available/dashboard-main /etc/nginx/sites-enabled/

# 6. Tester la configuration
echo -e "${YELLOW}🧪 Test de la configuration...${NC}"
sudo nginx -t

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Configuration valide !${NC}"
    
    # 7. Redémarrer Nginx
    echo -e "${YELLOW}🔄 Redémarrage de Nginx...${NC}"
    sudo systemctl restart nginx
    
    echo -e "${GREEN}✅ Nginx redémarré avec succès !${NC}"
else
    echo -e "${RED}❌ Erreur dans la configuration Nginx !${NC}"
    echo "Restauration de la configuration précédente..."
    sudo rm -f /etc/nginx/sites-enabled/*
    sudo cp -r /etc/nginx/backup-configs/* /etc/nginx/sites-enabled/ 2>/dev/null
    exit 1
fi

# 8. Corriger les permissions Git
echo -e "${YELLOW}🔐 Correction des permissions Git...${NC}"
sudo chown -R ubuntu:ubuntu /var/www/dashboard-multi-modules/.git
sudo chmod -R 755 /var/www/dashboard-multi-modules/.git

# 9. Vérifier l'état final
echo ""
echo -e "${BLUE}📊 État final :${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}Sites activés :${NC}"
ls -la /etc/nginx/sites-enabled/
echo ""
echo -e "${GREEN}Status Nginx :${NC}"
sudo systemctl status nginx --no-pager | head -5
echo ""
echo -e "${GREEN}✅ Conflits résolus !${NC}"
echo ""
echo "Vous pouvez maintenant accéder à :"
echo "👉 http://myfullagency-connect.fr/dashboard.php?token=VOTRE_TOKEN"