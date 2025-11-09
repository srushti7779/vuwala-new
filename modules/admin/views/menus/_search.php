<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\MenusSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-menus-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'Label']) ?>

    <?= $form->field($model, 'route')->textInput(['maxlength' => true, 'placeholder' => 'Route']) ?>

    <?= $form->field($model, 'icon')->textInput(['maxlength' => true, 'placeholder' => 'Icon']) ?>

    <?= $form->field($model, 'parent_id')->textInput(['placeholder' => 'Parent']) ?>

    <?php /* echo $form->field($model, 'sort_order')->textInput(['placeholder' => 'Sort Order']) */ ?>

    <?php /* echo $form->field($model, 'status')->checkbox() */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
