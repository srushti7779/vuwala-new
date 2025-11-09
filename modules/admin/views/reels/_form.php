<?php

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Reels */
/* @var $form yii\widgets\ActiveForm */

// JS Blocks for related models
foreach (['ReelShareCounts', 'ReelTags', 'ReelsLikes', 'ReelsViewCounts'] as $relClass) {
    \mootensai\components\JsBlock::widget([
        'viewFile' => '_script',
        'pos' => \yii\web\View::POS_END,
        'viewParams' => [
            'class' => $relClass,
            'relID' => strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $relClass)),
            'value' => \yii\helpers\Json::encode($model->{lcfirst($relClass)}),
            'isNewRecord' => $model->isNewRecord ? 1 : 0,
        ],
    ]);
}

$user = Yii::$app->user->identity;
$query = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE]);
if ($user->user_role === User::ROLE_VENDOR) {
    $query->andWhere(['user_id' => $user->id]);
}
$vendorList = ArrayHelper::map($query->orderBy('id')->asArray()->all(), 'id', 'business_name');

?>

<div class="reels-form">
    <?php $form = ActiveForm::begin([
        'id' => 'reels-form',
        'type' => ActiveForm::TYPE_VERTICAL,
        'options' => ['enctype' => 'multipart/form-data'],
        'fieldConfig' => ['options' => ['class' => 'form-group col-lg-12']],
    ]); ?>

    <?= $form->errorSummary($model); ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>

    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => $vendorList,
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor')],
        'pluginOptions' => ['allowClear' => false],
    ]) ?>

    <!-- Video Upload -->
<div class="col-lg-6">
    <?= $form->field($model, 'video')->widget(\kartik\file\FileInput::classname(), [
        'options' => ['accept' => 'video/*'],
        'pluginOptions' => [
            'previewFileType' => 'video',
            'initialPreview' => $model->video ? [Yii::getAlias('@web') . '/' . $model->video] : [],
            'initialPreviewAsData' => true,
            'overwriteInitial' => true,
            'showUpload' => false,
        ],
    ]) ?>
</div>

<!-- Thumbnail Upload -->
<div class="col-lg-6">
    <?= $form->field($model, 'thumbnail')->widget(\kartik\file\FileInput::classname(), [
        'options' => ['accept' => 'image/*'],
        'pluginOptions' => [
            'previewFileType' => 'image',
            'initialPreview' => $model->thumbnail ? [Yii::getAlias('@web') . '/' . $model->thumbnail] : [],
            'initialPreviewAsData' => true,
            'overwriteInitial' => true,
            'showUpload' => false,
        ],
    ]) ?>
</div>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title']) ?>

    <?= $form->field($model, 'description')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => ['preset' => 'full', 'inline' => false],
    ]) ?>

    <?= $form->field($model, 'view_count')->textInput(['type' => 'number', 'placeholder' => 'View Count']) ?>
    <?= $form->field($model, 'share_count')->textInput(['type' => 'number', 'placeholder' => 'Share Count']) ?>
    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <!-- Tabs Section (if used for related models) -->
    <?= \kartik\tabs\TabsX::widget([
        'position' => \kartik\tabs\TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'pluginOptions' => ['bordered' => true, 'sideways' => true, 'enableCache' => false],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
