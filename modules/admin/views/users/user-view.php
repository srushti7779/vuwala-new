<?php
use app\modules\admin\models\ComboOrder;
use app\modules\admin\models\Orders;
use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use kartik\tabs\TabsX as Bootstrap4Tabs;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];

$this->registerCss(<<<CSS
/* Modern Social Media Profile Styling */
.user-view {
    max-width: 1200px;
    margin: 0 auto;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.profile-header {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.cover-photo {
    height: 200px;
    background: linear-gradient(135deg, #3b5998, #8b9dc3);
    position: relative;
}

.profile-picture {
    position: absolute;
    bottom: -50px;
    left: 30px;
    border: 4px solid #fff;
    border-radius: 50%;
    width: 120px;
    height: 120px;
    background: #fff;
}

.profile-info {
    padding: 2rem 1.5rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.profile-details {
    margin-left: 160px;
}

.tab-content {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1.5rem;
}

.nav-tabs {
    border-bottom: 1px solid #e5e5e5;
    margin-bottom: 1.5rem;
}

.nav-tabs .nav-link {
    color: #333;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    color: #1877f2;
    border-bottom: 2px solid #1877f2;
    background: transparent;
}

.card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.card-header {
    background: #fff;
    border-bottom: 1px solid #e5e5e5;
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.table {
    background: #fff;
}

.btn-primary {
    background-color: #1877f2;
    border-color: #1877f2;
}

.btn-primary:hover {
    background-color: #166fe5;
    border-color: #166fe5;
}

.form-check {
    margin-bottom: 1rem;
}

.alert {
    border-radius: 6px;
    padding: 1rem;
}
CSS);
?>

<div class="container-fluid user-view">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="cover-photo"></div>
        <div class="profile-info">
            <img src="https://via.placeholder.com/120" class="profile-picture" alt="Profile">
            <div class="profile-details">
                <h2 class="mb-1">
                    <?= Html::encode($model->first_name) ?>
                    <span class="text-muted small">(@<?= Html::encode($model->username) ?>)</span>
                </h2>
                <div class="text-muted">
                    <?= Html::encode($model->email) ?> | <?= Html::encode($model->contact_no) ?>
                </div>
                <!-- Checkboxes for Permissions and Store Types -->
                <div class="mt-3">
                    <?php
                    echo Html::beginForm(['update', 'id' => $model->id], 'post', ['class' => 'd-flex flex-wrap']);
                    ?>
                    <div class="form-check me-4">
                        <?= Html::checkbox('allow_onboarding', $model->allow_onboarding, [
                            'class' => 'form-check-input',
                            'id' => 'allowOnboarding'
                        ]) ?>
                        <?= Html::label('Allow Onboarding', 'allowOnboarding', ['class' => 'form-check-label']) ?>
                    </div>
                    <div class="form-check me-4">
                        <?= Html::checkbox('single_store', $model->vendor_store_type == User::VENDOR_STORE_TYPE_SINGLE, [
                            'class' => 'form-check-input',
                            'id' => 'singleStore'
                        ]) ?>
                        <?= Html::label('Single Store', 'singleStore', ['class' => 'form-check-label']) ?>
                    </div>
                    <div class="form-check me-4">
                        <?= Html::checkbox('multi_store', $model->vendor_store_type == User::VENDOR_STORE_TYPE_MULTI, [
                            'class' => 'form-check-input',
                            'id' => 'multiStore'
                        ]) ?>
                        <?= Html::label('Multi Store', 'multiStore', ['class' => 'form-check-label']) ?>
                    </div>
                    <?= Html::submitButton('Update Permissions', ['class' => 'btn btn-primary mt-2']) ?>
                    <?php echo Html::endForm(); ?>
                </div>
            </div>
            <div>
                <?= Html::a('<i class="fas fa-edit"></i> Create Store', [
                    '/admin/vendor-details/create',
                    'id' => $model->id
                ], [
                    'class' => 'btn btn-primary',
                    'style' => 'margin-bottom:10px;',
                    'title' => 'Create a new store'
                ]) ?>
                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary me-2']) ?>
                <?= Html::a('<i class="fas fa-trash-alt"></i>', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-outline-danger',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Tabbed Content -->
    <?php
    $userId = Yii::$app->request->get('id');
    $vendorQuery = VendorDetails::find()->where(['user_id' => $userId]);
    $storeCount = $vendorQuery->count();

    echo Bootstrap4Tabs::widget([
        'items' => [
            [
                'label' => '<i class="fas fa-user me-2"></i> User Information',
                'content' => $this->render('_user_info', ['model' => $model]),
                'active' => true,
            ],
            [
                'label' => '<i class="fas fa-box-open me-2"></i> Order History',
                'content' => $this->render('_order_history', ['model' => $model]),
            ],
            [
                'label' => '<i class="fas fa-store me-2"></i> My Stores (' . $storeCount . ')',
                'content' => $this->render('_my_stores', ['model' => $model, 'vendorQuery' => $vendorQuery, 'storeCount' => $storeCount]),
            ],
            [
                'label' => '<i class="fas fa-wallet me-2"></i> Wallet Transactions',
                'content' => $this->render('_wallet_transactions', ['model' => $model]),
            ],
            [
                'label' => '<i class="fas fa-question-circle me-2"></i> Quiz Questions & Answers',
                'content' => $this->render('_quiz_history'),
            ],
        ],
        'encodeLabels' => false,
        'options' => ['class' => 'nav-tabs'],
    ]);
    ?>
</div>

<?php
$this->registerJs(<<<JS
// Ensure only one store type checkbox is selected at a time
$('#singleStore').on('change', function() {
    if ($(this).is(':checked')) {
        $('#multiStore').prop('checked', false);
    }
});
$('#multiStore').on('change', function() {
    if ($(this).is(':checked')) {
        $('#singleStore').prop('checked', false);
    }
});
JS);
?>

<!-- Partial View: _user_info.php -->
<?php
$this->beginBlock('_user_info');
?>
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user me-2"></i>User Information</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-sm table-borderless mb-0'],
            'attributes' => [
                'username',
                'email',
                'first_name',
                'contact_no',
                [
                    'attribute' => 'user_role',
                    'value' => function ($model) {
                        return ucfirst($model->user_role);
                    },
                ],
            ],
        ]) ?>
    </div>
</div>
<?php
$this->endBlock();
?>

<!-- Partial View: _order_history.php -->
<?php
$this->beginBlock('_order_history');
?>
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-box-open me-2"></i>Order History</h5>
    </div>
    <div class="card-body">
        <?php
        $orderIdFromUrl = Yii::$app->request->get('id');
        $orderModel = Orders::findOne($orderIdFromUrl);
        $comboQuery = ComboOrder::find()
            ->where(['order_id' => $orderIdFromUrl])
            ->orderBy(['id' => SORT_DESC]);

        $orderDataProvider = new ActiveDataProvider([
            'query' => $comboQuery,
            'pagination' => ['pageSize' => 10],
        ]);

        if ($orderDataProvider->getTotalCount() > 0) {
            echo GridView::widget([
                'dataProvider' => $orderDataProvider,
                'hover' => true,
                'condensed' => true,
                'responsiveWrap' => false,
                'bordered' => false,
                'striped' => true,
                'layout' => '{items}{pager}',
                'tableOptions' => ['class' => 'table table-sm table-hover mb-0'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    'order_id',
                    'vendor_details_id',
                    'combo_package_id',
                    'status',
                    [
                        'attribute' => 'amount',
                        'format' => ['decimal', 2],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'header' => 'Actions',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a(
                                    '<i class="fas fa-eye"></i>',
                                    Url::to(['/admin/orders/view', 'id' => $model->id]),
                                    [
                                        'title' => 'View Order',
                                        'class' => 'btn btn-sm btn-outline-primary',
                                    ]
                                );
                            },
                        ],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                ],
            ]);
        } else {
            echo '<div class="text-muted small"><i class="fas fa-info-circle"></i> No combo orders found for this Order ID.</div>';
        }
        ?>
    </div>
</div>
<?php
$this->endBlock();
?>

<!-- Partial View: _my_stores.php -->
<?php
$this->beginBlock('_my_stores');
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-store me-2"></i>
            My Stores
            <span class="ms-2 h6">(<?= $storeCount ?>)</span>
        </h5>
        <span class="badge <?= $model->vendor_store_type == User::VENDOR_STORE_TYPE_SINGLE ? 'bg-primary' : 'bg-success' ?>">
            <?= $model->vendor_store_type == User::VENDOR_STORE_TYPE_SINGLE ? 'Single Store Vendor' : 'Multi-Store Vendor' ?>
        </span>
    </div>
    <div class="card-body">
        <?php
        if ($storeCount > 0) {
            if ($model->vendor_store_type == User::VENDOR_STORE_TYPE_SINGLE) {
                if ($storeCount > 1) {
                    echo '<div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i> This vendor is marked as Single Store but has multiple stores!
                    </div>';
                }
                $singleStore = $vendorQuery->one();
                echo '<div class="single-store-view">';
                echo DetailView::widget([
                    'model' => $singleStore,
                    'options' => ['class' => 'table table-sm table-bordered'],
                    'attributes' => [
                        'business_name',
                        'gst_number',
                        'main_category_id',
                        'address',
                        'status:boolean',
                        'created_on:datetime',
                    ],
                ]);
                echo '</div>';
                echo Html::a('<i class="fas fa-eye"></i> View Store Details',
                    ['/admin/vendor-details/view', 'id' => $singleStore->id],
                    ['class' => 'btn btn-primary mt-2']
                );
            } else {
                echo '<div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> This vendor has ' . $storeCount . ' locations.
                </div>';
                $vendorDataProvider = new ActiveDataProvider([
                    'query' => $vendorQuery->orderBy(['id' => SORT_DESC]),
                    'pagination' => ['pageSize' => 10],
                ]);
                echo GridView::widget([
                    'dataProvider' => $vendorDataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'business_name',
                        'gst_number',
                        'main_category_id',
                        [
                            'attribute' => 'status',
                            'value' => function($model) {
                                return $model->status ? 'Active' : 'Inactive';
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="fas fa-eye"></i>', $url, [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'View Store'
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]);
            }
        } else {
            echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No stores found for this vendor.
            </div>';
        }
        ?>
    </div>
</div>
<?php
$this->endBlock();
?>

<!-- Partial View: _wallet_transactions.php -->
<?php
$this->beginBlock('_wallet_transactions');
?>
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-wallet me-2"></i>Wallet Transactions</h5>
    </div>
    <div class="card-body">
        <?php
        $walletQuery = $model->getWalletTransactions()->orderBy(['id' => SORT_DESC]);
        $walletTransactions = $walletQuery->all();

        if (!empty($walletTransactions)) {
            $walletDataProvider = new ActiveDataProvider([
                'query' => $walletQuery,
                'pagination' => ['pageSize' => 10],
            ]);

            echo GridView::widget([
                'dataProvider' => $walletDataProvider,
                'hover' => true,
                'condensed' => true,
                'responsiveWrap' => false,
                'bordered' => false,
                'striped' => true,
                'layout' => '{items}{pager}',
                'tableOptions' => ['class' => 'table table-sm table-hover mb-0'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    'order_id',
                    [
                        'attribute' => 'amount',
                        'format' => ['decimal', 2],
                    ],
                    [
                        'attribute' => 'payment_type',
                        'value' => function($model) {
                            return $model->payment_type == 1 ? 'Credit' : ($model->payment_type == 2 ? 'Debit' : 'Unknown');
                        },
                    ],
                    'method_reason',
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            switch ($model->status) {
                                case 1: return 'Pending';
                                case 2: return 'Success';
                                case 3: return 'Cancelled';
                                default: return 'Unknown';
                            }
                        },
                    ],
                    'created_on',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'header' => 'Actions',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', ['/orders/view', 'id' => $model->id], [
                                    'title' => 'View Order',
                                    'class' => 'btn btn-sm btn-outline-primary',
                                ]);
                            },
                        ],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                ],
            ]);
        } else {
            echo '<div class="text-muted small"><i class="fas fa-info-circle"></i> No transactions found.</div>';
        }
        ?>
    </div>
</div>
<?php
$this->endBlock();
?>

<!-- Partial View: _quiz_history.php -->
<?php
$this->beginBlock('_quiz_history');
?>
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-question-circle me-2"></i>Quiz Questions & Answers</h5>
    </div>
    <div class="card-body">
        <?php
        $quizData = Yii::$app->db->createCommand("
            SELECT 
                qq.id AS question_id,
                qq.question_text,
                qq.quiz_id,
                qq.question_type,
                qq.status,
                qa.answer_text,
                qa.is_correct
            FROM quiz_questions qq
            LEFT JOIN quiz_answers qa ON qq.id = qa.question_id
            WHERE qq.status = 1
            ORDER BY qq.id, qa.id
        ")->queryAll();

        $groupedData = [];
        foreach ($quizData as $row) {
            $qid = $row['question_id'];
            if (!isset($groupedData[$qid])) {
                $groupedData[$qid] = [
                    'question_id' => $qid,
                    'question_text' => $row['question_text'],
                    'answers' => [],
                ];
            }
            if (!empty($row['answer_text'])) {
                $groupedData[$qid]['answers'][] = [
                    'answer_text' => $row['answer_text'],
                    'is_correct' => $row['is_correct'],
                ];
            }
        }

        $finalData = [];
        foreach ($groupedData as $item) {
            $answersText = '';
            foreach ($item['answers'] as $answer) {
                $answersText .= ($answer['is_correct'] ? '<b>' : '') .
                    Html::encode($answer['answer_text']) .
                    ($answer['is_correct'] ? ' ✅</b>' : '') . '<br>';
            }
            $finalData[] = [
                'question_id' => $item['question_id'],
                'question_text' => $item['question_text'],
                'answers_text' => $answersText ?: '<i class="text-muted">No answers</i>',
            ];
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $finalData,
            'pagination' => ['pageSize' => 10],
        ]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => true,
            'condensed' => true,
            'responsiveWrap' => false,
            'bordered' => false,
            'striped' => true,
            'layout' => '{items}{pager}',
            'tableOptions' => ['class' => 'table table-sm table-hover mb-0'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'question_text',
                    'label' => 'Question',
                ],
                [
                    'attribute' => 'answers_text',
                    'format' => 'raw',
                    'label' => 'Answers (Correct ones marked ✅)',
                ],
            ],
        ]);
        ?>
    </div>
</div>
<?php
$this->endBlock();
?>