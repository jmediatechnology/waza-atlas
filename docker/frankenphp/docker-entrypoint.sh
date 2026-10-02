#!/bin/sh
set -e

# Only prepare the app when starting the server or running PHP; `docker compose run app sh` stays untouched.
if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

	# Dev: vendor/ comes from your working copy through the bind mount. Install on every start:
	# it is a no-op when everything is in sync, adds packages that composer.json gained since the
	# last install, and stops with Composer's own explanation when composer.lock is out of date
	# or was resolved for another PHP version.
	if [ "$APP_ENV" != 'prod' ]; then
		if ! composer install --prefer-dist --no-progress --no-interaction; then
			echo >&2
			echo 'Dependencies could not be installed (see above). If composer.lock is out of date, run:' >&2
			echo '  docker compose run --rm app composer update' >&2
			exit 1
		fi
	fi

	if [ -z "$APP_SECRET" ] && [ "$APP_ENV" = 'prod' ]; then
		echo 'APP_SECRET is not set. Generate one with: openssl rand -hex 32' >&2
		exit 1
	fi

	mkdir -p var/cache var/log "${MOTION_DIR:-var/motions}"
	case "${DATABASE_URL:-}" in
		sqlite:///*) mkdir -p "$(dirname "${DATABASE_URL#sqlite:///}")" ;;
	esac

	if [ "${RUN_MIGRATIONS:-1}" = '1' ]; then
		php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --all-or-nothing
	fi

	# Idempotent: updates the 100 techniques in place and only adds bundled motions that are missing.
	if [ "${SEED_CATALOGUE:-1}" = '1' ]; then
		php bin/console app:seed --no-interaction
	fi

	# Dev runs as root with a bind mount; make var/ writable for both root and www-data.
	if [ "$(id -u)" = '0' ]; then
		setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var "${MOTION_DIR:-var/motions}" 2>/dev/null || true
		setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var "${MOTION_DIR:-var/motions}" 2>/dev/null || true
	fi

	echo 'Waza Atlas is ready.'
fi

exec docker-php-entrypoint "$@"
