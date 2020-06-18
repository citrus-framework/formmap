<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

/**
 * エレメントタイプ
 */
class ElementType
{
    /** var type int */
    public const VAR_TYPE_INT = 'int';

    /** var type float */
    public const VAR_TYPE_FLOAT = 'float';

    /** var type numeric */
    public const VAR_TYPE_NUMERIC = 'numeric';

    /** var type string */
    public const VAR_TYPE_STRING = 'string';

    /** var type alphabet */
    public const VAR_TYPE_ALPHABET = 'alphabet';

    /** var type alphabet & numeric */
    public const VAR_TYPE_ALPHANUMERIC = 'alphanumeric';

    /** var type alphabet & numeric & marks */
    public const VAR_TYPE_AN_MARKS = 'an_marks';

    /** var type date */
    public const VAR_TYPE_DATE = 'date';

    /** var type time */
    public const VAR_TYPE_TIME = 'time';

    /** var type datetime */
    public const VAR_TYPE_DATETIME = 'datetime';

    /** var type bool */
    public const VAR_TYPE_BOOL = 'bool';

    /** var type file */
    public const VAR_TYPE_FILE = 'file';

    /** var type telephone */
    public const VAR_TYPE_TELEPHONE = 'telephone';

    /** var type tel */
    public const VAR_TYPE_TEL = 'tel';

    /** var type year */
    public const VAR_TYPE_YEAR = 'year';

    /** var type month */
    public const VAR_TYPE_MONTH = 'month';

    /** var type day */
    public const VAR_TYPE_DAY = 'day';

    /** var type email */
    public const VAR_TYPE_EMAIL = 'email';


    /** form type element */
    public const FORM_TYPE_ELEMENT = 'element';

    /** form type text */
    public const FORM_TYPE_TEXT = 'text';

    /** form type text */
    public const FORM_TYPE_TEXTAREA = 'textarea';

    /** form type search */
    public const FORM_TYPE_SEARCH = 'search';

    /** form type hidden */
    public const FORM_TYPE_HIDDEN = 'hidden';

    /** form type select */
    public const FORM_TYPE_SELECT = 'select';

    /** form type password */
    public const FORM_TYPE_PASSWD = 'password';

    /** form type submit */
    public const FORM_TYPE_SUBMIT = 'submit';

    /** form type button */
    public const FORM_TYPE_BUTTON = 'button';

    /** form type label */
    public const FORM_TYPE_LABEL = 'label';

    /** html tag span */
    public const HTML_TAG_SPAN = 'span';

    /** @var string[] 文字列系要素 */
    public static $STRINGS = [
        self::VAR_TYPE_STRING,
        self::VAR_TYPE_ALPHABET,
        self::VAR_TYPE_AN_MARKS,
        self::VAR_TYPE_TEL,
        self::VAR_TYPE_TELEPHONE,
    ];

    /** @var string[] 数値系要素 */
    public static $NUMERICALS = [
        self::VAR_TYPE_INT,
        self::VAR_TYPE_FLOAT,
        self::VAR_TYPE_NUMERIC,
    ];



    /**
     * formmapのelement配列要素からフォームインスタンスを生成
     *
     * @param array $element formmap要素
     * @return Element|null フォームインスタンス
     */
    public static function generate(array $element): ?Element
    {
        $form_type = $element['form_type'];

        switch ($form_type) {
            // デフォルトエレメント
            case ElementType::FORM_TYPE_ELEMENT:
                return new Element($element);
            // 隠し要素
            case ElementType::FORM_TYPE_HIDDEN:
                return new Hidden($element);
            // パスワード
            case ElementType::FORM_TYPE_PASSWD:
                return new Password($element);
            // SELECT
            case ElementType::FORM_TYPE_SELECT:
                return new Select($element);
            // SUBMIT
            case ElementType::FORM_TYPE_SUBMIT:
                return new Submit($element);
            // ボタン
            case ElementType::FORM_TYPE_BUTTON:
                return new Button($element);
            // インプットテキスト
            case ElementType::FORM_TYPE_TEXT:
                return new Text($element);
            // テキストエリア
            case ElementType::FORM_TYPE_TEXTAREA:
                return new Textarea($element);
            // 検索エリア
            case ElementType::FORM_TYPE_SEARCH:
                return new Search($element);
            // 該当なし
            default:
                return null;
        }
    }
}
