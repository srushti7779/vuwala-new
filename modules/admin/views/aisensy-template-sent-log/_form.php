<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyTemplateSentLog */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="aisensy-template-sent-log-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'template_id')->textInput(['placeholder' => 'Template'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'contact_number')->textInput(['maxlength' => true, 'placeholder' => 'Contact Number'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'sent_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Sent Date'),
                'autoclose' => true
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'sent_at')->textInput(['placeholder' => 'Sent At'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'message_id')->textInput(['maxlength' => true, 'placeholder' => 'Message'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'message_status')->dropDownList([ 'sent' => 'Sent', 'delivered' => 'Delivered', 'read' => 'Read', 'failed' => 'Failed', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'api_response')->textarea(['rows' => 6])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'template_params')->textInput(['placeholder' => 'Template Params'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>