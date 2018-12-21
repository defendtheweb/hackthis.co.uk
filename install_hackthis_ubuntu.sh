#!/bin/bash

cat <<Caption


-------------------------------------------------------------
                   H a c k   T h i s ! !
                  The Hacker's Playground
 
                 https://www.hackthis.co.uk/
-------------------------------------------------------------
         Development Environment Installation Script
-------------------------------------------------------------


Caption

function isPackageInstalled {
	packagePolicyOutput=`apt-cache policy $1`
	echo $packagePolicyOutput | grep -v none | grep Installed > /dev/null
	return $?
}

function addRepositoryForPHP5 {
    echo -e '\t Adding ondrej/php PPA in order to install php5.'
    sudo add-apt-repository ppa:ondrej/php
    # If the command was not found, install software-properties-common
    if [ $? != 0 ]; then
        echo -e '\t Adding ondrej/php PPA unsuccessful, installing dependency and retrying.'
        sudo apt-get install software-properties-common
        sudo add-apt-repository ppa:ondrej/php
    fi
    sudo apt-get update
}

function installMissingPackages {

	isPackageInstalled $1
	if [ $? = 0 ]; then
		echo -e "\t $1 already installed."
	else
		echo -e "\t Installing package $1:"
		sudo apt-get install $1 || {
			echo
			echo -e "\t \e[0;31mFailed to install $1 package. Aborting.\e[m"
			exit 1
		}
	fi
}

function getMySqlCredentials {

	success=1;
	until [ $success = 0 ]; do

		# Avoid asking twice if already have the answer, just check it
		if [ -z "$mysql_user" ]; then
			read -p "         Please enter mysql user name: " mysql_user
			read -sp "         Please enter mysql password: " mysql_pass
			echo # needed newline since password isn't echoed
		fi

		#if the password is blank (unsecure, but happens...) don't use -p at all
		if [ -z "$mysql_pass" ]; then
			pass_clause=""
		else
			pass_clause="-p${mysql_pass}"
		fi
	
		# Connect and quit just to make sure the credentials work
		mysql -u $mysql_user $pass_clause -e "quit"
		if [ $? = 0 ]; then
			success=0;
		else
			echo -e "\t Couldn't connect to mysql server with these username and password.";
			echo -e "\t Trying again... (press ctrl+c to quit)."
			mysql_user=""
		fi
	done;
}

# Install script should only work on Ubuntu
uname -a | grep Ubuntu > /dev/null || { 
	echo This script is intended to run on Ubuntu machines. 
	echo For a Windows installation run install_hackthis_windows.sh.
	echo
	echo Please press Enter to exit.
	read
	exit 1 
}

# Make sure sudo is used to run this script
whoami | grep root > /dev/null || { 
	echo You need root permissions to run this script.
	echo Please use \'sudo !!\' to re-run as root. 
	exit 1 
}

# Make sure the script is run from the project's Git root directory, indentified by the README.md file
ls README.md > /dev/null 2>&1 || { 
	echo Please run this script from HackThis.co.uk Git\'s root direcotry.
	exit 1
}
git_root_dir=`pwd`

# Package installation
required_packages="apache2 php5 libapache2-mod-php5 mysql-server php5-mysql php5-ldap"

# Check if the Ubuntu release is >= 16.04 and change php5 to php5.6
if [ $(lsb_release -r | tr -dc '0-9') -ge 1604 ]; then
    addRepositoryForPHP5
    required_packages="apache2 php5.6 libapache2-mod-php5.6 mysql-server php5.6-mysql php5.6-ldap"
fi

echo Checking installed packages
for package in $required_packages; do
	installMissingPackages $package
done

# Start web server if not started yet
service apache2 status
if [ $? != 0 ]; then
	service apache2 start
fi

# .htaccess and php include_path configuration
echo Configuring .htaccess and php include_path
if [ -e html/.htaccess ]; then 
	echo -e '\t \e[0;31m**** OVERWRITING ****\e[m The existing .htaccess file was overwritten.'
fi
cp html/example.htaccess html/.htaccess
include_path="${git_root_dir}/files/"
sed -i "s|/path/to/hackthis.co.uk/files/|${include_path}|" html/.htaccess 

# Apache setup (name based virtual host)

# Choose virtual host name (for example ht.com)
echo Apache setup
while [ -z "$vdomain" ]; do
	echo -ne "\t Please choose a local domain name to use (name based virtual host, e.g. ht.com): "
	read vdomain
done
# Prepare directory structure and make a soft link to the html folder
mkdir -p /var/www/vhosts/${vdomain}/log
chmod 755 /var/www/vhosts/${vdomain}/log
ln -s -f -T ${git_root_dir}/html /var/www/vhosts/${vdomain}/htdocs
# Add the site definition to the available sites and enable it
cat > /etc/apache2/sites-available/${vdomain}.conf <<VirtualHostDefinition
<virtualhost *:80>

	# Admin email, Server Name (domain name) and any aliases
	ServerAdmin webmaster@${vdomain}
	ServerName  ${vdomain}
	ServerAlias ${vdomain}

	# Index file and Document Root (where the public files are located)
	DirectoryIndex index.php
	DocumentRoot /var/www/vhosts/${vdomain}/htdocs
	<Directory /var/www/vhosts/${vdomain}/htdocs>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>

	# Custom log file locations
	LogLevel warn  
	ErrorLog  /var/www/vhosts/${vdomain}/log/error.log
	CustomLog /var/www/vhosts/${vdomain}/log/access.log combined

</virtualhost>
VirtualHostDefinition
a2ensite $vdomain
service apache2 reload
# Add the virtual domain to /etc/hosts
echo "127.0.0.1 $vdomain" >> /etc/hosts

# config.php
echo Configuring config.php
if [ -e files/config.php ]; then 
	echo -e '\t \e[0;31m**** OVERWRITING ****\e[m The existing files/config.php file was overwritten.'
fi

getMySqlCredentials
cp files/example.config.php files/config.php
echo -e "\t Updating domain definition in config.php"
sed -i "s/example.org/${vdomain}/" files/config.php
echo -e "\t Updating mysql credentials in config.php"
sed -i "s/'root'/'${mysql_user}'/" files/config.php
sed -i "s/'pass'/'${mysql_pass}'/" files/config.php

# MySql database setup
echo "Initializing HackThis database... (previous data is overwritten)"
getMySqlCredentials
mysql -u $mysql_user $pass_clause < setup.sql
echo -e "\t HackThis database was initialized"

# Setting up directories and permissions
echo Setting up directories and permissions
mkdir -p html/files/css/min{,/light,/dark}
chmod 777 html/files/css/min{,/light,/dark}
mkdir -p html/files/js/min
chmod 777 html/files/js/min
mkdir -p files/uploads/users
chmod 777 files/uploads/users
mkdir -p files/cache{,/twig}
chmod 777 files/cache{,/twig}
mkdir -p files/logs
chmod 777 files/logs

# Enabeling ModRewrite to solve RewriteEngine issue
echo Enabeling Mod_Rewrite
a2enmod rewrite
service apache2 restart

echo Done!

