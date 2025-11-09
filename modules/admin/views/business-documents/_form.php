<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BusinessDocuments */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="business-documents-form">

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

    <?php 
    
    if(empty($vendor_details_id)) {
      echo   $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
            'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
            'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    } 
    ?>



    <?php
  echo $form->field($model, 'file')->widget(FileInput::classname(), [
    'options' => ['multiple' => false, 'accept' => 'image/*,.pdf'], // Allow images + PDF
    'pluginOptions' => [
        'previewFileType' => 'any', // Support image + PDF preview
        'initialPreview' => [
            $model->file,
        ],
        'initialPreviewAsData' => true,
        'overwriteInitial' => true,
        'showUpload' => false,
        'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
    ],
]);
    ?>


    <?= $form->field($model, 'document_type')->dropDownList($model->getDocumentTypeOptionsOptions()) ?>


    <?= $form->field($model, 'status')->dropDownList(
        $model->getStateOptions(),
        ['prompt' => 'Select Status']
        ) ?>

<?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
  
    <?php ActiveForm::end(); ?>

</div>
