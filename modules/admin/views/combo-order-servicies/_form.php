<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ComboOrderServicies */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="combo-order-servicies-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'order_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Orders::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Orders')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'combo_package_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\ComboPackages::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Combo packages')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'service_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Services::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Services')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'price')->textInput(['placeholder' => 'Price'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'total_price')->textInput(['placeholder' => 'Total Price'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'qty')->textInput(['placeholder' => 'Qty'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>