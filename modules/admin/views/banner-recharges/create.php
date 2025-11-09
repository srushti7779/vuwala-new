<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BannerRecharges */

$this->title = Yii::t('app', 'Create Banner Recharges');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Banner Recharges'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="banner-recharges-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
