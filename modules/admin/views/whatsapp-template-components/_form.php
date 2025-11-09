<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappTemplateComponents */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="whatsapp-template-components-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'template_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\WhatsappTemplates::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Whatsapp templates')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'type')->dropDownList([ 'header' => 'Header', 'body' => 'Body', 'button' => 'Button', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'subtype')->dropDownList([ 'text' => 'Text', 'image' => 'Image', 'document' => 'Document', 'video' => 'Video', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'param_order')->textInput(['placeholder' => 'Param Order'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'default_value')->textarea(['rows' => 6])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'variable_name')->textInput(['maxlength' => true, 'placeholder' => 'Variable Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'is_required')->checkbox()  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>