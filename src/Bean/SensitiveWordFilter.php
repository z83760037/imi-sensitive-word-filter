<?php

namespace Phpch\Imi\SensitiveWordFilter\Bean;

use Imi\Bean\Annotation\Bean;

/**
 * @Bean("SensitiveWordFilter")
 */
class SensitiveWordFilter
{
	protected $type;
	
	protected $matchType;
	
	
	protected $array;
	
	protected $mysql;
	
	
	protected $file;
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getMatchType()
	{
		return $this->matchType;
	}
	
	public function getArray()
	{
		return $this->array;
	}
	
	public function getMysql()
	{
		return $this->mysql;
	}
	
	public function getFile()
	{
		return $this->file;
	}
}
