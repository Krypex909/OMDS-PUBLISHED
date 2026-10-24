<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    audit_log(db(), (int) current_user()['id'], 'logout', 'user', (string) current_user()['id'], 'User logged out');
}
logout_user();
start_omds_session();
flash_set('success', 'You have been logged out.');
redirect('login.php');
