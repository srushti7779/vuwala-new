<?php

use app\modules\admin\models\Courses;
use app\modules\admin\models\Subjects;
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
                    'user_role',
                    [
                        'attribute' => 'id',

                    ],
                    [
                        'attribute' => 'permission',

                        "format" => 'raw',
                        'value' => function ($data) {
                            $date = $data->getPermissionStatusBadges();

                            return $date;
                        }
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

                if (!empty($providerUserAssignSubjects)) {
                    if ($providerUserAssignSubjects->totalCount) {
                        $gridUserAssignCourses = [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute'  => 'course_id',
                                'value'  => function ($data) {
                                    $course = Courses::find()->where(['id' => $data->course_id])->one();
                                    return $course->name . ' ('. $course->plan->name .')';
                                }
                            ],
                            [
                                'attribute'  => 'subject_id',
                                'value'  => function ($data) {
                                    $subject = Subjects::find()->where(['id' => $data->subject_id])->one();
                                    return $subject->name;
                                }
                            ],
                        ];
                        echo Gridview::widget([
                            'dataProvider' => $providerUserAssignSubjects,
                            'pjax' => false,
                            'panel' => [
                                'type' => GridView::TYPE_PRIMARY,
                                'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Subadmin Courses')),
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