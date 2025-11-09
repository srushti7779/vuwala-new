<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\WhatsappRegistrationRequestsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-whatsapp-registration-requests-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'source')->textInput(['maxlength' => true, 'placeholder' => 'Source']) ?>

    <?= $form->field($model, 'src_id')->textInput(['placeholder' => 'Src']) ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'placeholder' => 'Username']) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Email']) ?>

    <?php /* echo $form->field($model, 'contact_no')->textInput(['maxlength' => true, 'placeholder' => 'Contact No']) */ ?>

    <?php /* echo $form->field($model, 'first_name')->textInput(['maxlength' => true, 'placeholder' => 'First Name']) */ ?>

    <?php /* echo $form->field($model, 'last_name')->textInput(['maxlength' => true, 'placeholder' => 'Last Name']) */ ?>

    <?php /* echo $form->field($model, 'business_name')->textInput(['maxlength' => true, 'placeholder' => 'Business Name']) */ ?>

    <?php /* echo $form->field($model, 'gst_number')->textInput(['maxlength' => true, 'placeholder' => 'Gst Number']) */ ?>

    <?php /* echo $form->field($model, 'city_id')->textInput(['placeholder' => 'City']) */ ?>

    <?php /* echo $form->field($model, 'address')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'created_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Created At'),
                'autoclose' => true,
            ]
        ],
    ]); */ ?>

    <?php /* echo $form->field($model, 'updated_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Updated At'),
                'autoclose' => true,
            ]
        ],
    ]); */ ?>

    <?php /* echo $form->field($model, 'extra')->textInput(['placeholder' => 'Extra']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
