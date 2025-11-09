<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "guest_user_deposits".
 *
 * @property integer $id
 * @property integer $guest_user_id
 * @property integer $stores_has_user_id
 * @property double $amount
 * @property string $date_and_time
 * @property integer $payment_mode
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $created_user_id
 * @property integer $updated_user_id
 *
 * @property \app\modules\admin\models\User $guestUser
 * @property \app\modules\admin\models\StoresHasUsers $storesHasUser
 * @property \app\modules\admin\models\User $createdUser
 * @property \app\modules\admin\models\User $updatedUser
 */
class GuestUserDeposits extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'guestUser',
            'storesHasUser',
            'createdUser',
            'updatedUser'
        ];
    }
    // Constants for Status
    const STATUS_ACTIVE = 1;        // Active transaction
    const STATUS_PENDING = 2;       // Pending transaction
    const STATUS_COMPLETED = 3;     // Completed transaction
    const STATUS_FAILED = 4;        // Failed transaction

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const PAYMENT_TYPE_ONLINE = 1;
    const PAYMENT_TYPE_QR = 2;
    const PAYMENT_TYPE_WALLET = 3;
    const PAYMENT_TYPE_COD = 4;

    const PAYMENT_TYPE_CREDIT = 1;  // Money added to the wallet
    const PAYMENT_TYPE_DEBIT = 2;   // Money deducted from the wallet

 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['guest_user_id', 'stores_has_user_id', 'amount', 'date_and_time', 'payment_mode'], 'required'],
            [['guest_user_id', 'stores_has_user_id', 'payment_mode', 'status', 'created_user_id', 'updated_user_id'], 'integer'],
            [['amount'], 'number'],
            [['date_and_time', 'created_on', 'updated_on'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'guest_user_deposits';
    }

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

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_PENDING) {
            return '<span class="badge badge-warning">Pending</span>';
        } elseif ($this->status == self::STATUS_COMPLETED) {
            return '<span class="badge badge-info">Completed</span>';
        } elseif ($this->status == self::STATUS_FAILED) {
            return '<span class="badge badge-danger">Failed</span>';
        }

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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'guest_user_id' => Yii::t('app', 'Guest User ID'),
            'stores_has_user_id' => Yii::t('app', 'Stores Has User ID'),
            'amount' => Yii::t('app', 'Amount'),
            'date_and_time' => Yii::t('app', 'Deposit Date And Time'),
            'payment_mode' => Yii::t('app', 'Payment Mode'),
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
    public function getGuestUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'guest_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoresHasUser()
    {
        return $this->hasOne(\app\modules\admin\models\StoresHasUsers::className(), ['id' => 'stores_has_user_id']);
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
    public static function getCreditedAmount($guest_user_id,$store_has_user_id){
        $guest_user_deposits = GuestUserDeposits::find()
            ->where(['guest_user_id' => $guest_user_id])
            ->andWhere(['store_has_user_id' => $store_has_user_id])
            ->andWhere(['payment_type' => GuestUserDeposits::PAYMENT_TYPE_CREDIT])
            ->sum('amount');

        return $guest_user_deposits;
    }
    public static function getDebitedAmount($guest_user_id,$store_has_user_id){
        $guest_user_deposits = GuestUserDeposits::find()
            ->where(['guest_user_id' => $guest_user_id])
            ->andWhere(['store_has_user_id' => $store_has_user_id])
            ->andWhere(['payment_type' => GuestUserDeposits::PAYMENT_TYPE_DEBIT])
            ->sum('amount');

        return $guest_user_deposits;
    }

    public static function getAvailableDepositBalance($guest_user_id,$store_has_user_id){
        $credited_amount = self::getCreditedAmount($guest_user_id,$store_has_user_id);
        $debited_amount = self::getDebitedAmount($guest_user_id,$store_has_user_id);
        return $credited_amount - $debited_amount;
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
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\GuestUserDepositsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\GuestUserDepositsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['guest_user_id'] =  $this->guest_user_id;
        
                $data['stores_has_user_id'] =  $this->stores_has_user_id;
        
                $data['amount'] =  $this->amount;

                $data['order_id'] =  $this->order_id;
                $data['payment_type'] =  $this->payment_type;

        
                $data['date_and_time'] =  $this->date_and_time;
        
                $data['payment_mode'] =  $this->payment_mode;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['created_user_id'] =  $this->created_user_id;
        
                $data['updated_user_id'] =  $this->updated_user_id;
        
            return $data;
}


}


