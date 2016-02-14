<?php

namespace app\models\db;

/**
 * @package app\models\db
 *
 * @property int $id
 * @property int $motionId
 * @property int $position
 * @property int $userId
 * @property string $role
 * @property string $comment
 * @property int $personType
 * @property string $name
 * @property string $organization
 * @property string $resolutionDate
 * @property string $contactEmail
 * @property string $contactPhone
 *
 * @property User $user
 * @property Motion $motion
 */
class MotionSupporter extends ISupporter
{

    /**
     * @return string
     */
    public static function tableName()
    {
        /** @var \app\models\settings\AntragsgruenApp $app */
        $app = \Yii::$app->params;
        return $app->tablePrefix . 'motionSupporter';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMotion()
    {
        return $this->hasOne(Motion::class, ['id' => 'motionId']);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['motionId', 'position', 'role'], 'required'],
            [['id', 'motionId', 'position', 'userId', 'personType'], 'number'],
            [['resolutionDate', 'contactEmail', 'contactPhone'], 'safe'],
            [['position', 'comment', 'personType', 'name', 'organization'], 'safe'],
        ];
    }
}
