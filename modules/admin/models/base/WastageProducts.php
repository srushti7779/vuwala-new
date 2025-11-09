<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "wastage_products".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property integer $product_id
 * @property integer $uom_id
 * @property integer $quantity
 * @property string $batch_number
 * @property integer $wastage_type
 * @property string $reason_for_wastage
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\Products $product
 * @property \app\modules\admin\models\Units $uom
 */
class WastageProducts extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

    CONST WASTAGE_TYPE_DAMAGED = 1;
    CONST WASTAGE_TYPE_EXPIRED = 2;
    CONST WASTAGE_TYPE_OVERSTOCK = 3;


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
            'product',
            'uom',
            'wastageType'
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
            [['vendor_details_id', 'product_id', 'uom_id', 'quantity', 'batch_number', 'wastage_type'], 'required'],
            [['vendor_details_id', 'product_id', 'uom_id', 'quantity', 'wastage_type', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['reason_for_wastage'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['batch_number'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wastage_products';
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
            'product_id' => Yii::t('app', 'Product ID'),
            'uom_id' => Yii::t('app', 'Uom ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'batch_number' => Yii::t('app', 'Bach Number'),
            'wastage_type' => Yii::t('app', 'Wastage Type'),
            'reason_for_wastage' => Yii::t('app', 'Reason For Wastage'),
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
    public function getProduct()
    {
        return $this->hasOne(\app\modules\admin\models\Products::className(), ['id' => 'product_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUom()
    {
        return $this->hasOne(\app\modules\admin\models\Units::className(), ['id' => 'uom_id']);
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
     * @return \app\modules\admin\models\WastageProductsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\WastageProductsQuery(get_called_class());
    }

   public function getTotalWastageValue()
    {
        // Fetch product and SKU details
        $product = $this->product;
        if (!$product || !$product->sku || !$product->sku->base_unit_id) {
            return [
                'total_value' => 0,
                'unit_name' => 'Unknown',
                'error' => 'Product or SKU or base unit not found'
            ];
        }

        $sku = $product->sku;
        $purchasedPrice = $product->purchased_price ?? 0;

        // Fetch the base unit
        $baseUnit = Units::findOne($sku->base_unit_id);
        if (!$baseUnit) {
            return [
                'total_value' => 0,
                'unit_name' => 'Unknown',
                'error' => 'Base unit not found'
            ];
        }

        // Convert quantity to base unit
        $quantityInBaseUnit = $this->quantity;
        if ($this->uom_id !== $sku->base_unit_id) {
            $conversionPath = $sku->findConversionPath($sku->id, $this->uom_id, $sku->base_unit_id);
            if ($conversionPath) {
                foreach ($conversionPath as $conversion) {
                    $quantityInBaseUnit *= $conversion['quantity'];
                }
            } else {
                \Yii::warning("No conversion path found from uom_id {$this->uom_id} to base unit ID {$sku->base_unit_id} for wastage product ID {$this->id}", __METHOD__);
                return [
                    'total_value' => 0,
                    'unit_name' => $baseUnit->unit_name,
                    'error' => "No conversion path found from unit ID {$this->uom_id} to base unit ID {$sku->base_unit_id}"
                ];
            }
        }

        // Calculate total wastage value
        $totalValue = $purchasedPrice * $quantityInBaseUnit;

        return [
            'total_value' => round($totalValue, 2),
            'unit_name' => $baseUnit->unit_name,
            'error' => null
        ];
    }

   /**
     * @return \yii\db\ActiveQuery
     */
    public function getWastageType()
    {
        return $this->hasOne(\app\modules\admin\models\WasteTypes::className(), ['id' => 'wastage_type']);
    }

public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['product_id'] =  $this->product_id;

                $data['product_name'] =  $this->product->sku->product_name ?? '';
        
                $data['uom_id'] =  $this->uom_id;
        
                $data['quantity'] =  $this->quantity;
        
                $data['batch_number'] =  $this->batch_number;

                $data['total_value'] = $this->getTotalWastageValue()['total_value'] ?? 0;
                $data['total_value_of_unit_name'] = $this->getTotalWastageValue()['unit_name'] ?? '';

                $data['wastage_type'] =  $this->wastageType->wastage_type?? '';

                $data['reason_for_wastage'] =  $this->reason_for_wastage;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;

                $data['added_by'] =  $this->createUser->user_role;

                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


