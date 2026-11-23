-- Runs once when the MariaDB volume is created.
-- The dev database `bezvahania` is created by MARIADB_DATABASE; this adds the test database.
-- The wildcard grant lets parallel tests create bezvahania_testing_test_N databases.
CREATE DATABASE IF NOT EXISTS `bezvahania_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `bezvahania\_testing%`.* TO 'bezvahania'@'%';
FLUSH PRIVILEGES;
