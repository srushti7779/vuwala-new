<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorMainCategoryData */

$this->title = Yii::t('app', 'Create Vendor Main Category Data');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Main Category Datas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vendor-main-category-data-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
