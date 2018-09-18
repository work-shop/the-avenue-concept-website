#!/bin/bash

#npm run build

source ./.env

scp -P $KINSTA_PORT -r ./wp-content/themes/custom $KINSTA_USER@$KINSTA_IP:./public/wp-content/themes
scp -P $KINSTA_PORT -r ./wp-content/plugins $KINSTA_USER@$KINSTA_IP:./public/wp-content/
scp -P $KINSTA_PORT -r ./wp-content/mu-plugins $KINSTA_USER@$KINSTA_IP:./public/wp-content