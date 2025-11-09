#!/bin/bash

# Deployment Log File
LOG_FILE="/var/www/test-app/deploy.log"

# Rsync Target (S2 Private IP)
TARGET_SERVER="ubuntu@10.0.134.230"
TARGET_DIR="/var/www/test-app"
SSH_KEY="/home/ubuntu/esteticanow.pem"

# NGINX Config variables
DOMAIN1="test.esteticanow.com"
DOMAIN2="testone.esteticanow.com"
PHP_SOCKET="/run/php/php7.4-fpm.sock"
NGINX_CONF="/etc/nginx/sites-available/${DOMAIN1}"

echo "=========================="
echo "Starting Deployment Script"
echo "=========================="

# STEP 0 - Generate NGINX Config if not exist
if [ ! -f "$NGINX_CONF" ]; then
    echo "[STEP 0] Generating NGINX config for $DOMAIN1"
    sudo tee $NGINX_CONF > /dev/null <<EOL
server {
    listen 80;
    server_name ${DOMAIN1} ${DOMAIN2};

    root /var/www/test-app/web;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_SOCKET};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    access_log /var/log/nginx/test_access.log;
    error_log /var/log/nginx/test_error.log;
}
EOL

    # Enable nginx config
    sudo ln -s $NGINX_CONF /etc/nginx/sites-enabled/
    echo "✅ NGINX config created."
else
    echo "✅ NGINX config already exists. Skipping."
fi

# STEP 1 - Git Pull (S1)
echo "[STEP 1] Pulling latest code from GitHub..."
{
    git pull
} >> $LOG_FILE 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Git pull failed! Check $LOG_FILE for details."
    exit 1
fi
echo "✅ Git pull complete."

# STEP 2 - Composer Install (S1)
echo "[STEP 2] Running composer install..."
{
    composer install --no-dev --optimize-autoloader
} >> $LOG_FILE 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Composer install failed! Check $LOG_FILE for details."
    exit 1
fi
echo "✅ Composer install complete."

# STEP 3 - Global Permission Fix on S1
echo "[STEP 3] Setting global permissions on S1..."
sudo chown -R ubuntu:www-data /var/www/test-app
sudo find /var/www/test-app -type d -exec chmod 775 {} \;
sudo find /var/www/test-app -type f -exec chmod 664 {} \;

# STEP 4 - Ensure runtime folder exists on S1
echo "[STEP 4] Fixing runtime folder permissions on S1..."
sudo mkdir -p runtime
sudo chown -R ubuntu:www-data runtime
sudo chmod -R 775 runtime

# STEP 4.5 - Ensure target directory exists on S2
echo "[STEP 4.5] Checking if target directory exists on S2..."
ssh -i $SSH_KEY $TARGET_SERVER "sudo mkdir -p $TARGET_DIR && sudo chown -R ubuntu:www-data $TARGET_DIR"
echo "✅ Target directory ready on S2."

# STEP 5 - Rsync Code to S2 (without runtime and deploy.log)
echo "[STEP 5] Rsyncing code to S2..."
{
  rsync -avz --no-owner --no-group --no-times \
    --exclude 'runtime/' \
    --exclude 'deploy.log' \
    -e "ssh -i $SSH_KEY" \
    /var/www/test-app/ $TARGET_SERVER:$TARGET_DIR
} >> $LOG_FILE 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Rsync failed! Check $LOG_FILE for details."
    exit 1
fi
echo "✅ Rsync to S2 complete."

# STEP 6 - Permission fix on S2
echo "[STEP 6] Fixing permissions on S2..."
ssh -i $SSH_KEY $TARGET_SERVER "
    sudo chown -R ubuntu:www-data $TARGET_DIR &&
    sudo find $TARGET_DIR -type d -exec chmod 775 {} \; &&
    sudo find $TARGET_DIR -type f -exec chmod 664 {} \; &&
    sudo mkdir -p $TARGET_DIR/runtime &&
    sudo chown -R ubuntu:www-data $TARGET_DIR/runtime &&
    sudo chmod -R 775 $TARGET_DIR/runtime &&
    sudo chmod +x $TARGET_DIR/deploy.sh
"

# STEP 7 - Reload nginx (apply nginx config)
echo "[STEP 7] Reloading nginx on S1..."
sudo nginx -t && sudo systemctl reload nginx

echo "✅ Deployment Finished Successfully!"
