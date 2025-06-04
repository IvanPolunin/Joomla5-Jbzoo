<?php

namespace Akeeba\Engine\DevPlatform\Translate;

class Text
{
	public static bool $loaded = false;

	public static array $strings = [];

	public static function _(string $key): string
	{
		if (!self::$loaded)
		{
			self::load();
		}

		$key = strtoupper($key);

		return self::$strings[$key] ?? $key;
	}

	public static function load()
	{
		self::$loaded = true;
		self::$strings = parse_ini_file(__DIR__ . '/lang.ini');
	}
}