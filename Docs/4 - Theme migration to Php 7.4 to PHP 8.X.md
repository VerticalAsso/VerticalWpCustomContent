# Migration of the vertical and vertical-child themes
Our themes were stuck in PHP 7.4 and prevented the website to fully load.
We had to migrate them to a recent PHP 8.X in order to use latest WordPress revisions.

# Build the Php8Theme.Dockerfile
```sh
docker build -f Docker/Php8Theme.Dockerfile -t php-themes .
```
This image starts up with a working copy of [Rector](https://github.com/rectorphp/rector): a nice PHP tool that allows to perform quick migrations across PHP versions and refactors the code in consequence.

## Run the container and map the local themes to be transformed
```sh
docker run -it -v $(pwd)/themes:/usr/src/themes php-themes /bin/bash
```

Then from inside the container :
```sh
cd /usr/src/themes
rector --dry-run
rector process
```

=> This will apply refactorings to the source files in a bidirectional fashions (changes will be applied in our source code, outside the container.)

# Test the themes in the docker compose environment
See [3 - Docker compose.md documentation](3%20-%20Docker%20Compose.md) for details on how to set up such an environment.

First, start the docker compose stack :
```sh
docker compose -f Docker/docker-compose.yaml up
```
Then copy the themes we want to test :
```sh
docker cp themes/vertical 219358c80c72:/var/www/html/wp-content/themes
docker cp themes/vertical-child 219358c80c72:/var/www/html/wp-content/themes
```

Access the website in the [http://localhost:8080/wp-admin/themes.php](http://localhost:8080/wp-admin/themes.php) admin section and click the "try and previsualize" theme to try loading it.
Check for errors in the `/var/www/html/wp-content/debug.log` file and fix them one by one.
Generally, issues will relate to the "`create_function()`" being invalid in PHP 8.X.

Something looking like that :
```php
add_filter('login_errors', create_function('$a', "return null;"));
```

Should be transformed into this :
```php
add_filter('login_errors', function ($a) {
    return null;
});
```

Keep fixing issues as they appear and soon we'll have a working theme again !

# Retrieve modified theme and save it for reuse (using git or this repo)
```sh
docker cp 219358c80c72:/var/www/html/wp-content/themes/vertical $(pwd)/themes/vertical
docker cp 219358c80c72:/var/www/html/wp-content/themes/vertical-child $(pwd)/themes/vertical-child
```
