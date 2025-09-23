#!/usr/bin/env bash

DEV_HOSTNAME=$(echo $PLATFORM_ROUTES | base64 --decode | jq 'keys[0]' | tr -d \")

if [ "${PLATFORM_BRANCH}" != master ]; then
  echo "Running: wp search-replace 'https://${PRIMARY_DOMAIN}/' '${DEV_HOSTNAME}' --all-tables"
  wp search-replace "https://${PRIMARY_DOMAIN}/" "${DEV_HOSTNAME}" --all-tables

  echo "Publishing Release Page"
  TEST_PAGE_ID=$(wp db query 'select ID from wp_posts  WHERE post_title="News Release Test Story" AND post_type = "post"' --skip-column-names)
  wp post update ${TEST_PAGE_ID} --post_status=publish
fi