<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\OrderComplaints */

$this->title = Yii::t('app', 'Create Order Complaints');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Order Complaints'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-complaints-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
