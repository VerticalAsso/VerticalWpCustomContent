# Post-Mortem: XDebug Not Stepping in Dockerized WordPress

**Date:** 2025-06-04
**Author:** bebenlebricolo
**System/Module:** Docker + WordPress + XDebug

## What happened?
XDebug would not stop at breakpoints in VSCode after refactoring a WordPress plugin. Fatal errors were resolved, but step debugging still didn’t work.

## Investigation
- Checked plugin code and error logs (fixed a missing callback).
- Verified XDebug and VSCode configs.
- Noticed XDebug log file could not be written (`/tmp/xdebug.log` owned by root).
- Realized PHP was running as www-data, not root.

## Root Cause
XDebug could not write its log file due to file permission issues, which interfered with its operation.

## Resolution
Changed log file ownership to www-data and restarted the container. XDebug started working as expected.

### + XDebug config :

```ini
# /usr/local/etc/php/conf.d/xdebug.ini content
[xdebug]
xdebug.mode = debug
xdebug.start_with_request = yes
xdebug.client_port = 9003
xdebug.client_host = 'host.docker.internal'
xdebug.log = /var/log/xdebug.log
xdebug.discover_client_host=1
```

Then : `apachectl restart` from within the container. (Or maybe a docker compose stop & restart would do the trick)

## Lessons Learned / Next Steps
- Always check log file permissions for services running in containers.
- Consider adding a script or Docker healthcheck to verify permissions on critical files.

## References
- [XDebug log file docs](https://xdebug.org/docs/all_settings#log)