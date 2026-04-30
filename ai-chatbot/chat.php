<?php
declare(strict_types=1);
session_start();

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/rate-limit.php';
require_once __DIR__ . '/includes/openai.php';

try {
    $data = json_input();
    $clientKey = trim((string)($data['client_key'] ?? ''));
    $visitorId = substr(trim((string)($data['visitor_id'] ?? '')), 0, 32);
    $message = trim((string)($data['message'] ?? ''));
    $pageUrl = substr(trim((string)($data['page_url'] ?? '')), 0, 500);
    $referrer = substr(trim((string)($data['referrer'] ?? '')), 0, 500);
    $conversationId = trim((string)($data['conversation_id'] ?? ''));

    if ($clientKey === '' || $visitorId === '' || $message === '') json_response(['success' => false, 'error' => 'Invalid input'], 422);
    if (strlen($message) > $config['app']['max_message_length']) json_response(['success' => false, 'error' => 'Message too long'], 422);

    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM chatbot_clients WHERE public_key = :k AND status = "active" LIMIT 1');
    $stmt->execute(['k' => $clientKey]);
    $client = $stmt->fetch();
    if (!$client) json_response(['success' => false, 'error' => 'Client not found'], 404);

    $origin = normalize_origin($_SERVER['HTTP_ORIGIN'] ?? '');
    $allowed = normalize_origin($client['website_url'] ?? '');
    if ($allowed && $origin && $allowed !== $origin) json_response(['success' => false, 'error' => 'Origin blocked'], 403);
    if ($origin) header('Access-Control-Allow-Origin: ' . $origin);

    $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . $config['app']['ip_hash_salt']);
    if (!enforce_rate_limit($pdo, $client['id'], $ipHash, $visitorId)) json_response(['success' => false, 'error' => 'Rate limit reached'], 429);

    if ($conversationId === '') {
        $conversationId = random_id(16);
        $ins = $pdo->prepare('INSERT INTO chatbot_conversations (id, client_id, visitor_id, visitor_ip_hash, user_agent, page_url, referrer, created_at, updated_at) VALUES (:id,:client,:visitor,:ip,:ua,:page,:ref,UTC_TIMESTAMP(6),UTC_TIMESTAMP(6))');
        $ins->execute(['id'=>$conversationId,'client'=>$client['id'],'visitor'=>$visitorId,'ip'=>$ipHash,'ua'=>substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''),0,255),'page'=>$pageUrl,'ref'=>$referrer]);
    }

    $pdo->prepare('INSERT INTO chatbot_messages (conversation_id, role, content, created_at) VALUES (:c, "user", :m, UTC_TIMESTAMP(6))')->execute(['c'=>$conversationId,'m'=>$message]);

    $hist = $pdo->prepare('SELECT role, content FROM chatbot_messages WHERE conversation_id=:c ORDER BY id DESC LIMIT 12');
    $hist->execute(['c'=>$conversationId]);
    $history = array_reverse($hist->fetchAll());

    $global = "You are a website AI assistant for this business only. Be concise, friendly, lead-focused. Never invent prices, availability, guarantees, legal/medical/financial claims. If unsure, say a team member can follow up.";
    if ((int)$client['lead_capture_enabled'] === 1) $global .= " When user shows interest, ask for name, email, and phone.";
    $systemPrompt = $global . "\nClient prompt:\n" . ($client['system_prompt'] ?? '') . "\nBusiness context:\n" . ($client['business_context'] ?? '');

    $oa = openai_generate_reply($config, $systemPrompt, $history);
    $reply = $oa['reply'] ?: ($client['fallback_message'] ?: 'Thanks! A team member can follow up shortly.');

    $pdo->prepare('INSERT INTO chatbot_messages (conversation_id, role, content, token_input, token_output, created_at) VALUES (:c, "assistant", :m, :in_t, :out_t, UTC_TIMESTAMP(6))')
        ->execute(['c'=>$conversationId,'m'=>$reply,'in_t'=>$oa['input_tokens'],'out_t'=>$oa['output_tokens']]);

    $pdo->prepare('UPDATE chatbot_conversations SET updated_at = UTC_TIMESTAMP(6) WHERE id = :id')->execute(['id'=>$conversationId]);

    json_response(['success'=>true,'conversation_id'=>$conversationId,'reply'=>$reply]);
} catch (Throwable $e) {
    log_error('chat.php failed', ['error' => $e->getMessage()]);
    json_response(['success' => false, 'error' => 'Service unavailable'], 500);
}
