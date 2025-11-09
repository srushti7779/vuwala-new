<?php 

return [
    'adminEmail' => 'esteticateam011@gmail.com', // Replace with actual admin email
    'supportEmail' => '@gmail.com', // Replace with actual support email
    
    // AiSensy API Configuration - API key removed for security
    'aisensy_api_key' => null, // value removed
    'aisensy_api_url' => 'https://backend.aisensy.com/direct-apis/t1/messages',
    
    // Template Message Settings
    'template_message_settings' => [
        'daily_limit_enabled' => true,
        'max_retries' => 3,
        'retry_delay' => 5, // seconds
        'default_timeout' => 30, // seconds
        'log_api_responses' => true,
    ],
    
    // WhatsApp Media Limits (for validation)
    'whatsapp_media_limits' => [
        'image_max_size' => 5 * 1024 * 1024, // 5MB
        'video_max_size' => 16 * 1024 * 1024, // 16MB
        'document_max_size' => 100 * 1024 * 1024, // 100MB
        'audio_max_size' => 16 * 1024 * 1024, // 16MB
    ],
    
    // Contact Number Formatting
    'phone_formatting' => [
        'default_country_code' => '91', // India
        'min_length' => 10,
        'max_length' => 15,
    ],
];     

 