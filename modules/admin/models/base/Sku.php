<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "sku".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property string $sku_code
 * @property string $product_name
 * @property integer $brand_id
 * @property string $ean_code
 * @property integer $category_id
 * @property integer $service_type_id
 * @property integer $store_service_type_id
 * @property integer $product_type_id
 * @property double $tax_rate
 * @property integer $re_order_level_for_alerts
 * @property integer $uom_id_re_order_level
 * @property integer $min_quantity_need
 * @property string $description
 * @property string $image
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\Units $uomIdReOrderLevel
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\MainCategory $category
 * @property \app\modules\admin\models\StoreServiceTypes $storeServiceType
 * @property \app\modules\admin\models\ProductTypes $productType
 * @property \app\modules\admin\models\Brands $brand
 * @property \app\modules\admin\models\ServiceType $serviceType
 * @property \app\modules\admin\models\Units $uom
 * @property \app\modules\admin\models\UOMHierarchy[] $uOMHierarchies
 */
class Sku extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'products',
            'createUser',
            'uomIdReOrderLevel',
            'updateUser',
            'vendorDetails',
            'category',
            'storeServiceType',
            'productType',
            'brand',
            'serviceType',
            'uOMHierarchies',
            'baseUnit'
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
            [['vendor_details_id', 'sku_code', 'product_name', 'brand_id', 'ean_code', 'category_id', 'product_type_id',  'tax_rate', 'uom_id_re_order_level', 'min_quantity_need'], 'required'],
            [['vendor_details_id', 'brand_id', 'category_id', 'service_type_id', 'store_service_type_id', 'product_type_id', 'uom_id_re_order_level', 'min_quantity_need', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['tax_rate'], 'number'],
            [['description'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['sku_code', 'product_name', 'ean_code'], 'string', 'max' => 255],
            [['re_order_level_for_alerts'], 'string', 'max' => 1],
            [['image'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sku';
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
            'sku_code' => Yii::t('app', 'Sku Code'),
            'product_name' => Yii::t('app', 'Product Name'),
            'brand_id' => Yii::t('app', 'Brand ID'),
            'ean_code' => Yii::t('app', 'Ean Code'),
            'category_id' => Yii::t('app', 'Category ID'),
            'service_type_id' => Yii::t('app', 'Service Type ID'),
            'store_service_type_id' => Yii::t('app', 'Store Service Type ID'),
            'product_type_id' => Yii::t('app', 'Product Type ID'),
            'tax_rate' => Yii::t('app', 'Tax Rate'),
            're_order_level_for_alerts' => Yii::t('app', 'Re Order Level For Alerts'),
            'uom_id_re_order_level' => Yii::t('app', 'Uom Id Re Order Level'),
            'min_quantity_need' => Yii::t('app', 'Min Quantity Need'),
            'description' => Yii::t('app', 'Description'),
            'image' => Yii::t('app', 'Image'),
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
    public function getCreateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'create_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUomIdReOrderLevel()
    {
        return $this->hasOne(\app\modules\admin\models\Units::className(), ['id' => 'uom_id_re_order_level']);
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
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(\app\modules\admin\models\MainCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoreServiceType()
    {
        return $this->hasOne(\app\modules\admin\models\StoreServiceTypes::className(), ['id' => 'store_service_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductType()
    {
        return $this->hasOne(\app\modules\admin\models\ProductTypes::className(), ['id' => 'product_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(\app\modules\admin\models\Brands::className(), ['id' => 'brand_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(\app\modules\admin\models\ServiceType::className(), ['id' => 'service_type_id']);
    }

    public function getProducts()
    {
        return $this->hasMany(\app\modules\admin\models\Products::className(), ['sku_id' => 'id']);
    }



    public function getBaseUnit()
{
    return $this->hasOne(\app\modules\admin\models\Units::className(), ['id' => 'base_unit_id']);
}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUOMHierarchies()
    {
        return $this->hasMany(\app\modules\admin\models\UOMHierarchy::className(), ['sku_id' => 'id']);
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

public function convertBaseQuantity($sku_id)
{
    // Fetch SKU details
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'SKU or base unit not found'
        ];
    }

    // Fetch the base unit
    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'Base unit not found'
        ];
    }

    // Fetch all active products for the SKU
    $products = Products::find()->where(['sku_id' => $sku_id, 'status' => Products::STATUS_ACTIVE])->all();
    if (!$products) {
        return [
            'quantity' => 0,
            'unit_name' => $baseUnit->unit_name,
            'error' => 'No active products found for this SKU'
        ];
    }

    $totalBaseQuantity = 0;

    foreach ($products as $product) {
        $receivedUnitsId = $product->received_units_id;
        $unitsReceived = $product->units_received;

        // Fetch the received unit
        $receivedUnit = Units::findOne($receivedUnitsId);
        if (!$receivedUnit) {
            continue; // Skip if received unit is invalid
        }

        // If received unit is the same as base unit, add quantity directly
        if ($receivedUnitsId === $sku->base_unit_id) {
            $totalBaseQuantity += $unitsReceived;
            continue;
        }

        // Find conversion path from received unit to base unit
        $conversionPath = $this->findConversionPath($sku_id, $receivedUnitsId, $sku->base_unit_id);
        if (!$conversionPath) {
            continue; // Skip if no conversion path found
            // Alternative: Return error
            // return [
            //     'quantity' => 0,
            //     'unit_name' => $baseUnit->unit_name,
            //     'error' => "No conversion path found from unit ID {$receivedUnitsId} to base unit ID {$sku->base_unit_id}"
            // ];
        }

        // Apply conversion path
        $quantity = $unitsReceived;
        foreach ($conversionPath as $conversion) {
            $quantity *= $conversion['quantity'];
        }
        $totalBaseQuantity += $quantity;
    }

    return [
        'quantity' => round($totalBaseQuantity, 6),
        'unit_name' => $baseUnit->unit_name,
        'error' => null
    ];
}


public function findConversionPath($sku_id, $sourceUnitId, $targetUnitId)
{
    if (empty($sku_id) || empty($sourceUnitId) || empty($targetUnitId)) {
        throw new \InvalidArgumentException("SKU ID, source unit, and target unit are required.");
    }

    // BFS queue
    $queue   = [[
        'currentUnitId' => $sourceUnitId,
        'path'          => []
    ]];
    $visited = [$sourceUnitId];

    while (!empty($queue)) {
        $current       = array_shift($queue);
        $currentUnitId = $current['currentUnitId'];
        $currentPath   = $current['path'];

        // Fetch conversions where current is units_id (forward)
        $forwardConversions = UOMHierarchy::find()
            ->where([
                'sku_id'   => $sku_id,
                'units_id' => $currentUnitId,
                'status'   => UOMHierarchy::STATUS_ACTIVE
            ])
            ->all();

        // Fetch conversions where current is of_units_id (reverse)
        $reverseConversions = UOMHierarchy::find()
            ->where([
                'sku_id'      => $sku_id,
                'of_units_id' => $currentUnitId,
                'status'      => UOMHierarchy::STATUS_ACTIVE
            ])
            ->all();

        $conversions = array_merge($forwardConversions, $reverseConversions);

        foreach ($conversions as $conversion) {
            // Determine the next unit based on direction
            if ($conversion->units_id == $currentUnitId) {
                // Forward direction
                $nextUnitId = $conversion->of_units_id;
                $step = [
                    'from'     => $conversion->units_id,
                    'to'       => $conversion->of_units_id,
                    'quantity' => $conversion->quantity
                ];
            } else {
                // Reverse direction
                $nextUnitId = $conversion->units_id;
                $step = [
                    'from'     => $conversion->of_units_id,
                    'to'       => $conversion->units_id,
                    'quantity' => 1 / (float) $conversion->quantity  // invert factor
                ];
            }

            if (in_array($nextUnitId, $visited)) {
                continue; // already visited
            }

            $newPath = array_merge($currentPath, [$step]);

            // Check if we reached the target
            if ($nextUnitId == $targetUnitId) {
                return $newPath;
            }

            // Add to queue
            $queue[]   = [
                'currentUnitId' => $nextUnitId,
                'path'          => $newPath
            ];
            $visited[] = $nextUnitId;
        }
    }

    return false; // No path found
}



public function getTotalProductsReceived($sku_id, $target_unit_id = null)
{
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'SKU or base unit not found'
        ];
    }

    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'Base unit not found'
        ];
    }

    $targetUnit = $target_unit_id ? Units::findOne($target_unit_id) : $baseUnit;
    if (!$targetUnit) {
        return [
            'quantity' => 0,
            'unit_name' => $baseUnit->unit_name,
            'error' => 'Target unit not found'
        ];
    }

    $baseQuantity = $this->convertBaseQuantity($sku_id);
    if ($baseQuantity['error']) {
        return [
            'quantity' => 0,
            'unit_name' => $targetUnit->unit_name,
            'error' => $baseQuantity['error']
        ];
    } 

    $quantityInTargetUnit = $baseQuantity['quantity'];
    if ($targetUnit->id !== $baseUnit->id) {
        $conversionPath = $this->findConversionPath($sku_id, $targetUnit->id, $baseUnit->id);
        if ($conversionPath) {
            foreach ($conversionPath as $conversion) {
                $quantityInTargetUnit /= $conversion['quantity'];
            }
        } else {
            return [
                'quantity' => 0,
                'unit_name' => $targetUnit->unit_name,
                'error' => "No conversion path found from target unit ID {$targetUnit->id} to base unit ID {$baseUnit->id}"
            ];
        }
    } 

    return [
        'quantity' => round($quantityInTargetUnit, 6),
        'unit_name' => $targetUnit->unit_name,
        'error' => null
    ];
}

public function getActiveProductCount($sku_id, $target_unit_id = null)
{
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'SKU or base unit not found'
        ];
    }

    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'Base unit not found'
        ];
    }

    $targetUnit = $target_unit_id ? Units::findOne($target_unit_id) : $baseUnit;
    if (!$targetUnit) {
        return [
            'quantity' => 0,
            'unit_name' => $baseUnit->unit_name,
            'error' => 'Target unit not found'
        ];
    }

    $products = Products::find()
        ->where(['sku_id' => $sku_id, 'status' => Products::STATUS_ACTIVE])
        ->andWhere(['>=', 'expire_date', date('Y-m-d')])
        ->all();

    $totalActive = 0;
    foreach ($products as $product) {
        $unit = Units::findOne($product->received_units_id);
        if (!$unit) {
            \Yii::warning("Received unit ID {$product->received_units_id} not found for product ID {$product->id}");
            continue;
        }

        if ($unit->id === $sku->base_unit_id) {
            $totalActive += $product->units_received;
            continue;
        }

        $conversionPath = $this->findConversionPath($sku_id, $unit->id, $sku->base_unit_id);
        if ($conversionPath) {
            $quantity = $product->units_received;
            foreach ($conversionPath as $conversion) {
                $quantity *= $conversion['quantity'];
            }
            $totalActive += $quantity;
        } else {
            \Yii::warning("No conversion path found from unit ID {$unit->id} to base unit ID {$sku->base_unit_id} for product ID {$product->id}");
        }
    }

    $quantityInTargetUnit = $totalActive;
    if ($targetUnit->id !== $baseUnit->id) {
        $conversionPath = $this->findConversionPath($sku_id, $targetUnit->id, $baseUnit->id);
        if ($conversionPath) {
            foreach ($conversionPath as $conversion) {
                $quantityInTargetUnit /= $conversion['quantity'];
            }
        } else {
            return [
                'quantity' => 0,
                'unit_name' => $targetUnit->unit_name,
                'error' => "No conversion path found from target unit ID {$targetUnit->id} to base unit ID {$baseUnit->id}"
            ];
        }
    }

    return [
        'quantity' => round($quantityInTargetUnit, 6),
        'unit_name' => $targetUnit->unit_name,
        'error' => null
    ];
}

public function getExpiredProductCount($sku_id, $target_unit_id = null)
{
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return ['quantity' => 0, 'unit_name' => 'Unknown', 'error' => 'SKU or base unit not found'];
    }

    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return ['quantity' => 0, 'unit_name' => 'Unknown', 'error' => 'Base unit not found'];
    }

    $targetUnit = $target_unit_id ? Units::findOne($target_unit_id) : $baseUnit;
    if (!$targetUnit) {
        return ['quantity' => 0, 'unit_name' => $baseUnit->unit_name, 'error' => 'Target unit not found'];
    }

    $products = Products::find()
        ->where(['sku_id' => $sku_id, 'status' => Products::STATUS_ACTIVE])
        ->andWhere(['<', 'expire_date', date('Y-m-d')])
        ->all();

    $expiredUnits = 0;
    foreach ($products as $product) {
        $unit = Units::findOne($product->received_units_id);
        if (!$unit) {
            continue;
        }

        if ($unit->id === $sku->base_unit_id) {
            $expiredUnits += $product->units_received;
            continue;
        }

        $conversionPath = $this->findConversionPath($sku_id, $unit->id, $sku->base_unit_id);
        if ($conversionPath) {
            $quantity = $product->units_received;
            foreach ($conversionPath as $conversion) {
                $quantity *= $conversion['quantity'];
            }
            $expiredUnits += $quantity;
        }
    }

    // Subtract sold units from expired products
    $soldFromExpired = ProductOrderItems::find()
        ->joinWith('product')
        ->where(['products.sku_id' => $sku_id])
        ->andWhere(['<=', 'products.expire_date', date('Y-m-d')])
        ->andWhere(['product_order_items.status' => ProductOrderItems::STATUS_ACTIVE])
        ->all();

    foreach ($soldFromExpired as $item) {
        $unit = Units::findOne(['unit_name' => $item->units]);
        if (!$unit) {
            continue;
        }

        if ($unit->id === $sku->base_unit_id) {
            $expiredUnits -= $item->quantity;
            continue;
        }

        $conversionPath = $this->findConversionPath($sku_id, $unit->id, $sku->base_unit_id);
        if ($conversionPath) {
            $quantity = $item->quantity;
            foreach ($conversionPath as $conversion) {
                $quantity *= $conversion['quantity'];
            }
            $expiredUnits -= $quantity;
        }
    }

    $quantityInTargetUnit = max(0, $expiredUnits);
    if ($targetUnit->id !== $baseUnit->id) {
        $conversionPath = $this->findConversionPath($sku_id, $targetUnit->id, $baseUnit->id);
        if ($conversionPath) {
            foreach ($conversionPath as $conversion) {
                $quantityInTargetUnit /= $conversion['quantity'];
            }
        } else {
            return [
                'quantity' => 0,
                'unit_name' => $targetUnit->unit_name,
                'error' => "No conversion path found from target unit ID {$targetUnit->id} to base unit ID {$baseUnit->id}"
            ];
        }
    }

    return [
        'quantity' => round($quantityInTargetUnit, 6),
        'unit_name' => $targetUnit->unit_name,
        'error' => null
    ];
}



public function getWastageProductCount($sku_id, $target_unit_id = null)
{
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return ['quantity' => 0, 'unit_name' => 'Unknown', 'error' => 'SKU or base unit not found'];
    }

    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return ['quantity' => 0, 'unit_name' => 'Unknown', 'error' => 'Base unit not found'];
    }

    $targetUnit = $target_unit_id ? Units::findOne($target_unit_id) : $baseUnit;
    if (!$targetUnit) {
        return ['quantity' => 0, 'unit_name' => $baseUnit->unit_name, 'error' => 'Target unit not found'];
    }

    $wastages = WastageProducts::find()
        ->innerJoinWith('product')
        ->where(['products.sku_id' => $sku_id, 'wastage_products.status' => WastageProducts::STATUS_ACTIVE])
        ->all();

    $totalWastage = 0;
    foreach ($wastages as $wastage) {
        $unit = Units::findOne($wastage->uom_id);
        if (!$unit) {
            continue;
        }

        if ($unit->id === $sku->base_unit_id) {
            $totalWastage += $wastage->quantity;
            continue;
        }

        $conversionPath = $this->findConversionPath($sku_id, $unit->id, $sku->base_unit_id);
        if ($conversionPath) {
            $quantity = $wastage->quantity;
            foreach ($conversionPath as $conversion) {
                $quantity *= $conversion['quantity'];
            }
            $totalWastage += $quantity;
        }
    }

    $quantityInTargetUnit = $totalWastage;
    if ($targetUnit->id !== $baseUnit->id) {
        $conversionPath = $this->findConversionPath($sku_id, $targetUnit->id, $baseUnit->id);
        if ($conversionPath) {
            foreach ($conversionPath as $conversion) {
                $quantityInTargetUnit /= $conversion['quantity'];
            }
        } else {
            return [
                'quantity' => 0,
                'unit_name' => $targetUnit->unit_name,
                'error' => "No conversion path found from target unit ID {$targetUnit->id} to base unit ID {$baseUnit->id}"
            ];
        }
    }

    return [
        'quantity' => round($quantityInTargetUnit, 6),
        'unit_name' => $targetUnit->unit_name,
        'error' => null
    ];
}

public function getLastUpdatedUnitPrice($sku_id, $target_unit_id = null)
{
    // Fetch SKU details
    $sku = Sku::findOne($sku_id);
    if (!$sku) {
        \Yii::warning("SKU ID {$sku_id} not found", __METHOD__);
        return [
            'price' => null,
            'unit_id' => null,
            'unit_name' => null,
            'error' => 'SKU not found'
        ];
    }

    $base_unit_id = $sku->base_unit_id;

    // Fetch the most recently updated active product
    $product = Products::find()
        ->where(['sku_id' => $sku_id, 'status' => Products::STATUS_ACTIVE])
        ->orderBy(['updated_on' => SORT_DESC])
        ->one();
    if (!$product) {
        \Yii::warning("No active products found for SKU ID {$sku_id}", __METHOD__);
        return [
            'price' => null,
            'unit_id' => null,
            'unit_name' => null,
            'error' => 'No active products found'
        ];
    }

    // Fetch the product's received unit
    $baseUnit = Units::findOne($base_unit_id);
    if (!$baseUnit) {
        \Yii::warning("Base Unit not found", __METHOD__);
        return [
            'price' => null,
            'unit_id' => null,
            'unit_name' => null,
            'error' => 'Base unit not found'
        ];
    }

    // Return the selling price directly without conversion
    return [
        'price' => round($product->selling_price, 2),
        'unit_id' => $baseUnit->id,
        'unit_name' => $baseUnit->unit_name,
        'error' => null
    ];
}




    public function productLastUpdatedDateTime($sku_id)
    {
        $last_updated_price = Products::find()->where(['sku_id' => $sku_id])->orderBy(['updated_on' => SORT_DESC])->one();
        return $last_updated_price ? $last_updated_price->updated_on : null;
    }
    public  function getTotalProductsPurchasedByVendor($sku_id)
    {

        $products = Products::find()
            ->where(['sku_id' => $sku_id])
            ->sum('units_received');

        return $products??0;
    }

public function getAvailableStock($sku_id, $target_unit_id = null)
{
    // Fetch SKU details
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'SKU or base unit not found'
        ];
    }

    // Fetch the base unit
    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return [
            'quantity' => 0,
            'unit_name' => 'Unknown',
            'error' => 'Base unit not found'
        ];
    }

    // Determine the target unit
    $targetUnit = $target_unit_id ? Units::findOne($target_unit_id) : $baseUnit;
    if (!$targetUnit) {
        return [
            'quantity' => 0,
            'unit_name' => $baseUnit->unit_name,
            'error' => 'Target unit not found'
        ];
    }

    // Get total received quantity in base unit
    $receivedData = $this->convertBaseQuantity($sku_id);
    if ($receivedData['error']) {
        return [
            'quantity' => 0,
            'unit_name' => $targetUnit->unit_name,
            'error' => $receivedData['error']
        ];
    }
    $totalReceived = $receivedData['quantity'];

    // Get total sold quantity in base unit
    $totalSold = $this->totalUnitsSold($sku_id);

    // Get expired quantity in base unit
    $expiredData = $this->getExpiredProductCount($sku_id, $sku->base_unit_id); // Update this method
    $totalExpired = $expiredData['quantity'];

    // Get wastage quantity in base unit
    $wastageData = $this->getWastageProductCount($sku_id, $sku->base_unit_id); // Update this method
    $totalWastage = $wastageData['quantity'];

    // Calculate available stock in base unit
    $availableStock = max(0, $totalReceived - $totalSold - $totalExpired - $totalWastage);

    // Convert to target unit if different from base unit
    $quantityInTargetUnit = $availableStock;
    if ($targetUnit->id !== $baseUnit->id) {
        $conversionPath = $this->findConversionPath($sku_id, $targetUnit->id, $baseUnit->id);
        if ($conversionPath) {
            foreach ($conversionPath as $conversion) {
                $quantityInTargetUnit /= $conversion['quantity']; // Reverse conversion
            }
        } else {
            return [
                'quantity' => 0,
                'unit_name' => $targetUnit->unit_name,
                'error' => "No conversion path found from target unit ID {$targetUnit->id} to base unit ID {$baseUnit->id}"
            ];
        }
    }

    return [
        'quantity' => round($quantityInTargetUnit, 6),
        'unit_name' => $targetUnit->unit_name,
        'error' => null
    ];
}



    public function showProductList($sku_id)
    {
        $exists = Sku::find()
            ->where(['id' => $sku_id])
            ->andWhere(['IN', 'id', Products::find()->select('sku_id')])
            ->exists();

        return $exists; // returns true or false
    }

public function totalUnitsSold($sku_id)
{
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return 0;
    }

    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return 0;
    }

    $items = ProductOrderItems::find()
        ->joinWith('product.sku ps')
        ->where(['ps.id' => $sku_id, 'product_order_items.status' => self::STATUS_ACTIVE])
        ->all();

    $totalSold = 0;
    foreach ($items as $item) {
        $unit = Units::findOne(['unit_name' => $item->units]);
        if (!$unit) {
            continue;
        }

        if ($unit->id === $sku->base_unit_id) {
            $totalSold += $item->quantity;
            continue;
        }

        $conversionPath = $this->findConversionPath($sku_id, $unit->id, $sku->base_unit_id);
        if ($conversionPath) {
            $quantity = $item->quantity;
            foreach ($conversionPath as $conversion) {
                $quantity *= $conversion['quantity'];
            }
            $totalSold += $quantity;
        }
    }

    return round($totalSold, 6);
}

public function stockLevel($sku_id)
{
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return 0;
    }

    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return 0;
    }

    $targetUnit = $sku->uom_id_re_order_level ? Units::findOne($sku->uom_id_re_order_level) : $baseUnit;
    if (!$targetUnit) {
        $firstProduct = Products::find()
            ->where(['sku_id' => $sku_id, 'status' => Products::STATUS_ACTIVE])
            ->one();
        $targetUnit = $firstProduct ? Units::findOne($firstProduct->received_units_id) : Units::find()->where(['unit_name' => 'Piece'])->one();
        if (!$targetUnit) {
            return 0;
        }
    }

    $receivedData = $this->convertBaseQuantity($sku_id);
    $totalReceived = $receivedData['quantity'];
    if ($receivedData['error'] || $totalReceived <= 0) {
        return 0;
    }

    $totalSold = $this->totalUnitsSold($sku_id);
    $expiredData = $this->getExpiredProductCount($sku_id, $sku->base_unit_id);
    $totalExpired = $expiredData['quantity'];
    $wastageData = $this->getWastageProductCount($sku_id, $sku->base_unit_id);
    $totalWastage = $wastageData['quantity'];

    $stockLevel = max(0, $totalReceived - $totalSold - $totalExpired - $totalWastage);

    $quantityInTargetUnit = $stockLevel;
    if ($targetUnit->id !== $baseUnit->id) {
        $conversionPath = $this->findConversionPath($sku_id, $targetUnit->id, $baseUnit->id);
        if ($conversionPath) {
            foreach ($conversionPath as $conversion) {
                $quantityInTargetUnit /= $conversion['quantity'];
            }
        } else {
            return 0;
        }
    }

    return round(($quantityInTargetUnit / $totalReceived) * 100, 2);
}

    public function getSuppliers($sku_id)
    {
        // select only id, suppliers_firm_name, contact_person from VendorSuppliers
        return VendorSuppliers::find()
            ->alias('vs')
            ->innerJoinWith(['products.sku ps'])
            ->where(['ps.id' => $sku_id])
            ->select(['vs.id', 'vs.suppliers_firm_name', 'vs.contact_person'])
            ->all();
    }

    /**
     * @inheritdoc
     * @return \app\modules\admin\models\SkuQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\SkuQuery(get_called_class());
    }

public function isLowStock($sku_id)
{
    // Fetch SKU details
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->uomIdReOrderLevel) {
        return false; // No SKU or re-order unit, assume not low
    }

    // Get available stock in the re-order unit
    $stock = $this->getAvailableStock($sku_id, $sku->uom_id_re_order_level);
    $availableQuantity = $stock['quantity'];

    // Compare with min_quantity_need (assumed to be in uom_id_re_order_level)
    $minQuantityNeed = $sku->min_quantity_need;

    return $availableQuantity <= $minQuantityNeed;
}
public function getTotalStockValue($sku_id, $target_unit_id = null)
{
    // Fetch SKU details
    $sku = Sku::findOne($sku_id);
    if (!$sku || !$sku->base_unit_id) {
        return [
            'value' => 0,
            'unit_name' => 'Unknown',
            'currency' => 'INR',
            'error' => 'SKU or base unit not found'
        ];
    }

    // Fetch the base unit
    $baseUnit = Units::findOne($sku->base_unit_id);
    if (!$baseUnit) {
        return [
            'value' => 0,
            'unit_name' => 'Unknown',
            'currency' => 'INR',
            'error' => 'Base unit not found'
        ];
    }

    // Determine the target unit
    $targetUnit = $target_unit_id ? Units::findOne($target_unit_id) : $baseUnit;
    if (!$targetUnit) {
        return [
            'value' => 0,
            'unit_name' => $baseUnit->unit_name,
            'currency' => 'INR',
            'error' => 'Target unit not found'
        ];
    }

    // Fetch all active products for the SKU
    $products = Products::find()
        ->where(['sku_id' => $sku_id, 'status' => Products::STATUS_ACTIVE])
        ->all();

    if (!$products) {
        return [
            'value' => 0,
            'unit_name' => $targetUnit->unit_name,
            'currency' => 'INR',
            'error' => 'No active products found for this SKU'
        ];
    }

    $totalValueInBaseUnit = 0;

    foreach ($products as $product) {
        $receivedUnitsId = $product->received_units_id;
        $unitsReceived = $product->units_received;
        $sellingPrice = $product->selling_price; // Assumed to be in base unit (e.g., INR per Piece)

        // Fetch the received unit
        $receivedUnit = Units::findOne($receivedUnitsId);
        if (!$receivedUnit) {
            \Yii::warning("Received unit ID {$receivedUnitsId} not found for product ID {$product->id}", __METHOD__);
            continue;
        }

        // Convert quantity to base unit
        $quantityInBaseUnit = $unitsReceived;
        if ($receivedUnitsId !== $sku->base_unit_id) {
            $conversionPath = $this->findConversionPath($sku_id, $receivedUnitsId, $sku->base_unit_id);
            if ($conversionPath) {
                foreach ($conversionPath as $conversion) {
                    $quantityInBaseUnit *= $conversion['quantity'];
                }
            } else {
                \Yii::warning("No conversion path found for quantity from unit ID {$receivedUnitsId} to base unit ID {$sku->base_unit_id} for product ID {$product->id}", __METHOD__);
                continue;
            }
        }

        // Add to total value (selling price in base unit * quantity in base unit)
        $totalValueInBaseUnit += $sellingPrice * $quantityInBaseUnit;
    }

    // Convert total value to target unit
    $valueInTargetUnit = $totalValueInBaseUnit;
    if ($targetUnit->id !== $baseUnit->id) {
        $conversionPath = $this->findConversionPath($sku_id, $targetUnit->id, $sku->base_unit_id);
        if ($conversionPath) {
            foreach ($conversionPath as $conversion) {
                $valueInTargetUnit /= $conversion['quantity']; // Reverse conversion
            }
        } else {
            return [
                'value' => 0,
                'unit_name' => $targetUnit->unit_name,
                'currency' => 'INR',
                'error' => "No conversion path found from target unit ID {$targetUnit->id} to base unit ID {$sku->base_unit_id}"
            ];
        }
    }

    return [
        'value' => round($valueInTargetUnit, 2),
        'unit_name' => $targetUnit->unit_name,
        'currency' => 'INR',
        'error' => null
    ];
}

    public function asJson()
    {
        $data = [];
        $data['sku_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sku_code'] =  $this->sku_code;

        $data['product_name'] =  $this->product_name;
        $data['brand_id'] =  $this->brand_id;
        $data['brand_name'] =  $this->brand ? $this->brand->brand_name : null;

        $data['ean_code'] =  $this->ean_code;

        $data['category_id'] =  $this->category_id;

        $data['base_unit_id'] =  $this->base_unit_id;
        $data['base_unit_name'] =  $this->baseUnit ? $this->baseUnit->unit_name : null;

        $data['category_name'] =  $this->category ? $this->category->title : null;

        $data['service_type_id'] =  $this->service_type_id;

        $data['store_service_type_id'] =  $this->store_service_type_id;

        $data['product_type_id'] =  $this->product_type_id;

        $data['product_type_name'] =  $this->productType ? $this->productType->product_type_name : null;

        $data['getSuppliers'] =  $this->getSuppliers($this->id);

        $data['getTotalProductsReceived'] =  $this->getTotalProductsReceived($this->id);

        $data['stock'] =  $this->getAvailableStock($this->id);
        $data['totalUnitsSold'] = $this->totalUnitsSold($this->id);

        $data['is_low_stock'] = $this->isLowStock($this->id);

        $data['getExpiredProductCount'] =  $this->getExpiredProductCount($this->id);

        $data['tax_rate'] =  $this->tax_rate;

        $data['re_order_level_for_alerts'] =  $this->re_order_level_for_alerts;

        $data['uom_id_re_order_level'] =  $this->uom_id_re_order_level;

        $data['last_updated_price'] = $this->getLastUpdatedUnitPrice($this->id);

        $data['total_stock_value'] = $this->getTotalStockValue($this->id);

        $data['uom_name_re_order_level'] =  $this->uomIdReOrderLevel ? $this->uomIdReOrderLevel->unit_name : null;

        $data['last_updated_date_time'] = $this->productLastUpdatedDateTime($this->id);

        $data['min_quantity_need'] =  $this->min_quantity_need;

        $data['description'] =  $this->description;

        $data['image'] =  $this->image;

        $data['stock_level'] = $this->stockLevel($this->id);

        $data['showProductList'] = $this->showProductList($this->id);
        if(!empty($this->uOMHierarchies)){
            foreach($this->uOMHierarchies as $uom_hierarchy){
                $data['u_o_m_hierarchy'][] = $uom_hierarchy->asJson();
            }
        }else{
            $data['u_o_m_hierarchy'] = [];
        }

       

        $data['status'] =  $this->status;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        $data['create_user_id'] =  $this->create_user_id;

        $data['update_user_id'] =  $this->update_user_id;

        return $data;
    }


    public function asJsonForDropDownList()
    {
        $data = [];
        $data['sku_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sku_code'] =  $this->sku_code;

        $data['product_name'] =  $this->product_name;
        $data['brand_id'] =  $this->brand_id;
        $data['brand_name'] =  $this->brand ? $this->brand->brand_name : null;

        $data['ean_code'] =  $this->ean_code;

        $data['category_id'] =  $this->category_id;

        $data['category_name'] =  $this->category ? $this->category->title : null;

        $data['product_type_name'] =  $this->productType ? $this->productType->product_type_name : null;
        return $data;
    }





        public function asJsonWithOutPagination()
    {
        $data = [];
        $data['sku_id'] =  $this->id;
        $data['vendor_details_id'] =  $this->vendor_details_id;
        $data['sku_code'] =  $this->sku_code;
        $data['product_name'] =  $this->product_name;
        $data['brand_id'] =  $this->brand_id;
        $data['brand_name'] =  $this->brand ? $this->brand->brand_name : null;
        $data['ean_code'] =  $this->ean_code;
        $data['category_id'] =  $this->category_id;
        $data['category_name'] =  $this->category ? $this->category->title : null;


        return $data;
    }


 



    public function asJsonSkuForProductList()
    {
        $data = [];
        $data['product_name'] =  $this->product_name;
        return $data;
    }

    /**
     * Calculate profit for this SKU based on sales
     * @param string|null $startDate Start date (Y-m-d format)
     * @param string|null $endDate End date (Y-m-d format)
     * @return array Profit data with details
     */
    public function calculateProfitByDateRange($startDate = null, $endDate = null)
    {
        try {
            // Build the query for product order items
            $query = ProductOrderItems::find()
                ->joinWith(['product', 'productOrder'])
                ->where(['products.sku_id' => $this->id])
                ->andWhere(['product_order_items.status' => ProductOrderItems::STATUS_ACTIVE])
                ->andWhere(['product_orders.status' => ProductOrders::STATUS_COMPLETED]);

            // Add date filters if provided
            if ($startDate) {
                $query->andWhere(['>=', 'DATE(product_orders.created_on)', $startDate]);
            }
            if ($endDate) {
                $query->andWhere(['<=', 'DATE(product_orders.created_on)', $endDate]);
            }

            $orderItems = $query->all();

            $totalProfit = 0;
            $totalRevenue = 0;
            $totalCost = 0;
            $totalQuantitySold = 0;
            $salesCount = 0;

            foreach ($orderItems as $item) {
                $quantity = $item->quantity;
                $sellingPrice = $item->selling_price;
                $purchasedPrice = $item->product->purchased_price ?? 0;

                $itemRevenue = $sellingPrice * $quantity;
                $itemCost = $purchasedPrice * $quantity;
                $itemProfit = $itemRevenue - $itemCost;

                $totalRevenue += $itemRevenue;
                $totalCost += $itemCost;
                $totalProfit += $itemProfit;
                $totalQuantitySold += $quantity;
                $salesCount++;
            }

            return [
                'total_profit' => round($totalProfit, 2),
                'total_revenue' => round($totalRevenue, 2),
                'total_cost' => round($totalCost, 2),
                'total_quantity_sold' => $totalQuantitySold,
                'sales_count' => $salesCount,
                'profit_margin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sku_id' => $this->id,
                'sku_name' => $this->product_name,
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'total_profit' => 0,
                'total_revenue' => 0,
                'total_cost' => 0,
                'total_quantity_sold' => 0,
                'sales_count' => 0,
                'profit_margin' => 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sku_id' => $this->id,
                'sku_name' => $this->product_name,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get profit statistics for different time periods
     * @return array Profit statistics for today, this week, and this month
     */
    public function getProfitStatistics()
    {
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');

        return [
            'today' => $this->calculateProfitByDateRange($today, $today),
            'this_week' => $this->calculateProfitByDateRange($weekStart, $today),
            'this_month' => $this->calculateProfitByDateRange($monthStart, $today),
            'all_time' => $this->calculateProfitByDateRange()
        ];
    }

    /**
     * Calculate profit for a specific product
     * @param int $productId Product ID
     * @param string|null $startDate Start date
     * @param string|null $endDate End date
     * @return array Profit data for specific product
     */
    public static function calculateProfitForProduct($productId, $startDate = null, $endDate = null)
    {
        try {
            $query = ProductOrderItems::find()
                ->joinWith(['product', 'productOrder'])
                ->where(['product_order_items.product_id' => $productId])
                ->andWhere(['product_order_items.status' => ProductOrderItems::STATUS_ACTIVE])
                ->andWhere(['product_orders.status' => ProductOrders::STATUS_COMPLETED]);

            if ($startDate) {
                $query->andWhere(['>=', 'DATE(product_orders.created_on)', $startDate]);
            }
            if ($endDate) {
                $query->andWhere(['<=', 'DATE(product_orders.created_on)', $endDate]);
            }

            $orderItems = $query->all();
            $totalProfit = 0;
            $totalRevenue = 0;

            foreach ($orderItems as $item) {
                $quantity = $item->quantity;
                $sellingPrice = $item->selling_price;
                $purchasedPrice = $item->product->purchased_price ?? 0;

                $itemRevenue = $sellingPrice * $quantity;
                $itemCost = $purchasedPrice * $quantity;
                $totalProfit += ($itemRevenue - $itemCost);
                $totalRevenue += $itemRevenue;
            }

            return [
                'profit' => round($totalProfit, 2),
                'revenue' => round($totalRevenue, 2),
                'profit_margin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            return [
                'profit' => 0,
                'revenue' => 0,
                'profit_margin' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get daily profit data for chart visualization
     * @param int $vendorDetailsId Vendor ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Daily profit data for charts
     */
    public static function getDailyProfitChart($vendorDetailsId, $startDate, $endDate)
    {
        try {
            $query = ProductOrderItems::find()
                ->select([
                    'DATE(product_orders.created_on) as sale_date',
                    'SUM(product_order_items.selling_price * product_order_items.quantity) as revenue',
                    'SUM(products.purchased_price * product_order_items.quantity) as cost',
                    'SUM((product_order_items.selling_price - products.purchased_price) * product_order_items.quantity) as profit'
                ])
                ->joinWith(['product', 'productOrder'])
                ->where(['products.vendor_details_id' => $vendorDetailsId])
                ->andWhere(['product_order_items.status' => ProductOrderItems::STATUS_ACTIVE])
                ->andWhere(['product_orders.status' => ProductOrders::STATUS_COMPLETED])
                ->andWhere(['>=', 'DATE(product_orders.created_on)', $startDate])
                ->andWhere(['<=', 'DATE(product_orders.created_on)', $endDate])
                ->groupBy('DATE(product_orders.created_on)')
                ->orderBy('sale_date ASC')
                ->asArray()
                ->all();

            $chartData = [];
            $labels = [];
            $profits = [];

            foreach ($query as $row) {
                $labels[] = date('M d', strtotime($row['sale_date']));
                $profits[] = round($row['profit'], 2);
            }

            return [
                'labels' => $labels,
                'profits' => $profits,
                'datasets' => [[
                    'label' => 'Daily Profit',
                    'data' => $profits,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.1
                ]]
            ];

        } catch (\Exception $e) {
            return [
                'labels' => [],
                'profits' => [],
                'datasets' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format amount in K format (1000 = 1K)
     * @param float $amount Amount to format
     * @return string Formatted amount
     */
    public static function formatAmountInK($amount)
    {
        if ($amount >= 1000000) {
            return round($amount / 1000000, 1) . 'M';
        } elseif ($amount >= 1000) {
            return round($amount / 1000, 1) . 'K';
        } else {
            return round($amount, 2);
        }
    }
}
