<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Units */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'ProductServices', 
        'relID' => 'product-services', 
        'value' => \yii\helpers\Json::encode($model->productServices),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'ProductServicesUsed', 
        'relID' => 'product-services-used', 
        'value' => \yii\helpers\Json::encode($model->productServicesUseds),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'Products', 
        'relID' => 'products', 
        'value' => \yii\helpers\Json::encode($model->products),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'Sku', 
        'relID' => 'sku', 
        'value' => \yii\helpers\Json::encode($model->skus),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'UOMHierarchy', 
        'relID' => 'uomhierarchy', 
        'value' => \yii\helpers\Json::encode($model->uOMHierarchies),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'WastageProducts', 
        'relID' => 'wastage-products', 
        'value' => \yii\helpers\Json::encode($model->wastageProducts),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="units-form">

    <?php $form = ActiveForm::begin([
    'id' => 'login-form-inline',
    'type' => ActiveForm::TYPE_VERTICAL,
    'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
    'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
    'formConfig' => ['showErrors' => true],
    // set style for proper tooltips error display
    ]); ?>

    <?= $form->errorSummary($model); ?>
    <div class="row">
         <div class='col-lg-6 '>   <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

 </div> <div class='col-lg-6'>    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'unit_name')->textInput(['maxlength' => true, 'placeholder' => 'Unit Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'category')->dropDownList([ 'Weight' => 'Weight', 'Volume' => 'Volume', 'Count' => 'Count', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'uom_type')->dropDownList([ 'reference' => 'Reference', 'bigger' => 'Bigger', 'smaller' => 'Smaller', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'factor')->textInput(['maxlength' => true, 'placeholder' => 'Factor'])  ?> </div>

 </div> <?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'ProductServices')),
            'content' => $this->render('_formProductServices', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->productServices),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'ProductServicesUsed')),
            'content' => $this->render('_formProductServicesUsed', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->productServicesUseds),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'Products')),
            'content' => $this->render('_formProducts', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->products),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'Sku')),
            'content' => $this->render('_formSku', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->skus),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'UOMHierarchy')),
            'content' => $this->render('_formUOMHierarchy', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->uOMHierarchies),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'WastageProducts')),
            'content' => $this->render('_formWastageProducts', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->wastageProducts),
            ]),
        ],
    ];
    echo kartik\tabs\TabsX::widget([
        'items' => $forms,
        'position' => kartik\tabs\TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'pluginOptions' => [
            'bordered' => true,
            'sideways' => true,
            'enableCache' => false,
        ],
    ]);
    ?>
<?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>