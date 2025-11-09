<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\RoleMenuPermissions */

$this->title = Yii::t('app', 'Create Role Menu Permissions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Role Menu Permissions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="role-menu-permissions-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
