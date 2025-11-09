<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappConversationFlows */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="whatsapp-conversation-flows-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'language')->textInput(['maxlength' => true, 'placeholder' => 'Language'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'state')->textInput(['maxlength' => true, 'placeholder' => 'State'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'pattern')->textInput(['maxlength' => true, 'placeholder' => 'Pattern'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'response_text')->textarea(['rows' => 6])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'response_interactive')->textarea(['rows' => 6])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'next_state')->textInput(['maxlength' => true, 'placeholder' => 'Next State'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>