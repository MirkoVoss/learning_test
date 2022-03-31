start:
	./vendor/bin/sail up -d

migrate:
	./vendor/bin/sail artisan migrate

seeder:
	./vendor/bin/sail php artisan db:seed

install:
	composer install
	./vendor/bin/sail up -d
	./vendor/bin/sail artisan migrate
	./vendor/bin/sail php artisan db:seed
