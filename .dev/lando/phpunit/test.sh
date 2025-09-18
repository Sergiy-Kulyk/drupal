# We need to add drupal/core-dev for PHPUnit
# We do this in a check here so as not to run `composer require`
# every time we want to run tests.
if ! grep -q "drupal/core-dev" composer.json; then
  echo "Adding core tooling. This may take a moment...";
  composer require "drupal/core-dev-pinned"
fi
mkdir -p /app/web/sites/simpletest/browser_output
chmod 777 /app/web/sites/simpletest/browser_output
# Now we actually run the command...
/app/bin/phpunit /app/web/modules/custom/ -c /app/.dev/lando/phpunit/phpunit.xml --stop-on-failure --display-deprecations --testdox $1
