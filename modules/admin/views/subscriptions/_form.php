<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Subscriptions */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget([
    'viewFile' => '_script',
    'pos' => \yii\web\View::POS_END,
    'viewParams' => [
        'class' => 'VendorSubscriptions',
        'relID' => 'vendor-subscriptions',
        'value' => \yii\helpers\Json::encode($model->vendorSubscriptions),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="subscriptions-form">

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

    <?= $form->field($model, 'subscription_type')->dropDownList($model->getSubscriptionTypeOptions()) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title']) ?>

    <?= $form->field($model, 'description')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>

    <?= $form->field($model, 'image')->textInput(['maxlength' => true, 'placeholder' => 'Image']) ?>

    <!-- <?= $form->field($model, 'price')->textInput(['placeholder' => 'Price']) ?>  -->

    <!-- <?= $form->field($model, 'offer_price')->textInput(['placeholder' => 'Offer Price']) ?>

    <?= $form->field($model, 'validity_in_days')->textInput(['placeholder' => 'Validity In Days']) ?>

    <?= $form->field($model, 'validity_in_text')->textInput(['maxlength' => true, 'placeholder' => 'Validity In Text']) ?> -->

    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?php if ($model->isNewRecord)
    
    { ?> 
            
    <?php } ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>