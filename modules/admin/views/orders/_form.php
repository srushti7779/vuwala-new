<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Orders */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'CouponsApplied', 
        'relID' => 'coupons-applied', 
        'value' => \yii\helpers\Json::encode($model->couponsApplieds),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'OrderDetails', 
        'relID' => 'order-details', 
        'value' => \yii\helpers\Json::encode($model->orderDetails),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'VendorEarnings', 
        'relID' => 'vendor-earnings', 
        'value' => \yii\helpers\Json::encode($model->vendorEarnings),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="orders-form">

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

    <?= $form->field($model, 'json_details')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'qty')->textInput(['placeholder' => 'Qty']) ?>

    <?= $form->field($model, 'trans_type')->dropDownList($model->getTransTypeOptions()) ?> 


    <?= $form->field($model, 'payment_type')->dropDownList($model->getPaymentTypeOptions()) ?>

    <?= $form->field($model, 'sub_total')->textInput(['placeholder' => 'Sub Total']) ?>

    <?= $form->field($model, 'tip_amt')->textInput(['placeholder' => 'Tip Amt']) ?>

    <?= $form->field($model, 'tax')->textInput(['placeholder' => 'Tax']) ?>

    <?= $form->field($model, 'processing_charges')->textInput(['placeholder' => 'Processing Charges']) ?>

    <?= $form->field($model, 'service_charge')->textInput(['placeholder' => 'Service Charge']) ?>

    <?= $form->field($model, 'taxable_total')->textInput(['placeholder' => 'Taxable Total']) ?>

    <?= $form->field($model, 'total_w_tax')->textInput(['placeholder' => 'Total W Tax']) ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?= $form->field($model, 'cancel_reason')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'cancel_description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'schedule_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Schedule Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>

    <?= $form->field($model, 'schedule_time')->textInput(['maxlength' => true, 'placeholder' => 'Schedule Time']) ?>

    <?= $form->field($model, 'service_instruction')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'voucher_code')->textInput(['maxlength' => true, 'placeholder' => 'Voucher Code']) ?>

    <?= $form->field($model, 'voucher_amount')->textInput(['placeholder' => 'Voucher Amount']) ?>

    <?= $form->field($model, 'voucher_type')->textInput(['maxlength' => true, 'placeholder' => 'Voucher Type']) ?>
 

    <?= $form->field($model, 'payment_status')->dropDownList($model->getPaymentStatusOptions()) ?>

    <?= $form->field($model, 'ip_ress')->textInput(['maxlength' => true, 'placeholder' => 'Ip Ress']) ?>

    <?= $form->field($model, 'service_address')->textInput(['placeholder' => 'Service Address']) ?>

    <?= $form->field($model, 'otp')->textInput(['placeholder' => 'Otp']) ?>

    <?= $form->field($model, 'cgst')->textInput(['placeholder' => 'Cgst']) ?>

    <?= $form->field($model, 'sgst')->textInput(['placeholder' => 'Sgst']) ?>

    <?= $form->field($model, 'is_verify')->textInput(['placeholder' => 'Is Verify']) ?>

    <?= $form->field($model, 'service_type')->dropDownList($model->getServiceTypeOptions()) ?>

<?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'CouponsApplied')),
            'content' => $this->render('_formCouponsApplied', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->couponsApplieds),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'OrderDetails')),
            'content' => $this->render('_formOrderDetails', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->orderDetails),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'VendorEarnings')),
            'content' => $this->render('_formVendorEarnings', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->vendorEarnings),
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
