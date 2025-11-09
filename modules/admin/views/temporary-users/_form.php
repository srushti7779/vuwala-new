<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\TemporaryUsers */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="temporary-users-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'placeholder' => 'Username'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'contact_no')->textInput(['maxlength' => true, 'placeholder' => 'Contact No'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'unique_user_id')->textInput(['maxlength' => true, 'placeholder' => 'Unique User'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'placeholder' => 'First Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Email'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'device_token')->textInput(['maxlength' => true, 'placeholder' => 'Device Token'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'device_type')->textInput(['maxlength' => true, 'placeholder' => 'Device Type'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'user_role')->textInput(['maxlength' => true, 'placeholder' => 'User Role'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'referral_code')->textInput(['maxlength' => true, 'placeholder' => 'Referral Code'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'vendor_store_type')->textInput(['maxlength' => true, 'placeholder' => 'Vendor Store Type'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'brand_name')->textInput(['maxlength' => true, 'placeholder' => 'Brand Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'brand_logo')->textInput(['maxlength' => true, 'placeholder' => 'Brand Logo'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'is_featured')->dropDownList($model->getFeatureOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>