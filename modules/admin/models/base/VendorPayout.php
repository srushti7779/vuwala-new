<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "vendor_payout".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property double $amount
 * @property integer $payment_type
 * @property string $method_reason
 * @property integer $type_id
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class VendorPayout extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
           'vendorDetails',
            'createUser',
            'updateUser',
            'vendorSettlements'
        ];
    }





    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    const STATUS_APPROVED = 1;
    const STATUS_PROCESSING = 2; 
    const STATUS_REJECTED = 3;


    const PAYMENT_TYPE_CREDIT = 1;
    const PAYMENT_TYPE_DEBIT = 2;

    



 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_details_id', 'amount', 'payment_type', 'method_reason', 'status'], 'required'],
            [['vendor_details_id', 'payment_type', 'type_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['amount'], 'number'],
            [['method_reason', 'created_on', 'updated_on'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_payout';
    }

    public function getStateOptions()
    {
         return [
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_PROCESSING => 'Processing',
    ];
       
    }
    public function getStateOptionsBadges()
    {

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
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'amount' => Yii::t('app', 'Amount'),
            'payment_type' => Yii::t('app', 'Payment Type'),
            'method_reason' => Yii::t('app', 'Method Reason'),
            'type_id' => Yii::t('app', 'Type ID'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }
  





       /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'create_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'update_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorSettlements()
    {
        return $this->hasMany(\app\modules\admin\models\VendorSettlements::className(), ['vendor_payout_id' => 'id']);
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


    public function getBankDetails(){
        $bank_details=[];
        $vendor_details = VendorDetails::findOne(['id'=>$this->vendor_details_id]);
        if($vendor_details){
            $bank_details['account_holder_name'] = $vendor_details->account_holder_name;
            $bank_details['account_number'] = $vendor_details->account_number;
            $bank_details['ifsc_code'] = $vendor_details->ifsc_code;
            $bank_details['bank_name'] = $vendor_details->bank_name;
            $bank_details['bank_branch'] = $vendor_details->bank_branch;
            $bank_details['bank_state'] = $vendor_details->bank_state;
            $bank_details['bank_city'] = $vendor_details->bank_city;
        }
        return $bank_details;
    }


    /**
     * @inheritdoc
     * @return \app\modules\admin\models\VendorPayoutQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorPayoutQuery(get_called_class());
    }


    public function getOrdersCount(){
        $vendor_settlements = $this->getVendorSettlements()->count();
        return $vendor_settlements;
    }





public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['amount'] =  $this->amount;
        
                $data['payment_type'] =  $this->payment_type;
        
                $data['method_reason'] =  $this->method_reason;

                $data['orders_count'] = $this->getOrdersCount();

                $data['bank_details'] = $this->getBankDetails();

                $data['type_id'] =  $this->type_id;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


