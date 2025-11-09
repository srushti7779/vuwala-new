<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\AisensyWebhooksSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-aisensy-webhooks-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'event_type')->textInput(['maxlength' => true, 'placeholder' => 'Event Type']) ?>

    <?= $form->field($model, 'message_id')->textInput(['maxlength' => true, 'placeholder' => 'Message']) ?>

    <?= $form->field($model, 'from_number')->textInput(['maxlength' => true, 'placeholder' => 'From Number']) ?>

    <?= $form->field($model, 'to_number')->textInput(['maxlength' => true, 'placeholder' => 'To Number']) ?>

    <?php /* echo $form->field($model, 'status_value')->textInput(['maxlength' => true, 'placeholder' => 'Status Value']) */ ?>

    <?php /* echo $form->field($model, 'error_code')->textInput(['maxlength' => true, 'placeholder' => 'Error Code']) */ ?>

    <?php /* echo $form->field($model, 'error_message')->textInput(['maxlength' => true, 'placeholder' => 'Error Message']) */ ?>

    <?php /* echo $form->field($model, 'payload')->textInput(['placeholder' => 'Payload']) */ ?>

    <?php /* echo $form->field($model, 'headers')->textInput(['placeholder' => 'Headers']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
