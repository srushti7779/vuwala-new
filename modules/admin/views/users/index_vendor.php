<?php

    /* @var $this yii\web\View */
    /* @var $dataProvider yii\data\ActiveDataProvider */

    use app\models\user;
    use app\modules\admin\models\VendorDetails;
    use kartik\export\ExportMenu;
    use kartik\grid\GridView;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use yii\helpers\Url;

    // echo '<pre>';
    // var_dump($role); 
    // echo '<pre>';
    // die();

    $this->title = 'Vendor';

    $this->params['breadcrumbs'][] = $this->title;
    $search                        = "$('.search-button').click(function(){
    $('.search-form').toggle(1000);
    return false;
});";

    $js = <<< JS
    function sendRequest(status, id){
       if(status == true){
           val = 1;
       }else{
           val = 0;
       }
        $.ajax({
            url:'users/update-status',
            method:'post',
            data:{val:val,id:id},
            success:function(data){
              alert('status updated');
            },
            error:function(jqXhr,status,error){
                alert(error);
            }
        });
    }
JS;

    $this->registerJs($search);
?>
<div class="user-index">


     <p>
         <?php
             echo Html::a('Create Vendor', ['create-vendor'], ['class' => 'btn btn-success']);

         ?>
    </p>
    <?php
        $gridColumn = [
            ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],

            [
                'attribute'           => 'username',
                'label'               => Yii::t('app', 'Username'),
                'value'               => function ($model) {
                    return $model->username;
                },
                'filterType'          => GridView::FILTER_SELECT2,
                'filter'              => ArrayHelper::map(
                    User::find()
                        ->where(['user_role' => 'vendor'])
                        ->orderBy('id')->asArray()->all(),
                    'username', 'username'
                ),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions'  => ['placeholder' => 'User', 'id' => 'grid-staff-search-user_id'],
            ],
            [
                'attribute'           => 'business_name',
                'label'               => 'Business Name',
                'value'               => function ($model) {
                    $vendor = VendorDetails::find()
                        ->where(['user_id' => $model->id])
                        ->one();
                    return $vendor->business_name ?? '-';
                },
                'filterType'          => GridView::FILTER_SELECT2,
                'filter'              => ArrayHelper::map(
                    \app\modules\admin\models\VendorDetails::find()
                        ->select(['business_name'])
                        ->where(['IS NOT', 'business_name', null])
                        ->andWhere(['<>', 'business_name', ''])
                        ->distinct()
                        ->orderBy('business_name')
                        ->asArray()
                        ->all(),
                    'business_name',
                    'business_name'
                ),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions'  => ['placeholder' => 'Business Name'],
            ],

            [
                'attribute'           => 'location_name',
                'label'               => 'Location Name',
                'value'               => function ($model) {
                    $vendor = VendorDetails::find()
                        ->where(['user_id' => $model->id])
                        ->one();
                    return $vendor->location_name ?? '-';
                },
                'filterType'          => GridView::FILTER_SELECT2,
                'filter'              => ArrayHelper::map(
                    VendorDetails::find()
                        ->select(['location_name'])
                        ->where(['IS NOT', 'location_name', null])
                        ->andWhere(['<>', 'location_name', ''])
                        ->distinct()
                        ->orderBy('location_name')
                        ->asArray()
                        ->all(),
                    'location_name',
                    'location_name'
                ),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions'  => ['placeholder' => 'Select Location Name'],
            ],

            [
                'attribute' => 'email',
                'label'     => Yii::t('app', 'email'),
                'value'     => function ($model) {
                    return $model->email;
                },
            ],

            [
                'attribute' => 'first_name',
                'label'     => Yii::t('app', 'first name'),
                'value'     => function ($model) {
                    return $model->first_name;
                },
            ],

            // [
            //     'attribute' => 'last_name',
            //     'label' => Yii::t('app', 'last name'),
            //     'value' => function ($model) {
            //         return $model->last_name;
            //     },
            // ],

            // [
            //     'attribute' => 'date_of_birth',
            //     'label' => Yii::t('app', 'date of birth'),
            //     'value' => function ($model) {
            //         return $model->date_of_birth;
            //     },
            // ],

            // [
            //     'attribute' => 'gender',
            //     'label' => Yii::t('app', 'gender'),
            //     'value' => function ($model) {
            //         return $model->gender;
            //     },
            // ],

            [
                'attribute' => 'contact_no',
                'label'     => Yii::t('app', 'contact no'),
                'value'     => function ($model) {
                    return $model->contact_no;
                },
            ],

            [
                'attribute' => 'user_role',
                'label'     => Yii::t('app', 'user role'),
                'value'     => function ($model) {
                    return $model->user_role;
                },
            ],

            [
                'attribute' => 'status',
                'filter'    => \app\models\User::getStatusesList(),
                "format"    => 'raw',
                'value'     => function ($data) {
                    $html = '';

                    $html .= '<select id="status_list_' . $data->id . '" data-id="' . $data->id . '" >';
                    $lists = $data->getStatusesList();

                    foreach ($lists as $key => $list) {
                        if ($key == $data->status) {
                            $html .= '<option value="' . $key . '" selected>' . $list . '</option>';
                        } else {
                            $html .= '<option value="' . $key . '">' . $list . '</option>';
                        }
                    }
                    $html .= '</select>';

                    return $html;
                },
            ],
            [
                'class'      => 'kartik\grid\ActionColumn',
                'template'   => '{view} {update} {delete} {transfer}',
                'buttons'    => [
                    'transfer' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fas fa-share-square"></i>',
                            ['/admin/users/transfer-data', 'id' => $model->id],
                            [
                                'title'        => 'Transfer Vendor',
                                'data-confirm' => 'Are you sure you want to transfer this vendor to DB2?',
                                'data-method'  => 'post',
                                'class'        => 'btn btn-sm btn-warning',
                            ]
                        );
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'view') {
                        return $model->user_role === 'vendor'
                        ? Url::to(['users/vendor-view', 'id' => $model->id])
                        : Url::to(['users/view', 'id' => $model->id]);
                    } elseif ($action === 'transfer') {
                        return Url::to(['users/transfer-data', 'id' => $model->id]);
                    } elseif ($action === 'update') {
                        return Url::to(['users/update-vendor', 'id' => $model->id]);
                    }

                    return Url::to(["users/{$action}", 'id' => $model->id]);
                },
            ],

        ];
    ?>
    <?php echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => $gridColumn,
    'filterModel'  => $searchModel,

    'pjax'         => true,
    'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-user']],
    'panel'        => [
        'type'    => GridView::TYPE_PRIMARY,
        'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
    ],
    'export'       => false,
    // your toolbar can include the additional full export menu
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
]);?>

</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    $(document).on('change', 'select[id^=status_list_]', function() {
        var id = $(this).attr('data-id');
        var val = $(this).val();
        $.ajax({
            type: "POST",

            url: "<?php echo Url::toRoute(['users/status-change'])?>",


            data: {
                id: id,
                val: val
            },
            success: function(data) {
                swal("Good job!", "Status Successfully Changed!", "success");
            }
        });
    });
</script>