<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\StoreTimings */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="store-timings-form">

    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline', 
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
        'formConfig' => ['showErrors' => true],
        // set style for proper tooltips error display
    ]); ?>
  
    <?= $form->errorSummary($model); ?>
  
    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'day_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Days::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Days')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

   <?= $form->field($model, 'start_time')->widget(\kartik\datecontrol\DateControl::classname(), [
    'type' => \kartik\datecontrol\DateControl::FORMAT_TIME,
    'displayFormat' => 'php:h:i A',  
    'saveFormat' => 'php:H:i:s',      
    'ajaxConversion' => true,
    'options' => [
        'pluginOptions' => [
            'placeholder' => Yii::t('app', 'Choose Start Time'),
            'autoclose' => true,
        ]
    ]
]); ?>

<?= $form->field($model, 'close_time')->widget(\kartik\datecontrol\DateControl::classname(), [
    'type' => \kartik\datecontrol\DateControl::FORMAT_TIME,
    'displayFormat' => 'php:h:i A',   
    'saveFormat' => 'php:H:i:s',     
    'ajaxConversion' => true,
    'options' => [
        'pluginOptions' => [
            'placeholder' => Yii::t('app', 'Choose Close Time'),
            'autoclose' => true,
        ]
    ]
]); ?>


    <div class="col-md-12">
            <?= $form->field($model, 'status')->dropDownList(
                $model->getStateOptions(),
                ['prompt'=> 'Select Status']
            ) ?>
        </div>

<?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
  
    <?php ActiveForm::end(); ?>

</div>
