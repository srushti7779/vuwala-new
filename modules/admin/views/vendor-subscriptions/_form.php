<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\modules\admin\model\VendorDetails;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorSubscriptions */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="vendor-subscriptions-form">

    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
        'formConfig' => ['showErrors' => true],
        // set style for proper tooltips error display
    ]); ?>

    <?= $form->errorSummary($model); ?>


    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>


    <?= $form->field($model, 'subscription_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Subscriptions::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Subscriptions')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'duration')->textInput(['placeholder' => 'enter duration (in days) start & end dates will be set automatically']) ?>

    <?= $form->field($model, 'amount')->textInput(['placeholder' => 'Enter Amount']) ?>   

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


<?= $form->field($model, 'bill_generation_date_time')->widget(\kartik\datecontrol\DateControl::classname(), [
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

<?= $form->field($model, 'payment_received_datetime')->widget(\kartik\datecontrol\DateControl::classname(), [
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


<div class='col-lg-6'>    <?= $form->field($model, 'sent_invoice')->dropDownList([ 'Yes' => 'Yes', 'No' => 'No', ], ['prompt' => ''])  ?> </div>



    <?= $form->field($model, 'status')->dropDownList(
        $model->getStateOptions(),
        ['prompt'=>'Select Status']
        ) ?>


    <?php if ($model->vendorDetails && $model->vendorDetails->user): ?>
    <div class="row">

        <div class="col-md-6">
            <?= $form->field($model->vendorDetails->user, 'email')
                ->textInput(['readonly' => true])
                ->label('Email') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model->vendorDetails->user, 'contact_no')
                ->textInput(['readonly' => true])
                ->label('Contact No') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model->vendorDetails, 'address')
                ->textInput(['readonly' => true])
                ->label('Address') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model->vendorDetails, 'gst_number')
                ->textInput(['readonly' => true])
                ->label('GST Number') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model->vendorDetails, 'is_verified')
                ->dropDownList(['Yes' => 'Yes', 'No' => 'No'], ['disabled' => true])
                ->label('Is Verified') ?>
        </div>

    </div>
<?php endif; ?>




    <?php if ($model->isNewRecord) { ?><?php } ?> <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>