#!/bin/bash

#npm run build-production

source ./.env

#SED_REPLACE="s_//localhost:3000_//precollege.wsri.host_g ; s_//localhost:3001_//precollege.wsri.host_g ; s_//localhost:8080_//precollege.wsri.host_g"

#docker cp $DOCKER_WORDPRESS_CONTAINER:/var/www/html/wp-content/uploads ./dist/wp-content
#docker exec $DOCKER_DATABASE_CONTAINER mysqldump -u$WORDPRESS_DB_USER -p$WORDPRESS_DB_PASSWORD $WORDPRESS_DB_NAME | sed -e "$SED_REPLACE" > ./dist/migration.sql

scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/themes/custom $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/themes
#scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/plugins $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/
#scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/mu-plugins $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/
#scp -r ./dist/wp-content/uploads root@$DROPLET_IP:/var/www/html/wp-content/
#scp ./dist/migration.sql root@$DROPLET_IP:/root
#scp ./.remote.deploy.sh root@$DROPLET_IP:/root

#ssh root@$DROPLET_IP 'cd /root ; chmod +x ./.remote.deploy.sh ; ./.remote.deploy.sh'

#rm -rf ./dist/wp-content/uploads
#rm ./dist/migration.sql


# TODO: Add a hook to migrate and string-replace the database.

npm run build-development

curl -L https://theavenueconcept.org/kinsta-clear-cache-all/
