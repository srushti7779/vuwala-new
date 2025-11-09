<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\StoreServiceTypes */

$this->title = Yii::t('app', 'Create Store Service Types');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Store Service Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="store-service-types-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
