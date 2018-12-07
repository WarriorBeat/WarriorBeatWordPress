#!/bin/bash

# WordPress CLI
echo "Installing WP-CLI"
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# WP Core
echo "Installing Wordpress Core"
wp core install --allow-root --url="http://localhost:3000" --title="WBLocalWP" --admin_user="admin" --admin_email="someone@gmail.com" --skip-email --admin_password="admin" 
echo "Found Wordpress Install"
chmod -R 755 /var/www/html
chown www-data -R /var/www/html

# WP Config
echo "Setting WP Config Options"
wp config set WP_HOME http://localhost:3000 --type=constant --allow-root
wp config set WP_SITEURL http://localhost:3000 --type=constant --allow-root
wp config set WP_DEBUG true --raw --type=constant --allow-root
wp config set WP_DEBUG_LOG true --raw --type=constant --allow-root
wp config set WP_DEBUG_DISPLAY false --raw --type=constant --allow-root

# Plugins
echo "Installing plugins"
wp plugin install notification --force --activate --allow-root
wp plugin install blackbar --force --activate --allow-root
wp plugin install members --force --activate --allow-root
wp plugin install metronet-profile-picture --force --activate --allow-root
wp plugin install wp-polls --force --activate --allow-root
wp plugin install https://github.com/adrinoe/wp-polls-rest-api/archive/master.zip --force --activate --allow-root
wp plugin install post-meta-inspector --force --activate --allow-root
wp plugin install wordpress-importer --force --activate --allow-root

# Continue
exec "apache2-foreground"
