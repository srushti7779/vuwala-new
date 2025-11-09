<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyBulkMessageLog */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="aisensy-bulk-message-log-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'campaign_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\AisensyBulkCampaignLog::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Aisensy bulk campaign log')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'template_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\AisensyTemplates::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Aisensy templates')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'contact_number')->textInput(['maxlength' => true, 'placeholder' => 'Contact Number'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'message_id')->textInput(['maxlength' => true, 'placeholder' => 'Message'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'skip_reason')->textInput(['maxlength' => true, 'placeholder' => 'Skip Reason'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'sent_datetime')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Sent Datetime'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'delivered_datetime')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Delivered Datetime'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'read_datetime')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Read Datetime'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'error_message')->textInput(['maxlength' => true, 'placeholder' => 'Error Message'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'response_data')->textInput(['placeholder' => 'Response Data'])  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>