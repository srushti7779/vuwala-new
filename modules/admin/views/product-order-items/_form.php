<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductOrderItems */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'ProductOrderItemsAssignedDiscounts', 
        'relID' => 'product-order-items-assigned-discounts', 
        'value' => \yii\helpers\Json::encode($model->productOrderItemsAssignedDiscounts),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="product-order-items-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'product_order_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\ProductOrders::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Product orders')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'product_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Products::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Products')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'quantity')->textInput(['placeholder' => 'Item Count'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'units')->textInput(['maxlength' => true, 'placeholder' => 'Units'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'mrp_price')->textInput(['placeholder' => 'Mrp Price'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'selling_price')->textInput(['placeholder' => 'Selling Price'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'sub_total')->textInput(['placeholder' => 'Sub Total'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'tax_percentage')->textInput(['placeholder' => 'Tax Percentage'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'tax_amount')->textInput(['placeholder' => 'Tax Amount'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'total_with_tax')->textInput(['placeholder' => 'Total With Tax'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'ProductOrderItemsAssignedDiscounts')),
            'content' => $this->render('_formProductOrderItemsAssignedDiscounts', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->productOrderItemsAssignedDiscounts),
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