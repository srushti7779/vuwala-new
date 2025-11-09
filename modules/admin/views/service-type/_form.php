<?php

    use kartik\file\FileInput;
    use kartik\form\ActiveForm;
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $model app\modules\admin\models\ServiceType */
    /* @var $form yii\widgets\ActiveForm */

?>

<div class="service-type-form">

    <?php $form = ActiveForm::begin([
            'id'                   => 'login-form-inline',
            'type'                 => ActiveForm::TYPE_VERTICAL,
            'tooltipStyleFeedback' => true,                                                                          // shows tooltip styled validation error feedback
            'fieldConfig'          => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
            'formConfig'           => ['showErrors' => true],
            // set style for proper tooltips error display
    ]); ?>

    <?php echo $form->errorSummary($model);?>
    <div class="row">
         <div class='col-lg-6 '>   <?php echo $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']);?>

 </div> <div class='col-lg-6'>    <?php echo $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']);?> </div>

 <div class='col-lg-6'>    <?php echo $form->field($model, 'main_category_id')->widget(\kartik\widgets\Select2::classname(), [
    'data'          => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\MainCategory::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
    'options'       => ['placeholder' => Yii::t('app', 'Choose Main category')],
    'pluginOptions' => [
        'allowClear' => true,
    ],
]);?> </div>


<?php
    echo $form->field($model, 'image')->widget(FileInput::classname(), [
        'options'       => ['multiple' => false, 'accept' => 'image/*'],
        'pluginOptions' => [
            'previewFileType'      => 'image',
            'initialPreview'       => [
                $model->image,
            ],
            'initialPreviewAsData' => true,

            'overwriteInitial'     => true,

            'showUpload'           => false,
        ],
    ]);

?>

 <div class='col-lg-6'>    <?php echo $form->field($model, 'type')->textInput(['maxlength' => true, 'placeholder' => 'Type'])?> </div>

 <div class='col-lg-6'>    <?php echo $form->field($model, 'status')->dropDownList($model->getStateOptions())?> </div>
 <?php
     echo $form->field($model, 'is_tax_allowed', [
         'template' => "{input}{label}\n{error}",
     ])->checkbox([
         'class'        => 'form-check-input tax-allwed-checkbox',
         'labelOptions' => ['class' => 'form-check-label'],
         'value'        => 1,
         'uncheck'      => 0,
 ]) ?>


 </div>        <?php if ($model->isNewRecord) {?><?php }?>    <div class="form-group">
                            <?php echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'])?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>