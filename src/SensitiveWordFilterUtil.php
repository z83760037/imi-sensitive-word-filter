<?php

declare(strict_types=1);

namespace Phpch\Imi\SensitiveWordFilter;

use Exception;
use Imi\App;
use Phpch\Imi\SensitiveWordFilter\Bean\SensitiveWordFilter;

/**
 * Class WordBan
 *
 * @method static void init()
 * @method static boolean contains(string $txt) 判断是否包含敏感字符
 * @method static string replace(string $txt, string $replaceChar = '*', bool $repeat = true) 替换敏感字字符
 * @method static array getBadWord(string $txt, int $wordNum = 0) 获取文字中的敏感词
 * @method static string mark(string $txt, string $sTag, string $eTag) 标记
 */
class SensitiveWordFilterUtil
{
	/**
	 * @var SensitiveWordFilter
	 */
	private static SensitiveWordFilter $_worker;
	
	/**
	 * 静态方法解析执行。
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 * @throws Exception
	 */
	public static function __callStatic($name, $arguments)
	{
		if (static::$_worker == null) {
			static::$_worker =  App::getBean('SensitiveWordFilter');
		}
		if (!method_exists(static::$_worker, $name)) {
			throw new Exception('Can not found Method: ' . $name);
		}
		return call_user_func_array([static::$_worker, $name], $arguments);
	}
}
