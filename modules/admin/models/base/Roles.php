<?php

namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "roles".
 *
 * @property integer $id
 * @property integer $owner_user_id
 * @property string $role_name
 * @property string $description
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\RoleMenuPermissions[] $roleMenuPermissions
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $ownerUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\UserRoles[] $userRoles
 */
class Roles extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'roleMenuPermissions',
            'createUser',
            'ownerUser',
            'updateUser',
            'userRoles'
        ];
    }

    /**
     * @inheritdoc
     */
 public function rules()
{
    return [
        [['owner_user_id', 'role_name'], 'required'],
        [['owner_user_id', 'create_user_id', 'update_user_id'], 'integer'],
        [['created_on', 'updated_on'], 'safe'],
        [['role_name'], 'string', 'max' => 100],
        [['role_name'], 'match', 'pattern' => '/^[A-Za-z_]+$/', 'message' => 'Role name can only contain letters and underscores.'],
        [['description'], 'string', 'max' => 255],
        [['status'], 'string', 'max' => 1]
    ];
}


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'roles';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'owner_user_id' => Yii::t('app', 'Owner User ID'),
            'role_name' => Yii::t('app', 'Role Name'),
            'description' => Yii::t('app', 'Description'),
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
    public function getRoleMenuPermissions()
    {
        return $this->hasMany(\app\modules\admin\models\RoleMenuPermissions::className(), ['role_id' => 'id']);
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
    public function getOwnerUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'owner_user_id']);
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
    public function getUserRoles()
    {
        return $this->hasMany(\app\modules\admin\models\UserRoles::className(), ['role_id' => 'id']);
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
     * @return \app\modules\admin\models\RolesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\RolesQuery(get_called_class());
    }
}
