<?php

namespace app\components;

use app\modules\admin\models\Orders;
use yii\base\Component;

class OrderStats extends Component
{
    // public function getStatusButtons($model)
    // {
    //     switch ($model->status) {

    //         case Orders::STATUS_ACCEPTED:
    //             return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_STARTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Started
    //             </button>&nbsp;
    //             <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
    //             </button>';
    //             break;

    //         case Orders::STATUS_SERVICE_STARTED:
    //             return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_COMPLETED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Completed
    //             </button>&nbsp;
    //             <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
    //             </button>';
    //             break;

    //         case Orders::STATUS_SERVICE_COMPLETED:
    //             return '<button type="button" class="btn btn-success float-right" disabled><i class="far fa-check-square"></i> Service Completed
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_OWNER:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Shop Owner
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_USER:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By User
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_ADMIN:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Admin
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_HOME_VISITORS:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Home Visitor
    //             </button>';
    //             break;

    //         default:
    //             return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_ACCEPTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Accept Order
    //             </button>
    //             <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_OWNER . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Reject Order
    //             </button>';
    //     }
    // }

    // public function getWalkInStatusButtons($model) 
    // {
    //     switch ($model->status) {

    //         case Orders::STATUS_ACCEPTED:
    //             return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_STARTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Started
    //             </button>&nbsp;
    //             <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
    //             </button>';
    //             break; 

    //         case Orders::STATUS_SERVICE_STARTED:
    //             return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_COMPLETED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Completed
    //             </button>&nbsp;
    //             <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
    //             </button>'; 
    //             break;

    //         case Orders::STATUS_SERVICE_COMPLETED:
    //             return '<button type="button" class="btn btn-success float-right" disabled><i class="far fa-check-square"></i> Service Completed
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_OWNER:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Shop Owner
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_USER:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By User
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_ADMIN:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Admin
    //             </button>';
    //             break;

    //         case Orders::STATUS_CANCELLED_BY_HOME_VISITORS:
    //             return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Home Visitor
    //             </button>';
    //             break;

    //         default:
    //             return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_ACCEPTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Accept Order
    //             </button>
    //             <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_OWNER . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Reject Order
    //             </button>';
    //     }
    // }


    public function getStatusButtons($model)
    {
        switch ($model->status) {

            case Orders::STATUS_ACCEPTED:
                return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_STARTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Started
                </button>&nbsp;
                ' .
                    /* '<button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
                </button>' */
                    '';
                break;


            case Orders::STATUS_SERVICE_STARTED:
                return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_COMPLETED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Completed
                </button>&nbsp;
               ' .
                    /* '<button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
                </button>' */
                    '';
                break;

            case Orders::STATUS_SERVICE_COMPLETED:
                return '<button type="button" class="btn btn-success float-right" disabled><i class="far fa-check-square"></i> Service Completed
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_OWNER:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Shop Owner
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_USER:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By User
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_ADMIN:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Admin
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_HOME_VISITORS:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Home Visitor
                </button>';
                break;

            default:
                return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_ACCEPTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Accept Order
                </button>
                <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_OWNER . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Reject Order
                </button>';
        }
    }

    public function getWalkInStatusButtons($model)
    {
        switch ($model->status) {

            case Orders::STATUS_ACCEPTED:
                return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_STARTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Started
                </button>&nbsp;
             ' .
                    /* '<button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
                </button>' */
                    '';
                break;

            case Orders::STATUS_SERVICE_STARTED:
                return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_SERVICE_COMPLETED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Service Completed
                </button>&nbsp;
            ' .
                    /* '<button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_ADMIN . '" data-id="' . $model->id . '"><i class="fas fa-window-close"></i> Cancel Order
                </button>' */
                    '';  
                break;

            case Orders::STATUS_SERVICE_COMPLETED:
                return '<button type="button" class="btn btn-success float-right" disabled><i class="far fa-check-square"></i> Service Completed
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_OWNER:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Shop Owner
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_USER:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By User
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_ADMIN:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Admin
                </button>';
                break;

            case Orders::STATUS_CANCELLED_BY_HOME_VISITORS:
                return '<button type="button" class="btn btn-danger float-right" disabled><i class="far fa-check-square"></i> Order Cancelled By Home Visitor
                </button>';
                break;

            default:
                return '<button type="button" class="btn btn-success float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_ACCEPTED . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Accept Order
                </button>
                <button type="button" class="btn btn-danger float-right" id="order-status_' . $model->id . '" value="' . Orders::STATUS_CANCELLED_BY_OWNER . '" data-id="' . $model->id . '"><i class="far fa-check-square"></i> Reject Order
                </button>';
        }
    }
}
