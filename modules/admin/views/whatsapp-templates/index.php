<?php
/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\WhatsappTemplatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use app\models\User;
use kartik\grid\GridView;

$this->title = Yii::t('app', 'Whatsapp Templates');
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);
?>
<div class="whatsapp-templates-index">
<div class="card">
    <div class="card-body">
        <p>
            <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>
                <?= Html::a(Yii::t('app', 'Import Templates'), ['import'], ['class' => 'btn btn-primary', 'id' => 'import-templates-btn']) ?>
            <?php } ?>
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
$gridColumn = [
    ['class' => 'yii\grid\SerialColumn'],
    ['attribute' => 'id', 'visible' => false],
    'name',
    'language_code',
    'description:ntext',
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => function($model) {
            return $model->getStateOptionsBadges();
        },
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {delete}',
        'buttons' => [
            'view' => function($url, $model) {
                if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                    return Html::a('<span class="fas fa-eye" aria-hidden="true"></span>', $url);
                }
            },
            'update' => function($url, $model) {
                if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                    return Html::a('<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);
                }
            },
            'delete' => function($url, $model) {
                if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) {
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
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumn,
    'pjax' => true,
    'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-whatsapp-templates']],
    'panel' => [
        'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
    ],
    'export' => false,
    'toolbar' => [
        '{export}',
        ExportMenu::widget([
            'dataProvider' => $dataProvider,
            'columns' => $gridColumn,
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
            ],
        ]),
    ],
]); ?>
    </div>
</div>
</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
$(document).on('click', '#import-templates-btn', function(e) {
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: '<?= \yii\helpers\Url::to(['/admin/whatsapp-templates/import']) ?>',
        success: function(response) {
            if (response.success) {
                swal('Success!', response.message, 'success').then(function() {
                    window.location.reload();
                });
            } else {
                swal('Error!', response.message, 'error');
            }
        },
        error: function(xhr) {
            swal('Error!', 'Failed to import templates: ' + xhr.responseText, 'error');
        }
    });
});


</script>