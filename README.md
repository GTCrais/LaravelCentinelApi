# Laravel Centinel API for Laravel 5

This package provides API for downloading the application log file, and dumping and downloading the database. It ships with authentication middleware which protects the API routes.

Centinel API is designed to work in combination with [**Centinel**](https://centinel.online) - centralized application management system. 

> ##### For **Laravel 4.2** use [Centinel API v1.2](https://github.com/GTCrais/LaravelCentinelApi/tree/v1.2)

## Requirements

- PHP 5.6+
- Laravel 5.1+ (Laravel 5.0 is not supported due to incompatibility with Symfony components used in it)
- make sure your Cache driver is configured and is working
- Windows environment is not supported

## Installation: Laravel

- add `"gtcrais/laravel-centinel-api": "2.3.*"` to your `composer.json` and run `composer update`
- for Laravel `<=5.4` add `GTCrais\LaravelCentinelApi\LaravelCentinelApiServiceProvider::class,` to providers array in `/app/config/app.php` and run `composer dump-autoload`
- run `php artisan centinel-api:setup`

## Installation: Lumen

- add `"gtcrais/laravel-centinel-api": "2.3.*"` to your `composer.json` and run `composer update`
- uncomment `$app->withFacades();` in `/bootstrap/app.php`
- add `$app->configure('centinelApi');` to `/bootstrap/app.php`. This will load Centinel API configuration
- add `$app->register(GTCrais\LaravelCentinelApi\LumenCentinelApiServiceProvider::class);` to `/bootstrap/app.php` **below** config file registration
- run `php artisan centinel-api:setup`

## Additional Installation Notes

### Laravel 5.1

- add `routePrefix` to `VerifyCsrfToken` middleware's `$except` array. Example:  
```php
protected $except = [
	'Hts71OwsRTwjXDb5Kdp5zk5l6KsvEz7Q/*' // Note the /* at the end
];
```

### Lumen

Centinel API will first look for database connection configuration in `/config/database.php`. If it's not available there,
it will look in `.env` file. Make sure one of these options is available if you wish to use the database dump functionality.

## Usage

**It's highly recommended to use this plugin only on websites that use HTTPS!**

After going through the installation process you will find `centinelApi.php` configuration file in `/config` directory.
From there, copy `privateKey`, `encryptionKey` and `routePrefix` to [**Centinel**](https://centinel.online), and you're ready to schedule your application log checks and database backups.

### Config File

- `privateKey` - random string, used for authentication  
- `encryptionKey` - random string, used for additional security layer 
- `routePrefix` - random string, prefixing the API routes  
- `enabledRoutes` - if you only wish to expose a part of the API, you can disable either Log or Database routes here 
- `disableTimeBasedAuthorization` - set to `true` in case of your server's and Centinel's datetime being out of sync which results in `Request time mismatch` or `Too many API calls` error
- `zipPassword` - password used when zipping the database dump. **Make sure to save the Zip Password so you can restore your database in case of server crash**
- `database` - database settings and options

All of the database options except for `connection` are optional.

Some of the database options are not available for Laravel/Lumen 5.1, and on PHP 5 (regardless of the framework version).
For more details check [Spatie DB Dumper v1.5.1](https://github.com/spatie/db-dumper/tree/1.5.1)

For details on how to use the options check the installed version of the package.
For Laravel/Lumen 5.2+ on PHP 7 that will be [Spatie DB Dumper v2.9](https://github.com/spatie/db-dumper/tree/2.9.0)

To ignore a database setting/option, set it to `null`.

### API Routes

- [POST] `/{routePrefix}/create-log`  
- [POST] `/{routePrefix}/download-log`  
- [POST] `/{routePrefix}/dump-database`  
- [POST] `/{routePrefix}/download-database`

For more details check `/Controllers/CentinelApiController.php` controller.

### Database Backups

[Spatie DB Dumper](https://github.com/spatie/db-dumper) is used to make database dumps. **MySQL** and **PostgreSQL**
are supported, and require `mysqldump` and `pg_dump` utilities, respectively.

Additionally, on Laravel/Lumen **5.2+** applications running on **PHP 7**, **Sqlite** and **MongoDB** are supported, and require
`sqlite3` and `mongodump` utilities, respectively.

Centinel API will try to zip and password protect database dumps before sending them to Centinel. In case you're using PHP 7.2+, it will use 
PHP's native `ZipArchive` class to zip and encrypt the database. Otherwise, it will look for 7-Zip and Zip libraries to do so. If no option 
is available, dumps will be sent without being zipped and password protected.

Run `php artisan centinel-api:check-zip` to see which library is available on your server. Note that Zip encryption algorithm is much less
secure than that of ZipArchive and 7-Zip. Ultimately it is up to you to decide which level of security is satisfactory. You can always opt out of
backing up your database by disabling database backups in Centinel, and additionally, commenting out `DatabaseRoutes` in the
`enabledRoutes` config option.

### Authentication

For details check `/Middleware/AuthorizeCentinelApiRequest.php` middleware.

## License

Laravel Centinel API is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
