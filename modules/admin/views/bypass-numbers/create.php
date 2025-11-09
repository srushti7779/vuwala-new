<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BypassNumbers */

$this->title = Yii::t('app', 'Create Bypass Numbers');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Bypass Numbers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bypass-numbers-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
