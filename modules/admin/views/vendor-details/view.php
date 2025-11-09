<?php

use app\models\User;
use app\modules\admin\models\base\StoreTimingsHasBrakes;
use app\modules\admin\models\base\VendorDetails;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

$vendorId = $model->id;

// Prepare the API URL with dynamic data'

$apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $model->id;

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL            => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => '',
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => 'GET',
]);

$response = curl_exec($curl);
curl_close($curl);

$this->title                   = $model->business_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container">
    <div class="main-body">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="main-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">Vendor</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo Html::encode($this->title) ?></li>
            </ol>
        </nav>
        <!-- /Breadcrumb -->
        <?php
        $storeCount = VendorDetails::find()->where(['user_id' => $model->user_id])->count();
        $storeType  = ($storeCount > 1) ? 'Multi-Store Vendor' : 'Single-Store Vendor';

        // If you already have vendor_store_type in DB
        $vendorStoreType = $model->vendor_store_type ?? null;
        ?>

        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Store Type:</strong>
            </div>
            <div class="col-md-8">
                <?php if ($vendorStoreType == 1): ?>
                    <span class="badge bg-primary">Single-Store Vendor</span>
                <?php elseif ($vendorStoreType == 2): ?>
                    <span class="badge bg-success">Multi-Store Vendor</span>
                <?php else: ?>
                    <span class="badge bg-secondary"><?php echo $storeType ?></span> <!-- fallback from count -->
                <?php endif; ?>
            </div>
        </div>






        <div class="vendor-details-form container-fluid">



            <div class="row gutters-sm">
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="<?php echo $model->logo ?>" alt="Vendor Logo" class="rounded-circle img-thumbnail" width="150">
                            <div class="mt-3">
                                <!-- Business name display and edit -->
                                <div id="business-name-container">
                                    <h4 id="business-name"><?php echo Html::encode($model->business_name) ?></h4>
                                </div>
                                <p class="text-secondary mb-1"><?php echo Html::encode(strip_tags($model->description)) ?></p>
                                <p class="text-muted font-size-sm"><?php echo Html::encode(strip_tags($model->address)) ?></p>
                                <?php echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Website and Social Links -->
                    <div class="card mt-3">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">
                                    <i class="feather-globe mr-2"></i>Website
                                </h6>
                                <span class="text-secondary">
                                    <?php echo Html::a(
                                        $model->website_link,
                                        (strpos($model->website_link, 'http://') === 0 || strpos($model->website_link, 'https://') === 0) ? $model->website_link : 'http://' . $model->website_link,
                                        ['target' => '_blank']
                                    ) ?>
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- QR Code for Vendor -->


                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <h6 class="mb-3"><?php echo Yii::t('app', 'Scan QR Code for Store Info') ?></h6>
                            <!-- Use the $apiUrl for the QR code image source -->
                            <img src="<?php echo $apiUrl ?>" alt="QR Code" class="img-thumbnail">
                            <p class="text-muted font-size-sm"><?php echo Html::encode('Vendor ID: ' . $model->id) ?></p>
                        </div>
                    </div>

                </div>
                <!-- /Vendor Info Card -->

                <!-- Vendor Details -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">

                            <!-- Business Name -->
                            <div class="row" id="business_name-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Business Name</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="business_name"><?php echo Html::encode($model->business_name); ?></span>
                                    <input type="text" class="form-control edit-mode d-none" id="business_name-input"
                                        value="<?php echo Html::encode($model->business_name); ?>">
                                </div>

                            </div>


                            <hr>

                            <!-- Contact No -->
                            <div class="row" id="contact_no-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Contact No</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="contact_no">
                                        <?php echo $model->user ? Html::encode($model->user->contact_no) : '<span class="text-muted">Not Available</span>'; ?>
                                    </span>
                                    <input type="text" class="form-control edit-mode d-none" id="contact_no-input"
                                        value="<?php echo $model->user ? Html::encode($model->user->contact_no) : ''; ?>">
                                </div>

                            </div>


                            <hr>

                            <!-- Average Rating -->
                            <div class="row" id="avg_rating-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Average Rating</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="avg_rating"><?php echo Html::encode($model->avg_rating); ?></span>
                                    <input type="text" class="form-control edit-mode d-none" id="avg_rating-input"
                                        value="<?php echo Html::encode($model->avg_rating); ?>">
                                </div>

                            </div>
                            <hr>
                            <!-- GST Number -->
                            <div class="row" id="gst-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">GST Number</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="gst">
                                        <?php echo Html::encode($model->gst_number) ?>
                                    </span>
                                    <input type="text" class="form-control edit-mode d-none"
                                        id="gst-input"
                                        value="<?php echo Html::encode($model->gst_number) ?>">
                                </div>
                            </div>



                            <hr>
                            <!--- Account Number-->
                            <div class="row" id="account-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Account Number</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="account">
                                        <?php echo Html::encode($model->account_number) ?>
                                    </span>
                                    <input type="text" class="form-control edit-mode d-none"
                                        id="account-input"
                                        value="<?php echo Html::encode($model->account_number) ?>">
                                </div>
                            </div>
                            <hr>
                            <!--- IFSc Code--->
                            <div class="row" id="ifsc-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">IFSC Code</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="ifsc">
                                        <?php echo Html::encode($model->ifsc_code) ?>
                                    </span>
                                    <input type="text" class="form-control edit-mode d-none"
                                        id="ifsc-input"
                                        value="<?php echo Html::encode($model->ifsc_code) ?>">
                                </div>
                            </div>
                            <hr>


                            <!-- Commission -->
                            <div class="row" id="commission-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Commission</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="commission">
                                        <?php echo Html::encode($model->commission) ?>
                                        (<?php echo $model->getCommissionTypeBadge() ?>)
                                    </span>

                                    <input type="text" class="form-control edit-mode d-none" id="commission-input"
                                        value="<?php echo Html::encode($model->commission); ?>">
                                </div>
                            </div>


                            <hr>


                            <!-- Gender Preference -->
                            <div class="row" id="gender_type-container">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Gender Preference</h6>
                                </div>
                                <div class="col-sm-7 text-secondary">
                                    <span class="view-mode" id="gender_preference"><?php echo $model->getGenderBadge(); ?></span>
                                    <select id="gender_type-input" class="form-control edit-mode d-none">
                                        <option value="<?php echo $model::GENDER_MALE ?>" <?php echo $model->gender_type == $model::GENDER_MALE ? 'selected' : '' ?>>Male</option>
                                        <option value="<?php echo $model::GENDER_FEMALE ?>" <?php echo $model->gender_type == $model::GENDER_FEMALE ? 'selected' : '' ?>>Female</option>
                                        <option value="<?php echo $model::GENDER_UNISEX ?>" <?php echo $model->gender_type == $model::GENDER_UNISEX ? 'selected' : '' ?>>Unisex</option>
                                    </select>
                                </div>

                            </div>




                            <div class="row" id="gender_type-edit" style="display:none;">
                                <div class="col-sm-9 offset-sm-3">
                                    <select id="gender_type-input" class="form-control mb-2">
                                        <option value="<?php echo $model::GENDER_MALE ?>" <?php echo $model->gender_type == $model::GENDER_MALE ? 'selected' : '' ?>>Male</option>
                                        <option value="<?php echo $model::GENDER_FEMALE ?>" <?php echo $model->gender_type == $model::GENDER_FEMALE ? 'selected' : '' ?>>Female</option>
                                        <option value="<?php echo $model::GENDER_UNISEX ?>" <?php echo $model->gender_type == $model::GENDER_UNISEX ? 'selected' : '' ?>>Unisex</option>
                                    </select>

                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-sm-12 text-end">
                                    <button id="edit-all" class="btn btn-primary btn-sm">Edit</button>
                                    <button id="save-all" class="btn btn-success btn-sm d-none" data-id="<?php echo $model->id ?>">Save</button>
                                    <button id="cancel-all" class="btn btn-secondary btn-sm d-none">Cancel</button>
                                </div>
                            </div>
                            <hr>

                            <!-- Add Business Documents Button -->
                            <?php echo Html::a(Yii::t('app', 'Add Business Documents'), ['/admin/business-documents/create', 'vendor_details_id' => $model->id], ['class' => 'btn btn-success']) ?>

                        </div>
                        <hr>



                        <!-- Business Documents Section -->
                        <?php if ($providerBusinessDocuments->totalCount): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-secondary"><?php echo Yii::t('app', 'Business Documents') ?></h5>
                                    <?php echo GridView::widget([
                                        'dataProvider'  => $providerBusinessDocuments,
                                        'pjax'          => true,
                                        'pjaxSettings'  => ['options' => ['id' => 'kv-pjax-container-business-documents']],
                                        'columns'       => [
                                            ['class' => 'yii\grid\SerialColumn'],

                                            // Compact file column: show filename or file-icon

                                            [
                                                'attribute' => 'file',
                                                'format'    => 'raw',
                                                'value'     => function ($model) {
                                                    return Html::img($model->file, ['class' => 'img-thumbnail', 'style' => 'width:150px; height:auto;']);
                                                },
                                            ],
                                            //                  [
                                            //     'attribute' => 'file',
                                            //     'format' => 'raw',
                                            //     'value' => function ($model) {
                                            //         $fileUrl = $model->file;
                                            //         $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));

                                            //         if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $fileUrl)) {
                                            //             return Html::tag('div',
                                            //                 Html::img($fileUrl, [
                                            //                     'class' => 'img-thumbnail',
                                            //                     'style' => 'width: 100px; height: auto; object-fit: cover; margin-right: 10px;'
                                            //                 ]) .
                                            //                 Html::a('<i class="fa fa-download"></i>', $fileUrl, [
                                            //                     'class' => 'btn btn-sm btn-primary',
                                            //                     'download' => $fileName,
                                            //                     'title' => 'Download'
                                            //                 ]),
                                            //                 ['class' => 'd-flex align-items-center']
                                            //             );
                                            //         }

                                            //         if (preg_match('/\.(pdf)$/i', $fileUrl)) {
                                            //             return Html::tag('div',
                                            //                 '<i class="fa fa-file-pdf-o" style="font-size:28px;margin-right:10px;"></i>' .
                                            //                 Html::a('<i class="fa fa-download"></i>', $fileUrl, [
                                            //                     'class' => 'btn btn-sm btn-primary',
                                            //                     'download' => $fileName,
                                            //                     'title' => 'Download'
                                            //                 ]),
                                            //                 ['class' => 'd-flex align-items-center']
                                            //             );
                                            //         }

                                            //         // return Html::tag('div',
                                            //         //     '<i class="fa fa-file-o" style="font-size:28px;margin-right:10px;"></i>' .
                                            //         //     Html::a('<i class="fa fa-download"></i>', $fileUrl, [
                                            //         //         'class' => 'btn btn-sm btn-primary',
                                            //         //         'download' => $fileName,
                                            //         //         'title' => 'Download'
                                            //         //     ]),
                                            //         //     ['class' => 'd-flex align-items-center']
                                            //         // );
                                            //     }
                                            // ],

                                            'document_type',

                                            [
                                                'attribute' => 'status',
                                                'format'    => 'raw',
                                                'value'     => function ($model) {
                                                    return $model->getStateOptionsBadges();
                                                },
                                            ],

                                            // Action column: preview + download + update + delete
                                            [
                                                'class'    => 'kartik\grid\ActionColumn',
                                                'template' => '{preview} {downloadImage} {download} {update} {delete}',
                                                'buttons'  => [

                                                    /** Preview Button **/
                                                    'preview' => function ($url, $model) {
                                                        $raw     = $model->file;
                                                        $fileUrl = preg_match('#^https?://#i', $raw)
                                                            ? $raw
                                                            : Yii::getAlias('@web') . '/uploads/' . ltrim($raw, '/');

                                                        $id = $model->id;

                                                        if (preg_match('/\.(jpe?g|png|gif|pdf)$/i', $fileUrl)) {
                                                            $btn = Html::a('<i class="fa fa-eye"></i>', 'javascript:void(0);', [
                                                                'class'       => 'btn btn-sm btn-outline-info m-1',
                                                                'data-toggle' => 'modal',
                                                                'data-target' => '#previewModal-' . $id,
                                                                'title'       => Yii::t('app', 'Preview'),
                                                                'data-toggle' => 'tooltip',
                                                            ]);

                                                            $content = preg_match('/\.(jpe?g|png|gif)$/i', $fileUrl)
                                                                ? Html::img($fileUrl, ['style' => 'width:100%;height:auto;'])
                                                                : Html::tag('iframe', '', [
                                                                    'src'   => $fileUrl,
                                                                    'style' => 'width:100%;height:500px;border:0;',
                                                                ]);

                                                            $modal = <<<HTML
                                                                        <div class="modal fade" id="previewModal-{$id}" tabindex="-1" role="dialog">
                                                                            <div class="modal-dialog modal-lg" role="document">
                                                                                <div class="modal-content shadow-lg rounded">
                                                                                    <div class="modal-header bg-info text-white">
                                                                                        <h5 class="modal-title">Preview</h5>
                                                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="modal-body">{$content}</div>
                                                                                    <div class="modal-footer">
                                                                                        <a href="{$fileUrl}" class="btn btn-primary btn-sm" download>
                                                                                            <i class="fa fa-download"></i> Download
                                                                                        </a>
                                                                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                                                                                            Close
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    HTML;

                                                            return $btn . $modal;
                                                        }

                                                        return Html::a('<i class="fa fa-eye"></i>', $fileUrl, [
                                                            'class'       => 'btn btn-sm btn-primary m-1',
                                                            'download'    => basename($fileUrl),
                                                            'title'       => Yii::t('app', 'Preview'),
                                                            'data-toggle' => 'tooltip',
                                                        ]);
                                                    },

                                                    'downloadImage' => function ($url, $model) {
                                                        $fileUrl  = $model->file;
                                                        $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));

                                                        return Html::a(
                                                            '<i class="fa fa-download"></i>',
                                                            Url::to(['/admin/business-documents/download-image', 'id' => $model->id]), // ✅ correct controller
                                                            [
                                                                'class'       => 'btn btn-sm btn-success m-1',
                                                                'title'       => Yii::t('app', 'Download File'),
                                                                'data-toggle' => 'tooltip',
                                                                'data-pjax'   => 0, // ✅ prevent PJAX from hijacking the link
                                                            ]
                                                        );
                                                    },

                                                    /** Update **/
                                                    'update'        => function ($url, $model) {
                                                        $url = Url::to(['/admin/business-documents/update', 'id' => $model->id]);
                                                        if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())) {
                                                            return Html::a('<i class="fas fa-pencil-alt"></i>', $url, [
                                                                'class'       => 'btn btn-sm btn-warning m-1',
                                                                'title'       => Yii::t('app', 'Update'),
                                                                'data-toggle' => 'tooltip',
                                                            ]);
                                                        }
                                                    },

                                                    /** Delete **/
                                                    'delete'        => function ($url, $model) {
                                                        $url = Url::to(['/admin/business-documents/delete', 'id' => $model->id]);
                                                        if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())) {
                                                            return Html::a('<i class="fas fa-trash-alt"></i>', $url, [
                                                                'class'       => 'btn btn-sm btn-danger m-1',
                                                                'data'        => [
                                                                    'method'  => 'post',
                                                                    'confirm' => 'Are you sure?',
                                                                ],
                                                                'title'       => Yii::t('app', 'Delete'),
                                                                'data-toggle' => 'tooltip',
                                                            ]);
                                                        }
                                                    },
                                                ],
                                            ],

                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        <?php endif; ?>





                        <!-- Image Modal (Only for images) -->
                        <?php foreach ($providerBusinessDocuments->models as $model): ?>
                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $model->file)): ?>
                                <div class="modal fade" id="imageModal-<?php echo $model->id ?>" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel-<?php echo $model->id ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="imageModalLabel-<?php echo $model->id ?>"><?php echo Yii::t('app', 'Image Preview') ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="<?php echo $model->file ?>" class="img-fluid" alt="Preview">
                                            </div>
                                            <div class="modal-footer">
                                                <a href="<?php echo $model->file ?>" class="btn btn-primary" download="<?php echo basename($model->file) ?>"><i class="fa fa-download"></i><?php echo Yii::t('app', 'Download') ?></a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo Yii::t('app', 'Close') ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>



                        <!-- PDF Modal (Only for PDF files) -->
                        <?php foreach ($providerBusinessDocuments->models as $model): ?>
                            <?php if (preg_match('/\.(pdf)$/i', $model->file)): ?>
                                <div class="modal fade" id="pdfModal-<?php echo $model->id ?>" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel-<?php echo $model->id ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="pdfModalLabel-<?php echo $model->id ?>"><?php echo Yii::t('app', 'PDF Preview') ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <iframe src="<?php echo $model->file ?>" style="width: 100%; height: 500px;"></iframe>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="<?php echo $model->file ?>" class="btn btn-primary" download="<?php echo basename($model->file) ?>"><i class="fa fa-download"></i><?php echo Yii::t('app', 'Download') ?></a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo Yii::t('app', 'Close') ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>


                        <div class="card">
                            <div class="card-body">

                                <?php echo Html::a(Yii::t('app', 'Add images'), ['/admin/business-images/create', 'vendor_details_id' => Yii::$app->request->get('id')], ['class' => 'btn btn-success']) ?>

                            </div>
                        </div>
                        <hr>

                        <!-- Business Images Section -->
                        <?php if ($providerBusinessImages->totalCount): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <!-- ✅ New Button -->
                                    <div class="mb-3">
                                        <?php echo Html::button('Delete Bulk Images', [
                                            'class' => 'btn btn-info',
                                            'id'    => 'image-selected-btn',
                                        ]) ?>
                                    </div>
                                    <h5 class="card-title text-secondary"><?php echo Yii::t('app', 'Business Images') ?></h5>
                                    <?php echo GridView::widget([
                                        'dataProvider' => $providerBusinessImages,
                                        'pjax'         => true,
                                        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-business-images']],
                                        'columns'      => [
                                            [
                                                'class'           => 'kartik\grid\CheckboxColumn',
                                                'checkboxOptions' => function ($model) {
                                                    if (strtolower($model->status) === 'Active' || $model->status == 1) {
                                                        return ['value' => $model->id, 'class' => 'select-checkbox'];
                                                    }
                                                    return ['disabled' => true];
                                                },
                                            ],
                                            ['class' => 'yii\grid\SerialColumn'],
                                            [
                                                'attribute' => 'image_file',
                                                'format'    => 'html',
                                                'value'     => function ($model) {
                                                    return Html::img($model->image_file, ['class' => 'img-thumbnail', 'style' => 'width:150px; height:auto;']);
                                                },
                                            ],
                                            [
                                                'attribute' => 'status',
                                                'format'    => 'raw',
                                                'value'     => function ($model) {
                                                    return $model->getStateOptionsBadges();
                                                },
                                            ],

                                            [
                                                'class'    => 'kartik\grid\ActionColumn',
                                                'template' => ' {update} {delete}',
                                                'buttons'  => [

                                                    'update' => function ($url, $model) {
                                                        $url = Url::to(['/admin/business-images/update', 'id' => $model->id]);
                                                        if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())) {
                                                            return Html::a('<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);
                                                        }
                                                    },
                                                    'delete' => function ($url, $model) {
                                                        if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())) {
                                                            return Html::a('<span class="fas fa-trash-alt"></span>', 'javascript:void(0);', [
                                                                'class'    => 'single-delete',
                                                                'data-id'  => $model->id,
                                                                'data-url' => Url::to(['/admin/business-images/ajax-delete', 'id' => $model->id]),
                                                            ]);
                                                        }
                                                    },

                                                ],
                                            ],
                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        <?php endif; ?>



                        <!--- Staff creation button>-->
                        <div class="card">
                            <div class="card-body">

                                <p>
                                    <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>
                                        <?php echo Html::a(Yii::t('app', 'Create Staff'), ['/admin/staff/create', 'vendor_details_id' => $vendorId], ['class' => 'btn btn-success']) ?>
                                    <?php } ?>
                                </p>

                            </div>

                        </div>
                        <hr>
                        <!--- Staff creation button End>-->

                        <!-- Create Coupon Button -->
                        <div class="card">
                            <div class="card-body">
                                <p>
                                    <?php if (User::isAdmin() || User::isSubAdmin() || User::isVendor()) { ?>
                                        <?php echo Html::a(
                                            Yii::t('app', 'Create Coupon'),
                                            ['/admin/coupon/create-vendor', 'vendor_details_id' => $vendorId],
                                            ['class' => 'btn btn-success']
                                        ) ?>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                        <hr>
                        <div class="card">
                            <div class="card-body">
                                <p>
                                    <?php if (User::isAdmin() || User::isSubAdmin() || User::isVendor()) { ?>
                                        <?php echo Html::a(
                                            Yii::t('app', 'Create Packages'),
                                            ['/admin/packages/create', 'vendor_details_id' => $vendorId],
                                            ['class' => 'btn btn-success']
                                        ) ?>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                        <!-- Create Coupon Button  End-->
                        <!-- Create Service Coupons -->
                        <!-- <div class="card">
                            <div class="card-body">

                                <p>
                                    <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>
                                        <?php echo Html::a(Yii::t('app', 'Create Service Coupons'), ['/admin/service-has-coupons/create', 'vendor_details_id' => $vendorId], ['class' => 'btn btn-success']) ?>
                                    <?php } ?>
                                </p>

                            </div>

                        </div> -->
                        <!-- Create Service Coupons End-->
                        <!--- Service Coupon displaying data start -->
                        <!--                             <?php if ($providerServiceCoupon->getTotalCount() > 0): ?>
                            <?php echo \kartik\grid\GridView::widget([
                                                                    'dataProvider' => $providerServiceCoupon,
                                                                    'pjax'         => true,
                                                                    'striped'      => true,
                                                                    'hover'        => true,
                                                                    'bordered'     => true,
                                                                    'condensed'    => true,
                                                                    'responsive'   => true,
                                                                    'summary'      => false, // hide summary text "Showing 1-10 of..."
                                                                    'panel'        => [
                                                                        'type'    => \kartik\grid\GridView::TYPE_INFO,
                                                                        'heading' => '<i class="glyphicon glyphicon-book"></i> <b>Service Coupons</b>',
                                                                        'before'  => '<p class="text-muted">List of coupons available for this vendor’s services.</p>',
                                                                        'after'   => false,
                                                                    ],
                                                                    'toolbar'      => [
                                                                        '{toggleData}',
                                                                        '{export}', // allows table data toggle + export to Excel/PDF
                                                                    ],
                                                                    'export'       => [
                                                                        'fontAwesome' => true,
                                                                    ],
                                                                    'columns'      => [
                                                                        ['class' => 'yii\grid\SerialColumn'],

                                                                        [
                                                                            'attribute'      => 'service_id',
                                                                            'label'          => 'Service',
                                                                            'format'         => 'raw',
                                                                            'value'          => function ($model) {
                                                                                return $model->service
                                                                                    ? '<span class="badge badge-primary">' . $model->service->service_name . '</span>'
                                                                                    : '<span class="text-muted">' . $model->service_id . '</span>';
                                                                            },
                                                                            'contentOptions' => ['style' => 'text-align:center;'],
                                                                        ],

                                                                        [
                                                                            'attribute'      => 'coupon_id',
                                                                            'label'          => 'Coupon',
                                                                            'format'         => 'raw',
                                                                            'value'          => function ($model) {
                                                                                return '<span class="badge badge-success">#' . $model->coupon_id . '</span>';
                                                                            },
                                                                            'contentOptions' => ['style' => 'text-align:center;'],
                                                                        ],

                                                                        [
                                                                            'attribute'      => 'status',
                                                                            'format'         => 'raw',
                                                                            'value'          => function ($model) {
                                                                                return $model->getStateOptionsBadges(); // already returns styled badges
                                                                            },
                                                                            'contentOptions' => ['style' => 'text-align:center;'],
                                                                        ],

                                                                        [
                                                                            'class'          => 'yii\grid\ActionColumn',
                                                                            'header'         => 'Actions',
                                                                            'template'       => '{view} {update} {delete}',
                                                                            'buttons'        => [
                                                                                'view'   => function ($url, $model) {
                                                                                    return '<a href="' . $url . '" class="btn btn-sm btn-info" title="View"><i class="glyphicon glyphicon-eye-open"></i></a>';
                                                                                },
                                                                                'update' => function ($url, $model) {
                                                                                    return '<a href="' . $url . '" class="btn btn-sm btn-warning" title="Update"><i class="glyphicon glyphicon-pencil"></i></a>';
                                                                                },
                                                                                'delete' => function ($url, $model) {
                                                                                    return '<a href="' . $url . '" class="btn btn-sm btn-danger" title="Delete" data-confirm="Are you sure to delete this coupon?"><i class="glyphicon glyphicon-trash"></i></a>';
                                                                                },
                                                                            ],
                                                                            'contentOptions' => ['style' => 'text-align:center; white-space:nowrap;'],
                                                                        ],
                                                                    ],
                                                                ]); ?>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                <i class="glyphicon glyphicon-info-sign"></i>
                                No service coupons available for this vendor.
                            </div>
                        <?php endif; ?> -->
                        <!--- Service Coupon displaying data end -->


                        <!--- Service Coupon displaying data--end-->

                        <!--- staff displaying data-->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <?php
                                if ($providerStaff->totalCount) {
                                    $gridColumnStaff = [
                                        ['class' => 'yii\grid\SerialColumn'],
                                        [
                                            'class'    => 'yii\grid\ActionColumn',
                                            'template' => '{view} {update}', // Add delete
                                            'buttons'  => [
                                                'view'   => function ($url, $model) {
                                                    return Html::a(
                                                        '<span class="fas fa-eye"></span>',
                                                        ['staff/view', 'id' => $model->id, 'vendor_details_id' => $model->vendor_details_id],
                                                        ['title' => 'View']
                                                    );
                                                },
                                                'update' => function ($url, $model) {
                                                    return Html::a(
                                                        '<span class="fas fa-pencil-alt"></span>',
                                                        ['staff/update', 'id' => $model->id, 'vendor_details_id' => $model->vendor_details_id],
                                                        ['title' => 'Edit']
                                                    );
                                                },
                                                'delete' => function ($url, $model) {
                                                    return Html::a(
                                                        '<span class="fas fa-trash-alt"></span>',
                                                        ['staff/delete', 'id' => $model->id],
                                                        [
                                                            'title' => 'Delete',
                                                            'data'  => [
                                                                'confirm' => 'Are you sure you want to delete this staff member?',
                                                                'method'  => 'post',
                                                            ],
                                                        ]
                                                    );
                                                },
                                            ],
                                        ],

                                        ['attribute' => 'id', 'visible' => false],
                                        [
                                            'attribute' => 'user.username',
                                            'label'     => Yii::t('app', 'User'),
                                        ],
                                        [
                                            'attribute' => 'profile_image',
                                            'format'    => 'raw',
                                            'label'     => 'Image',
                                            'value'     => function ($model) {
                                                if (! empty($model->profile_image)) {
                                                    return Html::a(
                                                        Html::img($model->profile_image, [
                                                            'style' => 'width: 80px; height: 80px; object-fit: cover; border-radius: 8px;',
                                                            'alt'   => 'Image',
                                                        ]),
                                                        $model->profile_image,
                                                        ['target' => '_blank']
                                                    );
                                                } else {
                                                    return 'N/A';
                                                }
                                            },
                                        ],
                                        'mobile_no',
                                        'full_name',
                                        'aadhaar_number',
                                        'email:email',
                                        'gender',
                                        'dob',
                                        'experience',
                                        'specialization',
                                        'role',
                                        'current_status',
                                        'status',
                                        'created_on',
                                        'updated_on',

                                    ];

                                    echo GridView::widget([
                                        'dataProvider' => $providerStaff,
                                        'pjax'         => false,
                                        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-staff']],
                                        'panel'        => [
                                            'type'    => GridView::TYPE_PRIMARY,
                                            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Staff')),
                                        ],
                                        'export'       => false,
                                        'columns'      => $gridColumnStaff,
                                    ]);
                                }
                                ?>
                            </div>
                        </div>


                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-secondary mb-2"><?php echo Yii::t('app', 'Upload Services (Excel)') ?></h5>
                                <p class="text-muted"><?php echo Yii::t('app', 'You can bulk upload services using a properly formatted Excel file.') ?></p>

                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="mb-2">
                                        <?php echo Html::a(Yii::t('app', 'Download Example Format'), Url::to('@web/sample-filesservice-upload-example/sample-filesservice-upload-example.xlsx'), [
                                            'class'    => 'btn btn-sm btn-outline-info',
                                            'download' => true,
                                            'target'   => '_blank',
                                        ]) ?>
                                    </div>

                                    <div class="mb-2">
                                        <input type="file" id="excelFile" class="form-control-file" accept=".xlsx, .xls">
                                    </div>

                                    <div class="mb-2">
                                        <button id="uploadBtn" type="button" class="btn btn-success">Upload Excel</button>
                                    </div>
                                </div>

                                <div id="uploadResponse" class="mt-3 text-info"></div>
                            </div>
                        </div>
                        <hr>





                        <!-- Services Section -->
                        <?php if ($providerServices->totalCount): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-secondary"><?php echo Yii::t('app', 'Services') ?></h5>
                                    <?php echo GridView::widget([
                                        'dataProvider' => $providerServices,
                                        'pjax'         => true,
                                        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-services']],
                                        'columns'      => [
                                            ['class' => 'yii\grid\SerialColumn'],
                                            [
                                                'attribute' => 'subCategory.title',
                                                'label'     => Yii::t('app', 'Sub Category'),
                                            ],
                                            'service_name',
                                            'slug',
                                            [
                                                'attribute' => 'image',
                                                'format'    => 'html',
                                                'value'     => function ($model) {
                                                    return Html::img($model->image, ['class' => 'img-thumbnail', 'style' => 'width:100px; height:auto;']);
                                                },
                                            ],
                                            'price',
                                            'time',
                                            'home_visit:boolean',
                                            'walk_in:boolean',
                                            [
                                                'attribute' => 'status',
                                                'format'    => 'raw',
                                                'value'     => function ($model) {
                                                    return $model->getStateOptionsBadges();
                                                },
                                            ],
                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!--- add breaktimings model section-->
                        <?php

                        // Function to generate time options
                        function getTimeOptions($start = '06:00', $end = '23:30')
                        {
                            $times = [];
                            $current = strtotime($start);
                            $endTime = strtotime($end);
                            while ($current <= $endTime) {
                                $times[date('h:i A', $current)] = date('h:i A', $current);
                                $current = strtotime('+30 minutes', $current);
                            }
                            return $times;
                        }

                        $timeOptions = getTimeOptions();

                        // Modal Begin
                        Modal::begin([
                            'id' => 'breakModal',
                            'title' => '<h5>Add Break Timings</h5>',
                        ]);
                        ?>

                        <?= Html::hiddenInput('store_timing_id', '', ['id' => 'store_timing_id']); ?>

                        <!-- <div class="form-group">
                            <label>Day</label>
                            <input type="text" id="day-title" class="form-control" readonly>
                        </div> -->

                       <div id="break-slots-container">
                        <div class="break-slot">
                            <div class="form-group">
                                <label>Day</label>
                                <input type="text" class="form-control day-title" readonly>
                            </div>

                            <div class="form-group">
                                <label>Break Start Time</label>
                                <select name="timeSlots[0][start_time]" class="form-control mr-2">
                                    <option value="">Start Time</option>
                                    <?php foreach ($timeOptions as $time): ?>
                                        <option value="<?= $time ?>"><?= $time ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Break End Time</label>
                                <select name="timeSlots[0][end_time]" class="form-control mr-2">
                                    <option value="">End Time</option>
                                    <?php foreach ($timeOptions as $time): ?>
                                        <option value="<?= $time ?>"><?= $time ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <?= Html::button('Add Another Break', ['class' => 'btn btn-info btn-sm', 'id' => 'add-break-slot']) ?>
                        <?= Html::button('Save Breaks', ['class' => 'btn btn-success', 'id' => 'save-breaks-btn']) ?>
                    </div>

                        <?php Modal::end(); ?>

                        <!--- add breaktimings model section--end>-->
                        <hr>





                        <!-- store timings -->

                        <?php if ($providerStoreTimings): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-secondary">
                                        <?php echo Yii::t('app', 'Store Timings') ?>
                                    </h5>

                                    <?php \yii\widgets\Pjax::begin(['id' => 'store-timings-pjax']); ?>

                                    <?php $form = ActiveForm::begin([
                                        'id'     => 'timings-form',
                                        'action' => ['/admin/store-timings/bulk-update-timings'],
                                        'method' => 'post',
                                    ]); ?>

                                    <?php echo GridView::widget([
                                        'dataProvider' => $providerStoreTimings,
                                        'pjax'         => false, // disable inner Pjax
                                        'columns'      => [
                                            ['class' => 'yii\grid\SerialColumn'],
                                            ['attribute' => 'id', 'visible' => false],

                                            [
                                                'attribute' => 'day.title',
                                                'label'     => Yii::t('app', 'Day'),
                                            ],

                                            [
                                                'attribute' => 'start_time',
                                                'format'    => 'raw',
                                                'value'     => function ($model) {
                                                    return Html::input(
                                                        'text', // changed from 'time' to 'text' for AM/PM
                                                        "StoreTimings[{$model->id}][start_time]",
                                                        $model->start_time ? date('h:i A', strtotime($model->start_time)) : '',
                                                        ['class' => 'form-control start-time timepicker']
                                                    );
                                                },
                                            ],

                                            [
                                                'attribute' => 'close_time',
                                                'format'    => 'raw',
                                                'value'     => function ($model) {
                                                    return Html::input(
                                                        'text', // changed from 'time' to 'text'
                                                        "StoreTimings[{$model->id}][close_time]",
                                                        $model->close_time ? date('h:i A', strtotime($model->close_time)) : '',
                                                        ['class' => 'form-control close-time timepicker']
                                                    );
                                                },
                                            ],

                                            [
                                                'attribute' => 'status',
                                                'format'    => 'raw',
                                                'value'     => function ($model) {
                                                    return $model->getStateOptionsBadges();
                                                },
                                            ],

                                            [
                                                'class'    => 'kartik\grid\ActionColumn',
                                                'template' => '{update} {add-break}',
                                                'buttons'  => [
                                                    'update'    => function ($url, $model) {
                                                        $url = Url::to(['/admin/store-timings/update', 'id' => $model->id]);
                                                        if (in_array(Yii::$app->user->identity->user_role, User::fullAccessRoles())) {
                                                            return Html::a(
                                                                '<span class="fas fa-pencil-alt" aria-hidden="true"></span>',
                                                                $url
                                                            );
                                                        }
                                                    },
                                                    'add-break' => function ($url, $model) {
                                                        return Html::a(
                                                            '<span class="fas fa-plus" aria-hidden="true"></span> Add Break', // ✅ icon + text
                                                            'javascript:void(0);',
                                                            [
                                                                'class'       => 'btn btn-sm btn-info add-break-btn',
                                                                'data-id'     => $model->id,
                                                                'data-day'    => $model->day->title,
                                                                'data-toggle' => 'modal',
                                                                'data-target' => '#breakModal',
                                                            ]
                                                        );
                                                    },

                                                ],
                                            ],
                                            [
                                                'label'  => 'Breaks',
                                                'format' => 'raw',
                                                'value'  => function ($model) {
                                                    $breaks = StoreTimingsHasBrakes::find()
                                                        ->where(['store_timing_id' => $model->id])
                                                        ->all();

                                                    if (! $breaks) {
                                                        return '<span class="badge badge-secondary">No Breaks</span>';
                                                    }

                                                    $html = '';
                                                    foreach ($breaks as $b) {
                                                        $html .= '<div class="badge badge-info d-flex align-items-center justify-content-between mb-1" style="padding:5px;">' .
                                                            '<span>' . date('h:i A', strtotime($b->start_time)) . ' - ' . date('h:i A', strtotime($b->end_time)) . '</span>' .
                                                            \yii\helpers\Html::a(
                                                                '<i class="fas fa-times text-white"></i>',
                                                                ['/admin/store-timings/remove-break-timings', 'id' => $b->id],
                                                                [
                                                                    'class'   => 'ml-2 remove-break',
                                                                    'data-id' => $b->id,
                                                                    'style'   => 'color:white; text-decoration:none;',
                                                                    'title'   => 'Remove Break',
                                                                ]
                                                            ) .
                                                            '</div>';
                                                    }

                                                    return $html;
                                                },
                                            ],

                                        ],
                                    ]); ?>

                                    <!-- Update All button -->
                                    <div class="text-center mt-3">
                                        <?php echo Html::submitButton('Update Timings', [
                                            'class' => 'btn btn-success',
                                            'name'  => 'updateAll',
                                        ]) ?>
                                    </div>

                                    <!--                                         <?php ActiveForm::end(); ?> -->
                                    <?php \yii\widgets\Pjax::end(); ?>

                                    <!-- Flash message placeholder -->
                                    <div id="timings-message" class="mt-3"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        $js = <<<JS
// Form submit via AJAX
$('#timings-form').on('beforeSubmit', function(e){
    e.preventDefault();
    var form = $(this);

    // Collect only edited inputs (start_time & close_time)
    var editedInputs = $('#timings-form .start-time, #timings-form .close-time').filter(function(){
        return $(this).data('edited'); // only those marked as edited
    });

    if(editedInputs.length === 0){
        $('#timings-message').html('<div class="alert alert-warning">No changes detected.</div>');
        return false;
    }

    // Create a custom object to send only edited rows
    var payload = {};
    editedInputs.each(function(){
        var row   = $(this).closest('tr');
        var id    = row.find('td input[name*="[start_time]"]').attr('name').match(/\[(\d+)\]/)[1];
        var start = row.find('.start-time').val();
        var close = row.find('.close-time').val();

        payload[id] = {
            start_time: start,
            close_time: close
        };
    });

    // Submit via AJAX
    $.post(form.attr('action'), { StoreTimings: payload }, function(response){
        if(response.success){
            $.pjax.reload({container:'#store-timings-pjax'});
            $('#timings-message').html('<div class="alert alert-success">'+response.message+'</div>');
        } else {
            $('#timings-message').html('<div class="alert alert-danger">'+response.message+'</div>');
        }
    });

    return false;
});

// Mark inputs as edited when changed
$(document).on('change', '.start-time, .close-time', function(){
    $(this).data('edited', true);
});
$(document).on('click', '.add-break-btn', function () {
    let id = $(this).data('id');
    let day = $(this).data('day');

    $('#store_timing_id').val(id);
    $('#day-title').val(day);
});
$('.timepicker').timepicker({
    timeFormat: 'HH:mm',   // 24-hour format
    interval: 15,          // minutes step
    minTime: '00:00',      // start at midnight
    maxTime: '23:45',      // end at 23:45
    startTime: '00:00',    // first dropdown item
    dynamic: false,
    dropdown: true,
    scrollbar: true
});
///////
let breakIndex = 1;
$('#add-break-slot').on('click', function() {
    let slotHtml = $('.break-slot:first').clone();
    slotHtml.find('select').each(function() {
        let name = $(this).attr('name');
        let newName = name.replace(/\d+/, breakIndex);
        $(this).attr('name', newName).val('');
    });
    $('.break-slot:last').after(slotHtml);
    breakIndex++;
});




JS;

                        $this->registerJs($js);
                        ?>



                        <!-- Categorys Section-->
                        <?php if ($providerVendorMainCategories->totalCount > 0): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-secondary"><?php echo Yii::t('app', 'Main Categories') ?></h5>
                                    <div class="row">
                                        <?php foreach ($providerVendorMainCategories->models as $cat): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="border rounded p-3 h-100">
                                                    <h6><?php echo Html::encode($cat->mainCategory->title ?? 'N/A') ?></h6>
                                                    <div><?php echo $cat->getStateOptionsBadges() ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (! empty($model->catalog_file)): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-secondary"><?php echo Yii::t('app', 'Catalog File') ?></h5>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php
                                            $catalogFileUrl = Yii::getAlias('@web/uploads/catalogs/') . $model->catalog_file;
                                            ?>
                                            <a href="<?php echo $catalogFileUrl ?>" target="_blank" class="btn btn-outline-primary">
                                                <i class="fa fa-file-pdf-o"></i> <?php echo Html::encode($model->catalog_file) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>


                    </div>
                </div>
            </div>
        </div>
        <?php
        $uploadUrl              = Url::to(['/admin/vendor-details/upload-services-excel']); // Endpoint to handle upload
        $toggleOrderApprovalUrl = Url::to(['/admin/vendor-details/toggle-order-approval']); // New endpoint for toggle

        $csrfToken = Yii::$app->request->csrfToken;
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const uploadBtn = document.getElementById('uploadBtn');
                const fileInput = document.getElementById('excelFile');
                const responseBox = document.getElementById('uploadResponse');
                const allowOrderToggle = document.getElementById('allowOrderToggle');


                uploadBtn.addEventListener('click', function(event) {
                    event.preventDefault();

                    if (!fileInput.files.length) {
                        alert('Please select an Excel file.');
                        return;
                    }

                    // Disable button and show loading text
                    uploadBtn.disabled = true;
                    uploadBtn.textContent = 'Uploading...';

                    const formData = new FormData();
                    formData.append('excel_file', fileInput.files[0]);
                    formData.append('_csrf', '<?php echo $csrfToken ?>');
                    formData.append('vendor_details_id', '<?php echo $vendorId ?>');

                    fetch('<?php echo $uploadUrl ?>', {
                            method: 'POST',
                            body: formData,
                        })
                        .then(async (response) => {
                            const data = await response.json();
                            if (response.ok) {
                                responseBox.innerHTML = '<strong>Success:</strong> ' + data.message;
                            } else {
                                throw data;
                            }
                        })
                        .catch(error => {
                            responseBox.innerHTML = '<strong>Error:</strong> ' + (error.message || 'Upload failed.');
                        })
                        .finally(() => {
                            uploadBtn.disabled = false;
                            uploadBtn.textContent = 'Upload Excel';
                        });
                });




                // Allow order approval toggle functionality
                allowOrderToggle.addEventListener('change', function() {
                    const isChecked = this.checked;
                    const vendorId = this.getAttribute('data-vendor-id');

                    // Disable toggle while processing
                    this.disabled = true;

                    const formData = new FormData();
                    formData.append('_csrf', '<?php echo $csrfToken ?>');
                    formData.append('id', vendorId);
                    formData.append('allow_order_approval', isChecked ? '1' : '0');

                    fetch('<?php echo $toggleOrderApprovalUrl ?>', {
                            method: 'POST',
                            body: formData,
                        })
                        .then(async (response) => {
                            const data = await response.json();
                            if (response.ok && data.status === 'success') {
                                // Update the status text
                                toggleStatusText.textContent = isChecked ? 'Enabled' : 'Disabled';

                                // Show success message (optional)
                                if (responseBox) {
                                    responseBox.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                        '<strong>Success:</strong> Order approval setting updated successfully.' +
                                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                        '<span aria-hidden="true">&times;</span></button></div>';

                                    // Auto-hide after 3 seconds
                                    setTimeout(() => {
                                        const alert = responseBox.querySelector('.alert');
                                        if (alert) {
                                            alert.remove();
                                        }
                                    }, 3000);
                                }
                            } else {
                                throw new Error(data.message || 'Update failed');
                            }
                        })
                        .catch(error => {
                            // Revert toggle state on error
                            this.checked = !isChecked;
                            toggleStatusText.textContent = !isChecked ? 'Enabled' : 'Disabled';

                            // Show error message
                            if (responseBox) {
                                responseBox.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                    '<strong>Error:</strong> ' + (error.message || 'Failed to update order approval setting.') +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                    '<span aria-hidden="true">&times;</span></button></div>';
                            } else {
                                alert('Error: ' + (error.message || 'Failed to update order approval setting.'));
                            }
                        })
                        .finally(() => {
                            // Re-enable toggle
                            this.disabled = false;
                        });
                });
            });
        </script>

        <!-- ✅ SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


        <script>
            const updateUrl = '<?php echo Yii::$app->urlManager->createUrl(['/admin/vendor-details/update-vendor-details']) ?>';
            const csrfToken = '<?php echo Yii::$app->request->csrfToken ?>';
            // ✅ SweetAlert Delete Function
            function deleteBusinessImages(ids, single = false, url = null) {
                if (ids.length === 0) {
                    Swal.fire("Oops!", "No images selected.", "warning");
                    return;
                }

                Swal.fire({
                    title: "Are you sure?",
                    text: single ?
                        "You are about to delete this image." : "You are about to delete " + ids.length + " image(s).",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel",
                    dangerMode: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: single ? url : "<?php echo \yii\helpers\Url::to(['/admin/business-images/bulk-delete']) ?>",
                            data: {
                                ids: ids,
                                _csrf: yii.getCsrfToken()
                            },
                            success: function(data) {
                                if (data.status === "success") {
                                    Swal.fire("Deleted!", data.message, "success").then(() => {
                                        $.pjax.reload({
                                            container: "#kv-pjax-container-business-images"
                                        });
                                    });
                                } else {
                                    Swal.fire("Oops!", data.message, "error");
                                }
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong!", "error");
                            }
                        });
                    }
                });
            }

            // ✅ Bulk delete button flow
            $(document).on('click', '#image-selected-btn', function(e) {
                e.preventDefault();
                var ids = $('.select-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
                deleteBusinessImages(ids);
            });

            // ✅ Single delete icon flow
            $(document).on('click', '.single-delete', function(e) {
                e.preventDefault();
                var id = $(this).data("id");
                var url = $(this).data("url");
                deleteBusinessImages([id], true, url);
            });
            // Enable edit mode
            $(document).on('click', '#edit-all', function() {
                $('.view-mode').hide();
                $('.edit-mode').removeClass('d-none');
                $('#edit-all').hide();
                $('#save-all, #cancel-all').removeClass('d-none');
            });

            // Cancel edit
            $(document).on('click', '#cancel-all', function() {
                $('.view-mode').show();
                $('.edit-mode').addClass('d-none');
                $('#save-all, #cancel-all').addClass('d-none');
                $('#edit-all').show();
            });

            // Save all changes
            $(document).on('click', '#save-all', function() {
                let id = $(this).data('id');
                let data = {
                    id: id,
                    business_name: $('#business_name-input').val(),
                    contact_no: $('#contact_no-input').val(),
                    avg_rating: $('#avg_rating-input').val(),
                    commission: $('#commission-input').val(),
                    gender_type: $('#gender_type-input').val(),
                    gst_number: $('#gst-input').val(),
                    account_number: $('#account-input').val(),
                    ifsc_code: $('#ifsc-input').val(),
                    _csrf: $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    url: '<?php echo \yii\helpers\Url::to(['/admin/vendor-details/update-vendor-details']) ?>', // ✅ Yii2 generated route
                    method: 'POST',
                    data: data,
                    success: function(resp) {
                        if (resp.success) {
                            // Update UI
                            $('#business_name').text(resp.data.business_name);
                            $('#business-name').text(resp.data.business_name);
                            $('#contact_no').text(resp.data.contact_no);
                            $('#avg_rating').text(resp.data.avg_rating);
                            $('#commission').html(
                                resp.data.commission + " " + resp.data.commission_type_badge
                            );
                            $('#gender_preference').html(resp.data.gender_badge);
                            $('#gst').text(resp.data.gst_number);
                            $('#account').text(resp.data.account_number);
                            $('#ifsc').text(resp.data.ifsc_code);

                            $('#cancel-all').click(); // back to view mode

                            // ✅ SweetAlert Success
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Vendor details updated successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            // ❌ SweetAlert Error
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: resp.message || 'Something went wrong. Please try again.',
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: xhr.responseText || 'Could not connect to server.',
                        });
                    }
                });
            });
            //break timings remove popup


            $(document).on('click', '.remove-break', function(e) {
                e.preventDefault();
                var link = $(this);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to remove this break?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: link.attr('href'),
                            type: 'POST',
                            success: function(response) {
                                if (response.success) {
                                    // Remove the badge div
                                    link.closest('.badge').fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                    Swal.fire('Deleted!', response.message, 'success');
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
            let breakIndex = 1; // start after first slot

            // Open modal and prefill day
            $(document).on('click', '.add-break-btn', function() {
                let storeTimingId = $(this).data('id');
                let dayTitle = $(this).data('day');

                $('#store_timing_id').val(storeTimingId);

                // Reset break slots
                let firstSlot = $('.break-slot:first').clone();
                firstSlot.find('select').val('');
                firstSlot.find('.day-title').val(dayTitle);

                $('#break-slots-container').html(firstSlot);
                breakIndex = 1;

                $('#breakModal').modal('show');
            });

            // Add another break slot dynamically
            $('#add-break-slot').on('click', function() {
                let dayTitle = $('#day-title').val(); // keep same day
                let newSlot = $('.break-slot:first').clone();

                newSlot.find('select').each(function() {
                    let name = $(this).attr('name');
                    name = name.replace(/\d+/, breakIndex);
                    $(this).attr('name', name);
                    $(this).val('');
                });

                newSlot.find('.day-title').val(dayTitle); // fill day
                $('#break-slots-container').append(newSlot);
                breakIndex++;
            });

            // Save all breaks via AJAX
            $('#save-breaks-btn').on('click', function() {
                let storeTimingId = $('#store_timing_id').val();
                let timeSlots = [];

                $('.break-slot').each(function() {
                    let start = $(this).find('select[name*="[start_time]"]').val();
                    let end = $(this).find('select[name*="[end_time]"]').val();
                    if (start && end) {
                        timeSlots.push({
                            start_time: start,
                            end_time: end
                        });
                    }
                });

                if (timeSlots.length === 0) {
                    Swal.fire('Error', 'Please select at least one break.', 'error');
                    return;
                }

                $.ajax({
                    url: '<?= \yii\helpers\Url::to(['/admin/store-timings/add-break-timings']) ?>',
                    type: 'POST',
                    data: {
                        store_timing_id: storeTimingId,
                        timeSlots: timeSlots
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                html: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $.pjax.reload({
                                container: '#store-timings-pjax'
                            });
                            $('#breakModal').modal('hide');
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong!', 'error');
                    }
                });
            });
        </script>