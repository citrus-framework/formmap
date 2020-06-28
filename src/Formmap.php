<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFormmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Configurable;
use Citrus\Formmap\Element;
use Citrus\Formmap\ElementType;
use Citrus\Formmap\FormmapException;
use Citrus\Formmap\FormmapObject;
use Citrus\Http\HttpException;
use Citrus\Http\Server\Request;
use Citrus\Variable\PathBinders;
use Citrus\Variable\Singleton;
use Exception;

/**
 * フォームマップ
 */
class Formmap extends Configurable
{
    use Singleton;

    /** @var string message tag */
    public const MESSAGE_TAG = 'formmap';

    /** @var bool validate null is require safe */
    public $validate_null_safe = false;

    /** @var array(string::'form id' => CitrusFormElement) */
    private $elements = [];

    /** @var array(string::'namespace' => array(string::'form id' => CitrusFormElement)) map array */
    private $maps = [];

    /** @var array(string::'namespace' => array(string::'form id' => string::'class name')) map array */
    private $classes = [];

    /** @var string[] ファイル読み込みリスト */
    private $loaded_files = [];

    /** @var bool bind済みかどうか */
    private $is_bound = false;



    /**
     * エレメント取得のマジックメソッド
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->elements[$name];
    }



    /**
     * formmap definition loader
     *
     * @param string $path
     * @return $this
     * @throws FormmapException
     */
    public function load(string $path): self
    {
        // 指定したフォームマップファイルが存在しない
        FormmapException::exceptionIf(is_null($path), sprintf('Formmap定義ファイル「%s」が存在しません', $path));
        if (false === file_exists($path))
        {
            // ファイル名だけの場合を考慮する
            $path = sprintf('%s/%s', $this->configures['path'], basename($path));
            FormmapException::exceptionElse(file_exists($path), sprintf('Formmap定義ファイル「%s」が存在しません', $path));
        }

        // 多重読み込み防止
        if (true === in_array($path, $this->loaded_files))
        {
            return $this;
        }

        // load formmap
        $formmap_list = include($path);

        // parse formmap
        foreach ($formmap_list as $namespace => $formmaps)
        {
            foreach ($formmaps as $form_id => $formmap)
            {
                $class_name = $formmap['class'];
                $prefix = ($formmap['prefix'] ?? '');
                $elements = $formmap['elements'];

                // parse element
                foreach ($elements as $element_id => $element)
                {
                    // エレメントの生成
                    $form = ElementType::generate($element);
                    // 外部情報の設定
                    $form->id = $element_id;
                    $form->prefix = $prefix;
                    // element_idの設定
                    $element_id = $form->prefix . $form->id;
                    // 各要素への設定
                    $this->elements[$element_id] = $form;
                    $this->maps[$namespace][$form_id][$element_id] =& $this->elements[$element_id];
                    $this->classes[$namespace][$form_id] = $class_name;
                }
            }
        }

        // 多重読み込み防止
        $this->loaded_files[] = $path;
        // 多重バインド防止
        $this->is_bound = false;

        return $this;
    }



    /**
     * form data binder
     *
     * @param bool $force 強制バインド
     * @return $this
     * @throws HttpException
     */
    public function bind(bool $force = false): self
    {
        // 多重バインド防止
        if (true === $this->is_bound and false === $force)
        {
            return $this;
        }

        $request = Request::generate();
        $request_list = $request->gets()
                        + $request->posts()
                        + $request->jsons();

        // CitrusRouterからのリクエストを削除
        if (true === isset($request_list['url']))
        {
            unset($request_list['url']);
        }
        $prefix = ($request_list['prefix'] ?? '');

        // $this->mapsには$this->elementsの参照から渡される。
        foreach ($request_list as $ky => $vl)
        {
            // imageボタン対応
            if (0 < preg_match('/.*(_y|_x)$/i', $ky))
            {
                $ky = substr($ky, 0, -2);

                if (true === isset($this->elements[$prefix.$ky]))
                {
                    if (false === is_array($this->elements[$prefix.$ky]->value))
                    {
                        $this->elements[$prefix.$ky]->value = [];
                    }
                    $this->elements[$prefix.$ky]->value[] = $vl;
                }
                else
                {
                    $this->elements[$prefix.$ky] = Element::generateIdAndValue($prefix.$ky, [ $vl ]);
                }
            }
            else
            {
                if (true === isset($this->elements[$prefix.$ky]))
                {
                    $this->elements[$prefix.$ky]->value = $vl;
                }
                else
                {
                    $this->elements[$prefix.$ky] = Element::generateIdAndValue($prefix.$ky, $vl);
                }
            }
        }

        // 多重バインド防止
        $this->is_bound = true;

        return $this;
    }



    /**
     * form data binder
     *
     * @param mixed|null $object
     * @param string     $prefix
     */
    public function bindObject($object = null, string $prefix = ''): void
    {
        $request_list = get_object_vars($object);

        // $this->mapsには$this->elementsの参照から渡される。
        foreach ($request_list as $ky => $vl)
        {
            if (true === isset($this->elements[$prefix.$ky]))
            {
                $this->elements[$prefix.$ky]->value = $vl;
            }
            else
            {
                $this->elements[$prefix.$ky] = new Element(['id' => $prefix.$ky, 'value' => $vl]);
            }
        }
    }



    /**
     * validate
     *
     * @param string|null $form_id
     * @return int
     */
    public function validate(string $form_id = null): int
    {
        try
        {
            $list = [];
            if (true === is_null($form_id))
            {
                $list = $this->elements;
            }
            else
            {
                foreach ($this->maps as $ns_data)
                {
                    foreach ($ns_data as $data_id => $data)
                    {
                        if ($data_id === $form_id)
                        {
                            $list = $data;
                            break 2;
                        }
                    }
                }
            }
            $result = 0;
            /** @var Element $element */
            foreach ($list as $element)
            {
                // NULLの場合検証スキップ
                if (true === $element->validate_null_safe and true === is_null($element->value))
                {
                    continue;
                }
                // 検証
                $result += $element->validate();
            }
            return $result;
        }
        catch (Exception $e)
        {
            throw FormmapException::convert($e);
        }
    }



    /**
     * generation
     *
     * @param string $namespace
     * @param string $form_id
     * @return FormmapObject
     */
    public function generate(string $namespace, string $form_id): FormmapObject
    {
        $class_name = $this->classes[$namespace][$form_id];

        /** @var FormmapObject|PathBinders $object */
        $object = new $class_name();

        /** @var Element[] $properties */
        $properties = $this->maps[$namespace][$form_id];
        foreach ($properties as $one)
        {
            // object生成対象外はnullが設定されている
            if (true === is_null($one->property))
            {
                continue;
            }
            $one->convertType();
            $value = $one->filter();
            $object->setPathValue($one->property, $value);
        }

        return $object;
    }


    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'formmap';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'cache' => false,
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [
            'path',
            'cache',
        ];
    }
}
