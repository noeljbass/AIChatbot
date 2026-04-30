<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';

try {
    $data = json_input();
    $clientKey = trim((string)($data['client_key'] ?? ''));
    $conversationId = trim((string)($data['conversation_id'] ?? ''));
    $name = substr(trim((string)($data['name'] ?? '')),0,190);
    $email = substr(trim((string)($data['email'] ?? '')),0,190);
    $phone = substr(trim((string)($data['phone'] ?? '')),0,80);
    $message = trim((string)($data['message'] ?? ''));

    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM chatbot_clients WHERE public_key = :k LIMIT 1');
    $stmt->execute(['k' => $clientKey]);
    $client = $stmt->fetch();
    if (!$client) json_response(['success'=>false],404);

    $leadId = random_id(16);
    $pdo->prepare('INSERT INTO chatbot_leads (id, client_id, conversation_id, name, email, phone, message, page_url, created_at) SELECT :id, :client, c.id, :name, :email, :phone, :message, c.page_url, UTC_TIMESTAMP(6) FROM chatbot_conversations c WHERE c.id = :conv')
        ->execute(['id'=>$leadId,'client'=>$client['id'],'conv'=>$conversationId,'name'=>$name,'email'=>$email,'phone'=>$phone,'message'=>$message]);

    $pdo->prepare('UPDATE chatbot_conversations SET lead_name=:name, lead_email=:email, lead_phone=:phone, lead_status="captured", updated_at=UTC_TIMESTAMP(6) WHERE id=:id')
        ->execute(['id'=>$conversationId,'name'=>$name,'email'=>$email,'phone'=>$phone]);

    $adminLink = $config['app']['base_url'] . '/admin/conversation-view.php?id=' . urlencode($conversationId);
    $body = "New lead captured\n\nName: {$name}\nEmail: {$email}\nPhone: {$phone}\nMessage: {$message}\nConversation: {$conversationId}\nView: {$adminLink}";
    if (!empty($client['notification_email'])) {
        send_lead_email($config, $client['notification_email'], 'New AI chatbot lead: ' . $client['name'], $body);
        $pdo->prepare('UPDATE chatbot_leads SET sent_to_client_at = UTC_TIMESTAMP(6) WHERE id=:id')->execute(['id'=>$leadId]);
        $pdo->prepare('UPDATE chatbot_conversations SET lead_status="sent" WHERE id=:id')->execute(['id'=>$conversationId]);
    }

    json_response(['success'=>true]);
} catch (Throwable $e) {
    log_error('lead.php failed', ['error'=>$e->getMessage()]);
    json_response(['success'=>false],500);
}
