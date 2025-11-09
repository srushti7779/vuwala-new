<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Brand */

?>
<div class="brand-view">

    <div class="row">
        <div class="col-sm-9">
            <h2><?= Html::encode($model->id) ?></h2>
        </div>
    </div>

    <div class="row">
<?php 
    $gridColumn = [
        ['attribute' => 'id', 'visible' => false],
        'brand_name',
        'image',
        'is_global',
        'status',
        'created_on',
        'updated_on',
        [
            'attribute' => 'createUser.username',
            'label' => Yii::t('app', 'Create User'),
        ],
        [
            'attribute' => 'updateUser.username',
            'label' => Yii::t('app', 'Update User'),
        ],
    ];
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $gridColumn
    ]); 
?>
    </div>
</div>