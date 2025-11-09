<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\AisensyTemplateSentLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-aisensy-template-sent-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'template_id')->textInput(['placeholder' => 'Template']) ?>

    <?= $form->field($model, 'contact_number')->textInput(['maxlength' => true, 'placeholder' => 'Contact Number']) ?>

    <?= $form->field($model, 'sent_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Sent Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>

    <?= $form->field($model, 'sent_at')->textInput(['placeholder' => 'Sent At']) ?>

    <?php /* echo $form->field($model, 'message_id')->textInput(['maxlength' => true, 'placeholder' => 'Message']) */ ?>

    <?php /* echo $form->field($model, 'message_status')->dropDownList([ 'sent' => 'Sent', 'delivered' => 'Delivered', 'read' => 'Read', 'failed' => 'Failed', ], ['prompt' => '']) */ ?>

    <?php /* echo $form->field($model, 'api_response')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'template_params')->textInput(['placeholder' => 'Template Params']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
