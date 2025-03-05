CREATE DATABASE IF NOT EXISTS api_database_test;

GRANT ALL PRIVILEGES ON api_database_test.* TO api_user @'%';

FLUSH PRIVILEGES;
