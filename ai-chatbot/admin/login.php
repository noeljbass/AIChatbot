<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_validate($_POST['csrf_token'] ?? '')) $error='Invalid CSRF token';
  else {
    $stmt=db()->prepare('SELECT * FROM chatbot_admin_users WHERE email=:e LIMIT 1');
    $stmt->execute(['e'=>trim((string)($_POST['email']??''))]);
    $u=$stmt->fetch();
    if($u && password_verify((string)($_POST['password']??''),$u['password_hash'])){$_SESSION['admin_user_id']=$u['id']; header('Location: index.php'); exit;}
    $error='Invalid credentials';
  }
}
?><!doctype html><html><body><h1>Admin Login</h1><?php if($error):?><p><?=e($error)?></p><?php endif;?><form method='post'><input type='hidden' name='csrf_token' value='<?=e(csrf_token())?>'><input name='email'><input type='password' name='password'><button>Login</button></form></body></html>
