<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

/**
 * セレクトボックス
 */
class Select extends Element
{
    /**
     * to string
     *
     * @param array $appends
     * @return string
     */
    public function toString(array $appends = []): string
    {
        $elements = [
            'type' => 'select',
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'value' => ($this->value ?? $this->callValue() ?? $this->callDefault()),
            'default' => $this->callDefault(),
            'class' => $this->class,
            'style' => $this->style,
            'accesskey' => $this->accesskey,
        ];
        $elements = self::appendOption($elements, $appends);

        return self::generateTag('select', $elements, $this->options);
    }
}
