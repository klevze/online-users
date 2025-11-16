# Laravel plugin for displaying online users on a webpage

<!-- Project cover image -->
![Online Users](assets/card.png)

You can also set these variables in your `.env` file (example below).

[![Latest Version on Packagist](https://img.shields.io/packagist/v/klevze/online-users.svg?style=flat-square)](https://packagist.org/packages/klevze/online-users)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/klevze/online-users/run-tests-laravel-12.yml?branch=main&label=tests&style=flat-square)](https://github.com/klevze/online-users/actions?query=workflow%3Arun-tests-laravel-12+branch%3Amain)

<!-- Per-Laravel-version badges (each runs a workflow file) -->
[![Laravel 12](https://img.shields.io/github/actions/workflow/status/klevze/online-users/run-tests-laravel-12.yml?branch=main&label=Laravel%2012&style=flat-square)](https://github.com/klevze/online-users/actions?query=workflow%3Arun-tests-laravel-12+branch%3Amain)
[![Laravel 12](https://img.shields.io/github/actions/workflow/status/klevze/online-users/run-tests-laravel-12.yml?branch=main&label=Laravel%2012&style=flat-square)](https://github.com/klevze/online-users/actions?query=workflow%3Arun-tests-laravel-12+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/klevze/online-users/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/klevze/online-users/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/klevze/online-users.svg?style=flat-square)](https://packagist.org/packages/klevze/online-users)

> Note: This repository currently runs CI only for Laravel 12. The older per-version workflow files for Laravel 10 and 11 were removed to simplify the workflow matrix and remove duplicate CI runs. If you need per-Laravel-version badges again, recreate the per-version workflows or restore `run-tests-laravel-10.yml` and `run-tests-laravel-11.yml`.

"Online Users" is a Laravel package designed to effortlessly track and display the real-time count of users currently active on your web application. With seamless integration, this package provides a quick and reliable solution for monitoring and presenting the dynamic online user presence, enhancing the overall user experience on your Laravel-powered website.

## Installation

You can install the package via composer:

```bash
composer require klevze/online-users
```

You can publish and run the migrations and configuration with:

```bash
php artisan vendor:publish --tag="online-users-migrations"
php artisan vendor:publish --tag="online-users-config"
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
       ];

   ```

<!-- Demo moved to the bottom of README -->
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

You can customize the activity threshold and tracking strategy by publishing and editing the `config/online-users.php` file.

Compatibility: This package supports Laravel 10, 11 and 12 and requires PHP 8.1 or newer. If you are using Laravel 12, ensure your application meets Laravel 12's PHP requirements.

### IP anonymization (privacy)

For privacy-conscious deployments you can anonymize user IP addresses before saving them to the database. To enable anonymization:

1. Publish the config: `php artisan vendor:publish --tag="online-users-config"`.
2. Set `anonymize_ip` to `true` and set `ip_salt` to a large secret string in `config/online-users.php` or via environment variables.

Or set in your `.env`:

```env
ONLINE_USERS_ANONYMIZE_IP=true
ONLINE_USERS_IP_SALT=your-long-random-salt
```

When enabled, the package will store a sha256 hash of the IP address concatenated with the salt to help protect user privacy while still allowing approximate uniqueness checks.

### Upgrading and migrating existing installs

To add the `user_ip_hash` column (if you installed the package before hashed IP support was added) you should publish and run the new migration:

```bash
php artisan vendor:publish --tag="online-users-migrations"
php artisan migrate
```


After running the migration you can populate the newly created hash column using this command (requires a valid `ONLINE_USERS_IP_SALT`):

```bash
php artisan online-users:populate-ip-hash
```

The `online-users:populate-ip-hash` command will compute the hash for rows that don't yet have one. It uses your configured `ONLINE_USERS_IP_SALT` and `ONLINE_USERS_HASH_ALGORITHM`.

If you want to keep raw IPs along with hashed IPs for additional analysis, set `ONLINE_USERS_STORE_RAW_IP=true`.

### Dropping raw IPs (optional)

If you decide to stop storing raw IPs after you are certain `user_ip_hash` has been populated, run the migration that removes the raw column:

```bash
php artisan vendor:publish --tag="online-users-migrations"
php artisan migrate
```

Before dropping raw IPs, please ensure you have a backup of your database. Recommended workflow:

1. Make a backup of your database.
2. Run `php artisan migrate` to add `user_ip_hash` (if not already present).
3. Run `php artisan online-users:populate-ip-hash` to fill the `user_ip_hash` column.
4. Verify data and application functionality.
5. If all good, run `php artisan migrate` which will pick up the `drop` migration to remove the `user_ip` column.


**Note:** hashing is irreversible; if you need the raw IP for logging, do not enable this option.

## Demo

You can see a working demo at these sites:

- [The Wallpapers](https://thewallpapers.net)
- [Joke Station](https://jokestation.org)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Automated Code Style Fixes

This project runs `php-cs-fixer` in CI. To allow the code-style workflow to auto-commit fixes back to pull requests, add a repository secret named `STYLE_BOT_TOKEN` with a personal access token (PAT) that has `repo` permissions. When set, the workflow will commit fixes directly to the PR branch.

If you do not set `STYLE_BOT_TOKEN`, the workflow will attempt to use the default `GITHUB_TOKEN` where permitted (works for branches in the same repository but not for PRs from forks).

When the workflow runs on a PR it will now create a new pull request with automatic style fixes (rather than pushing changes directly to the originating branch). The original PR will receive a comment linking to the fixes PR.
