<div class="form-group" id="add-support-tickets-has-files">
<?php
use kartik\grid\GridView;
use kartik\builder\TabularForm;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\file\FileInput;

$dataProvider = new ArrayDataProvider([
    'allModels' => $row,
    'pagination' => [
        'pageSize' => -1
    ]
]);
echo TabularForm::widget([
    'dataProvider' => $dataProvider,
    'formName' => 'SupportTicketsHasFiles',
    'checkboxColumn' => false,
    'actionColumn' => false,
    'attributeDefaults' => [
        'type' => TabularForm::INPUT_TEXT,
    ],
    'attributes' => [
        "id" => ['type' => TabularForm::INPUT_HIDDEN, 'columnOptions' => ['hidden'=>true]],
       'file' => [
    'type' => TabularForm::INPUT_WIDGET,
    'widgetClass' => FileInput::class,
    'options' => function($model, $key) {
        return [
            'options' => [
                'name' => "SupportTicketsHasFiles[{$key}][file]",
                'accept' => '*',
            ],
            'pluginOptions' => [
                'showPreview' => true,
                'showUpload' => false,
                'showRemove' => true,
                'browseLabel' => 'Select File',
            ]
        ];
    },
],
        'status' => ['type'=>TabularForm::INPUT_DROPDOWN_LIST, 
            'items'=>[1 => 'Active', 0 => 'In Active', 2=>'Delete'],
            'columnOptions'=>['width'=>'185px']],
        'del' => [
            'type' => 'raw',
            'label' => '',
            'value' => function($model, $key) {
                return
                    Html::hiddenInput('Children[' . $key . '][id]', (!empty($model['id'])) ? $model['id'] : "") .
                    Html::a('<i class="fa fa-trash"></i>', '#', ['title' =>  Yii::t('app', 'Delete'), 'onClick' => 'delRowSupportTicketsHasFiles(' . $key . '); return false;', 'id' => 'support-tickets-has-files-del-btn']);
            },
        ],
    ],
    'gridSettings' => [
        'panel' => [
            'heading' => false,
            'type' => GridView::TYPE_DEFAULT,
            'before' => false,
            'footer' => false,
            'after' => Html::button('<i class="fa fa-plus"></i>' . Yii::t('app', 'Add Support Tickets Has Files'), ['type' => 'button', 'class' => 'btn btn-success kv-batch-create', 'onClick' => 'addRowSupportTicketsHasFiles()']),
        ]
    ]
]);
echo  "    </div>\n\n";
?>

