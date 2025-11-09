<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "product_order_items".
 *
 * @property integer $id
 * @property integer $product_order_id
 * @property integer $product_id
 * @property integer $quantity
 * @property string $units
 * @property double $mrp_price
 * @property double $selling_price
 * @property double $sub_total
 * @property integer $tax_percentage
 * @property integer $tax_amount
 * @property double $total_with_tax
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\ProductOrders $productOrder
 * @property \app\modules\admin\models\Products $product
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\ProductOrderItemsAssignedDiscounts[] $productOrderItemsAssignedDiscounts
 */
class ProductOrderItems extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'productOrder',
            'product',
            'updateUser',
            'createUser',
            'productOrderItemsAssignedDiscounts'
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
            [['product_order_id', 'product_id', 'quantity', 'mrp_price', 'selling_price', 'sub_total', 'tax_percentage', 'tax_amount', 'total_with_tax'], 'required'],
            [['product_order_id', 'product_id', 'quantity', 'tax_percentage', 'tax_amount', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['mrp_price', 'selling_price', 'sub_total', 'total_with_tax'], 'number'],
            [['created_on', 'updated_on'], 'safe'],
            [['units'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_order_items';
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
            'product_order_id' => Yii::t('app', 'Product Order ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'quantity' => Yii::t('app', 'Item Count'),
            'units' => Yii::t('app', 'Units'),
            'mrp_price' => Yii::t('app', 'Mrp Price'),
            'selling_price' => Yii::t('app', 'Selling Price'),
            'sub_total' => Yii::t('app', 'Sub Total'),
            'tax_percentage' => Yii::t('app', 'Tax Percentage'),
            'tax_amount' => Yii::t('app', 'Tax Amount'),
            'total_with_tax' => Yii::t('app', 'Total With Tax'),
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
    public function getProductOrder()
    {
        return $this->hasOne(\app\modules\admin\models\ProductOrders::className(), ['id' => 'product_order_id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getProductOrderItemsAssignedDiscounts()
    {
        return $this->hasMany(\app\modules\admin\models\ProductOrderItemsAssignedDiscounts::className(), ['product_order_item_id' => 'id']);
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
     * @return \app\modules\admin\models\ProductOrderItemsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ProductOrderItemsQuery(get_called_class());
    }
    public function asJson()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['product_order_id'] =  $this->product_order_id;

        $data['product_id'] =  $this->product_id;

        $data['product_name'] =  $this->product->sku->product_name ?? '';

        $data['quantity'] =  $this->quantity;

        $data['units'] =  $this->units;

        $data['mrp_price'] =  $this->mrp_price;

        $data['selling_price'] =  $this->selling_price;

        $data['sub_total'] =  $this->sub_total;

        $data['tax_percentage'] =  $this->tax_percentage;

        $data['tax_amount'] =  $this->tax_amount;

        $data['total_with_tax'] =  $this->total_with_tax;

        $data['status'] =  $this->status;

        $data['created_on'] =  $this->created_on;

        $data['updated_on'] =  $this->updated_on;

        $data['create_user_id'] =  $this->create_user_id;

        $data['update_user_id'] =  $this->update_user_id;

        return $data;
    }
}
