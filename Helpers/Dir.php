<?php

namespace App\SourceBundle\Helpers;

use Symfony\Component\Config\Definition\Exception\Exception;

class Dir {

	public static function Src()
	{
		$src = realpath('./../src');
		if ( ! is_dir($src))
			throw new Exception('src path not found');

		return $src;
	}

	public static function Web()
	{
		$web = realpath('./');
		if ( ! is_dir($web))
			throw new Exception('web path not found');

		return $web;
	}
}