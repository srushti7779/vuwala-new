<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Cart */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'CartItems', 
        'relID' => 'cart-items', 
        'value' => \yii\helpers\Json::encode($model->cartItems),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'CouponsApplied', 
        'relID' => 'coupons-applied', 
        'value' => \yii\helpers\Json::encode($model->couponsApplieds),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="cart-form">

    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline', 
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
        'formConfig' => ['showErrors' => true],
        // set style for proper tooltips error display
    ]); ?>
  
    <?= $form->errorSummary($model); ?>
  
    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\User::find()->orderBy('id')->asArray()->all(), 'id', 'username'),
        'options' => ['placeholder' => Yii::t('app', 'Choose User')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'quantity')->textInput(['placeholder' => 'Quantity']) ?>

    <?= $form->field($model, 'amount')->textInput(['placeholder' => 'Amount']) ?>

    <?= $form->field($model, 'tip')->textInput(['placeholder' => 'Tip']) ?>

    <?= $form->field($model, 'wallet')->textInput(['placeholder' => 'Wallet']) ?>

    <?= $form->field($model, 'service_instructions')->textInput(['maxlength' => true, 'placeholder' => 'Service Instructions']) ?>

    <?= $form->field($model, 'details')->textInput(['maxlength' => true, 'placeholder' => 'Details']) ?>

    <?= $form->field($model, 'cgst')->textInput(['placeholder' => 'Cgst']) ?>

    <?= $form->field($model, 'sgst')->textInput(['placeholder' => 'Sgst']) ?>

    <?= $form->field($model, 'coupon_code')->textInput(['maxlength' => true, 'placeholder' => 'Coupon Code']) ?>

    <?= $form->field($model, 'coupon_discount')->textInput(['placeholder' => 'Coupon Discount']) ?>

    <?= $form->field($model, 'coupon_applied_id')->textInput(['placeholder' => 'Coupon Applied']) ?>

    <?= $form->field($model, 'service_fees')->textInput(['placeholder' => 'Service Fees']) ?>

    <?= $form->field($model, 'other_charges')->textInput(['placeholder' => 'Other Charges']) ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?= $form->field($model, 'service_address')->textInput(['placeholder' => 'Service Address']) ?>

    <?= $form->field($model, 'service_time')->textInput(['maxlength' => true, 'placeholder' => 'Service Time']) ?>

    <?= $form->field($model, 'service_date')->textInput(['maxlength' => true, 'placeholder' => 'Service Date']) ?>

    <?= $form->field($model, 'type_id')->textInput(['placeholder' => 'Type']) ?>

<?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'CartItems')),
            'content' => $this->render('_formCartItems', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->cartItems),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'CouponsApplied')),
            'content' => $this->render('_formCouponsApplied', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->couponsApplieds),
            ]),
        ],
    ];
    echo kartik\tabs\TabsX::widget([
        'items' => $forms,
        'position' => kartik\tabs\TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'pluginOptions' => [
            'bordered' => true,
            'sideways' => true,
            'enableCache' => false,
        ],
    ]);
    ?>
<?php } ?>    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
  
    <?php ActiveForm::end(); ?>

</div>
