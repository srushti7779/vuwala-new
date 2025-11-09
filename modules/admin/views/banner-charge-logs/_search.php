<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\BannerChargeLogsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-banner-charge-logs-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\User::find()->orderBy('id')->asArray()->all(), 'id', 'username'),
        'options' => ['placeholder' => Yii::t('app', 'Choose User')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'banner_id')->textInput(['maxlength' => true, 'placeholder' => 'Banner']) ?>

    <?= $form->field($model, 'action')->dropDownList([ 'click' => 'Click', 'view' => 'View', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'charge_amount')->textInput(['maxlength' => true, 'placeholder' => 'Charge Amount']) ?>

    <?php /* echo $form->field($model, 'ip_address')->textInput(['maxlength' => true, 'placeholder' => 'Ip Address']) */ ?>

    <?php /* echo $form->field($model, 'performed_at')->textInput(['placeholder' => 'Performed At']) */ ?>

    <?php /* echo $form->field($model, 'user_agent')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'status')->textInput(['placeholder' => 'Status']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
