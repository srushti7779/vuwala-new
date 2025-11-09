<div class="form-group" id="add-vendor-details">
<?php
use kartik\grid\GridView;
use kartik\builder\TabularForm;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;

$dataProvider = new ArrayDataProvider([
    'allModels' => $row,
    'pagination' => [
        'pageSize' => -1
    ]
]);
echo TabularForm::widget([
    'dataProvider' => $dataProvider,
    'formName' => 'VendorDetails',
    'checkboxColumn' => false,
    'actionColumn' => false,
    'attributeDefaults' => [
        'type' => TabularForm::INPUT_TEXT,
    ],
    'attributes' => [
        "id" => ['type' => TabularForm::INPUT_HIDDEN, 'columnOptions' => ['hidden'=>true]],
        'user_id' => [
            'label' => 'User',
            'type' => TabularForm::INPUT_WIDGET,
            'widgetClass' => \kartik\widgets\Select2::className(),
            'options' => [
                'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\User::find()->orderBy('username')->asArray()->all(), 'id', 'username'),
                'options' => ['placeholder' => Yii::t('app', 'Choose User')],
            ],
            'columnOptions' => ['width' => '200px']
        ],
        'business_name' => ['type' => TabularForm::INPUT_TEXT],
        'description' => ['type' => TabularForm::INPUT_TEXTAREA],
        'main_category_id' => [
            'label' => 'Main category',
            'type' => TabularForm::INPUT_WIDGET,
            'widgetClass' => \kartik\widgets\Select2::className(),
            'options' => [
                'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\MainCategory::find()->orderBy('title')->asArray()->all(), 'id', 'title'),
                'options' => ['placeholder' => Yii::t('app', 'Choose Main category')],
            ],
            'columnOptions' => ['width' => '200px']
        ],
        'website_link' => ['type' => TabularForm::INPUT_TEXT],
        'gst_number' => ['type' => TabularForm::INPUT_TEXT],
        'latitude' => ['type' => TabularForm::INPUT_TEXT],
        'longitude' => ['type' => TabularForm::INPUT_TEXT],
        'coordinates' => ['type' => TabularForm::INPUT_TEXT],
        'address' => ['type' => TabularForm::INPUT_TEXTAREA],
        'logo' => ['type' => TabularForm::INPUT_TEXT],
        'shop_licence_no' => ['type' => TabularForm::INPUT_TEXT],
        'avg_rating' => ['type' => TabularForm::INPUT_TEXT],
        'min_order_amount' => ['type' => TabularForm::INPUT_TEXT],
        'commission_type' => ['type' => TabularForm::INPUT_TEXT],
        'commission' => ['type' => TabularForm::INPUT_TEXT],
        'offer_tag' => ['type' => TabularForm::INPUT_TEXT],
        'service_radius' => ['type' => TabularForm::INPUT_TEXT],
        'min_service_fee' => ['type' => TabularForm::INPUT_TEXT],
        'discount' => ['type' => TabularForm::INPUT_TEXT],
        'is_top_shop' => ['type' => TabularForm::INPUT_CHECKBOX,
            'options' => [
                'style' => 'position : relative; margin-top : -9px'
            ]
        ],
        'gender_type' => ['type' => TabularForm::INPUT_TEXT],
        'status' => ['type'=>TabularForm::INPUT_DROPDOWN_LIST, 
            'items'=>[1 => 'Active', 0 => 'In Active', 2=>'Delete'],
            'columnOptions'=>['width'=>'185px']],
        'service_type_home_visit' => ['type' => TabularForm::INPUT_CHECKBOX,
            'options' => [
                'style' => 'position : relative; margin-top : -9px'
            ]
        ],
        'service_type_walk_in' => ['type' => TabularForm::INPUT_CHECKBOX,
            'options' => [
                'style' => 'position : relative; margin-top : -9px'
            ]
        ],
        'del' => [
            'type' => 'raw',
            'label' => '',
            'value' => function($model, $key) {
                return
                    Html::hiddenInput('Children[' . $key . '][id]', (!empty($model['id'])) ? $model['id'] : "") .
                    Html::a('<i class="fa fa-trash"></i>', '#', ['title' =>  Yii::t('app', 'Delete'), 'onClick' => 'delRowVendorDetails(' . $key . '); return false;', 'id' => 'vendor-details-del-btn']);
            },
        ],
    ],
    'gridSettings' => [
        'panel' => [
            'heading' => false,
            'type' => GridView::TYPE_DEFAULT,
            'before' => false,
            'footer' => false,
            'after' => Html::button('<i class="fa fa-plus"></i>' . Yii::t('app', 'Add Vendor Details'), ['type' => 'button', 'class' => 'btn btn-success kv-batch-create', 'onClick' => 'addRowVendorDetails()']),
        ]
    ]
]);
echo  "    </div>\n\n";
?>

