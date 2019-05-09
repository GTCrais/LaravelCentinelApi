<?php

namespace GTCrais\LaravelCentinelApi\Console\Commands;

use GTCrais\LaravelCentinelApi\Classes\LogFile;
use GTCrais\LaravelCentinelApi\Classes\Zipper;
use Illuminate\Console\Command;

class CheckZipLibraries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'centinel-api:check-zip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks availability of Zip and 7-Zip libraries';

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
		$this->info("Checking Zip libraries availability...");

		if (!$this->validateZipPassword()) {
			$this->info("ZIP password hasn't been set. Please check the centinelApi.php config file, or run 'php artisan centinel-api:setup' console command.");

			return;
		}

		$filePath = LogFile::getOrCreateFirstExistingLogFilePath();
		$zipPath = storage_path('logs/laravel.zip');

		$errorMessage = Zipper::createNativeZip($filePath, $zipPath);

		if (file_exists($zipPath)) {
			unlink($zipPath);

			if (!$errorMessage) {
				$this->info("You're using PHP version " . PHP_VERSION . " so native Zip encryption is available! Your database dumps will be zipped and encrypted with AES-256.");

				return;
			}
		}

		$this->info($errorMessage ?? "Native Zip encryption is not available.");

		$errorMessage = Zipper::create7zip($filePath, $zipPath);

		if (file_exists($zipPath)) {
			unlink($zipPath);

			if (!$errorMessage) {
				$this->info("7-zip is available! Your database dumps will be zipped using 7-Zip and encrypted with AES-256.");

				return;
			}
		}

		$this->info($errorMessage ?? "7-zip is not available.");

		$errorMessage = Zipper::createRegularZip($filePath, $zipPath);

		if (file_exists($zipPath)) {
			unlink($zipPath);

			if (!$errorMessage) {
				$this->info("Zip is available! Your database dumps will be zipped using Zip library and protected using password from the Centinel API config file.");
				$this->info("It is your responsibility to read up on Zip password protection and decide if this level of security is satisfactory.");

				return;
			}
		}

		$this->info($errorMessage ?? "Zip library is not available.");

		$this->info(
			$errorMessage ?
			"Please fix the errors listed above and run the 'php artisan centinel-api:check-zip' command again." :
			"Your database dumps will be sent to Centinel without being zipped and password protected beforehand."
		);

		return;
    }

	protected function validateZipPassword()
	{
		return config('centinelApi.zipPassword') ? true : false;
    }
}
