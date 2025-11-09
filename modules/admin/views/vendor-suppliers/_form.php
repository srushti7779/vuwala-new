<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorSuppliers */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="vendor-suppliers-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'suppliers_firm_name')->textInput(['maxlength' => true, 'placeholder' => 'Suppliers Firm Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true, 'placeholder' => 'Contact Person'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'gst_number')->textInput(['maxlength' => true, 'placeholder' => 'Gst Number'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true, 'placeholder' => 'Phone Number'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'mail')->textInput(['maxlength' => true, 'placeholder' => 'Mail'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'location')->textInput(['maxlength' => true, 'placeholder' => 'Location'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>