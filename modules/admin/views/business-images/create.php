<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BusinessImages */

$this->title = Yii::t('app', 'Create Business Images');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Business Images'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="business-images-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'vendor_details_id'=>$vendor_details_id,

    ]) ?>
    </div>
    </div>
</div>
