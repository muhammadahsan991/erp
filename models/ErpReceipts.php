<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%erp_receipts}}".
 *
 * @property int $id_erp_receipts
 * @property string $tracking_nr
 * @property string $cod_received
 * @property string $chq_nr
 * @property string $deposit_date
 * @property string $created_at
 * @property string $updated_at
 */
class ErpReceipts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%erp_receipts}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cod_received', 'chq_nr', 'deposit_date'], 'required'],
            [['cod_received'], 'number'],
            [['deposit_date', 'created_at', 'updated_at'], 'safe'],
            [['tracking_nr'], 'string', 'max' => 255],
            [['chq_nr'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_erp_receipts' => 'Id Erp Receipts',
            'tracking_nr' => 'Tracking Nr',
            'cod_received' => 'Cod Received',
            'chq_nr' => 'Chq Nr',
            'deposit_date' => 'Deposit Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
