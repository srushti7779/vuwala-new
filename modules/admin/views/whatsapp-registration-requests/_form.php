<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappRegistrationRequests */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="whatsapp-registration-requests-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'source')->textInput(['maxlength' => true, 'placeholder' => 'Source'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'src_id')->textInput(['placeholder' => 'Src'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'placeholder' => 'Username'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Email'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'contact_no')->textInput(['maxlength' => true, 'placeholder' => 'Contact No'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'placeholder' => 'First Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true, 'placeholder' => 'Last Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'business_name')->textInput(['maxlength' => true, 'placeholder' => 'Business Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'gst_number')->textInput(['maxlength' => true, 'placeholder' => 'Gst Number'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'city_id')->textInput(['placeholder' => 'City'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'address')->textarea(['rows' => 6])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'created_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Created At'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'updated_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Updated At'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'extra')->textInput(['placeholder' => 'Extra'])  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>