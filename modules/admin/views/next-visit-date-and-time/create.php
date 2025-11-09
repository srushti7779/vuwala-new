<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\NextVisitDateAndTime */

$this->title = Yii::t('app', 'Create Next Visit Date And Time');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Next Visit Date And Times'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="next-visit-date-and-time-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
