<?php

namespace GTCrais\LaravelCentinelApi\Classes;


class Zipper
{
	public static function createRegularZip($filePath, $zipPath)
	{
		try {
			shell_exec('zip -j -P ' . escapeshellarg(self::getZipPassword()) . ' ' . escapeshellarg($zipPath) . ' ' . escapeshellarg($filePath));
		} catch (\Throwable $e) {
			if (file_exists($zipPath)) {
				unlink($zipPath);
			}

			return 'The following error has occurred while trying to use the Zip library: ' . $e->getMessage();
		}

		return null;
	}

	public static function create7zip($filePath, $zipPath)
	{
		try {
			shell_exec('7za a -p' . escapeshellarg(self::getZipPassword()) . ' -mem=AES256 -mx=0 -tzip ' . escapeshellarg($zipPath) . ' ' . escapeshellarg($filePath));
		} catch (\Throwable $e) {
			if (file_exists($zipPath)) {
				unlink($zipPath);
			}

			return 'The following error has occurred while trying to use the 7-zip library: ' . $e->getMessage();
		}

		return null;
	}

	public static function createNativeZip($filePath, $zipPath)
	{
		if (version_compare(PHP_VERSION, '7.2', '>=')) {
			try {
				$zip = new \ZipArchive();

				if (method_exists($zip, 'setEncryptionName')) {
					$zip->open($zipPath, \ZipArchive::CREATE);
					$zip->setPassword(self::getZipPassword());
					$zip->addFile($filePath, basename($filePath));
					$zip->setEncryptionName(basename($filePath), \ZipArchive::EM_AES_256);
					$zip->close();
				}
			} catch (\Throwable $e) {
				if (file_exists($zipPath)) {
					unlink($zipPath);
				}

				return 'The following error has occurred while trying to use the native Zip library: ' . $e->getMessage();
			}
		}

		return null;
	}

	protected static function getZipPassword()
	{
		return config('centinelApi.zipPassword');
	}
}