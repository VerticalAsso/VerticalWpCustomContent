# Start from the official MariaDB image
FROM mariadb:latest AS vertical-database


# Set environment variables
ENV MARIADB_ROOT_PASSWORD=rootpassword
ENV MARIADB_DATABASE=verticalws34

# Copy your backup SQL file into the container
COPY ./BackedUpContent/database-dump.sql /tmp/backup.sql

# Prepend USE statement to the SQL file and move it to the init directory
RUN echo "USE \`${MARIADB_DATABASE}\`;" > /docker-entrypoint-initdb.d/restore.sql && \
    cat /tmp/backup.sql >> /docker-entrypoint-initdb.d/restore.sql

# Ensure no data exists in /var/lib/mysql during image build
RUN rm -rf /var/lib/mysql*
