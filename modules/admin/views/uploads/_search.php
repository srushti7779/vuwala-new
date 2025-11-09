<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\UploadsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-uploads-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'entity_type')->textInput(['maxlength' => true, 'placeholder' => 'Entity Type']) ?>

    <?= $form->field($model, 'entity_id')->textInput(['placeholder' => 'Entity']) ?>

    <?= $form->field($model, 'file_url')->textInput(['maxlength' => true, 'placeholder' => 'File Url']) ?>

    <?= $form->field($model, 'file_name')->textInput(['maxlength' => true, 'placeholder' => 'File Name']) ?>

    <?php /* echo $form->field($model, 'file_type')->textInput(['maxlength' => true, 'placeholder' => 'File Type']) */ ?>

    <?php /* echo $form->field($model, 'file_size')->textInput(['placeholder' => 'File Size']) */ ?>

    <?php /* echo $form->field($model, 'extension')->textInput(['maxlength' => true, 'placeholder' => 'Extension']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
