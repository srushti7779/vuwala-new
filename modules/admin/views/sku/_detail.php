<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Sku */

?>
<div class="sku-view">

    <div class="row">
        <div class="col-sm-9">
            <h2><?= Html::encode($model->id) ?></h2>
        </div>
    </div>

    <div class="row">
<?php 
    $gridColumn = [
        ['attribute' => 'id', 'visible' => false],
        [
            'attribute' => 'vendorDetails.id',
            'label' => Yii::t('app', 'Vendor Details'),
        ],
        'sku_code',
        'product_name',
        'brand_id',
        'ean_code',
        [
            'attribute' => 'category.title',
            'label' => Yii::t('app', 'Category'),
        ],
        [
            'attribute' => 'storeServiceType.id',
            'label' => Yii::t('app', 'Store Service Type'),
        ],
        [
            'attribute' => 'productType.id',
            'label' => Yii::t('app', 'Product Type'),
        ],
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