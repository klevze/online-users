# Laravel plugin for displaying online users on a webpage

[![Latest Version on Packagist](https://img.shields.io/packagist/v/klevze/online-users.svg?style=flat-square)](https://packagist.org/packages/klevze/online-users)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/klevze/online-users/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/klevze/online-users/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/klevze/online-users/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/klevze/online-users/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/klevze/online-users.svg?style=flat-square)](https://packagist.org/packages/klevze/online-users)

"Online Users" is a Laravel package designed to effortlessly track and display the real-time count of users currently active on your web application. With seamless integration, this package provides a quick and reliable solution for monitoring and presenting the dynamic online user presence, enhancing the overall user experience on your Laravel-powered website.

## Installation

You can install the package via composer:

```bash
composer require klevze/online-users
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="online-users-migrations"
php artisan migrate
```

## Integration with Laravel's Kernel Middleware

To enable the "Online Users" middleware in your Laravel application, follow these steps:

1. Open the `app/Http/Kernel.php` file in your Laravel project.

2. Locate the `$middlewareGroups` property, specifically within the `web` middleware group.

3. Add the following line to the `web` middleware group:

   ```php
   protected $middlewareGroups = [
       'web' => [
           // ... other middleware entries
           \Klevze\OnlineUsers\Middleware\TrackUserActivity::class,
           // ... other middleware entries
       ],
       // ... other middleware groups
   ];
    ```

## Cleanup Inactive Users Console Command

The "CleanupInactiveUsers" console command provided by the "Online Users" package allows you to remove inactive users from the `user_activities` table. Follow the steps below to integrate and schedule the cleanup task.

1. Open the `app/Console/Kernel.php` file in your Laravel project.

2. Locate the `schedule` method and add the following entry to schedule the `cleanup:inactive-users` command every five minutes:

    ```php
    protected function schedule(Schedule $schedule)
    {
        // ... other scheduled tasks

        $schedule->command('cleanup:inactive-users')->everyFiveMinutes();

        // ... other scheduled tasks
    }
    ```

3. Save the changes to the `Kernel.php` file.

Now, the "CleanupInactiveUsers" console command will run every five minutes, cleaning up inactive users from the `user_activities` table.

## Usage

Once package is installed, you can use the `OnlineUsers` class to get the number of active users. For example, the following code will get the number of active users:

```php
$activeUsers = OnlineUsers::getActiveUsers();
```

Or you can use it directly in blade view:

```html
<p>Currently browsing: {{ OnlineUsers::getActiveUsers() ?? 0 }}</p>
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
