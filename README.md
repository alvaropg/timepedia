TimePedia
=========
A timeline Wikipedia viewer based in the Wikipedia categories using DBPedia and SPARQL.
It's in a very early stage of development (no security, no framework, for a rapid JSON prototyping of the timeline and the events).

Installation
------------

Apache with mod_rewrite enabled, PHP > 5.3 and Curl.

This is a virtual host example config:

        <VirtualHost *:80>
            ServerName timepedia.localhost
            DocumentRoot /var/www/timepedia
            SetEnv APPLICATION_ENV "development"
            <Directory /var/www/timepedia>
                DirectoryIndex index.php
                AllowOverride All
                Order allow,deny
                Allow from all
            </Directory>
        </VirtualHost>

Test
----

        curl -i -H "Accept: application/json" http://timepedia.localhost/timeline?category=French_Revolution > file.json
        curl -i -H "Accept: application/json" http://timepedia.localhost/timeline?category=Spanish_Civil_War > file.json
