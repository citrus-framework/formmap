<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

use Citrus\Intersection;
use Citrus\Variable\Strings;

/**
 * 隠し要素
 */
class Hidden extends Element
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
            'type' => 'hidden',
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'value' => ($this->value ?? $this->callValue() ?? $this->callDefault()),
            'class' => $this->class,
        ];
        $elements = self::appendOption($elements, $appends);

        return self::generateTag('input', $elements);
    }



    /**
     * call default value
     *
     * @return false|mixed|string
     */
    public function callDefault()
    {
        $value = $this->default;

        // デフォルト設定
        if (false === Strings::isEmpty($value))
        {
            // 変数タイプ別処理
            $value = Intersection::fetch($this->var_type, [
                // datetime
                ElementType::VAR_TYPE_DATETIME => function () {
                    return date('Y-m-d H:i:s', strtotime($this->default));
                },
                // date
                ElementType::VAR_TYPE_DATE => function () {
                    return date('Y-m-d', strtotime($this->default));
                },
            ], true);
        }
        return $value;
    }
}
