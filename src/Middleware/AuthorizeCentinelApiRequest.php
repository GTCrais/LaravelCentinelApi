<?php

namespace GTCrais\LaravelCentinelApi\Middleware;

use Carbon\Carbon;
use Closure;
use GTCrais\LaravelCentinelApi\Classes\Platform;
use GTCrais\LaravelCentinelApi\Lumen\RequestManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthorizeCentinelApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		if ($this->authorize($request)) {
			return $next($request);
		}

		return response("Unauthorized", 401);
    }

	protected function authorize(Request $request)
	{
		$enabledRoutes = config('centinelApi.enabledRoutes');
		$encryptedString = $request->get('string');
		$hash = $request->get('hash');

		if (Platform::getPlatform() == 'laravel') {
			$routeName = $request->route()->getName();
		} else {
			$routeName = RequestManager::getRouteName($request);
		}

		if (!$encryptedString || !$hash) {
			Log::error('Laravel Centinel API: \'string\' or \'hash\' fields not set');

			return false;
		}

		$routeType = '';

		if (Str::startsWith($routeName, 'centinelApiLog')) {
			$routeType = 'LogRoutes';
		} else if (Str::startsWith($routeName, 'centinelApiDatabase')) {
			$routeType = 'DatabaseRoutes';
		}

		if (!in_array($routeType, $enabledRoutes)) {
			Log::error('Laravel Centinel API: Route ' . $routeName . ' disabled');

			return false;
		}

		if (hash_hmac('sha256', $encryptedString, config('centinelApi.privateKey')) != $hash) {
			Log::error('Laravel Centinel API: Hash doesn\'t match');

			return false;
		}

		try {
			$payload = json_decode(base64_decode($encryptedString), true);
			$value = $payload['value'];
			$iv = base64_decode($payload['iv']);
			$decryptedString = openssl_decrypt($value, 'AES-256-CBC', config('centinelApi.encryptionKey'), 0, $iv);
		} catch (\Exception $e) {
			Log::error('Laravel Centinel API: Error while decrypting string - ' . $e->getMessage());

			return false;
		}

		$decryptedSegments = explode('|', $decryptedString);

		if (count($decryptedSegments) != 3) {
			Log::error('Laravel Centinel API: Invalid decrypted string');

			return false;
		}

		$dateTime = $decryptedSegments[1];

		if (!$dateTime) {
			Log::error('Laravel Centinel API: DateTime not present in the decrypted string');

			return false;
		}

		try {
			Carbon::parse($dateTime);
		} catch (\Exception $e) {
			Log::error('Laravel Centinel API: Received DateTime invalid');

			return false;
		}

		$receivedDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, 'UTC');
		$now = Carbon::now('UTC');
		$diffInSeconds = $receivedDateTime->diffInSeconds($now);

		if ($diffInSeconds > 45) {
			Log::error('Laravel Centinel API: request time mismatch (1)');

			return false;
		}

		$cacheKey = $routeName . 'AccessTime';
		$lastRouteAccessTime = \Cache::get($cacheKey);

		if ($lastRouteAccessTime) {
			if ($lastRouteAccessTime == $receivedDateTime) {
				Log::error(
					'Laravel Centinel API: Access to route ' . $routeName . ' attempted using a non-unique \'dateTime\' parameter. ' .
					'While this is likely not a security breach, changing your application private and encryption keys is recommended.'
				);

				return false;
			}

			$lastRouteAccessTime = Carbon::createFromFormat('Y-m-d H:i:s', $lastRouteAccessTime, 'UTC');

			if ($lastRouteAccessTime->diffInSeconds($receivedDateTime) < 90) {
				Log::error('Laravel Centinel API: Too many API calls for route ' . $routeName);

				return false;
			}

			if ($receivedDateTime < $lastRouteAccessTime) {
				Log::error('Laravel Centinel API: request time mismatch (2)');

				return false;
			}
		}

		Cache::put($cacheKey, $dateTime, 10);

		return true;
	}
}
