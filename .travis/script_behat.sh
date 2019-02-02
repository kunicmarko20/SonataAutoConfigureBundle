#!/usr/bin/env sh
set -ev

features/Fixtures/Project/bin/console --no-interaction cache:clear --env=test
features/Fixtures/Project/bin/console doctrine:schema:update --force --env=test
features/Fixtures/Project/bin/console assets:install
vendor/bin/behat
