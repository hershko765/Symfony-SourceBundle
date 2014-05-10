<?php

namespace App\SourceBundle\Components;

use App\SourceBundle\Helpers\Arr;
use App\SourceBundle\Helpers\Dir;
use App\SourceBundle\Helpers\Generator;
use Symfony\Component\Config\Definition\Exception\Exception;

class SilentLog {

	private static $instance;
	private $errors = [];
	private $logPath;

	private function __construct()
	{
		register_shutdown_function(function(){ $this->logToFile(); });
		$this->logPath = Dir::Src().'/Core/Logs';
		self::$instance = $this;
	}

	public static function getInstance()
	{
		if ( ! self::$instance) new SilentLog();

		return self::$instance;
	}

	public function silentLog($error)
	{
		$this->errors[] = $error;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function setLogPath($path)
	{
		$this->logPath = $path;
	}

	private function logToFile()
	{
		$file = Generator::uniqueHash(10);
		$logArr = [
			'user_agent' => Arr::get($_SERVER, 'HTTP_USER_AGENT'),
			'remote_addr' => Arr::get($_SERVER, 'REMOTE_ADDR'),
			'request_time' => Arr::get($_SERVER, 'REQUEST_TIME'),
			'errors' => $this->errors
		];

		file_put_contents($this->logPath.'/'.$file.'.txt', json_encode($logArr));
	}
}