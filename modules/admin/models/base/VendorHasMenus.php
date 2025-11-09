<?php
namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the base model class for table "vendor_has_menus".
 *
 * @property integer $id
 * @property integer $vendor_id
 * @property integer $menu_id
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendor
 * @property \app\modules\admin\models\Menus $menu
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 *
 */
class VendorHasMenus extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;
    public $menuIds = [];

    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */

    public function relationNames()
    {
        return [
            'vendor',
            'menu',
            'createUser',
            'updateUser',
        ];
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['vendor_id'], 'required'],
            [['vendor_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['status'], 'string', 'max' => 1],
            [['menu_id'], 'safe'], 
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_has_menus';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => Yii::t('app', 'ID'),
            'vendor_id'      => Yii::t('app', 'Vendor ID'),
            'menu_id'        => Yii::t('app', 'Menu ID'),
            'status'         => Yii::t('app', 'Status'),
            'created_on'     => Yii::t('app', 'Created On'),
            'updated_on'     => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_id']);
    }
    

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(\app\modules\admin\models\Menus::className(), ['id' => 'menu_id']);
    }
    public function getVendorHasMenuPermissions()
    {
        return $this->hasMany(VendorHasMenuPermissions::class, ['vendor_has_menu_id' => 'id']);
    }
    public function getVendorHasMenu()
{
    return $this->hasOne(\app\modules\admin\models\VendorHasMenus::class, ['id' => 'vendor_has_menu_id']);
}


public function getMenuPermissions()
{
    return $this->hasMany(\app\modules\admin\models\VendorHasMenuPermissions::class, ['vendor_has_menu_id' => 'id']);
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
     * @return \app\modules\admin\models\VendorHasMenusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorHasMenusQuery(get_called_class());
    }
}
