<?php

require_once __DIR__ . '/auth.php';

function active_qr_token(PDO $pdo, int $userId): ?string
{
    $stmt = $pdo->prepare('SELECT token FROM qr_tokens WHERE user_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? $row['token'] : null;
}

function medical_qr_url(int $userId): string
{
    $token = active_qr_token(db(), $userId);
    if (!$token) {
        $token = generate_qr_token(db());
        db()->prepare('INSERT INTO qr_tokens (user_id, token, is_active) VALUES (?, ?, 1)')->execute([$userId, $token]);
    }
    return app_base_url() . '/scan.php?t=' . urlencode($token);
}

function current_access_row(PDO $pdo, int $ownerId, int $granteeId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM record_access
         WHERE owner_user_id = ? AND grantee_user_id = ?
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$ownerId, $granteeId]);
    return $stmt->fetch() ?: null;
}

function has_granted_record_access(PDO $pdo, int $ownerId, int $granteeId): bool
{
    $row = current_access_row($pdo, $ownerId, $granteeId);
    if (!$row || $row['status'] !== 'granted') {
        return false;
    }
    if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
        return false;
    }
    return true;
}

function personnel_may_view_owner(array $viewer, int $ownerId): bool
{
    if ((int) $viewer['id'] === $ownerId) {
        return true;
    }
    if (!personnel_can_access_records($viewer)) {
        return false;
    }
    return has_granted_record_access(db(), $ownerId, (int) $viewer['id']);
}

function load_user_with_profile(int $userId): ?array
{
    $stmt = db()->prepare(
        'SELECT u.*, p.date_of_birth, p.sex, p.religion, p.contact_number,
                p.emergency_contact, p.emergency_phone, p.medical_conditions,
                p.allergies, p.current_medications, p.medical_history, p.general_status,
                p.profile_picture, pp.review_status, pp.organization_name,
                pp.specialization, pp.professional_role, pp.professional_id_number
         FROM users u
         LEFT JOIN user_profiles p ON p.user_id = u.id
         LEFT JOIN personnel_profiles pp ON pp.user_id = u.id
         WHERE u.id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}
