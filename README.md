


## Requirements

- PHP > 8.2+
- Laravel 11
- MySQL 8.2+ / 
- Composer



## Installation

1. Clone the repository:
   ```
   git clone 
   ```

2. Install dependencies:
   ```
   composer install
 
   ```

3. Set up environment:
   ```
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure database in `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=contoh
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Run migrations:
   ```
   php artisan migrate
    php artisan migrate:fresh --seed
   ```

6. Create storage link for file uploads
   ```
   php artisan storage:link
   ```

   ```
7. Activate Role & Permission
   ```
   
   php artisan shield:install

   ```

8. Start the development server:
   ```
   php artisan serve
   ```