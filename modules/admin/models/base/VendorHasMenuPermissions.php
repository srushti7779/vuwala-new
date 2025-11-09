<?php
namespace app\modules\admin\models\base;

use app\modules\admin\models\Menus;
use app\modules\admin\models\VendorHasMenus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the base model class for table "vendor_has_menu_permissions".
 *
 * @property integer $id
 * @property integer $vendor_has_menu_id
 * @property integer $menu_permissions_id
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorHasMenus $vendorHasMenu
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\MenuPermissions $menuPermissions
 */
class VendorHasMenuPermissions extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'vendorHasMenu',
            'createUser',
            'updateUser',
            'menuPermissions',
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_DELETE   = 2;

    const IS_FEATURED     = 1;
    const IS_NOT_FEATURED = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_has_menu_id', 'menu_permissions_id'], 'required'],
            [['vendor_has_menu_id', 'menu_permissions_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_has_menu_permissions';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE   => 'Active',

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_DELETE   => 'Deleted',

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

            self::IS_FEATURED     => 'Is Featured',
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
            'id'                  => Yii::t('app', 'ID'),
            'vendor_has_menu_id'  => Yii::t('app', 'Vendor Has Menu ID'),
            'menu_permissions_id' => Yii::t('app', 'Menu Permissions ID'),
            'status'              => Yii::t('app', 'Status'),
            'created_on'          => Yii::t('app', 'Created On'),
            'updated_on'          => Yii::t('app', 'Updated On'),
            'create_user_id'      => Yii::t('app', 'Create User ID'),
            'update_user_id'      => Yii::t('app', 'Update User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
   public function getVendorHasMenus()
{
    return $this->hasMany(VendorHasMenus::class, ['vendor_id' => 'id']);
}
 public function getMenus()
{
    return $this->hasMany(Menus::class, ['id' => 'menu_id'])
        ->via('vendorHasMenus');
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
    public function getMenuPermissions()
    {
        return $this->hasOne(\app\modules\admin\models\MenuPermissions::className(), ['id' => 'menu_permissions_id']);
    }

    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value'              => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class'              => BlameableBehavior::className(),
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }

    /**
     * @inheritdoc
     * @return \app\modules\admin\models\VendorHasMenuPermissionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorHasMenuPermissionsQuery(get_called_class());
    }
    public function asJson()
    {
        $data       = [];
        $data['id'] = $this->id;

        $data['vendor_has_menu_id'] = $this->vendor_has_menu_id;

        $data['menu_permissions_id'] = $this->menu_permissions_id;

        $data['status'] = $this->status;

        $data['created_on'] = $this->created_on;

        $data['updated_on'] = $this->updated_on;

        $data['create_user_id'] = $this->create_user_id;

        $data['update_user_id'] = $this->update_user_id;

        return $data;
    }

}
