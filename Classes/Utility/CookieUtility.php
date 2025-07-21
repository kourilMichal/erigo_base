<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2020 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Utility;

class CookieUtility
{
	public static function hasData(string $name): bool
	{
		return array_key_exists($name, $_COOKIE);
	}
	
	public static function getData(string $name)
	{
		if (static::hasData($name)) {
			return $_COOKIE[$name];
		}
		
		return null;
	}
	
	public static function setData(string $name, $value, string $expiresOffset = null, array $options = []): void
	{
		$expires = 0;
		if ($expiresOffset != null) {
			$expires = strtotime($expiresOffset);
		}
		
		$options = array_merge([
				'expires' => $expires,
				'path' => '/',
				'domain' => $_SERVER['SERVER_NAME'],
				
			], $options);
		
		setcookie($name, $value, $options);
	}
	
	public static function unsetData(string $name): void
	{
		static::setData($name, null, '-1 hour');
	}
}