# Laravel Centinel API for Laravel 4.2

This package provides API for downloading the application log file, and dumping and downloading the database. It ships with authentication middleware which protects the API routes.

## Requirements

- PHP 5.6
- make sure your Cache driver is configured and is working
- Windows environment is not supported

## Installation

- add `"gtcrais/laravel-centinel-api": "1.0.*"` to your `composer.json` and run `composer update`
- add `'GTCrais\LaravelCentinelApi\CentinelApiServiceProvider',` to providers array in `/app/config/app.php` and run `composer dump-autoload`
- run `php artisan centinel-api:setup`

## Usage

Laravel Centinel API is designed to work in combination with [Centinel](https://centinel.online) - centralized application management system. 

After going through the installation process you will find your `config.php` file in `/app/config/packages/gtcrais/laravel-centinel-api` directory.
From there, copy `privateKey`, `encryptionKey` and `routePrefix` to Centinel, and you're ready to schedule your application log checks and database backups.

### Config File

- `privateKey` - random string, used for authentication  
- `encryptionKey` - random string, used for additional security layer 
- `routePrefix` - random string, prefixing the API routes  
- `enabledRoutes` - if you only wish part of the API to be exposed, you can disabled either Log or Database routes here 
- `zipPassword` - password used when zipping the database dump
- `database` - database settings and options:
    - `connection` - required. `{default}` to use the default connection, or define connection explicitly
    - `port` - optional. Connection port
    - `unix_socket` - optional. Connection unix socket
    - `dump_binary_path` - optional. Path to dump utility
    - `timeout` - optional. Dump timeout
    - `includeTables` - optional. Dump only tables specified in the array
    - `excludeTables` - optional. Dump all tables except ones specified in the array

To ignore a database setting/option, set it to `null`.

### API Routes

- [POST] `/{routePrefix}/create-log`  
- [POST] `/{routePrefix}/download-log`  
- [POST] `/{routePrefix}/dump-database`  
- [POST] `/{routePrefix}/download-database`

For more details check `/Controllers/CentinelApiController.php` controller.

### Database Backups

[`spatie/db-dumper`](https://github.com/spatie/db-dumper/tree/1.5.1)(v1.5.1) is used to make database dumps. MySQL and PostgreSQL
are supported, and require `mysqldump` and `pg_dump` utilities, respectively.

Centinel API will try to zip and password protect database dumps before sending them to Centinel. It will look for 7-Zip and Zip
libraries to do so. If neither library is available, dumps will be sent without being zipped and password protected.

Run `php artisan centinel-api:check-zip` to see which library is available on your server. Note that Zip encryption alghoritm is much less
secure than that of 7-Zip. Ultimately it is up to you to decide which level of security is satisfactory. You can always opt out of
backing up your database by disabling database backups in Centinel, and additionally, commenting out `DatabaseRoutes` in the
`enabledRoutes` config option.

### Authentication

For details check `/Middleware/AuthorizeCentinelApiRequest.php` middleware.

## License

Laravel Centinel API is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
