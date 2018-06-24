#!/bin/bash

source ./.env

MIGRATION_MINUS_1_NAME="migration-$(date +%s).old"
MIGRATION_NAME="migration.sql"

mkdir -p ./migrations
docker exec $DOCKER_DATABASE_CONTAINER mysqldump -u$WORDPRESS_DB_USER -p$WORDPRESS_DB_PASSWORD $WORDPRESS_DB_NAME > ./migrations/$MIGRATION_MINUS_1_NAME
docker exec -i $DOCKER_DATABASE_CONTAINER mysql -u$WORDPRESS_DB_USER -p$WORDPRESS_DB_PASSWORD $WORDPRESS_DB_NAME < $1
