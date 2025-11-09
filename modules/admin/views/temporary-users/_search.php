<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\TemporaryUsersSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-temporary-users-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'placeholder' => 'Username']) ?>

    <?= $form->field($model, 'contact_no')->textInput(['maxlength' => true, 'placeholder' => 'Contact No']) ?>

    <?= $form->field($model, 'unique_user_id')->textInput(['maxlength' => true, 'placeholder' => 'Unique User']) ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'placeholder' => 'First Name']) ?>

    <?php /* echo $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Email']) */ ?>

    <?php /* echo $form->field($model, 'device_token')->textInput(['maxlength' => true, 'placeholder' => 'Device Token']) */ ?>

    <?php /* echo $form->field($model, 'device_type')->textInput(['maxlength' => true, 'placeholder' => 'Device Type']) */ ?>

    <?php /* echo $form->field($model, 'user_role')->textInput(['maxlength' => true, 'placeholder' => 'User Role']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'referral_code')->textInput(['maxlength' => true, 'placeholder' => 'Referral Code']) */ ?>

    <?php /* echo $form->field($model, 'vendor_store_type')->textInput(['maxlength' => true, 'placeholder' => 'Vendor Store Type']) */ ?>

    <?php /* echo $form->field($model, 'brand_name')->textInput(['maxlength' => true, 'placeholder' => 'Brand Name']) */ ?>

    <?php /* echo $form->field($model, 'brand_logo')->textInput(['maxlength' => true, 'placeholder' => 'Brand Logo']) */ ?>

    <?php /* echo $form->field($model, 'is_featured')->dropDownList($model->getFeatureOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
