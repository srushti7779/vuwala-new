<?php

namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "menus".
 *
 * @property integer $id
 * @property string $label
 * @property string $route
 * @property string $icon
 * @property integer $parent_id
 * @property integer $sort_order
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\MenuPermissions[] $menuPermissions
 */
class Menus extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'menuPermissions'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label'], 'required'],
            [['parent_id', 'sort_order', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['label', 'icon'], 'string', 'max' => 100],
            [['route'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menus';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'label' => Yii::t('app', 'Label'),
            'route' => Yii::t('app', 'Route'),
            'icon' => Yii::t('app', 'Icon'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
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
    public function getMenuPermissions()
    {
        return $this->hasMany(\app\modules\admin\models\MenuPermissions::className(), ['menu_id' => 'id']);
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
     * @return \app\modules\admin\models\MenusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\MenusQuery(get_called_class());
    }


    public function asJson()
{
    $data = []; 
    $data['id'] = $this->id;
    $data['label'] = $this->label; // Menu label shown on frontend
    $data['route'] = $this->route; // URL or route name
    $data['icon'] = $this->icon; // Menu icon class
    $data['parent_id'] = $this->parent_id; // Parent menu ID (for nesting)
    $data['sort_order'] = $this->sort_order; // Sort order for display
    $data['status'] = $this->status; // 1 = Active, 0 = Inactive
    $data['created_on'] = $this->created_on;
    $data['updated_on'] = $this->updated_on;
    $data['create_user_id'] = $this->create_user_id;
    $data['update_user_id'] = $this->update_user_id;
    
    return $data;
}


}
