<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/Database.php';

function audit_log(Database $db, string $action, string $entityType, ?int $entityId, string $summary, array $details = []): void
{
    $db->insert('audit_log', [
        'user_id' => current_user_id(),
        'username' => current_username(),
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'summary' => substr($summary, 0, 255),
        'details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
    ]);
}

function latest_audit_subquery(string $entityType, string $entityIdColumn): string
{
    return "(SELECT CONCAT(al.username, ' · ', al.summary, ' · ', DATE_FORMAT(al.created_at, '%d.%m.%Y %H:%i'))
             FROM audit_log al
             WHERE al.entity_type = '{$entityType}' AND al.entity_id = {$entityIdColumn}
             ORDER BY al.created_at DESC
             LIMIT 1)";
}
