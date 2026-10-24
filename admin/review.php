<?php
require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_set('error', 'Please use the Approve or Deny buttons on an application.');
    redirect('inbox.php');
}

verify_csrf();

$applicationId = (int) ($_POST['id'] ?? 0);
$decision = clean_text((string) ($_POST['decision'] ?? ''), 20);
$denialReason = clean_text((string) ($_POST['denial_reason'] ?? ''), 2000);

if ($applicationId < 1 || !in_array($decision, ['approve', 'deny'], true)) {
    flash_set('error', 'That review request was not valid.');
    redirect('inbox.php');
}

if ($decision === 'deny' && $denialReason === '') {
    flash_set('error', 'A denial reason is required.');
    redirect('application.php?id=' . $applicationId);
}

$pdo = db();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'SELECT pp.id, pp.user_id, pp.review_status, u.role, u.account_status, u.public_user_id, u.username
         FROM personnel_profiles pp
         JOIN users u ON u.id = pp.user_id
         WHERE pp.id = ?
         LIMIT 1
         FOR UPDATE'
    );
    $stmt->execute([$applicationId]);
    $row = $stmt->fetch();

    if (!$row) {
        $pdo->rollBack();
        flash_set('error', 'Application not found.');
        redirect('inbox.php');
    }

    if ((int) $row['user_id'] === (int) $admin['id']) {
        $pdo->rollBack();
        flash_set('error', 'You cannot review your own application.');
        redirect('application.php?id=' . $applicationId);
    }

    if ($row['review_status'] !== 'pending') {
        $pdo->rollBack();
        flash_set('error', 'This application has already been reviewed.');
        redirect('application.php?id=' . $applicationId);
    }

    if ($row['role'] !== 'personnel') {
        $pdo->rollBack();
        flash_set('error', 'This account is not a personnel application.');
        redirect('application.php?id=' . $applicationId);
    }

    $now = date('Y-m-d H:i:s');
    $adminId = (int) $admin['id'];
    $userId = (int) $row['user_id'];

    if ($decision === 'approve') {
        $reviewStatus = 'approved';
        $accountStatus = 'active';
        $reason = null;
        $auditAction = 'personnel_approved';
        $auditDetails = 'Approved personnel application #' . $applicationId
            . ' for account ' . $row['public_user_id'];
        $flashMessage = 'Application approved. The applicant now has authorized-personnel access.';
    } else {
        $reviewStatus = 'denied';
        $accountStatus = 'denied_personnel';
        $reason = $denialReason;
        $auditAction = 'personnel_denied';
        $auditDetails = 'Denied personnel application #' . $applicationId
            . ' for account ' . $row['public_user_id'];
        $flashMessage = 'Application denied. The applicant can still use regular-user features.';
    }

    $updateProfile = $pdo->prepare(
        'UPDATE personnel_profiles
         SET review_status = ?, reviewed_by = ?, reviewed_at = ?, denial_reason = ?
         WHERE id = ? AND review_status = ?'
    );
    $updateProfile->execute([
        $reviewStatus,
        $adminId,
        $now,
        $reason,
        $applicationId,
        'pending',
    ]);

    if ($updateProfile->rowCount() !== 1) {
        $pdo->rollBack();
        flash_set('error', 'This application could not be updated. It may have already been reviewed.');
        redirect('application.php?id=' . $applicationId);
    }

    $updateUser = $pdo->prepare(
        'UPDATE users
         SET account_status = ?
         WHERE id = ? AND role = ?'
    );
    $updateUser->execute([$accountStatus, $userId, 'personnel']);

    if ($updateUser->rowCount() !== 1) {
        $pdo->rollBack();
        flash_set('error', 'The applicant account could not be updated.');
        redirect('application.php?id=' . $applicationId);
    }

    audit_log(
        $pdo,
        $adminId,
        $auditAction,
        'personnel_profile',
        (string) $applicationId,
        $auditDetails
    );

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'The review could not be saved. Please try again.');
    redirect('application.php?id=' . $applicationId);
}

flash_set('success', $flashMessage);
redirect('application.php?id=' . $applicationId);
