<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Uploads */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="uploads-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'entity_type')->textInput(['maxlength' => true, 'placeholder' => 'Entity Type'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'entity_id')->textInput(['placeholder' => 'Entity'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'file_url')->textInput(['maxlength' => true, 'placeholder' => 'File Url'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'file_name')->textInput(['maxlength' => true, 'placeholder' => 'File Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'file_type')->textInput(['maxlength' => true, 'placeholder' => 'File Type'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'file_size')->textInput(['placeholder' => 'File Size'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'extension')->textInput(['maxlength' => true, 'placeholder' => 'Extension'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>