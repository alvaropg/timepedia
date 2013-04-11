TimePedia
=========
A timeline Wikipedia RESTFul service based in the Wikipedia categories using DBPedia and SPARQL.
It's in a very early stage of development

Installation
------------

Apache with mod_rewrite enabled, PHP > 5.3, Zend Framework 2 and Curl.

This is a virtual host example config:

        <VirtualHost *:80>
	        ServerName timepedia.localhost
	        ServerAdmin alvaropg@gmail.com
	        DocumentRoot /var/www/html/timepedia/public
	        SetEnv APPLICATION_ENV "development"
	        ErrorLog logs/timpedia-error_log
	        CustomLog logs/timepedia-access_log common
	        <Directory /var/www/html/timepedia/public>
                	DirectoryIndex index.php
	                AllowOverride All
                	Order allow,deny
	                Allow from all
	        </Directory>
        </VirtualHost>

Test
----

        curl -i -H "Accept: application/json" http://timepedia.localhost/timeline.json/French_Revolution > file.json
        curl -i -H "Accept: application/json" http://timepedia.localhost/timeline.json/Spanish_Civil_War > file.json
