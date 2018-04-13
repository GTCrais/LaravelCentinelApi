<?php

namespace GTCrais\LaravelCentinelApi\Controllers;

use App\Http\Controllers\Controller;
use GTCrais\LaravelCentinelApi\Classes\Database;
use GTCrais\LaravelCentinelApi\Classes\Platform;
use GTCrais\LaravelCentinelApi\Classes\Zipper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CentinelApiController extends Controller
{
	public function createLog()
	{
		$data = $this->getDefaultDataSet();

		$filePath = storage_path('logs/' . Platform::getLogFilename());

		try {
		    if (file_exists($filePath)) {
				$logContents = file_get_contents($filePath);

				if (!trim($logContents)) {
					$data['success'] = true;

					return response()->json($data);
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
			Log::error($e);
			$data['message'] = "Error while creating the log file: " . $e->getMessage();
		}

		return response()->json($data);
	}

	public function downloadLog(Request $request)
	{
		$filePath = $request->get('filePath');
		$fullFilePath = storage_path($filePath);

		if (!$filePath || !file_exists($fullFilePath)) {
			return response("Incorrect dataset.", 422);
		}

		return response()->download($fullFilePath);
	}

	public function dumpDatabase()
	{
		$data = $this->getDefaultDataSet();

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
			$data['filePath'] = $zipFilename ?: $filename;
		} catch (\Exception $e) {
			Log::error($e);
			$data['message'] = "Error while dumping database: " . $e->getMessage();
		}

		return response()->json($data);
	}

	public function downloadDatabase(Request $request)
	{
		$filename = $request->get('filePath');
		$fullFilePath = Database::getDumpPath($filename);

		if (!$filename || !file_exists($fullFilePath)) {
			return response("Incorrect dataset.", 422);
		}

		return response()->download($fullFilePath)->deleteFileAfterSend(true);
	}

	protected function zipDatabase($filePath)
	{
		$zipFilename = 'databasedump.zip';
		$zipPath = Database::getDumpPath($zipFilename);

		// Try native zip
		Zipper::createNativeZip($filePath, $zipPath);

		// Try 7-zip
		if (!file_exists($zipPath)) {
			Zipper::create7zip($filePath, $zipPath);
		}

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

	protected function getPlatform()
	{
		return Platform::getPlatform();
	}

	protected function getPlatformVersion()
	{
		return Platform::getPlatformVersion();
	}

	protected function getDefaultDataSet()
	{
		return [
			'success' => false,
			'filesize' => 0,
			'filePath' => null,
			'message' => null,
			'platform' => $this->getPlatform(),
			'platformVersion' => $this->getPlatformVersion()
		];
	}
}