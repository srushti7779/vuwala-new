<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\BrandsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-brands-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'brand_name')->textInput(['placeholder' => 'Brand Name']) ?>

    <?= $form->field($model, 'image')->textInput(['maxlength' => true, 'placeholder' => 'Image']) ?>

    <?= $form->field($model, 'is_global')->textInput(['placeholder' => 'Is Global']) ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
