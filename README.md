# VerticalWpCustomContent
Vertical association WordPress custom content (plugins / themes)

# Fetch backups and sql dumps
1. Fetch a backed up content from the website (sftp, see [Docs/Website-BackupProcedure](Docs/1%20-%20WebsiteBackupProcedure.md)).
2. Fetch a fresh SQL backup

-> Dump all files under
BackedUpContent/
  * wp-content
  * wp-config.php
  * database-dump.sql


# Build docker images
Then, build the docker images :
```shell
docker build -f Dockerfile.wordpress -t wordpress-vertical .
docker build -f Dockerfile.mariadb -t mariadb-vertical .
```
