#!/bin/bash

# WordPress CLI
echo "Installing WP-CLI"
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

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
# Continue
exec "apache2-foreground"