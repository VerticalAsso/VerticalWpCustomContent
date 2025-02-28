1. Install MySql
-> sudo pacman -S mysql (choose MariaDB)
2. Install DBeaver
-> May need to use : sudo archlinux-java set java-22-openjdk
3. follow : https://dev.to/sujit-shrc/mariadb-installation-made-easy-a-guide-for-arch-based-linux-users-19o4
4. Create new user in DB : 
   mariadb> : CREATE USER test IDENTIFIED BY 'test'@localhost;
5. Create a new database :
   mariadb> : CREATE DATABASE vertical34;
6. Restore the database
   sudo mariadb vertical34 < ~/Téléchargements/verticalws34.sql


Note : see https://stackoverflow.com/questions/14011968/user-cant-access-a-database !!!

7. Grant new user all rights on new database
   mariadb>: USE vertical34;  // Selects vertical34 database
   mariadb>: GRANT USAGE ON *.* TO 'test'@'localhost' IDENTIFIED BY 'test';
   mariadb>: GRANT USAGE ON *.* TO 'test'@'%' IDENTIFIED BY 'test';
   mariadb>: GRANT ALL PRIVILEGES ON vertical34 TO 'test'@localhost;
   mariadb>: GRANT ALL PRIVILEGES ON vertical34 TO 'test'@%;
   mariadb>: FLUSH PRIVILEGES;
   mariadb>: SHOW GRANTS FOR test;
  
8. In DBeaver : create a new mariadb connection with user "test", password "test"
   -> We should see the restored database
   
9. Drop "test" database, automatically created when creating a new user
  mariadb>: DROP DATABASE test


