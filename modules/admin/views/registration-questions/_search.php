<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\RegistrationQuestionsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-registration-questions-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'question_text')->textInput(['maxlength' => true, 'placeholder' => 'Question Text']) ?>

    <?= $form->field($model, 'column_name')->textInput(['maxlength' => true, 'placeholder' => 'Column Name']) ?>

    <?= $form->field($model, 'target_table')->dropDownList([ 'users' => 'Users', 'vendor_details' => 'Vendor details', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'type')->dropDownList([ 'text' => 'Text', 'number' => 'Number', 'email' => 'Email', 'choice' => 'Choice', 'date' => 'Date', 'phone' => 'Phone', ], ['prompt' => '']) ?>

    <?php /* echo $form->field($model, 'required')->checkbox() */ ?>

    <?php /* echo $form->field($model, 'sort_order')->textInput(['placeholder' => 'Sort Order']) */ ?>

    <?php /* echo $form->field($model, 'meta')->textInput(['placeholder' => 'Meta']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
