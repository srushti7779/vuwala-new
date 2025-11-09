<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\HomeVisitors */

$this->title = Yii::t('app', 'Create Home Visitors');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Home Visitors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="home-visitors-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
