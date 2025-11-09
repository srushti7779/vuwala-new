<?php
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;

    $dataProvider = new ArrayDataProvider([
        'allModels' => $model->uOMHierarchies,
        'key' => 'id'
    ]);
    $gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],
        ['attribute' => 'id', 'visible' => false],
        [
                'attribute' => 'units.id',
                'label' => Yii::t('app', 'Units')
            ],
        'quantity',
        [
                'attribute' => 'ofUnits.id',
                'label' => Yii::t('app', 'Of Units')
            ],
        'status',
        'created_on',
        'updated_on',
        [
                'attribute' => 'createUser.username',
                'label' => Yii::t('app', 'Create User')
            ],
        [
                'attribute' => 'updateUser.username',
                'label' => Yii::t('app', 'Update User')
            ],
        [
            'class' => 'yii\grid\ActionColumn',
            'controller' => 'uomhierarchy'
        ],
    ];
    
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'containerOptions' => ['style' => 'overflow: auto'],
        'pjax' => true,
        'beforeHeader' => [
            [
                'options' => ['class' => 'skip-export']
            ]
        ],
        'export' => [
            'fontAwesome' => true
        ],
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'showPageSummary' => false,
        'persistResize' => false,
    ]);
