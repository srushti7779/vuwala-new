<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorHasMenus */

$this->title = Yii::t('app', 'Create Vendor Has Menus');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Has Menuses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vendor-has-menus-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
