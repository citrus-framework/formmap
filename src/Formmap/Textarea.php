<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

/**
 * テキストエリア
 */
class Textarea extends Element
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
            'type' => 'text',
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'class' => $this->class,
            'style' => $this->style,
            'size' => $this->size,
            'maxlength' => $this->max,
            'placeholder' => $this->placeholder,
        ];
        $elements = self::appendOption($elements, $appends);

        return self::generateTag('textarea', $elements, [
            ($this->value ?? $this->callValue() ?? $this->callDefault()),
        ]);
    }
}
