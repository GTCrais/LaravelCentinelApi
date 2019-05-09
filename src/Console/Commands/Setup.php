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
	protected $signature = 'centinel-api:setup';

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
    public function handle()
    {
		// Create 'config' directory if it doesn't exist
		if (!is_dir(base_path('/config'))) {
			mkdir(base_path('/config'));
		}

		// Copy config
		copy(__DIR__ . '/../../config/centinelApi.php', base_path('/config/centinelApi.php'));

		// Generate private key
		$privateKey = Str::random(32);

		file_put_contents(base_path('/config/centinelApi.php'), preg_replace(
			"/'privateKey' => ''/",
			"'privateKey' => '" . $privateKey . "'",
			file_get_contents(base_path('/config/centinelApi.php'))
		));

		// Generate encryption key
		$encryptionKey = Str::random(32);

		file_put_contents(base_path('/config/centinelApi.php'), preg_replace(
			"/'encryptionKey' => ''/",
			"'encryptionKey' => '" . $encryptionKey . "'",
			file_get_contents(base_path('/config/centinelApi.php'))
		));

		// Generate route prefix
		$routePrefix = Str::random(32);

		file_put_contents(base_path('/config/centinelApi.php'), preg_replace(
			"/'routePrefix' => ''/",
			"'routePrefix' => '" . $routePrefix . "'",
			file_get_contents(base_path('/config/centinelApi.php'))
		));

		// Generate zip password
		$zipPassword = Str::random(32);

		file_put_contents(base_path('/config/centinelApi.php'), preg_replace(
			"/'zipPassword' => ''/",
			"'zipPassword' => '" . $zipPassword . "'",
			file_get_contents(base_path('/config/centinelApi.php'))
		));

		// Make configuration immediately available
		// for the check-zip console command
		config(['centinelApi.privateKey' => $privateKey]);
		config(['centinelApi.encryptionKey' => $encryptionKey]);
		config(['centinelApi.routePrefix' => $routePrefix]);
		config(['centinelApi.zipPassword' => $zipPassword]);

		$this->info("Private key, encryption key, routes prefix and zip password successfully generated");

		$this->call('centinel-api:check-zip');
    }
}
