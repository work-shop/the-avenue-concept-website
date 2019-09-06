#!/bin/bash

#npm run build-production

source ./.env

# theme
scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/themes/custom $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/themes

# specific file
# scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/themes/custom/partials/footer_site.php $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/themes/custom/partials

# scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/plugins/woocommerce-subscriptions $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/plugins/

# specific folder
#scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/themes/custom/js $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/themes/custom

#scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/plugins $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/
#scp -P $KINSTA_PRODUCTION_PORT -r ./wp-content/mu-plugins $KINSTA_PRODUCTION_USER@$KINSTA_PRODUCTION_IP:./public/wp-content/


#npm run build-development

curl -L https://theavenueconcept.org/kinsta-clear-cache-all/
