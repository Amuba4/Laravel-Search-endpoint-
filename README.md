# Laravel Search API

## Setup Instructions

1. Clone the repository:
   ```bash
   git clone <repo-url>

2. composer install
3. Configure environment variables in .env.
   Open the .env file and configure your database, Redis, and other necessary settings:

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=<your-database-name>
   DB_USERNAME=<your-database-username>
   DB_PASSWORD=<your-database-password>

   CACHE_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
4. php artisan migrate --seed
5. redis-server
6. php artisan serve

<!-- ----------------------------------------------------------------------------------------------------- -->
## API Endpoints
## GET http://127.0.0.1:8080/items: Search endpoint with sorting, filtering, and pagination.
## GET http://127.0.0.1:8080/items/export-csv: Export items to Excel file.
<!-- -----------------------------------------Info------------------------------------------------------------ -->

## here i am using redis cache and generating the cache key using request data first then at the search time checking that key is already exists or not if exists then fetch data from cache or else hit the db query. i am also keeping log you will find the logs in storage/logs/laravel.logs. you can also get the cache key from logs using tinker check the key is exists or not with below command

## Cache::has('cache_key'); // Replace 'cache_key' with the actual cache key



###############################################################################################################
## Suggestions
Environment Configuration for Redis: Ensure Redis is installed and running on the local machine or in a Docker container if you're using Docker.

Testing the Endpoints: After setting up the server, you should test the API using Postman or cURL to make sure everything works as expected.



