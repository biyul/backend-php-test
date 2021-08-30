all:
	docker-compose build

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

ssh:
	docker exec -it php bash -l

ssh-sudo:
	docker exec -u 0 -it php bash -l

ssh-mysql:
	docker exec -it mysql bash -l

asknicely:
	# Security risk, but for demo purposes, eh.
	docker exec -w /var/www php composer install
	docker exec -w /home mysql mysql -u root -pletmein ac_todos < resources/database.sql
	docker exec -w /home mysql mysql -u root -pletmein ac_todos < resources/fixtures.sql
    # ;date.timezone =
