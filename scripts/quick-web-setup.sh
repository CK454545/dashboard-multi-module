#!/bin/bash

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 Configuration rapide du serveur web${NC}"
echo "========================================"

# 1. Installation de PHP et Nginx
echo -e "${YELLOW}📦 Installation de PHP et Nginx...${NC}"
sudo apt update
sudo apt install -y nginx php8.1-fpm php8.1-sqlite3 php8.1-mbstring php8.1-curl php8.1-json php8.1-xml

# 2. Démarrage des services
echo -e "${YELLOW}🔧 Démarrage des services...${NC}"
sudo systemctl start nginx
sudo systemctl start php8.1-fpm
sudo systemctl enable nginx
sudo systemctl enable php8.1-fpm

# 3. Configuration Nginx
echo -e "${YELLOW}🌐 Configuration de Nginx...${NC}"
sudo tee /etc/nginx/sites-available/dashboard > /dev/null <<'EOF'
server {
    listen 80;
    listen [::]:80;
    
    server_name myfullagency-connect.fr www.myfullagency-connect.fr _;
    
    root /var/www/dashboard-multi-modules/web;
    index index.php dashboard.php index.html;
    
    # PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Sécurité
    location ~ /\.(ht|git|json|db|sql) {
        deny all;
    }
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    # Logs
    access_log /var/log/nginx/dashboard-access.log;
    error_log /var/log/nginx/dashboard-error.log;
}
EOF

# 4. Activation du site
echo -e "${YELLOW}🔗 Activation du site...${NC}"
sudo ln -sf /etc/nginx/sites-available/dashboard /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# 5. Test et redémarrage
echo -e "${YELLOW}🔄 Test et redémarrage de Nginx...${NC}"
sudo nginx -t
sudo systemctl restart nginx

# 6. Permissions
echo -e "${YELLOW}🔐 Application des permissions...${NC}"
sudo chown -R www-data:www-data /var/www/dashboard-multi-modules/
sudo find /var/www/dashboard-multi-modules/ -type d -exec chmod 755 {} \;
sudo find /var/www/dashboard-multi-modules/ -type f -exec chmod 644 {} \;
sudo chmod 666 /var/www/dashboard-multi-modules/database/database.db
sudo chmod 777 /var/www/dashboard-multi-modules/database/

echo -e "${GREEN}✅ Configuration terminée !${NC}"
echo ""
echo -e "${BLUE}Vous pouvez maintenant accéder à :${NC}"
echo "👉 https://myfullagency-connect.fr/dashboard.php?token=VOTRE_TOKEN"
echo ""
echo -e "${YELLOW}Si vous avez une erreur 502, attendez 30 secondes et réessayez.${NC}"