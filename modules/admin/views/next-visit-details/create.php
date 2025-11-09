<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\NextVisitDetails */

$this->title = Yii::t('app', 'Create Next Visit Details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Next Visit Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="next-visit-details-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
