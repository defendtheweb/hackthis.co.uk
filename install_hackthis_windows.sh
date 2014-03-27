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

function waitForEnterAndExit {
	echo
	echo Please press Enter to quit.
	read
	exit 1
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
		if [ -z "$mysql_client" ]; then
			mysql_client=`ls /c/wamp/bin/mysql/*/bin/mysql.exe 2>/dev/null`;
		fi;
		if [ -z "$mysql_client" ]; then
			echo -e "\t Couldn't find mysql client.";
			echo -e "\t Should have been in c:\wamp\\\\bin\mysql\__mysql_version__\\\\bin\mysql.exe"
			echo
			echo -e "\e[0;31mAborting...\e[m"
			waitForEnterAndExit;
		fi;

		$mysql_client -u $mysql_user $pass_clause -e "quit"
		if [ $? = 0 ]; then
			success=0;
		else
			echo -e "\t Couldn't connect to mysql server with these username and password.";
			echo -e "\t Trying again... (press ctrl+c to quit)."
			mysql_user=""
		fi
	done;
}

# Install script should only work on cygwin
uname -a | grep MINGW > /dev/null || { 
	echo This script is intended to run on Windows machines. 
	echo It can only be run through git Bash \(MINGW\) on windows.
	echo For an Ubuntu installation run install_hackthis_ubuntu.sh.
	waitForEnterAndExit
}

# Make sure the script is run from the project's Git root directory, indentified by the README.md file
ls README.md > /dev/null 2>&1 || { 
	echo Please run this script from HackThis.co.uk Git\'s root direcotry.
	waitForEnterAndExit
}
git_root_dir=`pwd`

# Make sure you have admin privileges to run this script
# If not, create a vb script and a batch file in /tmp
# The vb script elevates privileges and runs the batch file
# which in turn runs this install script again.
net file 2>&1 | grep "error" > /dev/null && {
	echo You need root permissions to run this script.
	echo Elevating permissions in another window...

	cat > /tmp/OEgetPrivileges.vbs <<GetPrivilegesVBS

Set UAC = CreateObject("Shell.Application")  
Set Shell = WScript.CreateObject("WScript.Shell")
tempFolder = Shell.ExpandEnvironmentStrings("%TEMP%")
UAC.ShellExecute tempFolder & "\win_install_script.bat", "ELEV", "", "runas", 1  
GetPrivilegesVBS

	cat > /tmp/win_install_script.bat <<WIN_INSTALL_SCRIPT
cmd.exe /c ""C:\Program Files (x86)\Git\bin\sh.exe" --login -i -c "cd $git_root_dir; ./install_hackthis_windows.sh; echo Press Enter to quit ...; read l;"" 

WIN_INSTALL_SCRIPT

	cscript //nologo /tmp/OEgetPrivileges.vbs
	exit 1 
}

# Checking installed packages (sort of)
echo Checking installed packages

# Make sure that wampmanager.exe is found in C:\wamp\wampmanager.exe
ls /c/wamp/wampmanager.exe > /dev/null 2>&1 || {
	echo
	echo -e "\t Couldn't find WAMP installation in c:\\wamp."
	echo -e "\t Make sure you downloaded and installed WAMP from http://www.wampserver.com/en/"
	echo
	echo -e "\e[0;31mAborting...\e[m"
	waitForEnterAndExit
}
echo -e "\t WAMP installation found in C:\wamp"

# Make sure that sh.exe is found in C:\Program Files (x86)\Git\bin\sh.exe
ls /c/Program\ Files\ \(x86\)/Git/bin/sh.exe > /dev/null 2>&1 || {
	echo
	echo -e "\t Git Bash was not installed in the default location."
	echo -e '\t Installation requires sh.exe to be found at C:\Program Files (x86)\Git\\bin\sh.exe.'
	echo
	echo -e "\e[0;31mAborting...\e[m"
	waitForEnterAndExit
}
echo -e "\t Git Bash installation found in C:\Program Files (x86)/Git/bin/sh.exe"

# Make sure mysql client is installed in C:\wamp\bin\mysql\[mysql version\bin\mysql.exe
mysql_client=`ls /c/wamp/bin/mysql/*/bin/mysql.exe 2>/dev/null`;
if [ -z "$mysql_client" ]; then
	echo
	echo -e "\t Couldn't find mysql client.";
	echo -e "\t Should have been in c:\wamp\\\\bin\mysql\__mysql_version__\\\\bin\mysql.exe"
	echo
	echo -e "\e[0;31mAborting...\e[m"
	waitForEnterAndExit;
fi;

# Make sure ruby is installed (needed for sass compilation)
ruby_exe=`ls /c/Ruby*/bin/ruby.exe 2>/dev/null | head -1`;
if [ -z "$ruby_exe" ]; then
	echo
	echo -e "\t Couldn't find Ruby installation.";
	echo -e "\t Should have been in c:\\\\Ruby...\\\\bin\\\\ruby.exe"
	echo -e "\t You can download it from http://www.rubyinstaller.org/"
	echo -e "\t \e[0;31mMake sure to check \"Add Ruby Executable to your PATH\" during installation!\e[m"
	echo -e "\t \e[0;31mIf you didn't, just run the installation again, no need to uninstall first.\e[m"
	echo
	echo -e "\e[0;31mAborting...\e[m"
	waitForEnterAndExit;
fi;

# Make sure ruby executables are in the path
gem list --local > /dev/null 2>&1 || {
	echo
	echo -e "\t Ruby executables aren't in your PATH.";
	echo -e "\t Please run the Ruby installation again (no need to uninstall first) and make sure to"
   	echo -e "\t check \"Add Ruby Executable to your PATH\" during installation."
	echo -e "\t You can download the installation from http://www.rubyinstaller.org/"
	echo
	echo -e "\e[0;31mAborting...\e[m"
	waitForEnterAndExit;
}

# Install scss gem if not already installed
gem list --local 2>/dev/null | grep sass > /dev/null || {
	echo -e "\t Installing Sass gem...";
	gem install sass || {
		echo
		echo -e "\e[0;31mCouldn't install Sass gem!\e[m"
		echo
		echo -e "\e[0;31mAborting...\e[m"
		waitForEnterAndExit;
	}
}

# Apache setup (name based virtual host)
echo Configuring Apache alias to be localhost/hackthis

# Make sure Apache webserver is up
net start wampapache 2>&1 | grep "started" > /dev/null || {
	echo -e "\t \e[0;31mApache web server couldn't be started. Aborting.\e[m"
	waitForEnterAndExit
}

# Setting alias for hackthis (localhost/hackthis)
msdirname=${git_root_dir:1}/html/
msdirname=${msdirname/\//:\/}		# replace only the first / 

cat > /c/wamp/alias/hackthis.conf <<AliasDefintion
Alias /hackthis/ "${msdirname}"

DocumentRoot ${msdirname}
<Directory "${msdirname}">
    Options Indexes FollowSymLinks MultiViews
    AllowOverride all
        Order allow,deny
    Allow from all
</Directory>
AliasDefintion

# Refresh definitions with stop and start
net stop wampapache 2>&1 | grep "stopped" > /dev/null || {
	echo -e "\t \e[0;31mApache web server couldn't be stopped. Aborting.\e[m"
	waitForEnterAndExit
}
net start wampapache 2>&1 | grep "started" > /dev/null || {
	echo -e "\t \e[0;31mApache web server couldn't be started. Aborting.\e[m"
	waitForEnterAndExit
}
echo -e '\t The alias localhost/hackthis/ was created.'

# .htaccess and php include_path configuration
echo Configuring .htaccess and php include_path
if [ -e html/.htaccess ]; then 
	echo -e '\t The existing .htaccess file was overwritten.'
fi
cp html/example.htaccess html/.htaccess
include_path=${git_root_dir:1}/files/	# remove leading / before drive letter and add files dir
include_path=${include_path/\//:\/}		# replace only the first / with : to convert to dos format 
# Replace the : with ; which is the path separator is Windows
sed -i "s|:/path/to/hackthis.co.uk/files/|;${include_path}|" html/.htaccess 

# config.php
echo Configuring config.php
if [ -e files/config.php ]; then 
	echo -e '\t The existing files/config.php file was overwritten.'
fi

getMySqlCredentials
cp files/example.config.php files/config.php
echo -e "\t Updating domain definition in config.php"
sed -i "s/example.org/localhost\/hackthis/" files/config.php
echo -e "\t Updating mysql credentials in config.php"
sed -i "s/'root'/'${mysql_user}'/" files/config.php
sed -i "s/'pass'/'${mysql_pass}'/" files/config.php

# MySql database setup
echo "Initializing HackThis database... (previous data is overwritten)"
getMySqlCredentials
$mysql_client -u $mysql_user $pass_clause < setup.sql
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

echo Done!
echo Press Enter to quit
read
