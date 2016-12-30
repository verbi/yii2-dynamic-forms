<?php

namespace verbi\yii2DynamicForms\components;

use yii\helpers\Url;

/*
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/yii2-dynamic-forms/
 * @license https://opensource.org/licenses/GPL-3.0
 */

class SearchForm extends Form {

    public $method = 'get';

    protected static function getAttributesForForm($item) {
        if ($item instanceof \yii\base\model) {
            if ($item->hasMethod('getAttributesForSearchForm')) {
                return $item->getAttributesForSearchForm();
            } else {
                return $item->attributes();
            }
        } elseif (property_exists($item, 'modelClass')) {
            $modelClass = $item->modelClass;
            $model = new $modelClass;
            if ($model->hasMethod('getAttributesForSearchForm')) {
                return $model->getAttributesForSearchForm();
            }
        }
        return parent::getAttributesForForm();
    }

    public static function widget($options = []) {
        $formOptions = [
            'method' => 'get',
            'action' => static::getAction($options),
        ];
        if (isset($options['formOptions'])) {
            $options['formOptions'] = array_merge($formOptions, $options['formOptions']);
        } else {
            $options['formOptions'] = $formOptions;
        }

        return parent::widget($options);
    }

    protected function getAction(array &$options = []) {
        $action = '';
        if (isset($options['formOptions']) && isset($options['formOptions']['action'])) {
            $action = $options['formOptions']['action'];
        }
        $action = Url::to($action);
        $method = 'get';
        if (isset($options['formOptions']) && isset($options['formOptions']['method'])) {
            $method = $options['formOptions']['method'];
        }
        $attributes = [];
        if (!strcasecmp($method, 'get') && ($pos = strpos($action, '?')) !== false) {
            if (isset($options['model'])) {
                $modelClass = $options['model']->className();

                $attributes = array_map(
                        function(&$item) use (&$modelClass) {
                    return $modelClass . '[' . $item . ']';
                }, array_keys(array_merge($attributes, static::getAttributesForForm($options['model'])))
                );
            }
            if (isset($options['items']) && is_array($options['items'])) {
                
            }
            // query parameters in the action are ignored for GET method
            // we use hidden fields to add them back
            foreach (explode('&', substr($action, $pos + 1)) as $pair) {
                if (($pos1 = strpos($pair, '=')) !== false) {
                    if (in_array(urldecode(substr($pair, 0, $pos1)), $attributes)) {
                        $hiddenInputs[] = static::hiddenInput(
                                        urldecode(substr($pair, 0, $pos1)), urldecode(substr($pair, $pos1 + 1))
                        );
                    }
                } else {
                    if (in_array(urldecode(substr($pair, 0, $pos1)), $attributes)) {
                        $hiddenInputs[] = static::hiddenInput(urldecode($pair), '');
                    }
                }
            }
            $action = substr($action, 0, $pos);
        }
        return $action;
    }

}
