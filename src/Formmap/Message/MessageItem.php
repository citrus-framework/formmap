<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap\Message;

use Citrus\Variable\Stockers\StockedItem;

/**
 * フォームマップのメッセージ
 */
class MessageItem extends StockedItem
{
    /** @var string message type */
    public const TYPE_MESSAGE = 'message';

    /** @var string message type */
    public const TYPE_SUCCESS = 'success';

    /** @var string message type */
    public const TYPE_WARNING = 'warning';

    /* @var string* message type */
    public const TYPE_ERROR = 'error';
}
