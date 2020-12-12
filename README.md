# comics-collector
## installation
1. Run `docker-compose up -d` 
2. Run `docker-compose exec phpfpm composer install` to install project
3. Run `docker-compose exec phpfpm ./bin/phpunit` to run tests
