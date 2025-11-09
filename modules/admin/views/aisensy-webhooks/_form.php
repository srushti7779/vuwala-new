<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyWebhooks */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="aisensy-webhooks-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'event_type')->textInput(['maxlength' => true, 'placeholder' => 'Event Type'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'message_id')->textInput(['maxlength' => true, 'placeholder' => 'Message'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'from_number')->textInput(['maxlength' => true, 'placeholder' => 'From Number'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'to_number')->textInput(['maxlength' => true, 'placeholder' => 'To Number'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status_value')->textInput(['maxlength' => true, 'placeholder' => 'Status Value'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'error_code')->textInput(['maxlength' => true, 'placeholder' => 'Error Code'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'error_message')->textInput(['maxlength' => true, 'placeholder' => 'Error Message'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'payload')->textInput(['placeholder' => 'Payload'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'headers')->textInput(['placeholder' => 'Headers'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>