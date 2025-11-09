<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\DeliveryAddressSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-delivery-address-search">

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

    <?= $form->field($model, 'address')->textInput(['maxlength' => true, 'placeholder' => 'Address']) ?>

    <?= $form->field($model, 'location')->textInput(['maxlength' => true, 'placeholder' => 'Location']) ?>

    <?= $form->field($model, 'latitude')->textInput(['maxlength' => true, 'placeholder' => 'Latitude']) ?>

    <?php /* echo $form->field($model, 'longitude')->textInput(['maxlength' => true, 'placeholder' => 'Longitude']) */ ?>

    <?php /* echo $form->field($model, 'address_label')->textInput(['maxlength' => true, 'placeholder' => 'Address Label']) */ ?>

    <?php /* echo $form->field($model, 'land_mark')->textInput(['maxlength' => true, 'placeholder' => 'Land Mark']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'contact_no')->textInput(['maxlength' => true, 'placeholder' => 'Contact No']) */ ?>

    <?php /* echo $form->field($model, 'contact_name')->textInput(['maxlength' => true, 'placeholder' => 'Contact Name']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
