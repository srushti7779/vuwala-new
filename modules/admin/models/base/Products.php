<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "products".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property integer $sku_id
 * @property integer $discount_allowed
 * @property integer $supplier_id
 * @property string $batch_number
 * @property string $purchase_date
 * @property double $mrp_price
 * @property double $selling_price
 * @property string $expire_date
 * @property double $units_received
 * @property integer $received_units_id
 * @property string $invoice_number
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $purchased_price
 * @property string $ean_code

 * 
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\VendorSuppliers $supplier
 * @property \app\modules\admin\models\Sku $sku
 * @property \app\modules\admin\models\Units $units
 * @property \app\modules\admin\models\Units $receivedUnits
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class Products extends \yii\db\ActiveRecord
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
            'supplier',
            'sku',
            'receivedUnits',
            'createUser',
            'updateUser',
            'productOrderItems',
            'productServices'
        ];
    }

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_details_id', 'sku_id', 'supplier_id', 'batch_number', 'purchase_date', 'mrp_price', 'selling_price', 'expire_date', 'units_received', 'received_units_id', 'invoice_number'], 'required'],
            [['vendor_details_id', 'sku_id', 'units_id', 'supplier_id', 'received_units_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [[ 'mrp_price', 'selling_price', 'units_received'], 'number'],
            [['purchase_date', 'expire_date', 'created_on', 'updated_on'], 'safe'],
            [['discount_allowed'], 'string', 'max' => 1],
            [['batch_number', 'invoice_number'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products';
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
        } elseif ($this->status == self::STATUS_DELETE) {
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
            'sku_id' => Yii::t('app', 'Sku ID'),
            'discount_allowed' => Yii::t('app', 'Discount Allowed'),
            'supplier_id' => Yii::t('app', 'Supplier ID'),
            'batch_number' => Yii::t('app', 'Batch Number'),
            'purchase_date' => Yii::t('app', 'Purchase Date'),
            'mrp_price' => Yii::t('app', 'Mrp Price'),
            'selling_price' => Yii::t('app', 'Selling Price'),
            'expire_date' => Yii::t('app', 'Expire Date'),
            'units_received' => Yii::t('app', 'Units Received'),
            'received_units_id' => Yii::t('app', 'Received Units ID'),
            'invoice_number' => Yii::t('app', 'Invoice Number'),
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
    public function getSupplier()
    {
        return $this->hasOne(\app\modules\admin\models\VendorSuppliers::className(), ['id' => 'supplier_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSku()
    {
        return $this->hasOne(\app\modules\admin\models\Sku::className(), ['id' => 'sku_id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceivedUnits()
    {
        return $this->hasOne(\app\modules\admin\models\Units::className(), ['id' => 'received_units_id']);
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
    public function getProductOrderItems()
    {
        return $this->hasMany(\app\modules\admin\models\ProductOrderItems::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductServices()
    {
        return $this->hasMany(\app\modules\admin\models\ProductServices::className(), ['product_id' => 'id']);
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
     * @return \app\modules\admin\models\ProductsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ProductsQuery(get_called_class());
    }

    public static function calculateExpiringDaysCount($expire_date)
    {
        if (!empty($expire_date)) {
            $today = new \DateTime(date('Y-m-d'));
            $expire = new \DateTime($expire_date);
            $interval = $today->diff($expire);
            return $interval->invert ? 0 : $interval->days;
        } else {
            return null;
        }
    }


    public function getExpiringProductCount($days = 10)
    {
        $today = date('Y-m-d');
        $toDate = date('Y-m-d', strtotime("+$days days"));

        // Get all active products expiring within the window
        $products = Products::find()
            ->where(['sku_id' => $this->sku_id, 'status' => self::STATUS_ACTIVE])
            ->andWhere(['between', 'expire_date', $today, $toDate])
            ->all();

        if (empty($products)) {
            return [
                'expiring_stock' => 0,
                'unit_name' => 'Unknown',
                'error' => 'No active products expiring within the specified period'
            ];
        }

        // Fetch SKU details
        $sku = $this->sku;
        if (!$sku || !$sku->base_unit_id) {
            return [
                'expiring_stock' => 0,
                'unit_name' => 'Unknown',
                'error' => 'SKU or base unit not found'
            ];
        }

        // Fetch the base unit
        $baseUnit = Units::findOne($sku->base_unit_id);
        if (!$baseUnit) {
            return [
                'expiring_stock' => 0,
                'unit_name' => 'Unknown',
                'error' => 'Base unit not found'
            ];
        }

        $totalReceivedInBaseUnit = 0;
        $productIds = [];

        // Convert units_received to base unit for each product
        foreach ($products as $product) {
            $quantityInBaseUnit = $product->units_received;
            if ($product->received_units_id !== $sku->base_unit_id) {
                $conversionPath = $sku->findConversionPath($sku->id, $product->received_units_id, $sku->base_unit_id);
                if ($conversionPath) {
                    foreach ($conversionPath as $conversion) {
                        $quantityInBaseUnit *= $conversion['quantity'];
                    }
                } else {
                    \Yii::warning("No conversion path found from unit ID {$product->received_units_id} to base unit ID {$sku->base_unit_id} for product ID {$product->id}", __METHOD__);
                    continue;
                }
            }
            $totalReceivedInBaseUnit += $quantityInBaseUnit;
            $productIds[] = $product->id;
        }

        // Sum sold units from ProductOrderItems (assume quantity is in base unit)
        $soldUnits = ProductOrderItems::find()
            ->where(['in', 'product_id', $productIds])
            ->sum('quantity') ?? 0;

        // Calculate expiring stock
        $expiringUnits = $totalReceivedInBaseUnit - $soldUnits;

        return [
            'expiring_stock' => max(0, round($expiringUnits, 2)),
            'unit_name' => $baseUnit->unit_name,
            'error' => null
        ];
    }


      public function getAvailableStock()
    {
        // Fetch SKU details
        $sku = $this->sku;
        if (!$sku || !$sku->base_unit_id) {
            return [
                'available_stock' => 0,
                'unit_name' => 'Unknown',
                'error' => 'SKU or base unit not found'
            ];
        }

        // Fetch the base unit
        $baseUnit = Units::findOne($sku->base_unit_id);
        if (!$baseUnit) {
            return [
                'available_stock' => 0,
                'unit_name' => 'Unknown',
                'error' => 'Base unit not found'
            ];
        }

        // Convert received units to base unit
        $quantityInBaseUnit = $this->units_received;
        if ($this->received_units_id !== $sku->base_unit_id) {
            $conversionPath = $sku->findConversionPath($sku->id, $this->received_units_id, $sku->base_unit_id);
            if ($conversionPath) {
                foreach ($conversionPath as $conversion) {
                    $quantityInBaseUnit *= $conversion['quantity'];
                }
            } else {
                \Yii::warning("No conversion path found from unit ID {$this->received_units_id} to base unit ID {$sku->base_unit_id} for product ID {$this->id}", __METHOD__);
                return [
                    'available_stock' => 0,
                    'unit_name' => $baseUnit->unit_name,
                    'error' => "No conversion path found from received unit ID {$this->received_units_id} to base unit ID {$sku->base_unit_id}"
                ];
            }
        }

        // Sum sold units from ProductOrderItems (assume quantity is in base unit)
        $soldUnits = ProductOrderItems::find()
            ->where(['product_id' => $this->id])
            ->sum('quantity') ?? 0;

        // Calculate available stock
        $availableStock = $quantityInBaseUnit - $soldUnits;

        return [
            'available_stock' => max(0, round($availableStock, 2)),
            'unit_name' => $baseUnit->unit_name,
            'error' => null
        ];
    }


    public function getProductOfPurchaseTotalValue(){
        return $this->purchased_price * $this->getAvailableStock()['available_stock'];
    }






    public function asJson()
    {
        $data = [];
        $data['products_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sku_id'] =  $this->sku_id;

        $data['product_name'] = $this->sku->product_name ?? null;

        $data['category_name'] = $this->sku->category->title ?? null;

        $data['discount_allowed'] =  $this->discount_allowed;


        $data['supplier_id'] =  $this->supplier_id;
        $data['suppliers_firm_name'] =  $this->supplier->suppliers_firm_name ?? null;
        $data['contact_person'] =  $this->supplier->contact_person ?? null;
        $data['phone_number'] =  $this->supplier->phone_number ?? null;

        $data['batch_number'] =  $this->batch_number;

        $data['ean_code'] =  $this->ean_code;

        $data['expiring_product_count'] = $this->getExpiringProductCount()['expiring_stock'] ?? 0;
        $data['expiring_product_count_unit'] = $this->getExpiringProductCount()['unit_name'] ?? '';

        $data['expiring_days_count'] = Products::calculateExpiringDaysCount($this->expire_date);

        $data['getAvailableStock'] = $this->getAvailableStock();

        $data['purchased_price'] = $this->purchased_price;

        $data['purchase_date'] =  $this->purchase_date;

        $data['mrp_price'] =  $this->mrp_price;

        $data['selling_price'] =  $this->selling_price;

        $data['expire_date'] =  $this->expire_date;

        $data['units_received'] =  $this->units_received;

        $data['received_units_id'] =  $this->received_units_id;

        $data['invoice_number'] =  $this->invoice_number;

        $data['status'] =  $this->status;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        $data['create_user_id'] =  $this->create_user_id;

        $data['update_user_id'] =  $this->update_user_id;

        return $data;
    }


        public function asJsonSelectBachNumber()
    {
        $data = [];
        $data['products_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sku_id'] =  $this->sku_id;

        $data['product_name'] = $this->sku->product_name ?? null;

        $data['category_name'] = $this->sku->category->title ?? null;

        $data['batch_number'] =  $this->batch_number;

        $data['ean_code'] =  $this->ean_code;

        $data['expiring_product_count'] = $this->getExpiringProductCount()['expiring_stock'] ?? 0;
        $data['expiring_product_count_unit'] = $this->getExpiringProductCount()['unit_name'] ?? '';

        $data['expiring_days_count'] = Products::calculateExpiringDaysCount($this->expire_date);

        $data['getAvailableStock'] = $this->getAvailableStock();

        $data['purchased_price'] = $this->purchased_price;

        $data['purchase_date'] =  $this->purchase_date;

        $data['mrp_price'] =  $this->mrp_price;

        $data['selling_price'] =  $this->selling_price;

        $data['expire_date'] =  $this->expire_date;

        $data['units_received'] =  $this->units_received;

        $data['received_units_id'] =  $this->received_units_id;


        return $data;
    }
}

