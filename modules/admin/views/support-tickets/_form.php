<?php

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SupportTickets */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'SupportTicketsHasFiles', 
        'relID' => 'support-tickets-has-files', 
        'value' => \yii\helpers\Json::encode($model->supportTicketsHasFiles),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
$vendor_details_id = Yii::$app->request->get('vendor_details_id', null);
?>

<div class="support-tickets-form">

    <?php $form = ActiveForm::begin([
    'id' => 'login-form-inline',
    'type' => ActiveForm::TYPE_VERTICAL,
    'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
    'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
    'formConfig' => ['showErrors' => true],
    // set style for proper tooltips error display
    ]); ?>

    <?= $form->errorSummary($model); ?>
    <div class="row">
         <div class='col-lg-6 '>   <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

 </div> <div class='col-lg-6'>    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']);  ?> </div>

<div class='col-lg-6'> 
        <?php

        $user = Yii::$app->user->identity;

        $query = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE]);

        if ($user->user_role === User::ROLE_VENDOR) {
            $query->andWhere(['user_id' => $user->id]);
        } elseif ($user->user_role === User::ROLE_ADMIN) {
        }

        $vendorList = ArrayHelper::map(
            $query->orderBy('id')->asArray()->all(),
            'id',
            'business_name'
        );
        ?>

        <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
            'data' => $vendorList,
            'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
            'pluginOptions' => [
                'allowClear' => false
            ],
        ]); ?>
</div>



 <div class='col-lg-6'>    <?= $form->field($model, 'subject')->textInput(['maxlength' => true, 'placeholder' => 'Subject'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'message')->textarea(['rows' => 6])  ?> </div>
<div class='col-lg-6'>
    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions(), ['prompt' => 'Select Status']) ?>
</div>




 </div> <?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'SupportTicketsHasFiles')),
            'content' => $this->render('_formSupportTicketsHasFiles', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->supportTicketsHasFiles),
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