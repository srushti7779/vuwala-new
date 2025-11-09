#!/bin/bash

# Deployment Log File
LOG_FILE="/var/www/test-app/deploy.log"

# Rsync Target (S2 Private IP)
TARGET_SERVER="ubuntu@10.0.134.230"
TARGET_DIR="/var/www/test-app"
SSH_KEY="/home/ubuntu/esteticanow.pem"

echo "=========================="
echo "Starting Deployment Script"
echo "=========================="

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
if [ ! -d "runtime" ]; then
    echo "Creating runtime folder..."
    sudo mkdir -p runtime
fi

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
# STEP 6 - Permission fix on S2
echo "[STEP 6] Fixing permissions on S2..."
ssh -i $SSH_KEY $TARGET_SERVER "bash -s" <<EOF >> $LOG_FILE 2>&1
  set -e

  echo "Changing ownership..."
  sudo chown -R ubuntu:www-data $TARGET_DIR

  echo "Setting directory permissions..."
  sudo find $TARGET_DIR -type d -exec chmod 775 {} \;

  echo "Setting file permissions..."
  sudo find $TARGET_DIR -type f -exec chmod 664 {} \;

  echo "Ensuring runtime folder exists and has correct permissions..."
  sudo mkdir -p $TARGET_DIR/runtime
  sudo chown -R ubuntu:www-data $TARGET_DIR/runtime
  sudo chmod -R 775 $TARGET_DIR/runtime

EOF

if [ $? -ne 0 ]; then
    echo "❌ Permission fixing on S2 failed! Check $LOG_FILE for details."
    exit 1
fi

echo "✅ Permissions on S2 fixed successfully."


# STEP 7 - Reload nginx (optional)
echo "[STEP 7] Reloading nginx on S1..."
sudo nginx -t && sudo systemctl reload nginx

echo "✅ Deployment Finished Successfully!"
