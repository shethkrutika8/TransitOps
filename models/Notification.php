<?php
// models/Notification.php

require_once __DIR__ . '/Model.php';

class Notification extends Model
{
    protected $table = 'notifications';

    public function getUnreadCount(): int
    {
        $sql = "SELECT COUNT(*) AS count FROM notifications WHERE is_read = 0";
        $stmt = self::query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($row['count'] ?? 0);
    }

    public function getLatest(int $limit = 10): array
    {
        $sql = "SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT :limit";
        $stmt = self::db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM notifications ORDER BY is_read ASC, created_at DESC";
        $stmt = self::query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markRead(int $id): bool
    {
        return $this->update($id, ['is_read' => 1]);
    }

    public function markAllRead(): bool
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
        return (bool) self::query($sql);
    }

    public static function generateSystemAlerts(): void
    {
        $db = Database::connect();
        $today = date('Y-m-d');
        $warn30 = date('Y-m-d', strtotime('+30 days'));

        $insertNotification = function (PDO $db, string $title, string $message, string $type, int $relatedId, string $relatedType): void {
            $sql = "INSERT INTO notifications (title, message, type, related_id, related_type)
                    VALUES (:title, :message, :type, :related_id, :related_type)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'related_id' => $relatedId,
                'related_type' => $relatedType
            ]);
        };

        $driverSql = "SELECT id, name, employee_id, license_expiry
                      FROM drivers
                      WHERE license_expiry <= :warn
                      AND status != 'Suspended'
                      AND id NOT IN (
                          SELECT related_id
                          FROM notifications
                          WHERE related_type = 'Driver'
                          AND type IN ('danger','warning')
                          AND DATE(created_at) = :today
                      )";
        $stmt = $db->prepare($driverSql);
        $stmt->execute(['warn' => $warn30, 'today' => $today]);
        $expiringDrivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($expiringDrivers as $d) {
            $daysLeft = (strtotime($d['license_expiry']) - time()) / 86400;
            $type = $daysLeft < 0 ? 'danger' : 'warning';
            $title = $daysLeft < 0
                ? "Driver License EXPIRED: {$d['name']}"
                : "Driver License Expiring Soon: {$d['name']}";

            $message = $daysLeft < 0
                ? "Driver {$d['name']} ({$d['employee_id']}) has an expired license (Expired: {$d['license_expiry']}). Assignment blocked."
                : "Driver {$d['name']} ({$d['employee_id']}) license expires in " . ceil($daysLeft) . " days ({$d['license_expiry']}).";

            $insertNotification($db, $title, $message, $type, (int)$d['id'], 'Driver');
        }

        $vehicleSql = "SELECT id, registration_number, name, insurance_expiry, rc_expiry
                       FROM vehicles
                       WHERE status != 'Retired'
                       AND (
                           insurance_expiry <= :warn_ins
                           OR rc_expiry <= :warn_rc
                       )
                       AND id NOT IN (
                           SELECT related_id
                           FROM notifications
                           WHERE related_type = 'Vehicle'
                           AND DATE(created_at) = :today
                       )";
        $stmt = $db->prepare($vehicleSql);
        $stmt->execute(['warn_ins' => $warn30, 'warn_rc' => $warn30, 'today' => $today]);
        $expiringVehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($expiringVehicles as $v) {
            $insDays = (strtotime($v['insurance_expiry']) - time()) / 86400;
            $rcDays = (strtotime($v['rc_expiry']) - time()) / 86400;

            if ($insDays <= 30) {
                $type = $insDays < 0 ? 'danger' : 'warning';
                $title = $insDays < 0
                    ? "Insurance EXPIRED: {$v['registration_number']}"
                    : "Insurance Expiring: {$v['registration_number']}";

                $message = $insDays < 0
                    ? "Vehicle {$v['registration_number']} insurance expired on {$v['insurance_expiry']}."
                    : "Vehicle {$v['registration_number']} insurance expires in " . ceil($insDays) . " days.";

                $insertNotification($db, $title, $message, $type, (int)$v['id'], 'Vehicle');
            }

            if ($rcDays <= 30) {
                $type = $rcDays < 0 ? 'danger' : 'warning';
                $title = $rcDays < 0
                    ? "RC Certificate EXPIRED: {$v['registration_number']}"
                    : "RC Expiring: {$v['registration_number']}";

                $message = $rcDays < 0
                    ? "Vehicle {$v['registration_number']} RC Certificate expired on {$v['rc_expiry']}."
                    : "Vehicle {$v['registration_number']} RC expires in " . ceil($rcDays) . " days.";

                $insertNotification($db, $title, $message, $type, (int)$v['id'], 'Vehicle');
            }
        }

        $maintSql = "SELECT m.id, m.next_service_date, m.type, v.registration_number
                     FROM maintenance m
                     JOIN vehicles v ON m.vehicle_id = v.id
                     WHERE m.status != 'Completed'
                     AND m.next_service_date <= :warn
                     AND m.id NOT IN (
                         SELECT related_id
                         FROM notifications
                         WHERE related_type = 'Maintenance'
                         AND DATE(created_at) = :today
                     )";
        $stmt = $db->prepare($maintSql);
        $stmt->execute(['warn' => $warn30, 'today' => $today]);
        $dueMaintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dueMaintenance as $m) {
            $days = (strtotime($m['next_service_date']) - time()) / 86400;
            $type = $days < 0 ? 'danger' : 'warning';
            $title = "Service Due: {$m['registration_number']}";

            $message = $days < 0
                ? "Vehicle {$m['registration_number']} is overdue for scheduled {$m['type']} service (was due: {$m['next_service_date']})."
                : "Vehicle {$m['registration_number']} {$m['type']} service is due in " . ceil($days) . " days.";

            $insertNotification($db, $title, $message, $type, (int)$m['id'], 'Maintenance');
        }
    }
}