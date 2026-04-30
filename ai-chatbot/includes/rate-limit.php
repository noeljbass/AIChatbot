<?php
declare(strict_types=1);

function enforce_rate_limit(PDO $pdo, string $clientId, string $ipHash, string $visitorId): bool
{
    $sql = 'SELECT COUNT(*) FROM chatbot_conversations c JOIN chatbot_messages m ON m.conversation_id = c.id
            WHERE c.client_id = :client_id AND c.visitor_ip_hash = :ip_hash AND c.visitor_id = :visitor_id
            AND m.role = "user" AND m.created_at >= (UTC_TIMESTAMP() - INTERVAL 1 MINUTE)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['client_id' => $clientId, 'ip_hash' => $ipHash, 'visitor_id' => $visitorId]);
    return ((int) $stmt->fetchColumn()) < 20;
}
