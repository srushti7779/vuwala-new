<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\RegistrationQuestions */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="registration-questions-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'question_text')->textInput(['maxlength' => true, 'placeholder' => 'Question Text'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'column_name')->textInput(['maxlength' => true, 'placeholder' => 'Column Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'target_table')->dropDownList([ 'users' => 'Users', 'vendor_details' => 'Vendor details', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'type')->dropDownList([ 'text' => 'Text', 'number' => 'Number', 'email' => 'Email', 'choice' => 'Choice', 'date' => 'Date', 'phone' => 'Phone', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'required')->checkbox()  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'sort_order')->textInput(['placeholder' => 'Sort Order'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'meta')->textInput(['placeholder' => 'Meta'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>