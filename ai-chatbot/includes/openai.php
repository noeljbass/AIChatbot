<?php
declare(strict_types=1);

function openai_generate_reply(array $config, string $systemPrompt, array $history): array
{
    $input = [
        ['role' => 'system', 'content' => [['type' => 'input_text', 'text' => $systemPrompt]]],
    ];
    foreach ($history as $item) {
        $input[] = [
            'role' => $item['role'] === 'assistant' ? 'assistant' : 'user',
            'content' => [['type' => 'input_text', 'text' => $item['content']]],
        ];
    }

    $payload = [
        'model' => $config['openai']['model'],
        'input' => $input,
    ];

    $ch = curl_init('https://api.openai.com/v1/responses');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config['openai']['api_key'],
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => (int)$config['openai']['timeout'],
    ]);

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $status >= 400) {
        throw new RuntimeException('OpenAI API failure: ' . $error . ' status=' . $status);
    }

    $decoded = json_decode($raw, true);
    $reply = trim((string)($decoded['output_text'] ?? ''));
    if ($reply === '') {
        throw new RuntimeException('Empty OpenAI response');
    }

    return [
        'reply' => $reply,
        'input_tokens' => (int)($decoded['usage']['input_tokens'] ?? 0),
        'output_tokens' => (int)($decoded['usage']['output_tokens'] ?? 0),
    ];
}
