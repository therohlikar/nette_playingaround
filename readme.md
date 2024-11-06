## nette_playingaround
Is a short testing script for RestAPI.

### FRAMEWORKS AND TECHNOLOGIES
- [Nette Framework](https://nette.org/en/)
- [MySQL](https://hub.docker.com/_/mysql) Database
- [phpMyAdmin](https://hub.docker.com/_/phpmyadmin) GUI db
- [Contributte/Apitte](https://contributte.org/packages/contributte/apitte/)
- [Doctrine/ORM](https://www.doctrine-project.org) as DB manipulator
- [SwaggerUI](http://swagger.io) (with Contributte/Apitte/OpenAPI)
- [Docker](http://docker.com) to run it all
- [Postman](https://marketplace.visualstudio.com/items?itemName=Postman.postman-for-vscode) extension in Visual Studio Code

### START API
1. Simply have docker installed and use update command in the project

    docker compose up

2. Open up console on the server and create database tables using the ORM scheme using

    php bin/console orm:schema-tool:create
    
3. API runs on

    localhost:8080/api/base/

4. SwaggerUI runs on (WIP)

    localhost:8080/docs/swagger.html

### DEVELOPMENT

I used several times Google and ChatGPT to make this all happen. Beginning was "easy part", to connect the database via Doctrine/ORM and API was probably the hardest and I failed lost of times. 
I learnt a lot from this small project and it gave me so much motivation to do a few projects of my own using SwiftUI on the iOS development. 

Thank you for this task. 
