container=codesort-apache

up:
	docker-compose up -d

down:
	docker-compose rm -vsf
	docker-compose down -v --remove-orphans

build:
	docker-compose rm -vsf
	docker-compose down -v --remove-orphans
	docker-compose build
	docker-compose up -d

rector:
	docker exec -ti ${container} composer install
	docker exec -ti ${container} composer update
	docker exec -ti ${container} php vendor/bin/rector process --dry-run --debug

rector-update:
	docker exec -ti ${container} composer install
	docker exec -ti ${container} composer update
	docker exec -ti ${container} php vendor/bin/rector process --debug

jumpin:
	docker exec -ti ${container} bash

logs:
	docker-compose logs -f
