<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap\Validation;

use Citrus\Formmap\Element;
use Citrus\Formmap\FormmapException;

/**
 * フォームエレメントの検証(変数型)
 */
trait VarType
{
    /**
     * 型チェック(int)
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeInt(Element $element, $var): void
    {
        FormmapException::exceptionIf(
            (false === is_int(intval($var)) and
                false === is_numeric($var) and
                0 === preg_match('/^-?[0-9]*$/', $var)),
            sprintf('「%s」には整数を入力してください。', $element->name)
        );
        FormmapException::exceptionIf(
            (PHP_INT_MAX <= $var),
            sprintf('「%s」には「%s」以下の値を入力してください。', $element->name, PHP_INT_MAX)
        );
        FormmapException::exceptionIf(
            (PHP_INT_MIN >= $var),
            sprintf('「%s」には「%s」以上の値を入力してください。', $element->name, PHP_INT_MIN)
        );
    }



    /**
     * 型チェック(float)
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeFloat(Element $element, $var): void
    {
        FormmapException::exceptionIf(
            (false === is_float($var)),
            sprintf('「%s」には少数を入力してください。', $element->name)
        );
    }



    /**
     * 型チェック(数値として認識できる)
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeNumeric(Element $element, $var): void
    {
        FormmapException::exceptionIf(
            (false === is_numeric($var)),
            sprintf('「%s」には数字を入力してください。', $element->name)
        );
    }



    /**
     * 文字列チェック
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeString(Element $element, $var): void
    {
        FormmapException::exceptionIf(
            (false === is_string($var)),
            sprintf('「%s」には文字列を入力してください。', $element->name)
        );
    }



    /**
     * アルファベットチェック
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeAlphabet(Element $element, $var): void
    {
        FormmapException::exceptionIf(
            (0 === preg_match('/^[a-zA-Z]/', $var)),
            sprintf('「%s」には半角英字を入力してください。', $element->name)
        );
    }



    /**
     * 英数字チェック
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeAlphanumeric(Element $element, $var): void
    {
        FormmapException::exceptionIf(
            (0 === preg_match('/^[a-zA-Z0-9_.]/', $var)),
            sprintf('「%s」には半角英数字を入力してください。', $element->name)
        );
    }



    /**
     * 英数字と記号チェック
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeANMarks(Element $element, $var): void
    {
        FormmapException::exceptionIf(
            (0 === preg_match('/^[a-zA-Z0-9_.%&#-]/', $var)),
            sprintf('「%s」には半角英数字および記号を入力してください。', $element->name)
        );
    }
}
