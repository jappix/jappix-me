Jappix Me Installation
======================

1. Prerequisites
----------------

* Apache or lighttpd
* PHP 5+
* Python 2.7+
* A Linux OS

* xmpppy library (http://xmpppy.sourceforge.net/)


2. Deploy Jappix Me
-------------------

Simply copy all the files containted in the Jappix Me folder (including .htaccess which might be hidden) in your Jappix Me web directory.

Jappix Me must be served from the root of a domain. You will need to create a sub-domain of your existing domain and bind it to a virtual host on your Web server.


Rewrite rules must be added to your Web server configuration to make it work. Apache should already have them configured thanks to our .htaccess file.

You will just need to setup a proxy pass from /bosh to your BOSH server.


For those using lighttpd, here is the configuration you should add:

	$HTTP["host"] == "me.jappix.com" {
	        server.document-root = "/var/www/me.jappix.com"

	        url.access-deny = ( "config.xml" )
	        url.rewrite-once = ( "^/([^\/]+@[^\/]+|new|privacy|cache|invite|pending|py)(([\/]+)(.*))?$" => "/index.php?u=$1&s=$4", "^/bosh(.*)?" => "/http-bind$1" )
	        proxy.server = ( "bosh" => (( "host" => "127.0.0.1", "port" => 5280 )))

	        expire.url = ( "/css/" => "access plus 1 days",
	                       "/js/" => "access plus 1 days",
	                       "/img/" => "access plus 1 months" )
	        server.error-handler-404 = "/index.php?s=404"
	}

With the following modules: mod_access, mod_expire, mod_proxy, mod_rewrite (enable them at top of your configuration file)

Don't forget to tune it so that everything is fine with your server configuration (/bosh is a proxy to your BOSH server).


3. Configure Jappix Me
----------------------

Jappix Me only requires a dedicated XMPP account on your XMPP server to work. This account will be used by Jappix Me bots (those which will create and update profiles).

Simply open ./config.xml and fill in the file with the required information.

Don't forget to set app mode to production once Jappix Me is working well with no error (development prints errors).


4. Launch Jappix Me bot
-----------------------

Jappix Me bot must be launched regularly in order to process profile checks, creation and updates.

Open your crontab file: crontab -e

Then, paste these lines:

	# Jappix Me bot
	*/1 * * * * python /var/www/me.jappix.com/py/pending.py >>/dev/null
	*/5 * * * * python /var/www/me.jappix.com/py/update.py >>/dev/null

Don't forget to replace the absolute path to your Jappix Me base dir so that the scripts can be launched.


5. Secure Jappix Me
-------------------

In order to secure Jappix Me, please check that the following folders cannot be accessed from the Web:

* ./config.xml
* ./py (check by trying to access ./py/update.py)
* ./php
* ./cache
* ./invite
* ./pending


You should be done. Have fun with Jappix Me! ;)