<?php

use app\modules\admin\models\Banner;
use app\modules\admin\models\base\BannerRecharges;
use app\modules\admin\models\base\VendorDetails;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BannerRecharges */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="banner-recharges-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <!-- <?= $form->field($model, 'vendor_id')->widget(Select2::classname(), [
   
]); ?> -->

   <!-- <?= $form->field($model, 'banner_id')->widget(Select2::classname(), [
   
]); ?> -->


    <?= $form->field($model, 'amount')->textInput(['maxlength' => true, 'placeholder' => 'Amount']) ?>

     <?= $form->field($model, 'status')->textInput(['placeholder' => 'Status']) ?> 

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
