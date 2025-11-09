<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Reels */

$this->title = Yii::t('app', 'Create Reels');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reels'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reels-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
