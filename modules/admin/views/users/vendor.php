<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\modules\admin\models\User;
use yii\helpers\Html;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
use yii\helpers\Url;

$this->title = 'Vendor';
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
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
        <?= Html::a('Create Vendor', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php
    $gridColumn = [
        ['class' => 'yii\grid\SerialColumn'],
        ['attribute' => 'id', 'visible' => false],

        [
            'attribute' => 'username',
            'label' => Yii::t('app', 'username'),
            'value' => function($model){
             return $model->username;
                        },
        ],


        
        [
            'attribute' => 'email',
            'label' => Yii::t('app', 'email'),
            'value' => function($model){
             return $model->email;
                        },
        ],



        [
            'attribute' => 'first_name',
            'label' => Yii::t('app', 'first name'),
            'value' => function($model){
             return $model->first_name;
                        },
        ],


        [
            'attribute' => 'last_name',
            'label' => Yii::t('app', 'last name'),
            'value' => function($model){
             return $model->last_name;
                        },
        ],







        [
            'attribute' => 'contact_no',
            'label' => Yii::t('app', 'contact no'),
            'value' => function($model){
             return $model->contact_no;
                        },
        ],

        [
            'attribute' => 'user_role',
            'label' => Yii::t('app', 'user role'),
            'value' => function($model){
             return $model->user_role;
                        },
        ],




       
 
    
     
    
 


        [
            'attribute' => 'status',
            'filter'  =>  \app\models\User::getStatusesList(),
            "format" => 'raw',
            'value' => function ($data) {
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
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
             'template' => '{view} {update} {delete}',
             'buttons' => [
            'view'=> function($url,$model) {
            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                    return Html::a( '<span class="fas fa-eye" aria-hidden="true"></span>', $url);
                } 
                },
            'update'=> function($url,$model) {
            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                    return Html::a( '<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);

                } 
                },
            'delete'=> function($url,$model) {
                $url = Url::toRoute(['vendor-delete','id'=>$model->id]);

            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) {
                    return Html::a( '<span class="fas fa-trash-alt" aria-hidden="true"></span>', $url,[
                        'data' => [
                                    'method' => 'post',
                                     // use it if you want to confirm the action
                                     'confirm' => 'Are you sure?',
                                 ],
                                ]);
                } 
                },


        ]
            
           

        ],
    ];
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumn,
        'filterModel'=>$searchModel,

        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-user']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
        ],
        'export' => false,
        // your toolbar can include the additional full export menu
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

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    $(document).on('change', 'select[id^=status_list_]', function() {
        var id = $(this).attr('data-id');
        var val = $(this).val();
        $.ajax({
            type: "POST",

            url: "<?= Url::toRoute(['users/status-change']) ?>",


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