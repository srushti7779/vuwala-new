<?php

namespace app\modules\admin\models;

use Yii;
use yii\helpers\Json;
use \app\modules\admin\models\base\AisensyBulkCampaignLog as BaseAisensyBulkCampaignLog;

/**
 * This is the model class for table "aisensy_bulk_campaign_log".
 */
class AisensyBulkCampaignLog extends BaseAisensyBulkCampaignLog
{
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_PAUSED = 'paused';
    
    /**
     * Get campaign statistics
     * @return array
     */
    public function getStatistics()
    {
        $stats = [
            'total_contacts' => $this->total_contacts ?? 0,
            'sent_count' => $this->sent_count ?? 0,
            'delivered_count' => $this->delivered_count ?? 0,
            'failed_count' => $this->failed_count ?? 0,
            'skipped_count' => $this->skipped_count ?? 0,
            'pending_count' => $this->total_contacts - ($this->sent_count + $this->failed_count + $this->skipped_count)
        ];
        
        // Calculate rates
        if ($stats['total_contacts'] > 0) {
            $stats['success_rate'] = round(($stats['sent_count'] / $stats['total_contacts']) * 100, 2);
            $stats['delivery_rate'] = $stats['sent_count'] > 0 ? 
                round(($stats['delivered_count'] / $stats['sent_count']) * 100, 2) : 0;
            $stats['failure_rate'] = round(($stats['failed_count'] / $stats['total_contacts']) * 100, 2);
        } else {
            $stats['success_rate'] = 0;
            $stats['delivery_rate'] = 0;
            $stats['failure_rate'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Check if campaign is complete
     * @return bool
     */
    public function isComplete()
    {
        $stats = $this->getStatistics();
        return $stats['pending_count'] <= 0;
    }
    
    /**
     * Update campaign completion status
     */
    public function checkAndUpdateCompletion()
    {
        if ($this->isComplete() && $this->campaign_status !== self::STATUS_COMPLETED) {
            $this->campaign_status = self::STATUS_COMPLETED;
            if ($this->hasAttribute('completed_at')) {
                $this->completed_at = date('Y-m-d H:i:s');
            }
            $this->save(false);
            
            Yii::info("Campaign {$this->id} marked as completed", __METHOD__);
        }
    }
}
