webldappasswd
=============

Web frontend to change ldap password, based on http://ilya-evseev.narod.ru/posix/webldappasswd/

Minor changes to make it work with SUSE ldap server.

cd /srv/www/htdocs
git clone git@github.com:cyberorg/webldappasswd.git

cp ldap.php-sample ldap.php

Change the colored text to point to your correct ldap domain in ldap.php

$ldapFullUsername = "uid=$userLogin,ou=people,<font color="green">dc=capitalvia,dc=com"</font>;
