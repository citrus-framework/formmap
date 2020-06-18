<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

use Citrus\Collection;
use Citrus\Formmap;
use Citrus\Message;
use Citrus\Variable\Binders;
use Citrus\Variable\Strings;

/**
 * フォームエレメント
 */
class Element
{
    use Binders;

    /** @var string form id */
    public $id;

    /** @var string id prefix */
    public $prefix = '';

    /** @var string form form type */
    public $form_type;

    /** @var string form variable type */
    public $var_type;

    /** @var string form name */
    public $name = '';

    /** @var string form class */
    public $class;

    /** @var bool form input required */
    public $required = false;

    /** @var string[] options */
    public $options = [];

    /** @var string[] style */
    public $style = [];

    /** @var mixed form value */
    public $value;

    /** @var int validate max */
    public $max;

    /** @var int validate min */
    public $min;

    /** @var string property key */
    public $property;

    /** @var string[] filters  */
    public $filters = [];

    /** @var bool html escape */
    public $escape = true;

    /** @var mixed default value */
    public $default;

    /** @var string accesskey */
    public $accesskey;

    /** @var string placeholder */
    public $placeholder;

    /** @var int size */
    public $size;

    /** @var string src */
    public $src;

    /** @var int length, size or value lesser */
    public $lesser;

    /** @var int length, size or value greater */
    public $greater;

    /** @var bool validate null safe */
    public $validate_null_safe = false;



    /**
     * constructor.
     *
     * @param array|null $element フォーム情報
     */
    public function __construct(array $element = null)
    {
        $this->bindArray($element, true);
    }



    /**
     * to string accessor
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }



    /**
     * generate id and value
     *
     * @param string $id
     * @param mixed  $value
     * @return Element
     */
    public static function generateIdAndValue($id, $value): Element
    {
        return new static([
            'id' => $id,
            'value' => $value,
        ]);
    }



    /**
     * generate html tag
     *
     * @param string     $tag
     * @param array|null $elements
     * @param mixed|null $options
     * @return string
     */
    public static function generateTag(string $tag, array $elements = null, $options = null): string
    {
        // 閉じタグがあるタイプか否か
        $is_multiple_tag = in_array($tag, [
            ElementType::FORM_TYPE_SELECT,
            ElementType::FORM_TYPE_BUTTON,
            ElementType::FORM_TYPE_LABEL,
            ElementType::HTML_TAG_SPAN,
            ElementType::FORM_TYPE_TEXTAREA,
        ]);

        // フォーム要素
        $form_element = self::generateTagElement($elements);

        // 閉じタグが無いタイプ
        if (false === $is_multiple_tag)
        {
            return sprintf('<%s %s />', $tag, implode(' ', $form_element));
        }

        // 閉じタグがあるタイプ
        $inner_tags = [];
        if (true === is_array($options))
        {
            // select
            if (ElementType::FORM_TYPE_SELECT == $tag)
            {
                $inner_tags = Collection::stream($options)->map(function ($vl, $ky) use ($elements) {
                    $selected = ($elements['value'] ?: $elements['default']);
                    return sprintf('<option value="%s" %s>%s</option>',
                        $ky,
                        ($ky == $selected ? 'selected' : ''), // postデータはstringなので、曖昧比較する
                        $vl
                    );
                })->toList();
            }
        }
        // その他のタグ
        else
        {
            if (true === is_string($options))
            {
                $inner_tags[] = $options;
            }
        }

        // valueは渡されてきてもいらなくなる
        unset($form_element['value']);

        return sprintf('<%s %s>%s</%s>',
            $tag,
            implode(' ', $form_element),
            implode(PHP_EOL, $inner_tags),
            $tag
        );
    }



    /**
     * call value
     *
     * @return mixed|string
     */
    public function callValue()
    {
        if (true === $this->escape)
        {
            return htmlspecialchars(($this->value ?: ''), ENT_QUOTES);
        }
        return $this->value;
    }



    /**
     * call default value
     *
     * @return mixed|string
     */
    public function callDefault()
    {
        if (true === $this->escape)
        {
            return htmlspecialchars(($this->default ?: ''), ENT_QUOTES);
        }
        return $this->default;
    }



    /**
     * call prefixed id
     *
     * @return string
     */
    public function callPrefixedId(): string
    {
        if (true === Strings::isEmpty($this->prefix))
        {
            return $this->id;
        }
        return sprintf('%s%s', $this->prefix, $this->id);
    }



    /**
     * to string accessor with option
     *
     * @param array $option
     * @return string
     */
    public function option(array $option = []): string
    {
        return $this->toString($option);
    }



    /**
     * to span tag
     *
     * @return string
     */
    public function span(): string
    {
        $elements = [
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'class' => $this->class,
            'style' => $this->style,
        ];

        return self::generateTag('span', $elements, ($this->value ?? $this->callValue() ?? $this->callDefault()));
    }



    /**
     * to label tag
     *
     * @return string
     */
    public function label(): string
    {
        $elements = [
        ];

        return self::generateTag('label', $elements, $this->name);
    }



    /**
     * validate
     *
     * @return int
     */
    public function validate(): int
    {
        try
        {
            // 必須チェック
            Validation::required($this);
            // 変数型チェック
            Validation::varType($this);
            // 最大値チェック
            Validation::max($this);
            // 最小値チェック
            Validation::min($this);
        }
        catch (FormmapException $e)
        {
            Message::addError($e->getMessage(), null, Formmap::MESSAGE_TAG);
            return 1;
        }
        return 0;
    }



    /**
     * value convert type
     */
    public function convertType(): void
    {
        // result value
        $result = $this->value;

        // null return
        if (true === is_null($result))
        {
            return;
        }

        // convert
        $is_converted = false;
        switch ($this->var_type)
        {
            case ElementType::VAR_TYPE_INT:
                $is_converted = settype($result, 'int');
                break;
            case ElementType::VAR_TYPE_FLOAT:
            case ElementType::VAR_TYPE_NUMERIC:
                $is_converted = settype($result, 'float');
                break;
            case ElementType::VAR_TYPE_BOOL:
                $is_converted = settype($result, 'bool');
                break;
            default:
        }

        if (true === $is_converted)
        {
            $this->value = $result;
        }
    }



    /**
     * value filter
     *
     * @return mixed|null
     */
    public function filter()
    {
        // result value
        $result = $this->value;

        // non filter is null
        if (true === is_null($this->filters))
        {
            return $result;
        }

        // non filter is 空文字
        if ('' == $result)
        {
            return $result;
        }

        // one filter
        if (true === is_string($this->filters))
        {
            $this->filters = [ $this->filters ];
        }

        // filter list
        foreach ($this->filters as $one)
        {
            $filter_method_name = 'filter'.ucfirst(trim($one));
            $result = $this->$filter_method_name($result);
        }

        return $result;
    }



    /**
     * filter password hash
     *
     * @param $value
     * @return bool|string
     */
    public function filterPasswordHash($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }



    /**
     * filter like
     *
     * @param $value
     * @return bool|string
     */
    public function filterLike($value)
    {
        return sprintf('%%%s%%', $value);
    }



    /**
     * option appends
     *
     * @param array $elements
     * @param array $appends
     * @return array
     */
    protected static function appendOption(array $elements, array $appends): array
    {
        foreach ($appends as $ky => $append)
        {
            if (true === isset($elements[$ky]))
            {
                if (true === is_string($elements[$ky]))
                {
                    $elements[$ky] = [ $elements[$ky] ];
                }
                $elements[$ky][] = $append;
            }
            else
            {
                $elements[$ky] = [ $append ];
            }
        }
        return $elements;
    }



    /**
     * フォーム要素を補完する
     *
     * @param array|null $elements
     * @return array
     */
    private static function generateTagElement(array $elements = null): array
    {
        // 要素フォーマット
        $element_format = '%s="%s"';

        $form_element = [];
        foreach ($elements as $ky => $vl)
        {
            // 基本初期値はnullだが、例外的に空配列[]を利用している為
            // 0も通したい
            if (true === is_null($vl) || '' === $vl || [] === $vl)
            {
                continue;
            }

            $parameter_value = $vl;
            if (true === is_array($vl))
            {
                $glue = ('class' === $ky ? ' ' : ', ');
                $parameter_value = implode($glue, $vl);
            }

            $form_element[$ky] = sprintf($element_format, $ky, $parameter_value);
        }

        return $form_element;
    }
}
