# Run latest Wordpress in Docker
```bash
# Share host network with docker container

# in /etc/hosts, add 127.0.0.1 vertical-asso.fr  <<<<< - redirects to localhost when vertical-asso.fr is reached
# This is required to bind the local mariadb server (3306) to the docker website.
# But there is probably something to do by running the server locally within the docker instead, starting from a *.sql backuo
docker run -p 8000:80 --network=host wordpress:latest

# Copy all dumped wp-content into newly installed wordpress website
docker ps # retrieve container's id
docker cp www/wp-content 2b9b8f9effae:/var/www/html
docker cp www/wp-config.php 2b9b8f9effae:/var/www/html
```

# Open VsCode and connect to running container (attach to ...)
Edit wp-config.php and setup :

```php
define('DB_NAME', 'vertical34');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'test');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', 'test');

/** Adresse de l'hébergement MySQL. */
define('DB_HOST', '127.0.0.1');

// Enables direct upgrade method (so that Wordpress doesn't ask about ftp credentials and uploads in-situ)
define('FS_METHOD', 'direct');
```

# In database, change v34_options table
* Set option_name = "siteurl" to "http://vertical-asso.fr" instead of "https://vertical-asso.fr" -> drops the https support and then the certificate need.
* set option_name = "home" to the same

Then Open browser at `localhost:80` -> it should redirect to `http://vertical-asso.fr`
**⚠️ But before that, clear all cookies and session cache !!!**


# In case of plugin activation failure
* Set option_name = "active_plugins" to `a:0:{}` where pattern looks like this :
```
a : activated_plugins_count : { index : value ; s : <something> : "plugin name/file"; <second plugin>}
a:2:{i:0;s:19:"akismet/akismet.php";i:1;s:35:"backupwordpress/backupwordpress.php";}
```
=> In case of WordPress failing to load, reset this field to `a:0:{}` and reactivate plugins one by one.

# Note : see Wordpress documentation to upgrade manually
* => https://wordpress.org/documentation/article/updating-wordpress/