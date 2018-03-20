<?php

namespace GTCrais\LaravelCentinelApi\Controllers;

use GTCrais\LaravelCentinelApi\Classes\Database;
use GTCrais\LaravelCentinelApi\Classes\Zipper;

class CentinelApiController extends \BaseController
{
	public function createLog()
	{
		$data = [
			'success' => false,
			'filesize' => 0,
			'filePath' => null,
			'message' => null
		];

		$filePath = storage_path('logs/laravel.log');

		try {
		    if (file_exists($filePath)) {
				$logContents = file_get_contents($filePath);

				if (!trim($logContents)) {
					$data['success'] = true;

					return \Response::json($data);
				}

				$filesize = filesize($filePath);
				$foldersData = $this->createLogFolders();
				$newFilePath = 'logs/y' . $foldersData['year'] . '/m' . $foldersData['month'] . '/' . (date('Y-m-d__H_i_s')) . '.log';

				file_put_contents(storage_path($newFilePath), $logContents);
				file_put_contents($filePath, '');

				$data['success'] = true;
				$data['filesize'] = $filesize;
				$data['filePath'] = $newFilePath;
			} else {
				$data['message'] = "Log file doesn't exist";
			}
		} catch (\Exception $e) {
			\Log::error($e);
			$data['message'] = "Error while creating the log file: " . $e->getMessage();
		}

		return \Response::json($data);
	}

	public function downloadLog()
	{
		$filePath = \Request::get('filePath');
		$fullFilePath = storage_path($filePath);

		if (!$filePath || !file_exists($fullFilePath)) {
			return \Response::make("Incorrect dataset.", 422);
		}

		return \Response::download($fullFilePath);
	}

	public function dumpDatabase()
	{
		$data = [
			'success' => false,
			'filesize' => 0,
			'filePath' => null,
			'message' => null
		];

		try {
			$this->createDbDumpFolder();
			$this->emptyDbDumpFolder();

			$filename = Database::dump();
			$fullPath = Database::getDumpPath($filename);
			$zipFilename = $this->zipDatabase($fullPath);
			$fullPath = $zipFilename ? Database::getDumpPath($zipFilename) : $fullPath;
			$filesize = filesize($fullPath);

			$data['success'] = true;
			$data['filesize'] = $filesize;
			$data['filePath'] = $zipFilename;
		} catch (\Exception $e) {
			\Log::error($e);
			$data['message'] = "Error while dumping database: " . $e->getMessage();
		}

		return \Response::json($data);
	}

	public function downloadDatabase()
	{
		$filename = \Request::get('filePath');
		$fullFilePath = Database::getDumpPath($filename);

		if (!$filename || !file_exists($fullFilePath)) {
			return \Response::make("Incorrect dataset.", 422);
		}

		$deleteFile = \Request::get('deleteFile') ? true : false;

		return \Response::download($fullFilePath)->deleteFileAfterSend($deleteFile);
	}

	protected function zipDatabase($filePath)
	{
		$zipFilename = 'databasedump.zip';
		$zipPath = Database::getDumpPath($zipFilename);

		// Try 7-zip
		Zipper::create7zip($filePath, $zipPath);

		// Try regular zip
		if (!file_exists($zipPath)) {
			Zipper::createRegularZip($filePath, $zipPath);
		}

		// If Zip file was created successfully
		// return Zip filename
		if (file_exists($zipPath)) {
			if (file_exists($filePath)) {
				unlink($filePath);
			}

			return $zipFilename;
		}

		return null;
	}

	protected function createLogFolders()
	{
		$year = date("Y");
		$month = date("m");

		$folders = $this->getLogFolderPaths($year, $month);

		foreach ($folders as $folder) {
			if (!is_dir($folder)) {
				mkdir($folder);
			}
		}

		return [
			'year' => $year,
			'month' => $month
		];
	}

	protected function createDbDumpFolder()
	{
		$folder = Database::getDumpPath();

		if (!is_dir($folder)) {
			mkdir($folder);
		}
	}

	protected function emptyDbDumpFolder()
	{
		$folder = Database::getDumpPath();

		foreach (new \DirectoryIterator($folder) as $fileInfo) {
			if (!$fileInfo->isDot()) {
				unlink($fileInfo->getPath() . '/' . $fileInfo->getFilename());
			}
		}
	}

	protected function getLogFolderPaths($year, $month)
	{
		return [
			storage_path('logs/y' . $year),
			storage_path('logs/y' . $year . '/m' . $month),
		];
	}
}