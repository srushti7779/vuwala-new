<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SupportTicketsHasFiles */

$this->title = Yii::t('app', 'Create Support Tickets Has Files');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Support Tickets Has Files'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="support-tickets-has-files-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
