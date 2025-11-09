<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\WhatsappTemplateComponentsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-whatsapp-template-components-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'template_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\WhatsappTemplates::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Whatsapp templates')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'type')->dropDownList([ 'header' => 'Header', 'body' => 'Body', 'button' => 'Button', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'subtype')->dropDownList([ 'text' => 'Text', 'image' => 'Image', 'document' => 'Document', 'video' => 'Video', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'param_order')->textInput(['placeholder' => 'Param Order']) ?>

    <?php /* echo $form->field($model, 'default_value')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'variable_name')->textInput(['maxlength' => true, 'placeholder' => 'Variable Name']) */ ?>

    <?php /* echo $form->field($model, 'is_required')->checkbox() */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
