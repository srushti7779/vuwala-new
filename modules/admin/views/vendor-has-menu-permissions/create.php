<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorHasMenuPermissions */

$this->title = Yii::t('app', 'Create Vendor Has Menu Permissions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Has Menu Permissions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vendor-has-menu-permissions-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
