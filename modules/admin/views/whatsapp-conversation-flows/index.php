<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\WhatsappConversationFlowsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
use app\models\User;

$this->title = Yii::t('app', 'Whatsapp Conversation Flows');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("
    $('.search-button').click(function(){
        $('.search-form').toggle(1000);
        return false;
    });
");

?>

<div class="whatsapp-conversation-flows-index">
    <div class="card">
        <div class="card-body">
            <p>
                <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN): ?>
                    <?= Html::a(Yii::t('app', 'Create Whatsapp Conversation Flows'), ['create'], ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
                <?= Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']) ?>
            </p>
            <div class="search-form" style="display:none">
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

<?php
// GridView columns with formatters/HTML
$gridColumn = [
    ['class' => 'yii\grid\SerialColumn'],
    ['attribute' => 'id', 'visible' => false],
    'language',
    'state',
    'pattern',
    'response_text:ntext',
    'response_interactive:ntext',
    'next_state',
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => function($model) {
            return $model->getStateOptionsBadges();
        },
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update} {delete}',
        'buttons' => [
            'view' => function ($url, $model) {
                if (in_array(Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_MANAGER])) {
                    return Html::a('<span class="fas fa-eye" aria-hidden="true"></span>', $url);
                }
            },
            'update' => function ($url, $model) {
                if (in_array(Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_MANAGER])) {
                    return Html::a('<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);
                }
            },
            'delete' => function ($url, $model) {
                if (in_array(Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN])) {
                    return Html::a('<span class="fas fa-trash-alt" aria-hidden="true"></span>', $url, [
                        'data' => [
                            'method' => 'post',
                            'confirm' => 'Are you sure?',
                        ],
                    ]);
                }
            },
        ],
    ],
];

// Export-safe version without raw HTML/closures
$exportColumns = [
    ['class' => 'yii\grid\SerialColumn'],
    ['attribute' => 'id', 'visible' => false],
    'language',
    'state',
    'pattern',
    'response_text:ntext',
    'response_interactive:ntext',
    'next_state',
    [
        'attribute' => 'status',
        'value' => function($model) {
            // Customize logic as needed
            return $model->status == 1 ? 'Active' : 'Inactive';
        },
    ],
];
$gridColumn = [
    ['class' => 'yii\grid\SerialColumn'],
    'id',
    [
        'attribute' => 'response_text',
        'format' => 'ntext',
        'value' => function ($model) {
            return is_array($model->response_text)
                ? json_encode($model->response_text, JSON_PRETTY_PRINT)
                : $model->response_text;
        },
    ],
    [
        'attribute' => 'response_interactive',
        'format' => 'ntext',
        'value' => function ($model) {
            return is_array($model->response_interactive)
                ? json_encode($model->response_interactive, JSON_PRETTY_PRINT)
                : $model->response_interactive;
        },
    ],
   
];


$exportColumns = $gridColumn;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumn,
    'pjax' => true,
    'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-whatsapp-conversation-flows']],
    'panel' => [
        'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
    ],
    'export' => false,
    'toolbar' => [
        '{export}',
        ExportMenu::widget([
            'dataProvider' => $dataProvider,
            'columns' => $exportColumns,
            'target' => ExportMenu::TARGET_BLANK,
            'fontAwesome' => true,
            'dropdownOptions' => [
                'label' => 'Full',
                'class' => 'btn btn-default',
                'itemsBefore' => [
                    '<li class="dropdown-header">Export All Data</li>',
                ],
            ],
            'exportConfig' => [
                ExportMenu::FORMAT_PDF => false,
            ]
        ]),
    ],
]);


?>
        </div>
    </div>
</div>

<!-- SweetAlert & Status Change Script -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
$(document).on('change', 'select[id^=status_list_]', function () {
    var id = $(this).attr('data-id');
    var val = $(this).val();
    $.ajax({
        type: "POST",
        url: "/estetica_back_end/gii/default/status-change",
        data: {id: id, val: val},
        success: function (data) {
            swal("Good job!", "Status Successfully Changed!", "success");
        }
    });
});
</script>
