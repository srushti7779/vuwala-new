<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\UOMHierarchy */

$this->title = Yii::t('app', 'Create Uom Hierarchy');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Uom Hierarchies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="uomhierarchy-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
