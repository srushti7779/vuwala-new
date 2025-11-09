<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Expensive */

$this->title = Yii::t('app', 'Create Expensive');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Expensives'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expensive-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
