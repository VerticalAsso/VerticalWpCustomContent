# VerticalWpCustomContent
Vertical association WordPress custom content (plugins / themes)

## Fetch backups and sql dumps
1. Fetch a backed up content from the website (sftp, see [Docs/Website-BackupProcedure](Docs/1%20-%20WebsiteBackupProcedure.md)).
2. Fetch a fresh SQL backup

-> Dump all files under `./BackedUpContent/`

* wp-content
* wp-config.php
* database-dump.sql


# Local testing (and development)
## Build and run docker images
![Arch](Docs/Drawings/DockerComposeArch_annoté.png)

Follow the docs in [Docs/3 - Docker Compose.md](Docs/3%20-%20Docker%20Compose.md).
Don't forget to update `wp-config.php` first, then the `v34a_options` table in the datatabase.


## Test WordPress update
You can test db and plugin updates. Only the container files will be updated but it won't persist if you delete the container (use `docker compose stop` instead of `docker compose down`).
> Note : to fully clean volumes (hence **including** the database cache) : `docker compose down -v` will ensure all volumes are dropped as well.

## Test updated theme
To test the updated theme, uncomment the two theme related lines in the wordpress container `volume` section, and restart docker compose.


