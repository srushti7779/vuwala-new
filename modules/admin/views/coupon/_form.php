<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\models\User; // Make sure to import your User model

$role = Yii::$app->user->identity->user_role;

\mootensai\components\JsBlock::widget([
    'viewFile' => '_script',
    'pos'=> \yii\web\View::POS_END,
    'viewParams' => [
        'class' => 'CouponVendor',
        'relID' => 'coupon-vendor',
        'value' => \yii\helpers\Json::encode($model->couponVendors),
        'isNewRecord' => $model->isNewRecord ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget([
    'viewFile' => '_script',
    'pos'=> \yii\web\View::POS_END,
    'viewParams' => [
        'class' => 'CouponsApplied',
        'relID' => 'coupons-applied',
        'value' => \yii\helpers\Json::encode($model->couponsApplieds),
        'isNewRecord' => $model->isNewRecord ? 1 : 0
    ]
]);
?>

<div class="coupon-form">

    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true,
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']],
        'formConfig' => ['showErrors' => true],
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?php if ($role == User::ROLE_ADMIN || $role == User::ROLE_SUBADMIN): ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Name']) ?>
        <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'Code']) ?>
    <?php elseif ($role == User::ROLE_VENDOR): ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'readonly' => true, 'placeholder' => 'Vendor Coupon']) ?>
        <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => true, 'placeholder' => 'Auto-generated or fixed']) ?>
    <?php endif; ?>

  <?= $form->field($model, 'description')->textArea(['maxlength' => true, 'placeholder' => 'Description']) ?>

    <?= $form->field($model, 'discount')->textInput(['maxlength' => true, 'placeholder' => 'Discount']) ?>
    <?= $form->field($model, 'max_discount')->textInput(['maxlength' => true, 'placeholder' => 'Max Discount']) ?>
    <?= $form->field($model, 'min_cart')->textInput(['placeholder' => 'Min Cart']) ?>

    <?= $form->field($model, 'start_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Start Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>

    <?= $form->field($model, 'end_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose End Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>

    <?php if ($role == User::ROLE_ADMIN || $role == User::ROLE_SUBADMIN || $role == User::ROLE_VENDOR): ?>
        <?= $form->field($model, 'is_global')->checkbox() ?>

         <?= $form->field($model, 'status')->dropDownList(
            $model->getStateOptions(),
            ['prompt' => 'Select status']
        ) ?>
                
    <?php endif; ?>

    <?php if ($model->isNewRecord): ?>
        <?php
        $forms = [
            // Your dynamic form tabs if needed
        ];
     
        ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
<?php
$script = <<< JS
$('#login-form-inline').on('keypress', function (e) {
    if (e.which === 13 && e.target.tagName.toLowerCase() !== 'textarea') {
        e.preventDefault();
        return false;
    }
});
JS;
$this->registerJs($script);
?>



</div>
