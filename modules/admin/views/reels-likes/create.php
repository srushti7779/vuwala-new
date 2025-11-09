<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ReelsLikes */

$this->title = Yii::t('app', 'Create Reels Likes');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reels Likes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reels-likes-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
