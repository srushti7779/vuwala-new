<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\WhatsappConversationFlowsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-whatsapp-conversation-flows-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'language')->textInput(['maxlength' => true, 'placeholder' => 'Language']) ?>

    <?= $form->field($model, 'state')->textInput(['maxlength' => true, 'placeholder' => 'State']) ?>

    <?= $form->field($model, 'pattern')->textInput(['maxlength' => true, 'placeholder' => 'Pattern']) ?>

    <?= $form->field($model, 'response_text')->textarea(['rows' => 6]) ?>

    <?php /* echo $form->field($model, 'response_interactive')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'next_state')->textInput(['maxlength' => true, 'placeholder' => 'Next State']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
