#!/bin/bash

source ./.env

scp -P $KINSTA_STAGING_PORT -r ./wp-content/themes/custom $KINSTA_STAGING_USER@$KINSTA_STAGING_IP:./public/wp-content/themes
# scp -P $KINSTA_STAGING_PORT -r ./wp-content/plugins/woocommerce-name-your-price $KINSTA_STAGING_USER@$KINSTA_STAGING_IP:./public/wp-content/plugins/
# scp -P $KINSTA_STAGING_PORT -r ./wp-content/plugins/woocommerce-subscriptions $KINSTA_STAGING_USER@$KINSTA_STAGING_IP:./public/wp-content/plugins/
#scp -P $KINSTA_STAGING_PORT -r ./wp-content/mu-plugins $KINSTA_STAGING_USER@$KINSTA_STAGING_IP:./public/wp-content/

curl -L https://theavenueconcept.org/kinsta-clear-cache-all/
