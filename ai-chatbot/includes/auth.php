<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

function require_admin_auth(): void
{
    if (empty($_SESSION['admin_user_id'])) {
        header('Location: login.php');
        exit;
    }
}
