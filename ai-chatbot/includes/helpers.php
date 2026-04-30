<?php
declare(strict_types=1);

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s.u');
}

function random_id(int $length = 16): string
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    return is_array($data) ? $data : [];
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function log_error(string $message, array $context = []): void
{
    error_log('[ai-chatbot] ' . $message . ' ' . json_encode($context));
}

function normalize_origin(?string $origin): string
{
    if (!$origin) return '';
    $parts = parse_url($origin);
    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return '';
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    return strtolower($parts['scheme'] . '://' . $parts['host'] . $port);
}
