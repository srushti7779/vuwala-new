<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "vendor_expenses".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property integer $expense_type_id
 * @property integer $payment_mode
 * @property string $expense_date
 * @property string $amount
 * @property string $notes
 * @property string $image_url
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\VendorExpensesTypes $expenseType
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class VendorExpenses extends \yii\db\ActiveRecord
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
            'expenseType',
            'createUser',
            'updateUser'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const PAYMENT_MODE_CASH = 1;
    const PAYMENT_MODE_CHEQUE = 2;
    const PAYMENT_MODE_ONLINE = 3;
    const PAYMENT_MODE_OTHER = 4;

     public static function getPaymentModeOptions()
    {
        return [
            self::PAYMENT_MODE_CASH => 'Cash',
            self::PAYMENT_MODE_CHEQUE => 'Cheque',
            self::PAYMENT_MODE_ONLINE => 'Online',
            self::PAYMENT_MODE_OTHER => 'Other',
        ];
    }

    public function getPaymentModeOptionsBadges()
    {
        if ($this->payment_mode == self::PAYMENT_MODE_CASH) {
            return '<span class="badge badge-success">Cash</span>';
        } elseif ($this->payment_mode == self::PAYMENT_MODE_CHEQUE) {
            return '<span class="badge badge-primary">Cheque</span>';
        } elseif ($this->payment_mode == self::PAYMENT_MODE_ONLINE) {
            return '<span class="badge badge-info">Online</span>';
        } elseif ($this->payment_mode == self::PAYMENT_MODE_OTHER) {
            return '<span class="badge badge-warning">Other</span>';
        }
    }

    
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_details_id', 'expense_type_id', 'payment_mode', 'expense_date', 'amount'], 'required'],
            [['vendor_details_id', 'expense_type_id', 'payment_mode', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['expense_date', 'created_on', 'updated_on'], 'safe'],
            [['amount'], 'number'],
            [['notes'], 'string'],
            [['image_url'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_expenses';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_DELETE => 'Deleted',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-warning">In Active</span>';
        }elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
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
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'expense_type_id' => Yii::t('app', 'Expense Type ID'),
            'payment_mode' => Yii::t('app', 'Payment Mode'),
            'expense_date' => Yii::t('app', 'Expense Date'),
            'amount' => Yii::t('app', 'Amount'),
            'notes' => Yii::t('app', 'Notes'),
            'image_url' => Yii::t('app', 'Image Url'),
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
    public function getExpenseType()
    {
        return $this->hasOne(\app\modules\admin\models\VendorExpensesTypes::className(), ['id' => 'expense_type_id']);
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
     * @return \app\modules\admin\models\VendorExpensesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorExpensesQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['expense_type_id'] =  $this->expense_type_id;
                $data['expenseType'] = $this->expenseType ? $this->expenseType->type : null;

        
                $data['payment_mode'] =  $this->payment_mode;
        
                $data['expense_date'] =  $this->expense_date;
        
                $data['amount'] =  $this->amount;
        
                $data['notes'] =  $this->notes;
        
                $data['image_url'] =  $this->image_url;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


