#!/bin/bash

# Deployment script for CUA Course Planner on AWS EC2
# Usage: ./deploy.sh
# Required env vars: EC2_HOST, EC2_USER, EC2_KEY_PATH
# Example: EC2_HOST=ec2-xx-xx-xx-xx.compute-1.amazonaws.com EC2_USER=ec2-user EC2_KEY_PATH=~/.ssh/my-key.pem ./deploy.sh

set -e

EC2_HOST="${EC2_HOST:?EC2_HOST is required}"
EC2_USER="${EC2_USER:-ec2-user}"
EC2_KEY_PATH="${EC2_KEY_PATH:?EC2_KEY_PATH is required}"
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/html}"

echo "Deploying to $EC2_USER@$EC2_HOST:$DEPLOY_PATH ..."

ssh -i "$EC2_KEY_PATH" -o StrictHostKeyChecking=no "$EC2_USER@$EC2_HOST" bash <<EOF
  set -e
  cd "$DEPLOY_PATH"

  echo "--- Fixing storage and cache ownership for deploy user ---"
  sudo chown -R "$EC2_USER":"$EC2_USER" "$DEPLOY_PATH/storage" "$DEPLOY_PATH/bootstrap/cache" 2>/dev/null || true

  echo "--- Pulling latest code ---"
  git fetch origin main
  git reset --hard origin/main

  echo "--- Installing PHP dependencies ---"
  composer install --no-dev --optimize-autoloader

  echo "--- Installing Node dependencies and building assets ---"
  rm -rf node_modules
  npm ci
  npm run build

  echo "--- Running database migrations ---"
  php artisan migrate --force

  echo "--- Clearing Laravel caches ---"
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  php artisan cache:clear

  echo "--- Caching config/routes/views for production ---"
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

  echo "--- Removing node_modules and npm cache to free disk space ---"
  rm -rf node_modules
  npm cache clean --force 2>/dev/null || true

  echo "--- Restoring storage ownership to web server ---"
  sudo chown -R www-data:www-data "$DEPLOY_PATH/storage" 2>/dev/null || true

  echo "--- Restarting web server ---"
  sudo systemctl restart nginx || sudo systemctl restart apache2 || true
  sudo systemctl restart php8.5-fpm || sudo systemctl restart php-fpm || true

  echo "Deployment complete."
EOF
