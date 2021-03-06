<?php

namespace verbi\yii2DynamicForms\components;

use verbi\yii2Helpers\widgets\builder\ActiveForm;
use kartik\builder\TabularForm;
use verbi\yii2Helpers\Html;

/*
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/yii2-dynamic-forms/
 * @license https://opensource.org/licenses/GPL-3.0
*/
class Form extends \kartik\builder\Form {
    use \verbi\yii2Helpers\traits\WidgetTrait;
    public static function getOptions($options = []) {
        $formOptions = [
            'columns' => 2,
        ];
        
        if (isset($options['models']) && !isset($options['items'])) {
            $options['items'] = $options['models'];
            unset($options['models']);
        }
        return array_merge($formOptions, $options);
    }

    public static function widget($options = []) {
        $formOptions = [];
        if (isset($options['formOptions'])) {
            $formOptions = array_merge($formOptions, $options['formOptions']);
            unset($options['formOptions']);
        }
        ob_start();
        ob_implicit_flush(false);
        $formRendered = false;
        if(!isset($options['form'])) {
            $form = ActiveForm::begin($formOptions);
            $options['form'] = $form;
            $formRendered = true;
        }
        $options = static::getOptions($options);
        $output = static::getFormWidgets($options);
        echo $output;
        echo static::getFormButtons($options);
        if($formRendered) {
            ActiveForm::end();
        }
        echo static::getTabularModals($options);
        return ob_get_clean();
    }
    
    protected static function getFormButtons(&$options = []) {
        return Html::resetButton('Reset', ['class' => 'btn btn-default']) 
                . Html::submitButton('Submit', ['class' => 'btn btn-primary']);
    }

    protected static function getAttributesForForm($item) {
        $attributes = [];
        if($item instanceof \yii\base\model) {
            if($item->hasMethod('getAttributesForForm')) {
                $attributes = $item->getAttributesForForm();
            }
            else {
                $attributes = $item->attributes();
            }
        } elseif (property_exists($item, 'modelClass')) {
            $modelClass = $item->modelClass;
            $model = new $modelClass;
            if($model->hasMethod('getAttributesForForm')) {
                $attributes = $model->getAttributesForForm();
            }
        } elseif (property_exists($item, 'from') && $item->from) {
            foreach ($items->from as $tablename) {
                
            }
        }
        return $attributes;
    }

    public static function getFormWidgets(&$options = []) {
        $output = '';
        if (isset($options['model'])) {
            $options['attributes'] = static::getAttributesForForm($options['model']);
            $output .= \verbi\yii2Helpers\widgets\builder\Form::widget($options);
        }
        if (isset($options['items']) && is_array($options['items'])) {
            foreach ($options['items'] as $item) {
                $options2 = $options;
                unset($options2['items']);
                if ($item instanceof \yii\db\Query) {
                    $item = new \yii\data\ActiveDataProvider([
                        'query' => $item,
                    ]);
                    if (!isset($options2['attributes'])) {
                        $options2['attributes'] = static::getAttributesForForm($item->query);
                    }
                }
                elseif ($item instanceof \yii\base\Model) {
                    $options2['model'] = $item;
                    $options2['attributes'] = $options2['model']->getAttributesForForm();
                    $label = null;
                    if (isset($options2['label'])) {
                        $label = $options2['label'];
                        unset($options2['label']);
                    } elseif (method_exists($options2['model'], 'label')) {
                        $label = $options2['model']->label();
                    }
                    if($label) {
                        $output .= Html::fieldset(\verbi\yii2Helpers\widgets\builder::widget($options2), ['legend' => $label]);
                    }
                    else {
                        $output .= \verbi\yii2Helpers\widgets\builder::widget($options2);
                    }
                } elseif ($item instanceof \yii\data\BaseDataProvider) {
                    $options2['dataProvider'] = $item;
                    if (!isset($options2['attributes'])) {
                        $options2['attributes'] = [];
                    }
                    unset($options2['columns']);
                    if (!isset($options2['gridSettings']) || !isset($options2['gridSettings']['panel']) || !isset($options2['gridSettings']['panel']['after'])
                    ) {
                        $options2['gridSettings']['panel']['after'] = Html::a('<i class="glyphicon glyphicon-plus"></i> ' . \Yii::t('verbi', 'Add New'), '#', ['class' => 'btn btn-success']) . ' ' .
                                Html::a('<i class="glyphicon glyphicon-remove"></i> ' . \Yii::t('verbi', 'Delete'), '#', ['class' => 'btn btn-danger']) . ' ' .
                                Html::submitButton('<i class="glyphicon glyphicon-floppy-disk"></i> ' . \Yii::t('verbi', 'Save'), ['class' => 'btn btn-primary']);
                    }
                    $output.=TabularForm::widget($options2);
                }
            }
        }
        return $output;
    }

    protected static function getFormWidget($model, $options = []) {
        $options['model'] = $model;
        unset($options['models']);
        return Form::widget($options);
    }

    protected static function getTabularModals(&$options) {
        ob_start();
        ob_implicit_flush(false);
        if(isset($options['items'])) {
            foreach($options['items'] as $item) {
                if($item instanceof \yii\db\Query)
                {
                    $modelClass = $item->modelClass;
                    $model= new $modelClass;
                    
                    $formOptions = $options;
                    unset($formOptions['items']);
                    unset($formOptions['models']);
                    $formOptions['model']=$model;
                    \yii\bootstrap\Modal::begin([
                        'header' => '<h2>'.$model->label().'</h2>',
                        'toggleButton' => ['label' => '<i class="glyphicon glyphicon-plus"></i> ' . \Yii::t('verbi', 'Add New'), 'class' => 'btn btn-success'],
                    ]);
                    echo static::widget($formOptions);
                    \yii\bootstrap\Modal::end();
                }
            }
        }
        $output = ob_get_clean();
        return $output;
    }
}
