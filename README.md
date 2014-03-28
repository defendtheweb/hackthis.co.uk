HackThis
========
[![project status](http://stillmaintained.com/HackThis/hackthis.co.uk.png)](http://stillmaintained.com/HackThis/hackthis.co.uk)

This repository contains all code for http://www.hackthis.co.uk.

## Installation Instructions

You can set up the site on your own local machine and help the development.
The specific instructions differ depending on your operating system.
Following are instructions for Windows and Ubuntu. In the end you can find a general description of the process for any other OS.

### Ubuntu Installation

1. Clone this git repository to your current directory with
    ```
    git clone http://github.com/HackThis/hackthis.co.uk
    ```

2. Run the the installation script by using the following command
    ```
    sudo ./install_hackthis_ubuntu.sh
    ```
    and follow the instructions. The script will:
    - Install Apache, MySql, PHP and relevant libraries if not installed yet
    - Set up a virtual host name of your choosing on your local machine and set up the site there
    - Configure .htaccess and config.php with the appropriate definitions
    - Create hackthis database and tables

3. Navigate to your local copy of the website.
    Replace <your.virtual.hostname> below with the name chosen during the installation.

    ```
    http://<your.virtual.hostname>/?generate
    ```
    `?generate` is required after css/js changes to updated cache. Cache should autogenerate the first time each page is accessed and will autogenerate after a period of time.

### Windows Installation

1. Download and install WAMP server for windows from http://www.wampserver.com/en/
2. Download and install Git for windows (including Git Bash) from http://msysgit.github.io/
3. Start your WAMP server manager and make sure all services are running. The W icon in the system tray should be green.
4. Run Git Bash and issue the following command to clone this repository:

    ```
    git clone http://github.com/HackThis/hackthis.co.uk
    ```

5. Change the directory to the repository and run the installation script with the commands

    ```
    cd hackthis.co.uk
    ./install_hackthis_windows.sh
    ```
    Follow the instructions of the script until it's done.
    If an error occurs, the script will let you know what to do.
    Fix what's wrong and re-run the script until it ends successfully.
6. Open your broswer and navigate to

    ```
    http://localhost/hackthis/?generate
    ```
    `?generate` is required after css/js changes to updated cache. Cache should autogenerate the first time each page is accessed and will autogenerate after a period of time.

### Any other OS installation instructions

1. Install git and clone this repository (http://github.com/HackThis/hackthis.co.uk).
2. Install a LAMP stack (apache2, php5, libapache2-mod-php5, mysql-server, and php5-mysql).
3. Set up a virtual host name in /etc/apache2/sites-available/default. Make sure to include 'AllowOverride All'.

    Example configuration (for virtual host named ht.com)
    ```
    <virtualhost *:80>

        # Admin email, Server Name (domain name) and any aliases
        ServerAdmin webmaster@ht.com
        ServerName  ht.com
        ServerAlias ht.com

        # Index file and Document Root (where the public files are located)
        DirectoryIndex index.php
        DocumentRoot /var/www/vhosts/ht.com/htdocs
        <Directory /var/www/vhosts/ht.com/htdocs>
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Order allow,deny
			allow from all
        </Directory>

        # Custom log file locations
        LogLevel warn  
        ErrorLog  /var/www/vhosts/ht.com/log/error.log
        CustomLog /var/www/vhosts/ht.com/log/access.log combined

    </virtualhost>
    ```

4. Enable the new virtual host with a2ensite and restart Apache
5. Add a line to /etc/hosts mapping the virtual host to the local machine 127.0.0.1.
    For example for the virtual host va.com use
    ```
    sudo echo 127.0.0.1 va.com >> /etc/hosts
    ```

6. Import schema and testdata into MySQL
    ```
    cd hackthis.co.uk
    mysql -u <username> -p<password> < schema.sql
    mysql -u <username> -p<password> < testdata.sql
    ```

7. Configure paths in .htaccess. Change include_path to the path of your hackthis.co.uk/files/ directory, with trailing slash
    ```
    cp html/example.htaccess html/.htaccess
    nano html/.htaccess
    ```

8. Create and configure config file. Change path to the path of your hackthis.co.uk directory, without trailing slash. Next set MySQL credentials to match those used in setup, database is `hackthis`. Facebook, twitter and lastfm API keys are not required but some features will not work correctly.
    ```
    cp files/example.config.php files/config.php
    nano files/config.php
    ```

9. Create and set new folder privilages
    ```
    mkdir html/files/css/min
    mkdir html/files/css/min/light
    mkdir html/files/css/min/dark
    chmod 777 html/files/css/min
    chmod 777 html/files/css/min/light
    chmod 777 html/files/css/min/dark
    mkdir html/files/js/min
    chmod 777 html/files/js/min
    mkdir files/uploads/users
    chmod 777 files/uploads/users
    mkdir files/cache/twig
    chmod 777 files/cache
    chmod 777 files/cache/twig
    mkdir files/logs
    chmod 777 files/logs
    ```

10. Navigate to your local copy of the website
    ```
    http://<localhost or virtual host name>/?generate
    ```
    `?generate` is required after css/js changes to updated cache. Cache should autogenerate the first time each page is accessed and will autogenerate after a period of time.

## Documentation
Full documentation for the code is still under development - all documentation can be found [here](https://www.hackthis.co.uk/docs).

