<?php

use app\models\User;
use app\modules\admin\models\base\ProductServiceOrderMappings;
use app\modules\admin\models\ComboOrder;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderStatus;
use app\modules\admin\models\Staff;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Orders */

$this->title = "Order #" . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;



$status_array = [
  Orders::STATUS_NEW_ORDER,
  Orders::STATUS_ACCEPTED,
  Orders::STATUS_SERVICE_STARTED,
  Orders::STATUS_ASSIGNED_SERVICE_STAFF,
];
$this->registerCss(<<<CSS
.breadcrumb-custom {
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    color: #6c757d;
}
.breadcrumb-custom a {
    color: #0d6efd;
    text-decoration: none;
}
.card {
    border-radius: 0.75rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
    margin-bottom: 1rem;
    overflow: hidden;
}
.card-header {
    background: linear-gradient(to right, #030e3eff, #160447ff);
    color:#efefef;
    font-weight: 600;
    padding: 1rem 1.5rem;
}
.card-title {
    font-size: 1.1rem;
}
.table thead th {
    background-color: #f1f1f1;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
}
.table td, .table th {
    padding: 0.75rem;
    font-size: 0.9rem;
}
.badge {
    font-size: 0.85rem;
    padding: 0.4em 0.7em;
}
.total-row th {
    text-align: right;
    width: 70%;
}
.total-row td {
    text-align: left;
    font-weight: 500;
}
.btn {
    border-radius: 0.375rem;
}
.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
}
CSS);
?>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-9 col-md-8 col-sm-12">
        <div class="invoice p-3 mb-3">
          <!-- Title Row -->
          <div class="row">
            <div class="col-12">
              <h4>
                <i class="fas fa-globe"></i>&nbsp;<?= Html::encode(Yii::$app->name); ?>
                <small class="float-right">Order Placed: <?= $model->created_on; ?></small>
              </h4>
            </div>
          </div>

          <!-- Info Row -->
          <div class="row invoice-info mt-4">
            <div class="col-md-4 col-sm-6">
              <strong>From:</strong>
              <address>
                <strong><?= $model->vendorDetails->business_name; ?></strong><br>
                <?= $model->vendorDetails->address; ?><br>
                <?= $model->vendorDetails->user->contact_no; ?>
              </address>
            </div>
            <div class="col-md-4 col-sm-6">
              <strong>To:</strong>
              <address>
                <strong><?= $model->user->username; ?></strong><br>
                <?= !empty($model->deliveryAdd) && !empty($model->deliveryAdd->address) ? $model->deliveryAdd->address : ''; ?><br>
                <?= !empty($model->deliveryAdd) && !empty($model->deliveryAdd->location) ? $model->deliveryAdd->location : ''; ?>



              </address>
            </div>
            <div class="col-md-4 col-sm-12">
              <b>Invoice #<?= $model->id; ?></b><br>
              <b>Payment Type:</b> <?= $model->getPaymentTypeOptionBadges(); ?><br>
              <b>Payment Status:</b> <?= $model->getPaymentStatusOptionBadges(); ?><br>
              <b>Order Status:</b> <?= $model->getStateOptionsBadges(); ?><br>
              <b>Service Type:</b> <?= $model->getServiceTypeOptionBadges(); ?><br>
              <b>Service Date:</b> <?= $model->schedule_date; ?><br>
              <b>Service Address:</b>
              <?php if ($model->service_type === $model::HOME_VISIT): ?>
                <?= $model->deliveryAddress->address; ?>
              <?php elseif ($model->service_type === $model::WALK_IN): ?>

                <?= $model->vendorDetails->address; ?>
              <?php endif; ?>
            </div>

          </div>

          <!-- Table Row -->
          <div class="row mt-4">
            <div class="col-12 table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Qty</th>
                    <th>Service</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($providerOrderDetails->models as $items) : ?>
                    <tr>
                      <td><?= $items->qty; ?></td>
                      <td><?= $items->service->service_name; ?></td>
                      <td><?= $items->price; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Total Row -->
          <div class="row mt-4">
            <div class="col-lg-6"></div>
            <div class="col-lg-6">
              <table class="table">
                <tbody>
                  <tr>
                    <th style="width:50%">Subtotal:</th>
                    <td><?= $model->sub_total; ?></td>
                  </tr>
                  <tr>
                    <th>Tax:</th>
                    <td><?= $model->tax; ?></td>
                  </tr>
                  <tr>
                    <th>Tip:</th>
                    <td><?= $model->tip_amt; ?></td>
                  </tr>
                  <tr>
                    <th>Processing Fees:</th>
                    <td><?= $model->processing_charges; ?></td>
                  </tr>
                  <tr>
                    <th>Service Charge:</th>
                    <td><?= $model->service_charge; ?></td>
                  </tr>
                  <tr>
                    <th>Coupon Discount:</th>
                    <td><?= $model->voucher_amount; ?></td>
                  </tr>
                  <tr>
                    <th>Grand Total:</th>
                    <td><?= $model->total_w_tax; ?></td>
                  </tr>
                  <?php if (!empty($orderCancelled)) : ?>
                    <tr>
                      <th>Cancellation Charges:</th>
                      <td><?= $orderCancelled->cancellation_charges; ?></td>
                    </tr>
                    <tr>
                      <th><b>Refund Amount:</b></th>
                      <td><?= $model->total_w_tax - $orderCancelled->cancellation_charges; ?></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="row no-print mt-4">
            <div class="col-12">
              <a href="#" target="_blank" onclick="window.print()" class="btn btn-default"><i class="fas fa-print"></i> Print</a>
              <?= Html::a('<i class="fas fa-file-pdf"></i> Download Invoice PDF', ['/admin/orders/download-pdf', 'id' => $model->id], [
                'class' => 'btn btn-outline-danger',
                'target' => '_blank'
            ]) ?>

                 <!-- Approve Button for New Orders -->
                <button 
                  type="button" 
                  class="btn btn-approve ml-2" 
                  id="approve-order-btn"
                  data-order-id="<?= $model->id ?>"
                  data-vendor-allow-approval="<?= $model->vendorDetails->allow_order_approval ?? 0 ?>"
                >
                  <i class="fas fa-check-circle"></i> Approve Order
                </button>


              <?php
              if ($model->trans_type == Orders::TRANS_TYPE_HOME_VISIT) {
                echo Yii::$app->orderStatus->getStatusButtons($model);
              } else if ($model->trans_type == Orders::TRANS_TYPE_WALK_IN) {
                echo Yii::$app->orderStatus->getWalkInStatusButtons($model);
              }
              ?>
            </div>
          </div>
        </div>
      </div>





      <!-- Right Sidebar -->
      <div class="col-lg-3 col-md-4 col-sm-12">

        <!-- OTP -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title">OTP: <?= !empty($model->otp) ? $model->otp : '' ?></h3>
          </div>
        </div>
    <!-- Support Ticket Button -->
    <div class="card mb-3">
      <div class="card-body text-center">
        <?= Html::a(
          'Raise Support Ticket',
          ['/admin/order-complaints/support', 'order_id' => $model->id, 'store_id' => $model->vendor_details_id],
          ['class' => 'btn btn-danger btn-block']
        ); ?>
      </div>
    </div>


        <!-- Order Status History -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title">Order Status History</h3>
          </div>
          <div class="card-body">
            <?php
            try {
              $orderStatus = OrderStatus::find()->where(['order_id' => $model->id])->all();
              if (!empty($orderStatus)) {
                foreach ($orderStatus as $status) {
                  echo Html::tag('div', $status->remarks);
                }
              } else {
                echo 'No order status found';
              }
            } catch (\Exception $e) {
              Yii::error("Error fetching order status history for order ID {$model->id}: " . $e->getMessage(), __METHOD__);
              echo 'Error fetching order status history';
            }
            ?>
          </div>
        </div>



        <!-- Home Visitor Assignment Form (only if service type is HOME_VISIT) -->
        <?php if ($model->service_type == Orders::TRANS_TYPE_HOME_VISIT): ?>
          <?php
          try {
            $home_visitors_has_orders = HomeVisitorsHasOrders::find()->where(['order_id' => $model->id])->one();

            // If a Home Visitor is already assigned, display details with an option to reassign
            if (!empty($home_visitors_has_orders)) {
          ?>
              <div class="card mb-3">
                <div class="card-header">
                  <h3 class="card-title">Assigned Home Visitor</h3>
                </div>
                <div class="card-body">
                  <p><strong>Name:</strong> <?= $home_visitors_has_orders->homeVisitor->full_name ?></p>
                  <p><strong>Mobile:</strong> <?= $home_visitors_has_orders->homeVisitor->mobile_no ?></p>
                  <p><strong>Email:</strong> <?= $home_visitors_has_orders->homeVisitor->email ?></p>
                  <p><strong>Gender:</strong> <?= $home_visitors_has_orders->homeVisitor->gender == 'M' ? 'Male' : 'Female' ?></p>
                  <p><strong>Date of Birth:</strong> <?= Yii::$app->formatter->asDate($home_visitors_has_orders->homeVisitor->dob) ?></p>
                  <p><strong>Role:</strong> <?= $home_visitors_has_orders->homeVisitor->role ?></p>
                  <!-- Button to reassign home visitor -->
                  <?php


                  if (in_array($model->status, $status_array)) {
                    Html::button('Reassign Home Visitor', ['class' => 'btn btn-warning', 'id' => 'reassign-home-visitor-btn']);
                  }
                  ?>
                </div>
              </div>

              <!-- Reassign Form (initially hidden) -->
              <div class="card mb-3" id="reassign-form" style="display: none;">
                <div class="card-header">
                  <h3 class="card-title">Reassign Home Visitor</h3>
                </div>
                <div class="card-body">
                  <div class="home-visitors-form">
                    <?php

                    if (in_array($model->status, $status_array)) {

                      $form = ActiveForm::begin([
                        'id' => 'login-form-inline',
                        'type' => ActiveForm::TYPE_VERTICAL,
                        'tooltipStyleFeedback' => true,
                        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-12 col-sm-12 col-md-12 col-lg-12']],
                        'formConfig' => ['showErrors' => true],
                      ]);
                    ?>
                      <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

                      <?= $form->field($model, 'home_visitor_id')->widget(\kartik\widgets\Select2::classname(), [
                        'data' => \yii\helpers\ArrayHelper::map(Staff::find()
                          ->where(['role' => Staff::ROLE_HOME_VISITOR])
                          ->andWhere(['status' => Staff::STATUS_ACTIVE])
                          ->andWhere(['vendor_details_id' => $model->vendor_details_id])
                          ->orderBy('id')->asArray()->all(), 'id', function ($model) {
                          $current_status = ($model['current_status'] == Staff::CURRENT_STATUS_IDLE || $model['current_status'] == '') ? 'Idle' : 'Busy';
                          $full_name = $model['full_name'] . ' (' . $current_status . ')';
                          return $full_name;
                        }),
                        'options' => ['placeholder' => Yii::t('app', 'Choose Home Visitor')],
                        'pluginOptions' => [
                          'allowClear' => true
                        ],
                      ])->label('Home Visitor'); ?>

                      <?= Html::button(Yii::t('app', 'Reassign Home Visitor'), ['class' => 'btn btn-primary', 'id' => 'confirm-reassign-btn']) ?>
                        <?= Html::button(Yii::t('app', 'Reassign Home Visitor'), ['class' => 'btn btn-primary', 'id' => 'confirm-reassign-btn']) ?>
                    <?php ActiveForm::end();
                    }
                    ?>
                  </div>
                </div>
              </div>

            <?php
            } else {
              // No Home Visitor assigned yet, show the assignment form
            ?>
              <div class="card mb-3">
                <div class="card-header">
                  <h3 class="card-title">Assign Home Visitor</h3>
                </div>
                <div class="card-body">
                  <div class="home-visitors-form">
                    <?php
                    if (in_array($model->status, $status_array)) {


                      $form = ActiveForm::begin([
                        'id' => 'login-form-inline',
                        'type' => ActiveForm::TYPE_VERTICAL,
                        'tooltipStyleFeedback' => true,
                        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-12 col-sm-12 col-md-12 col-lg-12']],
                        'formConfig' => ['showErrors' => true],
                      ]);
                    ?>
                      <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

                      <?= $form->field($model, 'home_visitor_id')->widget(\kartik\widgets\Select2::classname(), [
                        'data' => \yii\helpers\ArrayHelper::map(Staff::find()
                          ->where(['role' => Staff::ROLE_HOME_VISITOR])
                          ->andWhere(['status' => Staff::STATUS_ACTIVE])
                          ->andWhere(['vendor_details_id' => $model->vendor_details_id])
                          ->orderBy('id')->asArray()->all(), 'id', function ($model) {
                          $current_status = ($model['current_status'] == Staff::CURRENT_STATUS_IDLE || $model['current_status'] == '') ? 'Idle' : 'Busy';
                          $full_name = $model['full_name'] . ' (' . $current_status . ')';
                          return $full_name;
                        }),
                        'options' => ['placeholder' => Yii::t('app', 'Choose Home Visitor')],
                        'pluginOptions' => [
                          'allowClear' => true
                        ],
                      ])->label('Home Visitor'); ?>

                      <?= Html::button(Yii::t('app', 'Assign Home Visitor'), ['class' => 'btn btn-primary', 'id' => 'assign-home-visitor-btn']) ?>
                    <?php ActiveForm::end();
                    }




                    ?>



                  </div>
                </div>
              </div>
          <?php
            }
          } catch (\Exception $e) {
            Yii::error("Error fetching Home Visitors for order ID {$model->id}: " . $e->getMessage(), __METHOD__);
            echo 'Error fetching Home Visitor data';
          }
          ?>
        <?php endif; ?>
      </div>
      

    </div>
  </div>
</section>

<!-- Modal for Assigning Delivery Boy -->
<div class="modal fade" id="modal-default" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Assign Delivery Boy</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="delivery-form">
          <div class="form-group">
            <label for="delivery_boy">Select Delivery Boy:</label>
            <select id="delivery_boy" class="form-control">
              <option value="">Select Name</option>
              <!-- Add delivery boys dynamically via JS or PHP -->
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="assign-delivery">Assign Order</button>
      </div>
    </div>
  </div>
</div>
 <!-- Order History -->
<div class="card shadow-sm mb-4">
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
<!---- Products Service order  history --->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5><i class="fas fa-box-open me-2"></i>Product Service Orders History</h5>
    </div>
    <div class="card-body">
        <?php
        $orderIdFromUrl = Yii::$app->request->get('id');

        $orderModel = Orders::findOne($orderIdFromUrl);

        $productServiceQuery = ProductServiceOrderMappings::find()
            ->where(['order_id' => $orderIdFromUrl])
            ->orderBy(['id' => SORT_DESC]);

        $orderDataProvider = new ActiveDataProvider([
            'query' => $productServiceQuery,
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

                    // Order Reference
             [
                'attribute' => 'order_id',
                'value' => function($model) {
                    return $model->order ? $model->order->id : 'N/A';
                },
            ],

            [
                'attribute' => 'product_order_id',
                'value' => function($model) {
                    return $model->product ? $model->product->id : 'N/A';
                },
            ],

                    // Vendor
                    [
                        'attribute' => 'vendor_details_id',
                        'value' => function($model) {
                            return $model->vendor ? $model->vendor->vendor_name : 'N/A';
                        },
                        'label' => 'Vendor',
                    ],

                    // Status with badge
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) {
                            $statuses = [
                                0 => '<span class="badge bg-secondary">Pending</span>',
                                1 => '<span class="badge bg-success">Completed</span>',
                                2 => '<span class="badge bg-danger">Cancelled</span>',
                            ];
                            return $statuses[$model->status] ?? '<span class="badge bg-dark">Unknown</span>';
                        },
                    ],

                    // Actions
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'header' => 'Actions',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a(
                                    '<i class="fas fa-eye"></i>',
                                    Url::to(['/admin/product-service-order-mappings/view', 'id' => $model->id]),
                                    [
                                        'title' => 'View Order Mapping',
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
            echo '<div class="text-muted small"><i class="fas fa-info-circle"></i> No product/service orders found for this Order ID.</div>';
        }
        ?>
    </div>
</div>

<!---- Products Service order  history --end ->


<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
  $(document).on('click', '[id^=order-status_]', function() {
    var id = $(this).data('id');
    var val = $(this).val();

    swal({
      title: "Are you sure?",
      text: "Once status is updated, you cannot undo it!",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((isConfirm) => {
      if (isConfirm) {
        $.post("<?= Url::toRoute(['/admin/orders/update-order-status']) ?>", {
          id: id,
          val: val
        }, function(response) {
          if (response.status == 'success') {
            swal("Success!", "Status updated successfully.", "success")
              .then(() => location.reload());
          } else {
            swal("Error", response.message, "error");
          }
        }).fail(function(jqXHR, textStatus) {
          swal("Error", "An unexpected error occurred: " + textStatus, "error");
        });
      }
    });
  });

  // Function to handle home visitor assignment
  $('#assign-home-visitor-btn').on('click', function() {
    var form = $('#login-form-inline');
    var formData = form.serialize();

    $.ajax({
      url: '<?= Url::to(['orders/assign-home-visitor']) ?>',
      type: 'POST',
      data: formData,
      success: function(response) {
        if (response.status === 'success') {
          swal("Success!", response.message, "success")
            .then(() => location.reload()); // Reload the page after success
        } else {
          swal("Error", response.message, "error");
        }
      },
      error: function(jqXHR, textStatus) {
        swal("Error", "An unexpected error occurred: " + textStatus, "error");
      }
    });
  });

  // Show Reassign Form when the reassign button is clicked
  $('#reassign-home-visitor-btn').on('click', function() {
    console.log("Reassign button clicked"); // Debugging log
    var reassignForm = $('#reassign-form');

    if (reassignForm.length > 0) {
      reassignForm.slideDown(); // Show the reassign form
      $(this).hide(); // Hide the reassign button
      console.log("Reassign form displayed"); // Debugging log
    } else {
      console.log("Reassign form not found in the DOM"); // If the form doesn't exist
    }
  });

  // Submit the Reassign Form via AJAX to the backend
  $('#confirm-reassign-btn').on('click', function() {
    var form = $('#login-form-inline');
    var formData = form.serialize();

    $.ajax({
      url: '<?= Url::to(['orders/reassign-home-visitor']) ?>', // Adjust the URL to match your backend action
      type: 'POST',
      data: formData,
      success: function(response) {
        if (response.status === 'success') {
          swal("Success!", response.message, "success")
            .then(() => location.reload()); // Reload the page after successful reassignment
        } else {
          swal("Error", response.message, "error");
        }
      },
      error: function(jqXHR, textStatus) {
        swal("Error", "An unexpected error occurred: " + textStatus, "error");
      }
    });
  });




   // Approve Order Button Handler
  $('#approve-order-btn').on('click', function() {
    var orderId = $(this).data('order-id');
    var vendorAllowApproval = $(this).data('vendor-allow-approval');
    
    // Determine next status based on vendor approval setting
    var nextStatus = vendorAllowApproval == 1 ? <?= Orders::STATUS_ACCEPTED ?> : <?= Orders::STATUS_ACCEPTED ?>;
    
    swal({
      title: "Approve Order?",
      text: "Are you sure you want to approve this order?",
      icon: "question",
      buttons: {
        cancel: "Cancel",
        confirm: {
          text: "Approve",
          value: true,
          className: "btn-success"
        }
      },
    }).then((isConfirm) => {
      if (isConfirm) {
        // Disable button to prevent double-click
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Approving...');
        
        $.post("<?= Url::toRoute(['/admin/orders/approve-order']) ?>", {
          id: orderId,
          next_status: nextStatus
        }, function(response) {
          if (response.status == 'success') {
            swal("Success!", response.message, "success")
              .then(() => location.reload());
          } else {
            swal("Error", response.message, "error");
            // Re-enable button on error
            $('#approve-order-btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Approve Order');
          }
        }).fail(function(jqXHR, textStatus) {
          swal("Error", "An unexpected error occurred: " + textStatus, "error");
          // Re-enable button on error
          $('#approve-order-btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Approve Order');
        });
      }
    });
  });
</script>