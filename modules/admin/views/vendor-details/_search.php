<?php

use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\VendorDetailsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-vendor-details-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(User::find()->orderBy('id')
        ->where(['user_role'=>User::ROLE_VENDOR])
        ->andWhere(['status'=>User::STATUS_ACTIVE])
        ->asArray()->all(), 'id', 'username'),
        'options' => ['placeholder' => Yii::t('app', 'Choose User')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'business_name')->textInput(['maxlength' => true, 'placeholder' => 'Business Name']) ?>



    <?= $form->field($model, 'main_category_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\MainCategory::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Main category')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?php /* echo $form->field($model, 'website_link')->textInput(['maxlength' => true, 'placeholder' => 'Website Link']) */ ?>

    <?php /* echo $form->field($model, 'gst_number')->textInput(['maxlength' => true, 'placeholder' => 'Gst Number']) */ ?>

    <?php /* echo $form->field($model, 'latitude')->textInput(['placeholder' => 'Latitude']) */ ?>

    <?php /* echo $form->field($model, 'longitude')->textInput(['placeholder' => 'Longitude']) */ ?>

    <?php /* echo $form->field($model, 'address')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'logo')->textInput(['maxlength' => true, 'placeholder' => 'Logo']) */ ?>

    <?php /* echo $form->field($model, 'shop_licence_no')->textInput(['maxlength' => true, 'placeholder' => 'Shop Licence No']) */ ?>

    <?php /* echo $form->field($model, 'avg_rating')->textInput(['maxlength' => true, 'placeholder' => 'Avg Rating']) */ ?>

    <?php /* echo $form->field($model, 'min_order_amount')->textInput(['placeholder' => 'Min Order Amount']) */ ?>

    <?php /* echo $form->field($model, 'commission_type')->textInput(['placeholder' => 'Commission Type']) */ ?>

    <?php /* echo $form->field($model, 'commission')->textInput(['placeholder' => 'Commission']) */ ?>

    <?php /* echo $form->field($model, 'offer_tag')->textInput(['maxlength' => true, 'placeholder' => 'Offer Tag']) */ ?>

    <?php /* echo $form->field($model, 'service_radius')->textInput(['placeholder' => 'Service Radius']) */ ?>

    <?php /* echo $form->field($model, 'min_service_fee')->textInput(['placeholder' => 'Min Service Fee']) */ ?>

    <?php /* echo $form->field($model, 'discount')->textInput(['placeholder' => 'Discount']) */ ?>

 

    <?php /* echo $form->field($model, 'gender_type')->textInput(['placeholder' => 'Gender Type']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'service_type_home_visit')->checkbox() */ ?>

    <?php /* echo $form->field($model, 'service_type_walk_in')->checkbox() */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
