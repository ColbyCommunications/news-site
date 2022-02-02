#!/usr/bin/env bash

printf "Installing NPM dependencies for Colby dependencies \n"

shopt -s extglob # Turns on extended globbing

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"  # This loads nvm

## colby news
cd web/wp-content/themes/colby-news-theme
composer install
composer dump-autoload
npm install
npm builder-production
cd -

# npm install
shopt -u extglob