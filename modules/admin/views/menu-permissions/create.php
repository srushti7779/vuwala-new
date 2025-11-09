<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\MenuPermissions */

$this->title = Yii::t('app', 'Create Menu Permissions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Menu Permissions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-permissions-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
