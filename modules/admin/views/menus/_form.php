<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Menus */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'MenuPermissions', 
        'relID' => 'menu-permissions', 
        'value' => \yii\helpers\Json::encode($model->menuPermissions),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="menus-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'placeholder' => 'Label']) ?>

    <?= $form->field($model, 'route')->textInput(['maxlength' => true, 'placeholder' => 'Route']) ?>

    <?= $form->field($model, 'icon')->textInput(['maxlength' => true, 'placeholder' => 'Icon']) ?>

    <?= $form->field($model, 'parent_id')->textInput(['placeholder' => 'Parent']) ?>

    <?= $form->field($model, 'sort_order')->textInput(['placeholder' => 'Sort Order']) ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <?php
    $forms = [
        [
            'label' => '<i class="glyphicon glyphicon-book"></i> ' . Html::encode(Yii::t('app', 'MenuPermissions')),
            'content' => $this->render('_formMenuPermissions', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->menuPermissions),
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
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
