## WordPress Posts API

Built an API GET Route using Laravel.

Created App\Http\Controllers\PostController.php file and added index method that returns JsonResponse data type
- Created request validation class for the query parameters rules
- Configured connection to WordPress database.
- Used Laravel methods for conditional querying to maintain fluent query building.
- Cache the results for each requested filtered combination.
- Added 'No posts available for that criteria.' response for results not found.
- Created pagination using offset and limit clause.

Implemented Sanctum a token-based authentication to access data; Created login and register endpoints.

API returns json response for all exceptions specifically 400 and 401 Unautorized status code

