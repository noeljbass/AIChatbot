<?php
session_start(); require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../db.php'; require_once __DIR__ . '/../includes/helpers.php'; require_admin_auth();
$rows=db()->query('SELECT * FROM chatbot_clients ORDER BY created_at DESC')->fetchAll();
?><!doctype html><html><body><h1>Clients</h1><a href='client-edit.php'>+ New</a><table border='1'><tr><th>Name</th><th>Key</th><th>Status</th><th></th></tr><?php foreach($rows as $r):?><tr><td><?=e($r['name'])?></td><td><?=e($r['public_key'])?></td><td><?=e($r['status'])?></td><td><a href='client-edit.php?id=<?=e($r['id'])?>'>Edit</a></td></tr><?php endforeach;?></table></body></html>
