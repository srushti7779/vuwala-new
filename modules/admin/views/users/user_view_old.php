<?php

use app\modules\admin\models\base\Category;
use app\modules\admin\models\base\Courses;
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Users */

//$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-9">
                    <h2><?= Yii::t('app', 'Users') . ' ' . Html::encode($this->title) ?></h2>
                </div>
                <div class="col-sm-3" style="margin-top: 15px">

                </div>
            </div> 
            <div class="row">
                <?php
                $gridColumn = [
                    ['attribute' => 'id', 'visible' => false],
                    'username',
                    'email',
                    'full_name',
                    'last_name',
                    'contact_no',
                    'user_role',
                    'date_of_birth',
                    [
                        'attribute' => 'id',

                    ],
                ];
                echo DetailView::widget([
                    'model' => $model,
                    'attributes' => $gridColumn
                ]);
                ?>
            </div>
        </div>
    </div>

    <?php ?>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <?php
                // var_dump($providerUserAssignSubjects);exit;

                if (!empty($userCourses)) {
                    if ($userCourses->totalCount) {
                        $gridUserAssignCourses = [
                            ['class' => 'yii\grid\SerialColumn'],

                            [
                                'attribute' => 'category_id',
                                'label' => Yii::t('app', 'Category'),
                                'value' => function ($model) {
                                    return $model->category->title ?? "";
                                },

                            ],

                            [
                                'attribute' => 'plan_id',
                                'label' => Yii::t('app', 'Plans'),
                                'value' => function ($model) {
                                    return $model->plan->name ?? "";
                                },


                            ],



                            [
                                'attribute' => 'course_id',
                                'label' => Yii::t('app', 'Course'),
                                'value' => function ($model) {
                                    return $model->course->name ?? "";
                                },

                            ],


                            [
                                'attribute' => 'payment_status',
                                'format' => 'raw',

                                'value' => function ($model) {
                                    return $model->getPaymentStatusOptionsBadges() ?? "";
                                }
                            ],
                            [
                                'attribute' => 'validity',
                                'label' => Yii::t('app', 'Valibity'),
                                'value' => function ($model) {
                                    return $model->course->validity ?? "";
                                },

                            ],
                            'price',


                            [
                                'attribute' => 'created_on',
                                'label' => 'Payment Date',
                                'value' => function ($model) {
                                    return $model->created_on ?? "";
                                }
                            ],


                        ];
                        echo Gridview::widget([
                            'dataProvider' => $userCourses,
                            'pjax' => false,
                            'panel' => [
                                'type' => GridView::TYPE_PRIMARY,
                                'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Enrolled Courses')),
                            ],
                            'export' => false,
                            'columns' => $gridUserAssignCourses
                        ]);
                    }
                }


                ?>
            </div>
        </div>
    </div>
</div>