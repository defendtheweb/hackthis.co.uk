HackThis
========
[![project status](http://stillmaintained.com/HackThis/hackthis.co.uk.png)](http://stillmaintained.com/HackThis/hackthis.co.uk)

This repository contains all code for http://www.hackthis.co.uk. 

## Short instructions
* Import schema.sql into MySQL database, optionally import data files
* Edit include_path in `html/.htaccess`
* Copy `files/example.config.php` to `files/config.php` and edit details

## Longer instructions (Ubuntu)
Install Apache, PHP, MySQL and required libraries
````
sudo apt-get install apache2
sudo apt-get install php5 libapache2-mod-php5 mysql-server php5-mysql
````

Configure Apache
````
sudo nano /etc/apache2/sites-available/default
```

Example configuration
````
NameVirtualHost *

ServerAdmin admin@site.com

DocumentRoot /home/username/hackthis.co.uk/html

Options Indexes FollowSymLinks MultiViews
AllowOverride All
Order allow,deny
````

Restart Apache
````
sudo /etc/init.d/apache2 restart
````

Import schema and testdata into MySQL
````
cd hackthis.co.uk
mysql -u <username> -p<password> < schema.sql
mysql -u <username> -p<password> < testdata.sql
````

Configure paths in .htaccess. Change include_path to the path of your hackthis.co.uk/files/ directory, with trailing slash
````
nano html/.htaccess
````

Create and configure config file. Change path to the path of your hackthis.co.uk directory, without trailing slash. Next set MySQL credentials to match those used in setup, database is `hackthis`. Facebook, twitter and lastfm API keys are not required but some features will not work correctly.
````
cp files/example.config.php files/config.php
nano files/config.php
````

Navigate to website
````
http://localhost/
````