<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "wallet".
 *
 * @property integer $id
 * @property integer $order_id 
 * @property integer $user_id
 * @property string $amount
 * @property integer $payment_type
 * @property string $method_reason
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $created_user_id
 * @property integer $updated_user_id
 *
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\User $createdUser
 * @property \app\modules\admin\models\User $updatedUser
 */
class Wallet extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'user',
            'createdUser',
            'updatedUser'
        ];
    }

    // Constants for Payment Types
    const PAYMENT_TYPE_CREDIT = 1;  // Money added to the wallet
    const PAYMENT_TYPE_DEBIT = 2;   // Money deducted from the wallet



    // Constants for Status
    const STATUS_ACTIVE = 1;        // Active transaction
    const STATUS_PENDING = 2;       // Pending transaction
    const STATUS_COMPLETED = 3;     // Completed transaction
    const STATUS_FAILED = 4;        // Failed transaction


    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const STATUS_CREDITED = 1;
    const STATUS_DEBITED = 2;


    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    public function getStateOptionsBadges()
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return '<span class="badge badge-success">Active</span>';
            case self::STATUS_PENDING:
                return '<span class="badge badge-warning">Pending</span>';
            case self::STATUS_COMPLETED:
                return '<span class="badge badge-primary">Completed</span>';
            case self::STATUS_FAILED:
                return '<span class="badge badge-danger">Failed</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }





    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'payment_type'], 'required'],
            [['user_id', 'payment_type', 'status', 'created_user_id', 'updated_user_id', 'order_id'], 'integer'], // Added order_id
            [['amount'], 'number'],
            [['method_reason'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
        ];
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wallet';
    }



    public function getFeatureOptions()
    {
        return [

            self::IS_FEATURED => 'Is Featured',
            self::IS_NOT_FEATURED => 'Not Featured',

        ];
    }

    public function getFeatureOptionsBadges()
    {
        if ($this->is_featured == self::IS_FEATURED) {
            return '<span class="badge badge-success">Featured</span>';
        } elseif ($this->is_featured == self::IS_NOT_FEATURED) {
            return '<span class="badge badge-danger">Not Featured</span>';
        }
    }


    public function getPaymentTypeOptions()
    {
        return [

            self::PAYMENT_TYPE_CREDIT => 'Credit',
            self::PAYMENT_TYPE_DEBIT => 'Debit',

        ];
    }

    public function getPaymentTypeOptionsBadges()
    {
        if ($this->payment_type == self::PAYMENT_TYPE_CREDIT) {
            return '<span class="badge badge-success">Credit</span>';
        } elseif ($this->payment_type == self::PAYMENT_TYPE_DEBIT) {
            return '<span class="badge badge-danger">Debit</span>';
        }
    }


    public static function getTotalCredit($user_id)
    {
        return self::find()
            ->where(['user_id' => $user_id, 'payment_type' => self::PAYMENT_TYPE_CREDIT, 'status' => self::STATUS_COMPLETED])
            ->sum('amount');
    }

    public static function getTotalDebit($user_id)
    {
        return self::find()
            ->where(['user_id' => $user_id, 'payment_type' => self::PAYMENT_TYPE_DEBIT, 'status' => self::STATUS_COMPLETED])
            ->sum('amount');
    }

    public static function getAvailableBalance($user_id)
    {
        // Calculate total credits (where payment type is CREDIT and status is completed)
        $credit = self::find()
            ->where(['user_id' => $user_id, 'payment_type' => self::PAYMENT_TYPE_CREDIT, 'status' => self::STATUS_COMPLETED])
            ->sum('amount');

        // Calculate total debits (where payment type is DEBIT and status is completed)
        $debit = self::find()
            ->where(['user_id' => $user_id, 'payment_type' => self::PAYMENT_TYPE_DEBIT, 'status' => self::STATUS_COMPLETED])
            ->sum('amount');

        // If no credits or debits, set to 0
        $credit = $credit ?: 0;
        $debit = $debit ?: 0;

        // Calculate the available balance
        $availableBalance = round($credit - $debit, 2);

        return $availableBalance; 
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'amount' => Yii::t('app', 'Amount'),
            'payment_type' => Yii::t('app', 'Payment Type'),
            'method_reason' => Yii::t('app', 'Method Reason'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'created_user_id' => Yii::t('app', 'Created User ID'),
            'updated_user_id' => Yii::t('app', 'Updated User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'created_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'updated_user_id']);
    }

    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value' => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_user_id',
                'updatedByAttribute' => 'updated_user_id',
            ],
        ];
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\WalletQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\WalletQuery(get_called_class());
    }
    public function asJson()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['order_id'] =  $this->order_id;

        $data['user_id'] =  $this->user_id;

        $data['amount'] =  $this->amount;

        $data['payment_type'] =  $this->payment_type;

        $data['method_reason'] =  $this->method_reason;

        $data['razorpay_order_id'] =  $this->razorpay_order_id;


        $data['transaction_id'] =  $this->transaction_id;


        $data['status'] =  $this->status;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        $data['created_user_id'] =  $this->created_user_id;

        $data['updated_user_id'] =  $this->updated_user_id;

        return $data;
    }
}
