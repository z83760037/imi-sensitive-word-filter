<?php

declare(strict_types=1);

namespace Phpch\Imi\SensitiveWordFilter;

use Imi\App;
use Phpch\Imi\SensitiveWordFilter\Bean\SensitiveWordFilter;
use SplFileObject;


class SensitiveWordFilterUtil
{
	private static ?HashMap $words = null;
	
	private static SensitiveWordFilter  $sensitiveWordFilter;
	
	private static function init(): ?HashMap
	{
		if (self::$words == null) {
			self::$words = new HashMap();
			self::$sensitiveWordFilter = App::getBean('SensitiveWordFilter');
			$type = ucfirst(self::$sensitiveWordFilter->getType());
			$method = 'setTreeBy'.$type;
			self::$method();
		}
		return self::$words;
	}
	
	/**
	 * 数组
	 *
	 * @return void
	 */
	private static  function setTreeByArray(): void
	{
		$sensitiveWords =	self::$sensitiveWordFilter->getArray();
		
		foreach ($sensitiveWords as $word) {
			self::buildDFA($word);
		}
	}
	
	/**
	 * 文件
	 *
	 * @return void
	 */
	private static function setTreeByFile(): void
	{
		$filepath = self::$sensitiveWordFilter->getFile();
		
		foreach ($filepath as $file) {
			if (!file_exists($file)) {
				continue;
			}
			
			self::readFile($file);
		}
	}
	
	/**
	 * mysql
	 *
	 * @return void
	 */
	private static function setTreeByMysql(): void
	{
		$mysql = self::$sensitiveWordFilter->getMysql();
		$sensitiveWords = $mysql['model']::query()->column($mysql['field']);
		foreach ($sensitiveWords as $word) {
			self::buildDFA($word);
		}
	}
	
	/**
	 *  将单个敏感词构建成树结构
	 *
	 * @param  string  $word
	 * @return void
	 */
	private static function buildDFA(string $word = ''): void
	{
		if ($word == '') {
			return;
		}
		
		$tree = self::$words;
		$wordLength = mb_strlen($word);
		for ($i = 0; $i < $wordLength; $i++) {
			$keyChar = mb_substr($word, $i, 1);
			$treeTmp = $tree->get($keyChar);
			if ($treeTmp) {
				$tree = $treeTmp;
			} else {
				$newTree = new HashMap();
				$newTree->put('isEnd', false);
				
				// 添加到集合
				$tree->put($keyChar, $newTree);
				$tree = $newTree;
			}
			
			// 到达最后一个节点
			if ($i == $wordLength - 1) {
				$tree->put('isEnd', true);
			}
		}
	}
	
	/**
	 * 读取文件
	 *
	 * @param $file
	 * @return void
	 */
	private static function readFile($file): void
	{
		$fp = new SplFileObject($file, 'rb');
		while (!$fp->eof()) {
			$fp->fseek(0, SEEK_CUR);
			$line = $fp->current(); // 当前行
			
			$line = trim($line);
			$line = explode(",", $line);
			foreach ($line as $v) {
				self::buildDFA($v);
			}
			
			// 指向下一个，不能少
			$fp->next();
		}
		
		$fp = null;
	}
	
	
	/**
	 * 判断是否包含敏感字符
	 *
	 * @param  string  $txt
	 * @return bool
	 */
	public static function contains(string $txt): bool
	{
		if (empty($txt)) {
			return false;
		}
		
		$len = mb_strlen($txt);
		for ($i = 0; $i < $len; $i++) {
			if (self::checkSensitiveWord($txt, $i) > 0) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 检查是否包含敏感词,如果存在返回长度,不存在返回0
	 *
	 * @param $txt
	 * @param $index
	 * @return int
	 */
	private static function checkSensitiveWord($txt, $index): int
	{
		$tempMap = self::init();
		$matchFlag = 0;
		$len = mb_strlen($txt);
		$flag = false;
		for ($i = $index; $i < $len; $i++) {
			// 获取key
			$word = mb_substr($txt, $i, 1);
			// 获取指定节点树
			$tempMap = $tempMap->get($word);
			if (!empty($tempMap)) {
				$matchFlag++;
				// 如果为最后一个匹配规则,结束循环，返回匹配标识数
				if (true === $tempMap->get('isEnd')) {
					$flag = true;
					if (self::$sensitiveWordFilter->getMatchType() == 1) {
						break;
					}
				}
			} else {
				break;
			}
		}
		
		if ($matchFlag < 2 || !$flag) {
			$matchFlag = 0;
		}
		return $matchFlag;
	}
	
	/**
	 * 替换敏感字字符
	 *
	 * @param  string  $txt          文本内容
	 * @param  string  $replaceChar  替换字符
	 * @param  bool    $repeat
	 * @return string
	 */
	public static function replace(string $txt, string $replaceChar = '*', bool $repeat = true): string
	{
		if (empty($txt)) {
			return $txt;
		}
		$badWordList = self::getBadWord($txt);
		
		// 未检测到敏感词，直接返回
		if (empty($badWordList)) {
			return $txt;
		}
		$badWordList = array_unique($badWordList);
		foreach ($badWordList as $badWord) {
			$hasReplacedChar = $replaceChar;
			if ($repeat) {
				$hasReplacedChar = self::getReplaceChars($replaceChar, mb_strlen($badWord));
			}
			$txt = str_replace($badWord, $hasReplacedChar, $txt);
		}
		return $txt;
	}
	
	/**
	 * 获取文字中的敏感词
	 *
	 * @param  string  $txt      文本
	 * @param  int     $wordNum  获取数量默认全部
	 * @return array
	 */
	public static function getBadWord(string $txt, int $wordNum = 0): array
	{
		$badWordList = [];
		$txtLen = mb_strlen($txt);
		for ($i = 0; $i < $txtLen; $i++) {
			$len = self::checkSensitiveWord($txt, $i);
			if ($len > 0) {
				$badWordList[] = mb_substr($txt, $i, $len);
				$i = $i + $len - 1;
				
				if ($wordNum > 0 && count($badWordList) == $wordNum) {
					return $badWordList;
				}
			}
		}
		
		return $badWordList;
	}
	
	/**
	 * 敏感词替换为对应长度的字符
	 *
	 * @param  string  $replaceChar  替换的字符串
	 * @param  int     $len          长度
	 * @return string
	 */
	public static function getReplaceChars(string $replaceChar, int $len): string
	{
		return str_repeat($replaceChar, $len);
	}
	
	/**
	 * 标记
	 *
	 * @author  chenhuan  2023/7/21
	 * @param  string  $txt   文本
	 * @param  string  $sTag  标签开头，如<span>
	 * @param  string  $eTag  标签开头，如</span>
	 * @return string
	 */
	public static function mark(string $txt, string $sTag, string $eTag): string
	{
		if (empty($txt)) {
			return $txt;
		}
		$badWordList = self::getBadWord($txt);
		
		// 未检测到敏感词，直接返回
		if (empty($badWordList)) {
			return $txt;
		}
		
		$badWordList = array_unique($badWordList);
		
		foreach ($badWordList as $badWord) {
			$hasReplacedChar = $sTag . $badWord . $eTag;
			$txt = str_replace($badWord, $hasReplacedChar, $txt);
		}
		return $txt;
	}
}