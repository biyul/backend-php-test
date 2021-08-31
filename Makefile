all:
	docker-compose build

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

ssh:
	docker exec -it php-bill12345 bash -l

ssh-sudo:
	docker exec -u 0 -it php-bill12345 bash -l

ssh-mysql:
	docker exec -it mysql-bill12345 bash -l
	# Run these manually.
	# docker exec -w /home mysql mysql -u root -p ac_todos < database.sql
	# docker exec -w /home mysql mysql -u root -p ac_todos < fixtures.sql

asknicely:
	docker exec -w /var/www php-bill12345 composer install
