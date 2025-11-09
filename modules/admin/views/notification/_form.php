<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Notification */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="notification-form">

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

    <?= $form->field($model, 'user_id')->textInput(['placeholder' => 'User']) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title']) ?>

    <?= $form->field($model, 'module')->textInput(['maxlength' => true, 'placeholder' => 'Module']) ?>

    <?= $form->field($model, 'icon')->textInput(['maxlength' => true, 'placeholder' => 'Icon']) ?>

    <?= $form->field($model, 'order_id')->textInput(['placeholder' => 'Order']) ?>

    <?= $form->field($model, 'created_user_id')->textInput(['placeholder' => 'Created User']) ?>

    <?= $form->field($model, 'created_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Created Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>

    <?= $form->field($model, 'mark_read')->textInput(['placeholder' => 'Mark Read']) ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?= $form->field($model, 'model_type')->textInput(['maxlength' => true, 'placeholder' => 'Model Type']) ?>

    <?= $form->field($model, 'check_on_ajax')->textInput(['placeholder' => 'Check On Ajax']) ?>

    <?= $form->field($model, 'is_deleted')->textInput(['placeholder' => 'Is Deleted']) ?>

    <?= $form->field($model, 'info_delete')->textInput(['maxlength' => true, 'placeholder' => 'Info Delete']) ?>

<?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
  
    <?php ActiveForm::end(); ?>

</div>
