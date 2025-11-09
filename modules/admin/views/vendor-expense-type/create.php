<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorExpenseType */

$this->title = Yii::t('app', 'Create Vendor Expense Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Expense Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vendor-expense-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
