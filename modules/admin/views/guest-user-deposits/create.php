<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\GuestUserDeposits */

$this->title = Yii::t('app', 'Create Guest User Deposits');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Guest User Deposits'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="guest-user-deposits-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
