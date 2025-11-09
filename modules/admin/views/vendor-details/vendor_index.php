<?php

    use app\models\User;
    use app\modules\admin\models\VendorDetails;
    use kartik\editable\Editable;
    use kartik\export\ExportMenu;
    use kartik\grid\GridView;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use yii\helpers\Url;

    $this->title                   = Yii::t('app', 'Vendor Details');
    $this->params['breadcrumbs'][] = $this->title;

?>

<div class="vendor-details-index">
    <div class="card">
        <div class="card-body">
          <p>
    <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUB_ADMIN): ?>
        <?= Html::button('Delete vendors', [
            'class' => 'btn btn-danger',
            'id'    => 'delete-selected',
            'style' => 'margin-bottom:10px; margin-right:10px;',
        ]) ?>

        <?= Html::a(
            '<i class="fas fa-store"></i> Create Store',
            ['/admin/vendor-details/create-vendor', 'id' => $model->id ?? null],
            [
                'class' => 'btn btn-success',
                'title' => 'Create a new store for this vendor',
                'style' => 'margin-bottom:10px;',
            ]
        ) ?>
    <?php endif; ?>
</p>

    <div class="card">
        <div class="card-body">
            <?php echo Html::a('Insert Bulk Data', ['vendor-details/import'], ['class' => 'btn btn-primary']) ?>

            <?php
                $gridColumn = [
                    [
                        'class'           => 'yii\grid\CheckboxColumn',
                        'checkboxOptions' => function ($model) {
                            return ['value' => $model->id];
                        },
                    ],
                    ['class' => 'yii\grid\SerialColumn'],
                    ['attribute' => 'id', 'visible' => false],
                    [
                        'attribute'           => 'user_id',
                        'label'               => Yii::t('app', 'Vendor'),
                        'value'               => fn($model)               => $model->user->email ?? null,
                        'filterType'          => GridView::FILTER_SELECT2,
                        'filter'              => \yii\helpers\ArrayHelper::map(User::find()
                                ->where(['user_role' => User::ROLE_VENDOR])
                                ->asArray()->all(), 'id', 'email'),
                        'filterWidgetOptions' => [
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions'  => ['placeholder' => 'Vendor', 'id' => 'grid-vendor-details-search-user_id'],
                    ],
                    [
                        'attribute'           => 'contact_no',
                        'label'               => Yii::t('app', 'Contact No'),
                        'value'               => fn($model)               => $model->user->contact_no ?? null,
                        'filterType'          => GridView::FILTER_SELECT2,
                        'filter'              => \yii\helpers\ArrayHelper::map(
                            User::find()->select(['contact_no'])->where(['user_role' => User::ROLE_VENDOR])->distinct()->asArray()->all(),
                            'contact_no', 'contact_no'
                        ),
                        'filterWidgetOptions' => ['pluginOptions' => ['allowClear' => true]],
                        'filterInputOptions'  => ['placeholder' => 'Contact No', 'id' => 'grid-vendor-details-search-contact_no'],
                    ],
                    'business_name',

                    'location_name',

                    'address',
                    'avg_rating',
                 
                    [
                        'attribute'           => 'is_verified',
                        'label'               => Yii::t('app', 'Is Verified'),
                        'value'               => fn($model)               => $model->is_verified == 1 ? 'Yes' : 'No',
                        'filter'              => [1 => 'Yes', 0 => 'No'],
                        'filterType'          => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => ['pluginOptions' => ['allowClear' => true]],
                        'filterInputOptions'  => ['placeholder' => 'Verified?', 'id' => 'grid-vendor-details-search-is_verified'],
                    ],
                    'gst_number',
                    [
                        'attribute' => 'logo',
                        'format'    => 'raw',
                        'value'     => fn($model)     => empty($model->logo)
                        ? 'N/A'
                        : Html::img($model->logo, ['alt' => 'Image', 'style' => 'width: 80px; height:80px;']),
                    ],
                    'shop_licence_no',
                    'account_number',
                    'ifsc_code',
                    [
                        'attribute' => 'status',
                        'format'    => 'raw',
                        'value'     => fn($model)     => $model->getStateOptionsBadges(),
                        'filter'    => (new VendorDetails())->getStateOptions(),
                    ],
                    [
                        'class'    => 'kartik\grid\ActionColumn',
                        'template' => '{view} {update} {delete}',
                        'buttons'  => [
                            'view'   => fn($url, $model)   => Html::a('<span class="fas fa-eye"></span>', $url),
                            'update' => fn($url, $model) => Html::a('<span class="fas fa-pencil-alt"></span>', $url),
                            'delete' => fn($url, $model) => Html::a('<span class="fas fa-trash-alt"></span>', $url, [
                                'data' => [
                                    'method'  => 'post',
                                    'confirm' => 'Are you sure?',
                                ],
                            ]),
                        ],
                    ],
                ];
            ?>

            <?php echo GridView::widget([
                    'id'           => 'vendor-details-grid',
                    'dataProvider' => $dataProvider,
                    'filterModel'  => $searchModel,
                    'columns'      => $gridColumn,
                    'pjax'         => false,
                    'panel'        => [
                        'type'    => GridView::TYPE_PRIMARY,
                        'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode($this->title),
                    ],
                    'export'       => false,
                    'toolbar'      => [
                        '{export}',
                        ExportMenu::widget([
                            'dataProvider'    => $dataProvider,
                            'columns'         => $gridColumn,
                            'target'          => ExportMenu::TARGET_BLANK,
                            'fontAwesome'     => true,
                            'dropdownOptions' => [
                                'label'       => 'Full',
                                'class'       => 'btn btn-default',
                                'itemsBefore' => [
                                    '<li class="dropdown-header">Export All Data</li>',
                                ],
                            ],
                            'exportConfig'    => [
                                ExportMenu::FORMAT_PDF => false,
                            ],
                        ]),
                    ],
            ]); ?>
        </div>
    </div>
</div>

<!-- SweetAlert CDN -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php
    use yii\web\JsExpression;
    $deleteUrl   = Url::to(['/admin/vendor-details/soft-delete-multiple']);
    $redirectUrl = Url::to(['/admin/vendor-details/']);
?>

<script>
$(document).ready(function () {
    $('#delete-selected').on('click', function () {
        var keys = $('#vendor-details-grid').yiiGridView('getSelectedRows');

        if (keys.length === 0) {
            swal("Oops!", "Please select at least one record.", "warning");
            return;
        }

        swal({
            title: "Are you sure?",
            text: "Do you want to delete the selected vendors?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    type: 'POST',
                    url: '<?php echo $deleteUrl ?>',
                    data: {
                        ids: keys,
                        _csrf: csrfToken
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            swal("Deleted!", " Vendor Records deleted successfully!", "success")
                                .then(() => {
                                    window.location.href = '<?php echo $redirectUrl ?>';
                                });
                        } else {
                            swal("Error!", response.message || "Something went wrong.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', error);
                        swal("Error!", "AJAX request failed.", "error");
                    }
                });
            }
        });
    });
});
</script>
