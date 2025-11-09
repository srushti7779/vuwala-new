<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ServiceType */

$this->title = Yii::t('app', 'Create Service Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Service Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-type-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
