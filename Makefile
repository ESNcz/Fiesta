all:

install-deps:
	docker run --rm --interactive --tty --volume "\$PWD:/app" composer install

up:
	docker-compose up

upd:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build