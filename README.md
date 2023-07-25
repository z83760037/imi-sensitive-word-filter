# imi-sensitive-word-filter
基于IMI的dfa敏感词过滤
<br><br>
安装：composer require phpch/imi-sensitive-word-filter
<br><br>

####  你需要添加的配置

```
'SensitiveWordFilter' => [
		'type' => 'mysql', // array,file, mysql
		'matchType' => 1, // 1:最小匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国]人,2:最大匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国人]
		'array' => [
			"中国",
			"中国人"
		],// 敏感词列表
		'mysql' => [
			'model' => \ImiApp\ApiServer\Model\EkSensitiveWord::class, // 模型名称
			'field' => 'word', // 敏感词字段
		],
		'file' => [
		
		],// 文件列表
	],
```
### 检测是否含有敏感词

    $islegal = SensitiveWordFilterUtil::contains($content);

### 敏感词过滤

    // 敏感词替换为*为例（会替换为相同字符长度的*）
    $filterContent =SensitiveWordFilterUtil::replace($content, '*');
    
     // 或敏感词替换为***为例
     $filterContent =SensitiveWordFilterUtil::replace($content, '***', false);

### 标记敏感词
     $markedContent = SensitiveWordFilterUtil::mark($content, '<mark>', '</mark>');

### 获取文字中的敏感词

    // 获取内容中所有的敏感词
    $sensitiveWordGroup =SensitiveWordFilterUtil::getBadWord($content);
    // 仅且获取一个敏感词
    $sensitiveWordGroup = SensitiveWordFilterUtil::getBadWord($content, 1);