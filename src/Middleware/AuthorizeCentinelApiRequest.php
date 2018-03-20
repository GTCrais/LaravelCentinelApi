<?php

namespace GTCrais\LaravelCentinelApi\Middleware;

use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthorizeCentinelApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function filter()
    {
		if (!$this->authorize()) {
			return \Response::make('Unauthorized', 401);
		}
    }

	protected function authorize()
	{
		$enabledRoutes = \Config::get('laravel-centinel-api::enabledRoutes');
		$encryptedString = \Input::get('string');
		$hash = \Input::get('hash');

		$routeName = \Route::currentRouteName();

		if (!$encryptedString || !$hash) {
			\Log::error('Laravel Centinel API: \'string\' or \'hash\' fields not set');

			return false;
		}

		$routeType = '';

		if (Str::startsWith($routeName, 'centinelApiLog')) {
			$routeType = 'LogRoutes';
		} else if (Str::startsWith($routeName, 'centinelApiDatabase')) {
			$routeType = 'DatabaseRoutes';
		}

		if (!in_array($routeType, $enabledRoutes)) {
			\Log::error('Laravel Centinel API: Route ' . $routeName . ' disabled');

			return false;
		}

		if (hash_hmac('sha256', $encryptedString, \Config::get('laravel-centinel-api::privateKey')) != $hash) {
			\Log::error('Laravel Centinel API: Hash doesn\'t match');

			return false;
		}

		try {
			$payload = json_decode(base64_decode($encryptedString), true);
			$value = $payload['value'];
			$iv = base64_decode($payload['iv']);
			$decryptedString = openssl_decrypt($value, 'AES-256-CBC', \Config::get('laravel-centinel-api::encryptionKey'), 0, $iv);
		} catch (\Exception $e) {
			\Log::error('Laravel Centinel API: Error while decrypting string - ' . $e->getMessage());

			return false;
		}

		$decryptedSegments = explode('|', $decryptedString);

		if (count($decryptedSegments) != 3) {
			\Log::error('Laravel Centinel API: Invalid decrypted string');

			return false;
		}

		$dateTime = $decryptedSegments[1];

		if (!$dateTime) {
			\Log::error('Laravel Centinel API: DateTime not present in the decrypted string');

			return false;
		}

		try {
			Carbon::parse($dateTime);
		} catch (\Exception $e) {
			\Log::error('Laravel Centinel API: Received DateTime invalid');

			return false;
		}

		$receivedDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, 'UTC');
		$now = Carbon::now('UTC');
		$diffInSeconds = $receivedDateTime->diffInSeconds($now);

		if ($diffInSeconds > 45) {
			\Log::error('Laravel Centinel API: request time mismatch (1)');

			return false;
		}

		$cacheKey = $routeName . 'AccessTime';
		$lastRouteAccessTime = \Cache::get($cacheKey);

		if ($lastRouteAccessTime) {
			if ($lastRouteAccessTime == $receivedDateTime) {
				\Log::error(
					'Laravel Centinel API: Access to route ' . $routeName . ' attempted using a non-unique \'dateTime\' parameter. ' .
					'While this is likely not a security breach, changing your application private and encryption keys is recommended.'
				);

				return false;
			}

			$lastRouteAccessTime = Carbon::createFromFormat('Y-m-d H:i:s', $lastRouteAccessTime, 'UTC');

			if ($lastRouteAccessTime->diffInSeconds($receivedDateTime) < 90) {
				\Log::error('Laravel Centinel API: Too many API calls for route ' . $routeName);

				return false;
			}

			if ($receivedDateTime < $lastRouteAccessTime) {
				\Log::error('Laravel Centinel API: request time mismatch (2)');

				return false;
			}
		}

		\Cache::put($cacheKey, $dateTime, 10);

		return true;
	}
}
