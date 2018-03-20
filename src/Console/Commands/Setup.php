<?php

namespace GTCrais\LaravelCentinelApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'centinel-api:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes config and generates private key and routes prefix';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
		// Copy config
		$dirs = [
			app_path('config/packages'),
			app_path('config/packages/gtcrais'),
			app_path('config/packages/gtcrais/laravel-centinel-api')
		];

		foreach ($dirs as $dir) {
			if (!is_dir($dir)) {
				mkdir($dir);
			}
		}

		\File::copy(__DIR__ . '/../../config/emptyConfig.php', app_path('config/packages/gtcrais/laravel-centinel-api/config.php'));

		// generate private key
		$privateKey = Str::random(32);

		file_put_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'), preg_replace(
			"/'privateKey' => ''/",
			"'privateKey' => '" . $privateKey . "'",
			file_get_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'))
		));

		// generate encryption key
		$encryptionKey = Str::random(32);

		file_put_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'), preg_replace(
			"/'encryptionKey' => ''/",
			"'encryptionKey' => '" . $encryptionKey . "'",
			file_get_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'))
		));

		// generate route prefix
		$routePrefix = Str::random(32);

		file_put_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'), preg_replace(
			"/'routePrefix' => ''/",
			"'routePrefix' => '" . $routePrefix . "'",
			file_get_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'))
		));

		// generate zip password
		$zipPassword = Str::random(32);

		file_put_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'), preg_replace(
			"/'zipPassword' => ''/",
			"'zipPassword' => '" . $zipPassword . "'",
			file_get_contents(app_path('config/packages/gtcrais/laravel-centinel-api/config.php'))
		));

		$this->info("Private key, encryption key, routes prefix and zip password successfully generated");

		$this->call('centinel-api:check-zip');
    }
}
