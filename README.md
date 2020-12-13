# comics-collector
## installation
1. Run `docker-compose up -d` 
2. Run `docker-compose exec phpfpm composer install` to install project
3. Run `docker-compose exec phpfpm ./bin/phpunit` to run tests
4. Open `localhost:8080` to see result or `localhost:8080/doc.json` to see documentation
