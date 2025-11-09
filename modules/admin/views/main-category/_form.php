<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;



\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'SubCategory', 
        'relID' => 'sub-category', 
        'value' => \yii\helpers\Json::encode($model->subCategories),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'VendorDetails', 
        'relID' => 'vendor-details', 
        'value' => \yii\helpers\Json::encode($model->vendorDetails),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="main-category-form">

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

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title']) ?>



         <?php 
    echo $form->field($model, 'image')->widget(FileInput::classname(), [
        'options' => ['multiple' => false, 'accept' => 'image/*'],
        'pluginOptions' => [
            'previewFileType' => 'image',
            'initialPreview' => [
                $model->image,
            ],
            'initialPreviewAsData' => true,

            'overwriteInitial' => true,

            'showUpload' => false,
        ],
    ]);

    ?>


 <?php 
    echo $form->field($model, 'icon')->widget(FileInput::classname(), [
        'options' => ['multiple' => false, 'accept' => 'image/*'],
        'pluginOptions' => [
            'previewFileType' => 'image',
            'initialPreview' => [
                $model->icon,
            ],
            'initialPreviewAsData' => true,

            'overwriteInitial' => true,

            'showUpload' => false,
        ],
    ]);

    ?>


    <?= $form->field($model, 'is_featured')->dropDownList($model->getFeatureOptions()) ?>

    <?= $form->field($model, 'offer_percentage')->textInput(['placeholder' => 'Offer Percentage']) ?>

    <?= $form->field($model, 'is_required_documents')->checkbox() ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?= $form->field($model, 'show_home')->checkbox() ?>

    <?= $form->field($model, 'sortOrder')->textInput(['placeholder' => 'SortOrder']) ?>

    <?= $form->field($model, 'position')->textInput(['placeholder' => 'Position']) ?>

    <?= $form->field($model, 'type_id')->textInput(['placeholder' => 'Type']) ?>

<?php if($model->isNewRecord){ ?>    
    
   
<?php } ?>    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
  
    <?php ActiveForm::end(); ?>

</div>
