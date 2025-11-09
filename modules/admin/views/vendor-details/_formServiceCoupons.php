<div class="form-group" id="add-service-coupons">
<?php
use kartik\grid\GridView;
use kartik\builder\TabularForm;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

$dataProvider = new ArrayDataProvider([
    'allModels' => $row,
    'pagination' => [
        'pageSize' => -1
    ]
]);

echo TabularForm::widget([
    'dataProvider' => $dataProvider,
    'formName' => 'ServiceHasCoupons',
    'checkboxColumn' => false,
    'actionColumn' => false,
    'attributeDefaults' => [
        'type' => TabularForm::INPUT_TEXT,
    ],
    'attributes' => [
        "id" => ['type' => TabularForm::INPUT_HIDDEN, 'columnOptions' => ['hidden'=>true]],
        'service_id' => ['type' => TabularForm::INPUT_TEXT],
        'coupon_id' => ['type' => TabularForm::INPUT_TEXT],
        'status' => [
            'type'=>TabularForm::INPUT_DROPDOWN_LIST, 
            'items'=>[1 => 'Active', 0 => 'Inactive', 2=>'Deleted'],
            'columnOptions'=>['width'=>'185px']
        ],
        'del' => [
            'type' => 'raw',
            'label' => '',
            'value' => function($model, $key) {
                return
                    Html::hiddenInput('Children[' . $key . '][id]', (!empty($model['id'])) ? $model['id'] : "") .
                    Html::a('<i class="fa fa-trash"></i>', '#', [
                        'title' => Yii::t('app', 'Delete'), 
                        'onClick' => 'delRowServiceHasCoupons(' . $key . '); return false;', 
                        'id' => 'service-coupon-del-btn'
                    ]);
            },
        ],
    ],
    'gridSettings' => [
        'panel' => [
            'heading' => false,
            'type' => GridView::TYPE_DEFAULT,
            'before' => false,
            'footer' => false,
            'after' => Html::button(
                '<i class="fa fa-plus"></i>' . Yii::t('app', 'Add Service Coupon'), 
                ['type' => 'button', 'class' => 'btn btn-success kv-batch-create', 'onClick' => 'addRowServiceHasCoupons()']
            ),
        ]
    ]
]);
echo "</div>\n";
?>
