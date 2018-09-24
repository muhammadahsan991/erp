<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%erp_cron_log}}".
 *
 * @property int $id_erp_cron_log
 * @property string $message 0=Erp Receipts Data Insertion, 1=Erp OMS Data Insertion, 2=Report Generation
 * @property string $created_at 0=Erp Receipts Data Insertion, 1=Erp OMS Data Insertion, 2=Report Generation
 * @property string $updated_at
 */
class ErpCronLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%erp_cron_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message'], 'required'],
            [['message'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_erp_cron_log' => 'Id Erp Cron Log',
            'message' => 'Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
