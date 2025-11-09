<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BannerChargeLogs */

$this->title = Yii::t('app', 'Create Banner Charge Logs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Banner Charge Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="banner-charge-logs-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
