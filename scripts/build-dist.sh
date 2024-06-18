#!/bin/bash

rm -rf dist/ vendor/
mkdir -p ./dist
zip -9 -r "dist/almapay-monthlypayments-magento2.zip" \
    Api/ \
    Block/ \
    Controller/ \
    Cron/ \
    CustomerData/ \
    etc/ \
    Gateway/ \
    Helpers/ \
    i18n/ \
    Model/ \
    Observer/ \
    Plugin/ \
    Setup/ \
    view/ \
    CHANGELOG.md \
    composer.json \
    crowdin.yml \
    LICENSE.txt \
    README.md \
    registration.php
