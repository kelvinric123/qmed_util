best through NOOBS

[required]
apache2 (to serve videos locally) 
 - sudo apt-get apache2
php 7.3 (to run process like syncing, logging, serving files etc)
 - sudo apt-get install php7.3
 - // sudo apt-get libapache2-mod-php7.3
pi user must be sudoer

in /etc/sysctl.conf
net.ipv6.conf.all.disable_ipv6 = 1

[optional]
git
composer

[setup]
- /etc/apache2/apache2.conf
  - change /var/www to /home/pi/qmed-utils/queuescreen/www
  - add Header set Access-Control-Allow-Origin "*" under /var/www
- /etc/apache2/sites-available/000-default.conf
  - change document root to /home/pi/qmed-utils/queuescreen/www
  - sudo a2enmod headers
  - sudo systemctl apache2 restart
- /etc/apache2/envvars
  - setboth user and group to pi
- download and unzip ngrok into ~ngrok/ngrok
  - download ngrok into /home/pi/qmed-utils/queuescreen/bin
  - set up ./ngrok authtoken token
- enable ssh through sudo raspi-config
  - change password to raspberry1