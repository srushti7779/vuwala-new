<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Staff */

$this->title = Yii::t('app', 'Create Staff');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Staff'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="staff-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('store_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
