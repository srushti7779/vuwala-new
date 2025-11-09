<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\BannerLogsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-banner-logs-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'banner_id')->textInput(['placeholder' => 'Banner']) ?>

    <?= $form->field($model, 'user_id')->textInput(['placeholder' => 'User']) ?>

    <?= $form->field($model, 'action_type')->dropDownList([ 'view' => 'View', 'click' => 'Click', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'ip_address')->textInput(['maxlength' => true, 'placeholder' => 'Ip Address']) ?>

    <?php /* echo $form->field($model, 'user_agent')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
