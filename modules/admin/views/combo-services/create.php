<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ComboServices */

$this->title = Yii::t('app', 'Create Combo Services');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Combo Services'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="combo-services-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
