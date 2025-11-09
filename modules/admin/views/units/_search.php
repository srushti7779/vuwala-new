<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\UnitsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-units-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'unit_name')->textInput(['maxlength' => true, 'placeholder' => 'Unit Name']) ?>

    <?= $form->field($model, 'category')->dropDownList([ 'Weight' => 'Weight', 'Volume' => 'Volume', 'Count' => 'Count', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?= $form->field($model, 'uom_type')->dropDownList([ 'reference' => 'Reference', 'bigger' => 'Bigger', 'smaller' => 'Smaller', ], ['prompt' => '']) ?>

    <?php /* echo $form->field($model, 'factor')->textInput(['maxlength' => true, 'placeholder' => 'Factor']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
