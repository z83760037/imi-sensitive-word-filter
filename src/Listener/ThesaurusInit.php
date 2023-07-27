<?php

declare(strict_types=1);

namespace Phpch\Imi\SensitiveWordFilter\Listener;

use Imi\Event\EventParam;
use Imi\Event\IEventListener;
use Imi\Bean\Annotation\Listener;
use Phpch\Imi\SensitiveWordFilter\SensitiveWordFilterUtil;

/**
 * @Listener(eventName="IMI.SERVER.WORKER_START")
 */
class ThesaurusInit implements IEventListener
{
	public function handle(EventParam $e): void
	{
		SensitiveWordFilterUtil::init();
	}
}
