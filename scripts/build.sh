#!/bin/sh

set -xe

# The $BUILD_NUMBER and $GIT_BRANCH are coming from Jenkins.
# The $CI_PIPELINE_IID, $CI_COMMIT_REF_NAME, $CI_COMMIT_REF_SLUG are coming from Gitlab CI.
if [ -z "${BUILD_NUMBER}" ]; then
  if [ -n "${CI_PIPELINE_IID}" ]; then
    BUILD_NUMBER=${CI_PIPELINE_IID}
  fi
fi
SPEC_DATE=`date +"%F"`
SPEC_BUILD_NUMBER=`printf "%05d" $BUILD_NUMBER`
if [ -n "${CI_COMMIT_REF_NAME}" ]; then
  SPEC_BRANCH=${CI_COMMIT_REF_NAME}
else
  SPEC_BRANCH=$(echo $GIT_BRANCH | sed -e "s|origin/||g")
fi
if [ -n "${CI_COMMIT_SHORT_SHA}" ]; then
  SPEC_SHA=${CI_COMMIT_SHORT_SHA}
else
  SPEC_SHA=`git rev-parse --short HEAD`
fi
SPEC_BUILD_INFO="${SPEC_DATE}-${SPEC_BUILD_NUMBER}-${SPEC_BRANCH}-${SPEC_SHA}"
if [ -z "${BUILD_COMMIT_USER_NAME}" ]; then
  if [ -n "${GITLAB_USER_NAME}" ]; then
    BUILD_COMMIT_USER_NAME="${GITLAB_USER_NAME}"
  else
    BUILD_COMMIT_USER_NAME="Automated build"
  fi
fi
if [ -z "${BUILD_COMMIT_USER_EMAIL}" ]; then
  if [ -n "${GITLAB_USER_EMAIL}" ]; then
    BUILD_COMMIT_USER_EMAIL="${GITLAB_USER_EMAIL}"
  else
    BUILD_COMMIT_USER_EMAIL="local@localhost"
  fi
fi

composer install --no-dev --no-progress --prefer-dist

rm -rf web/sites/default/files

find . -type d -name '.git' | xargs rm -rf
find . -type f -name '.git' | xargs rm -rf
find . -type f -name '.gitignore' | xargs rm -rf
find . -type f -name '.gitmodules' | xargs rm -rf
mv .gitignore.running .gitignore

DEPLOYMENT_IDENTIFIER_SETTING="\$settings['deployment_identifier'] = '${SPEC_BUILD_INFO}';"
echo "<?php\n\n${DEPLOYMENT_IDENTIFIER_SETTING}" > web/sites/default/settings.build.php
# This will fail a build in case if something wrong is written to the file.
php web/sites/default/settings.build.php

BUILD_INFO_STRING="Build ${SPEC_BUILD_INFO}"

TOP_ITEMS="bin
  config
  scripts/deploy.sh
  vendor
  web
  composer.json
  composer.lock
  .gitignore"

git init
git add -f ${TOP_ITEMS}

git \
  -c user.name="${BUILD_COMMIT_USER_NAME}" \
  -c user.email="${BUILD_COMMIT_USER_EMAIL}" \
  -c gc.auto=0 \
  commit -qm "${BUILD_INFO_STRING}"
git gc --aggressive

tar cJf build-${SPEC_BUILD_INFO}.tar.xz ${TOP_ITEMS} .git

echo build-${SPEC_BUILD_INFO}.tar.xz > ".build-info.file"
echo ${SPEC_BUILD_INFO} > ".build-info.spec"
