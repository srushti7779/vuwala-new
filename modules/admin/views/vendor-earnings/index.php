<?php
/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\VendorEarningsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use kartik\grid\GridView;

$this->title = Yii::t('app', 'Vendor Earnings');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="vendor-earnings-index">
    <div class="card">
        <div class="card-body">
            <div class="search-form" style="display:none">
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- ✅ New Button -->
            <div class="mb-3">
                <?= Html::button('Store Settlements', [
                    'class' => 'btn btn-warning',
                    'id' => 'store-selected-btn'
                ]) ?>
            </div>

            <?php
            $gridColumn = [
                [
                    'class' => 'kartik\grid\CheckboxColumn',
                    'checkboxOptions' => function ($model) {
                        if (strtolower($model->status) === 'approved' || $model->status == 1) {
                            return ['value' => $model->id, 'class' => 'select-checkbox'];
                        }
                        return ['disabled' => true];
                    },
                ],
                ['class' => 'yii\grid\SerialColumn'],
                ['attribute' => 'id', 'visible' => false],

                [
                    'attribute' => 'order_id',
                    'label' => Yii::t('app', 'Order'),
                    'value' => function ($model) {
                        return $model->order ? $model->order->id : Yii::t('app', 'No Order');
                    },
                ],

                [
                    'attribute' => 'vendor_details_id',
                    'label' => Yii::t('app', 'Vendor Username'),
                    'value' => function ($model) {
                        return $model->vendorDetails && $model->vendorDetails->user
                            ? $model->vendorDetails->user->username
                            : null;
                    },
                ],

                [
                    'attribute' => 'vendor_details_id',
                    'label' => Yii::t('app', 'Business Name'),
                    'value' => function ($model) {
                        return $model->vendorDetails->business_name;
                    },
                ],

                'order_sub_total',

                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    },
                ],
            ];
            ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumn,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-vendor-earnings']],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
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
                            ExportMenu::FORMAT_PDF => false
                        ]
                    ]),
                ],
            ]); ?>
        </div>
    </div>
</div>

<!-- SweetAlert -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
function storeApproved(ids) {
    if (ids.length === 0) {
        swal("Oops!", "No records selected.", "error");
        return;
    }

    swal({
        title: "Are you sure?",
        text: "You are about to store settlements for " + ids.length + " record(s).",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willSave) => {
        if (willSave) {
            $.ajax({
                type: "POST",
                url: "<?= \yii\helpers\Url::to(['/admin/vendor-earnings/store-approved']) ?>",
                data: {
                    ids: ids,
                    _csrf: yii.getCsrfToken()
                },
                success: function (data) {
                    if (data.success) {
                        swal("Success", data.message, "success").then(() => {
                            $.pjax.reload({container:"#kv-pjax-container-vendor-earnings"});
                        });
                    } else {
                        swal("Oops!", data.message, "error");
                    }
                },
                error: function () {
                    swal("Error!", "Something went wrong!", "error");
                }
            });
        }
    });
}

// ✅ Single checkbox click flow
$(document).on('change', '.select-checkbox', function() {
    var clickedBox = $(this);
    var ids = [clickedBox.val()];

    if (clickedBox.is(":checked")) {
        storeApproved(ids);
    }
});

// ✅ Button click flow (multi/single both)
$(document).on('click', '#store-selected-btn', function() {
    var ids = $('.select-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    storeApproved(ids);
});
</script>
