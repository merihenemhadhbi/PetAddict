# PetAddict

## Development server

Run `composer update` to download all the dependencies

Edit the `.env` file to your needs (connection wise) and create your database

Run `php bin/console make:migration` to generate migrations

Run `php bin/console doctrine:migrations:migrate` to execute the generated migrations

Run `php bin/console doctrine:fixtures:load` to insert fake data into the database

Run `php bin/console server:run` for an integared symfony dev server. Navigate to `http://localhost:8000/api/doc` to check out our list of api's.



