#!/bin/sh

set -xe

SCRIPTPATH="$( cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
APPPATH="$(dirname $SCRIPTPATH)"

ln -s ../../../../../settings/settings.local.php web/sites/default/settings.local.php
ln -s ../../../../../files/public web/sites/default/files

BUILDNAME="$(basename $APPPATH)"

rm -f ../active
ln -s ${BUILDNAME} ../active

DRUPAL_IS_NOT_INSTALLED=0
(${COMPOSE_COMMAND} exec -u www-data -w /srv/app/active appserver bin/drush status | grep bootstrap | grep -q Successful) || DRUPAL_IS_NOT_INSTALLED=1
if [ ${DRUPAL_IS_NOT_INSTALLED} -ne 0 ]; then
  echo "Drupal is not installed yet. Skipping deployment process."
  exit 0
fi
${COMPOSE_COMMAND} exec -u www-data -w /srv/app/active appserver bin/drush sql-dump --result-file=/srv/backup/dump-sql-${BUILD_SPEC}-`date +%F_%H-%M-%S`.sql
${COMPOSE_COMMAND} exec -u www-data -w /srv/app/active appserver bin/drush updb -y
${COMPOSE_COMMAND} exec -u www-data -w /srv/app/active appserver bin/drush cim -y
${COMPOSE_COMMAND} exec -u www-data -w /srv/app/active appserver bin/drush cr -y
