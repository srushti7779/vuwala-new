<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ComboPackagesCart */

$this->title = Yii::t('app', 'Create Combo Packages Cart');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Combo Packages Carts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="combo-packages-cart-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
