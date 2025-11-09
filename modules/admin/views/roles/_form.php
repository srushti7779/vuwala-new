<?php

use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Roles */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'RoleMenuPermissions', 
        'relID' => 'role-menu-permissions', 
        'value' => \yii\helpers\Json::encode($model->roleMenuPermissions),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'UserRoles', 
        'relID' => 'user-roles', 
        'value' => \yii\helpers\Json::encode($model->userRoles),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="roles-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>




    <?= $form->field($model, 'role_name')->textInput(['maxlength' => true, 'placeholder' => 'Role Name']) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true, 'placeholder' => 'Description']) ?>

    <?= $form->field($model, 'status')->checkbox() ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
