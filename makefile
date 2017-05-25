default:
	docker-compose up -d

db:
	docker exec -i -t db bash

php:
	docker exec -i -t php bash

nginx:
	docker exec -i -t nginx bash