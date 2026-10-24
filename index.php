<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$user = current_user();
if ($user) {
    redirect(home_path_for($user));
}
redirect('login.php');
