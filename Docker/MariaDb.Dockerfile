# Start from the official MariaDB image
FROM mariadb:latest AS vertical-database


# Set environment variables
# ENV MARIADB_ROOT_PASSWORD=rootpassword
ENV MARIADB_DATABASE=verticalws34

# Copy your backup SQL file into the container
COPY ./BackedUpContent/database-dump.sql /tmp/backup.sql

# Prepend USE statement to the SQL file
RUN echo "USE \`${MARIADB_DATABASE}\`;" > /docker-entrypoint-initdb.d/restore.sql && \
    cat /tmp/backup.sql >> /docker-entrypoint-initdb.d/restore.sql



# Set environment variables for MariaDB
# ENV MARIADB_ROOT_PASSWORD=test
# ENV MARIADB_DATABASE=vertical34
# ENV MARIADB_USER=vertical
# ENV MARIADB_PASSWORD=test

# # Copy your database dump into the container
# COPY BackedUpContent/database-dump.sql /tmp/backup.sql

# # Temporary entrypoint bypass to initialize the database
# # RUN mariadbmariad -uroot --initialize-insecure
# # RUN mariadb -uroot --user=vertical --skip-networking

# RUN mariadb -uroot -e "CREATE DATABASE ${MARIADB_DATABASE};"
# RUN mariadb -uroot -e "CREATE USER '${MARIADB_USER}'@'%' IDENTIFIED BY '${MARIADB_PASSWORD}';"
# RUN mariadb -uroot -e "GRANT ALL PRIVILEGES ON ${MARIADB_DATABASE}.* TO '${MARIADB_USER}'@'%';"
# RUN mariadb -uroot ${MARIADB_DATABASE} < /tmp/backup.sql

# # Clean up the temporary SQL file
# RUN rm -f /tmp/backup.sql

# # Expose MariaDB port
# EXPOSE 3306