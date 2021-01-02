#!/usr/bin/env bash
set -eo pipefail

function fixPermissions {
  ${COMPOSE} run --rm integration-exact-online chown -R www-data:www-data ./storage/ ./bootstrap/cache/
}

function ownAllTheThings {
  ${COMPOSE} run --rm integration-exact-online chown -R $(id -u):$(id -g) .
}

function createMicronet {
  if [ "$(docker network ls -q -f name=micronet)" = "" ]; then
    echo ""
    echo "Creating micronet network"
    docker network create micronet
  fi
}

ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Check if the file with environment variables exists, otherwise copy the default file.
if [ ! -f ${ROOT_DIR}/.env ]; then
  if [ ! -f ${ROOT_DIR}/.env.dist ]; then
    echo -e "\033[0;97;101m Unable to locate .env.dist file \033[0m" >&2
    exit 1
  fi

  cp -a ${ROOT_DIR}/.env.dist ${ROOT_DIR}/.env

  echo -e "\033[0;30;47m .env file has been created \033[0m"
fi
export $(cat ${ROOT_DIR}/.env | xargs)

COMPOSE="docker-compose"

if [ $# -gt 0 ]; then
  createMicronet

  # Check if services are running.
  RUNNING=$(${COMPOSE} ps -q)

  # Start services.
  if [ "$1" == "up" ]; then
    ${COMPOSE} up -d

    echo ""
    echo "integration-exact-online server running on http://localhost:${APP_PORT}"

  # Run a composer command on the integration-exact-online service.
  elif [ "$1" == "composer" ]; then
    shift 1
    ${COMPOSE} run --rm integration-exact-online composer "$@"
    ownAllTheThings
    fixPermissions

  # Run an artisan command on the integration-exact-online service.
  elif [ "$1" == "artisan" ]; then
    shift 1
    ${COMPOSE} run --rm integration-exact-online php artisan "$@"
    ownAllTheThings
    fixPermissions

  # Run phpunit tests.
  elif [ "$1" == "test" ]; then
    shift 1
    if [ "$IGNORE_TESTS" == "true" ]; then
      exit 0
    else
      ${COMPOSE} run --rm integration-exact-online ./vendor/bin/phpunit "$@"
    fi

  # Execute a command on a service.
  elif [ "$1" == "integration-exact-online" ]; then
    ${COMPOSE} run --rm "$@"

  # Setup the application.
  elif [ "$1" == "setup" ]; then
    # Start services if not running.
    if [ "${RUNNING}" == "" ]; then
      echo "Starting servers..."
      ${COMPOSE} up -d
    fi

    # Install Composer dependencies.
    echo ""
    echo "Installing Composer dependencies..."
    ownAllTheThings
    # first make sure the cache is writable
    ./mp.sh integration-exact-online chmod 777 /.composer/cache
    ./mp.sh composer install

    # Making directories writable for www-data.
    echo ""
    echo "Making directories writable for www-data..."
    fixPermissions

    # Stop services or restart if they were already running.
    if [ "${RUNNING}" == "" ]; then
      echo ""
      echo "Stopping servers..."
      ${COMPOSE} down
    else
      echo ""
      echo "Restarting servers..."
      ${COMPOSE} restart
    fi

  # Make the application up to date.
  elif [ "$1" == "update" ]; then
    ./mp.sh composer install

  # Upgrade dependencies.
  elif [ "$1" == "upgrade" ]; then
    ./mp.sh composer update

  else
    ${COMPOSE} "$@"
  fi
else
  ${COMPOSE} ps
fi