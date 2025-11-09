<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\ProductOrderItemsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-product-order-items-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'product_order_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\ProductOrders::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Product orders')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'product_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Products::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Products')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'quantity')->textInput(['placeholder' => 'Item Count']) ?>

    <?= $form->field($model, 'units')->textInput(['maxlength' => true, 'placeholder' => 'Units']) ?>

    <?php /* echo $form->field($model, 'mrp_price')->textInput(['placeholder' => 'Mrp Price']) */ ?>

    <?php /* echo $form->field($model, 'selling_price')->textInput(['placeholder' => 'Selling Price']) */ ?>

    <?php /* echo $form->field($model, 'sub_total')->textInput(['placeholder' => 'Sub Total']) */ ?>

    <?php /* echo $form->field($model, 'tax_percentage')->textInput(['placeholder' => 'Tax Percentage']) */ ?>

    <?php /* echo $form->field($model, 'tax_amount')->textInput(['placeholder' => 'Tax Amount']) */ ?>

    <?php /* echo $form->field($model, 'total_with_tax')->textInput(['placeholder' => 'Total With Tax']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
