<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "quiz_user_answers".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $quiz_id
 * @property integer $question_id
 * @property integer $answer_id
 * @property integer $is_correct
 * @property string $answered_on
 * @property integer $status
 *
 * @property \app\modules\admin\models\User $user
 * @property \app\modules\admin\models\Quizzes $quiz
 * @property \app\modules\admin\models\QuizQuestions $question
 * @property \app\modules\admin\models\QuizAnswers $answer
 */
class QuizUserAnswers extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'user',
            'quiz',
            'question',
            'answer'
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
            [['user_id', 'quiz_id', 'question_id', 'answer_id'], 'required'],
            [['user_id', 'quiz_id', 'question_id', 'answer_id', 'status'], 'integer'],
            [['answered_on'], 'safe'],
            [['is_correct'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'quiz_user_answers';
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
            'user_id' => Yii::t('app', 'User ID'),
            'quiz_id' => Yii::t('app', 'Quiz ID'),
            'question_id' => Yii::t('app', 'Question ID'),
            'answer_id' => Yii::t('app', 'Answer ID'),
            'is_correct' => Yii::t('app', 'Is Correct'),
            'answered_on' => Yii::t('app', 'Answered On'),
            'status' => Yii::t('app', 'Status'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuiz()
    {
        return $this->hasOne(\app\modules\admin\models\Quizzes::className(), ['id' => 'quiz_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(\app\modules\admin\models\QuizQuestions::className(), ['id' => 'question_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnswer()
    {
        return $this->hasOne(\app\modules\admin\models\QuizAnswers::className(), ['id' => 'answer_id']);
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
     * @return \app\modules\admin\models\QuizUserAnswersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\QuizUserAnswersQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['user_id'] =  $this->user_id;
        
                $data['quiz_id'] =  $this->quiz_id;
        
                $data['question_id'] =  $this->question_id;
        
                $data['answer_id'] =  $this->answer_id;
        
                $data['is_correct'] =  $this->is_correct;
        
                $data['answered_on'] =  $this->answered_on;
        
                $data['status'] =  $this->status;
        
            return $data;
}


}


