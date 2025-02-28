# WordPress website backup (on a linux machine, procedure may vary on a windows one)
Our WordPress may need backup from time to time.
This can be done via the commands :
```bash
sftp verticalws@ftp.cluster011.hosting.ovh.net
# Provide password, then from the sftp shell :
sftp: get -R /
```

## Better parallel download with lftp
> Note, found this trick on [https://superuser.com/a/726866](https://superuser.com/a/726866)
But this is sequential, whether this command is far more efficient (and works like RSync) :
```bash
# One-liner silent operation :
# lftp sftp://{user}:{password}@{server} -e 'mirror --verbose --use-pget-n=8 -c . . --parallel'
lftp sftp://verticalws:<password>@ftp.cluster011.hosting.ovh.net -e 'mirror --verbose --use-pget-n=8 -c . . --parallel'

#or simply
lftp sftp://verticalws@ftp.cluster011.hosting.ovh.net
# Then from the shell :
mirror --verbose --use-pget-n=8 -c . . --parallel
```


# Archiving backup for later
First thing, zip the whole folder and give it a relevant name (E.g : VerticalAssoWPBackup-15-02-2025-12h36.zip) without touching it.
Then, backup it safely on a disk drive, on various physical devices if need be.

# Cleaning up backup
1. Remove old backups
Going back to the raw synchronization performed earlier, we can remove the `/www/wp-content/backupwordpress-***` folder.
It contains auto saves and usually is very, very heavy.
We're already doing a backup, so ditch this directory.

2. Delete all gallery's `_backup` files
In the gallery (`www/wp-content/gallery`), wordpress automatically backs up *.jpg files.
Remove all *_backup files found in the gallery folder with tools such as a file explorer, using the search bar (search for "_backup", select all, delete).

