<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "vendor_suppliers".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property string $suppliers_firm_name
 * @property string $contact_person
 * @property string $gst_number
 * @property string $phone_number
 * @property string $mail
 * @property string $location
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $createUser
 */
class VendorSuppliers extends \yii\db\ActiveRecord
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
            'updateUser',
            'createUser',
            'products'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_details_id', 'suppliers_firm_name', 'contact_person', 'phone_number'], 'required'],
            [['vendor_details_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['suppliers_firm_name', 'contact_person', 'mail'], 'string', 'max' => 255],
            [['gst_number', 'phone_number'], 'string', 'max' => 20],
            [['location'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_suppliers';
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
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(\app\modules\admin\models\Products::className(), ['supplier_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'suppliers_firm_name' => Yii::t('app', 'Suppliers Firm Name'),
            'contact_person' => Yii::t('app', 'Contact Person'),
            'gst_number' => Yii::t('app', 'Gst Number'),
            'phone_number' => Yii::t('app', 'Phone Number'),
            'mail' => Yii::t('app', 'Mail'),
            'location' => Yii::t('app', 'Location'),
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
    public function getUpdateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'update_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'create_user_id']);
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
     * @return \app\modules\admin\models\VendorSuppliersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorSuppliersQuery(get_called_class());
    }


public function getTotalOrderCount(){
    $products = Products::find()->where(['supplier_id' => $this->id])->count();
   return $products;
}
public function getTotalSpent(){
    $purchased_price = Products::find()->where(['supplier_id' => $this->id])->sum('purchased_price');
    $units_received = Products::find()->where(['supplier_id' => $this->id])->sum('units_received');

    $totalSpent = $purchased_price * $units_received;

    return $totalSpent;
}


public function asJson(){
    $data = [] ; 
            $data['vendor_supplier_id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['suppliers_firm_name'] =  $this->suppliers_firm_name;
        
                $data['contact_person'] =  $this->contact_person;
        
                $data['gst_number'] =  $this->gst_number;
        
                $data['phone_number'] =  $this->phone_number;
        
                $data['mail'] =  $this->mail;
        
                $data['location'] =  $this->location;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


public function asJsonView(){
    $data = [] ; 
            $data['vendor_supplier_id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['suppliers_firm_name'] =  $this->suppliers_firm_name;
        
                $data['contact_person'] =  $this->contact_person;
        
                $data['gst_number'] =  $this->gst_number;
        
                $data['phone_number'] =  $this->phone_number;
        
                $data['mail'] =  $this->mail;
        
                $data['location'] =  $this->location;

                $data['total_order_count'] =  $this->getTotalOrderCount();

                $data['total_spent'] =  $this->getTotalSpent();

                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


public function asJsonForDropDown(){
    $data = [] ; 
            $data['vendor_supplier_id'] =  $this->id;
        
            $data['vendor_details_id'] =  $this->vendor_details_id;
        
            $data['suppliers_firm_name'] =  $this->suppliers_firm_name;
        
            $data['contact_person'] =  $this->contact_person;
        
            $data['gst_number'] =  $this->gst_number;
        
            $data['phone_number'] =  $this->phone_number;
        
            $data['mail'] =  $this->mail;
        
            $data['location'] =  $this->location;
        
            return $data;
}


}


