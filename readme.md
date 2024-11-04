To startup Docker, 'docker compose up'

To create schema, used ORM schema tool: 'php bin/console orm:schema-tool:create'

localhost:8080/api/base/

products -> GET product list
products -> POST create product item 

products/{id} -> PUT updates price of specific product item
products/delete/{id} -> DELETE deleted specific product item with its children (meaning the items in the price history)

history/{id} -> GET price history of specific product item


For testing purposes I used Postman in Visual Studio Code
- framework Nette with Apitte for API, mySQL for database and PHPmyAdmin for Db GUI
- ORM Doctrine to manipulate with DB

Actually happy with the results, tho it definitely can be improved. 