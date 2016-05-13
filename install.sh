#!/bin/bash
# Script d'installation de ZiNotes

# Installation des dépendances apt
read -p "Voulez-vous installer les paquets php5 et php5-sqlite via apt-get ?[yN]" -n 1 -r
if [[ $REPLY =~ ^[Yy]$ ]]
then
    sudo apt-get install php5 php5-sqlite
fi

echo "Installation des dépendances composer..."
php composer.phar self-update &&
php composer.phar install &&

echo "Initialisation du cache..."
rm -rf app/cache/prod/*

echo "Build des assets..."
app/console assetic:dump --env=prod
app/console assets:install --env=prod

echo "ZiNotes est installé dans le dossier : $(pwd)"
echo "Si vous avez configuré un serveur web, vous pouvez dès maintenant y accéder."
read -p "Sinon, voulez-vous démarrer le serveur web intégré ?[yN]" -n 1 -r
if [[ $REPLY =~ ^[Yy]$ ]]
then
    php -S localhost:8086 -t web/
fi

