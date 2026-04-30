<?php
declare(strict_types=1);

function send_lead_email(array $config, string $to, string $subject, string $body): bool
{
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: ' . $config['app']['mail_from_name'] . ' <' . $config['app']['mail_from_email'] . '>',
    ];
    return mail($to, $subject, $body, implode("\r\n", $headers));
}
