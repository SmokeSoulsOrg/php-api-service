-- Create the root@'%' user only if it doesn't exist
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

-- Create replica user
CREATE USER IF NOT EXISTS 'replica'@'%' IDENTIFIED WITH mysql_native_password BY 'replica_pass';
GRANT REPLICATION SLAVE ON *.* TO 'replica'@'%';

-- Apply changes
FLUSH PRIVILEGES;
