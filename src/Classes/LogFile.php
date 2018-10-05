<?php

namespace GTCrais\LaravelCentinelApi\Classes;


class LogFile
{
	public static function mergeLogs()
	{
		$data = [
			'tempLogFile' => null,
			'logFiles' => []
		];
		$platform = Platform::getPlatform();
		$logFiles = scandir(storage_path('logs'));

		$tempLog = $platform . '_temp_log.log';
		$tempLogPath = storage_path('logs/' . $tempLog);

		file_put_contents($tempLogPath, '');

		$data['tempLogFile'] = $tempLogPath;

		foreach ($logFiles as $logFile) {
			if (self::isLogFile($platform, $logFile)) {
				$logFilePath = storage_path('logs/' . $logFile);
				$content = file_get_contents($logFilePath);

				if (trim($content)) {
					file_put_contents($tempLogPath, file_get_contents($tempLogPath) . $content);
				}

				$data['logFiles'][] = $logFilePath;
			}
		}

		return $data;
	}

	public static function getOrCreateFirstExistingLogFilePath()
	{
		$filename = self::getOrCreateFirstExistingLogFilename();

		return storage_path('logs/' . $filename);
	}

	public static function getOrCreateFirstExistingLogFilename()
	{
		$filename = self::getFirstExistingLogFilename();

		if (!$filename) {
			$filename = Platform::getPlatform() . '.log';
			$filePath = storage_path('logs/' . $filename);

			file_put_contents($filePath, '');
		}

		return $filename;
	}

	protected static function getFirstExistingLogFilename()
	{
		$platform = Platform::getPlatform();
		$logFiles = scandir(storage_path('logs'));

		foreach ($logFiles as $logFile) {
			if (self::isLogFile($platform, $logFile)) {
				return $logFile;
			}
		}

		return null;
	}

	protected static function isLogFile($platform, $filename)
	{
		return (is_file(storage_path('logs/' . $filename)) && self::matchesLogfilePattern($platform, $filename));
	}

	protected static function matchesLogfilePattern($platform, $filename)
	{
		return preg_match('/^' . $platform . '(\-[0-9]{4}\-[0-9]{2}\-[0-9]{2})?\.log$/', $filename);
	}
}