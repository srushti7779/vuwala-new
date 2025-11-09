<?php

namespace app\modules\admin\models;

use Yii;
use yii\helpers\Json;
use \app\modules\admin\models\base\AisensyBulkMessageLog as BaseAisensyBulkMessageLog;

/**
 * This is the model class for table "aisensy_bulk_message_log".
 */
class AisensyBulkMessageLog extends BaseAisensyBulkMessageLog
{
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';
    
    /**
     * Update message status from webhook
     * @param string $status
     * @param string $messageId
     * @param array $payload
     */
    public function updateMessageStatus($status, $messageId = null, $payload = [])
    {
        try {
            // Update basic status
            if ($this->hasAttribute('status')) {
                $this->status = $status;
            }
            
            // Update message ID if provided
            if (!empty($messageId) && $this->hasAttribute('message_id')) {
                $this->message_id = $messageId;
            }
            
            // Update timestamps based on status
            $timestamp = date('Y-m-d H:i:s');
            
            switch ($status) {
                case self::STATUS_SENT:
                    if ($this->hasAttribute('sent_at')) {
                        $this->sent_at = $timestamp;
                    } elseif ($this->hasAttribute('sent_datetime')) {
                        $this->sent_datetime = $timestamp;
                    }
                    break;
                    
                case self::STATUS_DELIVERED:
                    if ($this->hasAttribute('delivered_at')) {
                        $this->delivered_at = $timestamp;
                    } elseif ($this->hasAttribute('delivered_datetime')) {
                        $this->delivered_datetime = $timestamp;
                    }
                    break;
                    
                case self::STATUS_READ:
                    if ($this->hasAttribute('read_at')) {
                        $this->read_at = $timestamp;
                    } elseif ($this->hasAttribute('read_datetime')) {
                        $this->read_datetime = $timestamp;
                    }
                    break;
                    
                case self::STATUS_FAILED:
                    if ($this->hasAttribute('failed_at')) {
                        $this->failed_at = $timestamp;
                    } elseif ($this->hasAttribute('failed_datetime')) {
                        $this->failed_datetime = $timestamp;
                    }
                    
                    // Store error information from payload
                    if (!empty($payload) && $this->hasAttribute('error_message')) {
                        $errorMessage = $this->extractErrorMessage($payload);
                        if ($errorMessage) {
                            $this->error_message = $errorMessage;
                        }
                    }
                    break;
            }
            
            // Store webhook response data
            if (!empty($payload)) {
                if ($this->hasAttribute('webhook_updates')) {
                    $existingUpdates = $this->webhook_updates ? Json::decode($this->webhook_updates) : [];
                    $existingUpdates[] = [
                        'status' => $status,
                        'timestamp' => $timestamp,
                        'message_id' => $messageId,
                        'payload_summary' => $this->summarizePayload($payload)
                    ];
                    $this->webhook_updates = Json::encode($existingUpdates);
                } elseif ($this->hasAttribute('response_data')) {
                    // Fallback to response_data field
                    $this->response_data = Json::encode([
                        'status' => $status,
                        'timestamp' => $timestamp,
                        'message_id' => $messageId,
                        'payload' => $payload
                    ]);
                }
            }
            
            // Save the changes
            if ($this->save(false)) {
                Yii::info("Bulk message log {$this->id} updated successfully with status: {$status}", __METHOD__);
            } else {
                Yii::error("Failed to save bulk message log {$this->id} updates", __METHOD__);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error updating message status for bulk message log {$this->id}: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Extract error message from webhook payload
     * @param array $payload
     * @return string|null
     */
    private function extractErrorMessage($payload)
    {
        // Check various error message locations
        if (isset($payload['error']['message'])) {
            return $payload['error']['message'];
        }
        
        if (isset($payload['error_message'])) {
            return $payload['error_message'];
        }
        
        if (isset($payload['entry'][0]['changes'][0]['value']['statuses'][0]['errors'][0]['title'])) {
            return $payload['entry'][0]['changes'][0]['value']['statuses'][0]['errors'][0]['title'];
        }
        
        if (isset($payload['message'])) {
            return $payload['message'];
        }
        
        return null;
    }
    
    /**
     * Summarize payload for storage
     * @param array $payload
     * @return array
     */
    private function summarizePayload($payload)
    {
        try {
            return [
                'has_entry' => isset($payload['entry']),
                'has_statuses' => isset($payload['entry'][0]['changes'][0]['value']['statuses']),
                'object_type' => $payload['object'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'size_kb' => round(strlen(Json::encode($payload)) / 1024, 2)
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to summarize payload'];
        }
    }
}
