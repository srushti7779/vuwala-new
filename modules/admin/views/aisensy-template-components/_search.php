<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\AisensyTemplateComponentsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-aisensy-template-components-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'template_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\AisensyTemplates::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Aisensy templates')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'component_index')->textInput(['placeholder' => 'Component Index']) ?>

    <?= $form->field($model, 'type')->textInput(['maxlength' => true, 'placeholder' => 'Type']) ?>

    <?= $form->field($model, 'format')->textInput(['maxlength' => true, 'placeholder' => 'Format']) ?>

    <?php /* echo $form->field($model, 'text')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'example')->textInput(['placeholder' => 'Example']) */ ?>

    <?php /* echo $form->field($model, 'buttons')->textInput(['placeholder' => 'Buttons']) */ ?>

    <?php /* echo $form->field($model, 'raw')->textInput(['placeholder' => 'Raw']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
