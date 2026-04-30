<?php
declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

return [
    'db' => [
        'host' => env_value('DB_HOST', 'localhost'),
        'name' => env_value('DB_NAME', ''),
        'user' => env_value('DB_USER', ''),
        'pass' => env_value('DB_PASS', ''),
        'charset' => env_value('DB_CHARSET', 'utf8mb4'),
    ],
    'openai' => [
        'api_key' => env_value('OPENAI_API_KEY', ''),
        'model' => env_value('OPENAI_MODEL', 'gpt-4.1-mini'),
        'timeout' => 45,
    ],
    'app' => [
        'base_url' => rtrim(env_value('CHATBOT_BASE_URL', 'https://MYDOMAIN.com/ai-chatbot'), '/'),
        'admin_email' => env_value('CHATBOT_ADMIN_EMAIL', ''),
        'mail_from_email' => env_value('MAIL_FROM_EMAIL', ''),
        'mail_from_name' => env_value('MAIL_FROM_NAME', 'AI Website Assistant'),
        'ip_hash_salt' => env_value('IP_HASH_SALT', 'change-me'),
        'max_message_length' => (int) env_value('MAX_MESSAGE_LENGTH', '1000'),
    ],
];
