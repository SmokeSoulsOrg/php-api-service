-- Grant replication privileges so we can check for the status in the replica:check command
CREATE USER IF NOT EXISTS 'sail'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON `pornstar_db`.* TO 'sail'@'%';
GRANT REPLICATION CLIENT ON *.* TO 'sail'@'%';

-- Apply changes
FLUSH PRIVILEGES;
