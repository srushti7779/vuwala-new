<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;
use yii\web\UrlRule;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BusinessImages */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="business-images-form">

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
    
    if(empty($vendor_details_id)){
    echo     $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
            'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
            'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    }
  ?>
<?php
echo $form->field($model, 'image_file')->widget(FileInput::classname(), [
    'options' => [
        'multiple' => true,
        'accept' => 'image/*',
    ],
    'pluginOptions' => [
        'previewFileType' => 'image',
        'initialPreview' => $model->image_file ? explode(',', $model->image_file) : [],
        'initialPreviewAsData' => true,
        'overwriteInitial' => false, // Changed from true to false
        'showUpload' => false,
        'maxFileCount' => 10,
        'showRemove' => true,
        'showCancel' => false,
        'browseLabel' => 'Select Images',
    ],
]);
?>


  <?= $form->field($model, 'status')->dropDownList(
    $model->getStateOptions(),
    ['prompt' => 'Select status']
) ?>

    <?php if ($model->isNewRecord) { ?><?php } ?> <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>