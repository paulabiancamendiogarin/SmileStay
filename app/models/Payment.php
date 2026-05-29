<?php

class Payment
{
    private $db;
    private $table = 'payments';
    private static array $columnCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    private function hasColumn(string $column): bool
    {
        if (!array_key_exists($column, self::$columnCache)) {
            try {
                $stmt = $this->db->prepare(
                    "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = ?
                       AND COLUMN_NAME = ?"
                );
                $stmt->execute([$this->table, $column]);
                self::$columnCache[$column] = ((int) $stmt->fetchColumn()) > 0;
            } catch (Throwable $e) {
                self::$columnCache[$column] = false;
            }
        }

        return (bool) self::$columnCache[$column];
    }

    private function supportsRejectedStatus(): bool
    {
        if (!$this->hasColumn('status')) {
            return false;
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'status'"
            );
            $stmt->execute([$this->table]);
            $type = strtolower((string) $stmt->fetchColumn());
            return str_contains($type, 'rejected');
        } catch (Throwable $e) {
            return false;
        }
    }

    public function create(array $data): int|false
    {
        if ($this->hasColumn('payment_method')) {
            $cols = ['booking_id', 'amount', 'payment_method', 'reference_code', 'qr_image_path', 'proof_image', 'status', 'expires_at', 'created_at'];
            $vals = [
                $data['booking_id'],
                $data['amount'],
                $data['payment_method'] ?? 'cash',
                $data['reference_code'],
                $data['qr_image_path'] ?? null,
                null,
                'pending',
                $data['expires_at'],
            ];
            if ($this->hasColumn('transaction_id')) {
                array_splice($cols, 4, 0, 'transaction_id', 'card_brand', 'card_last4');
                array_splice($vals, 4, 0, $data['transaction_id'] ?? null, $data['card_brand'] ?? null, $data['card_last4'] ?? null);
            }
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (" . implode(', ', $cols) . ") VALUES ({$placeholders}, NOW())"
            );
            $ok = $stmt->execute($vals);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} 
                (booking_id, amount, reference_code, qr_image_path, proof_image, status, expires_at, created_at)
                 VALUES (?, ?, ?, ?, NULL, 'pending', ?, NOW())"
            );
            $ok = $stmt->execute([
                $data['booking_id'],
                $data['amount'],
                $data['reference_code'],
                $data['qr_image_path'] ?? null,
                $data['expires_at'],
            ]);
        }

        return $ok ? (int) $this->db->lastInsertId() : false;
    }

    public function findByBookingId(int $bookingId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE booking_id = ? LIMIT 1");
        $stmt->execute([$bookingId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function updateQrPath(int $bookingId, ?string $relativePath): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET qr_image_path = ? WHERE booking_id = ?"
        );

        return $stmt->execute([$relativePath, $bookingId]);
    }

    public function updateProof(int $bookingId, string $relativePath): bool
    {
        $set = ['proof_image = ?', "status = 'pending'"];
        $params = [$relativePath];

        if ($this->hasColumn('updated_at')) {
            $set[] = 'updated_at = NOW()';
        }

        if ($this->supportsRejectedStatus()) {
            $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE booking_id = ? AND status IN ('pending','rejected')";
        } else {
            $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE booking_id = ? AND status = 'pending'";
        }

        $params[] = $bookingId;

        if (!$this->db->prepare($sql)->execute($params)) {
            return false;
        }

        return $this->syncBookingPaymentStatus($bookingId, 'pending', $proofPath);
    }

    public function verify(int $paymentId): bool
    {
        $payment = $this->findById($paymentId);
        if (!$payment) {
            return false;
        }

        $set = "status = 'verified'";
        if ($this->hasColumn('updated_at')) {
            $set .= ", updated_at = NOW()";
        }

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$set} WHERE id = ? AND status = 'pending'");
        if (!$stmt->execute([$paymentId])) {
            return false;
        }

        return $this->syncBookingPaymentStatus((int) $payment['booking_id'], 'paid', $payment['proof_image'] ?? null);
    }

    public function getAllWithBookingDetails(): array
    {
        $sql = "SELECT p.*, 
                       b.booking_reference, b.total_price AS booking_total, b.status AS booking_status,
                       b.check_in, b.check_out,
                       h.hotel_name, r.room_type,
                       u.name AS guest_name, u.email AS guest_email
                FROM {$this->table} p
                JOIN bookings b ON b.id = p.booking_id
                JOIN hotels h ON h.id = b.hotel_id
                JOIN rooms r ON r.id = b.room_id
                JOIN users u ON u.id = b.user_id
                ORDER BY p.created_at DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserIdWithBooking(int $userId): array
    {
        $orderCol = $this->hasColumn('updated_at') ? 'p.updated_at' : 'p.created_at';
        $sql = "SELECT p.*,
                       b.id AS booking_id, b.booking_reference, b.check_in, b.check_out, b.total_price,
                       h.hotel_name, r.room_type
                FROM {$this->table} p
                JOIN bookings b ON b.id = p.booking_id
                JOIN hotels h ON h.id = b.hotel_id
                JOIN rooms r ON r.id = b.room_id
                WHERE b.user_id = ?
                ORDER BY {$orderCol} DESC, p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function isVerifiedForBooking(int $bookingId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE booking_id = ? AND status = 'verified'"
        );
        $stmt->execute([$bookingId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function markCardPaid(int $bookingId, string $transactionId, ?string $brand = null, ?string $last4 = null): bool
    {
        $set = ["status = 'verified'"];
        $params = [];

        if ($this->hasColumn('updated_at')) {
            $set[] = 'updated_at = NOW()';
        }

        if ($this->hasColumn('payment_method')) {
            $set[] = "payment_method = 'card'";
        }

        if ($this->hasColumn('transaction_id')) {
            $set[] = 'transaction_id = ?';
            $params[] = $transactionId;
        } elseif ($this->hasColumn('reference_code') && $transactionId !== '') {
            $set[] = 'reference_code = ?';
            $params[] = $transactionId;
        }

        if ($this->hasColumn('card_brand') && $brand !== null) {
            $set[] = 'card_brand = ?';
            $params[] = $brand;
        }

        if ($this->hasColumn('card_last4') && $last4 !== null) {
            $set[] = 'card_last4 = ?';
            $params[] = $last4;
        }

        $params[] = $bookingId;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE booking_id = ?";
        if (!$this->db->prepare($sql)->execute($params)) {
            return false;
        }

        return $this->syncBookingPaymentStatus($bookingId, 'paid');
    }

    public function updateClientCashPayment(int $bookingId, string $proofPath, ?string $reference = null, ?string $notes = null): bool
    {
        $set = ['proof_image = ?', "status = 'pending'"];
        $params = [$proofPath];

        if ($this->hasColumn('updated_at')) {
            $set[] = 'updated_at = NOW()';
        }

        if ($reference !== null && $reference !== '') {
            if ($this->hasColumn('payment_reference')) {
                $set[] = 'payment_reference = ?';
                $params[] = $reference;
            } elseif ($this->hasColumn('reference_code')) {
                $set[] = 'reference_code = ?';
                $params[] = $reference;
            }
        }

        if ($notes !== null && $this->hasColumn('payment_notes')) {
            $set[] = 'payment_notes = ?';
            $params[] = $notes;
        }

        $params[] = $bookingId;

        if ($this->supportsRejectedStatus()) {
            $where = " WHERE booking_id = ? AND status IN ('pending','rejected')";
        } else {
            $where = " WHERE booking_id = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . $where;
        if (!$this->db->prepare($sql)->execute($params)) {
            return false;
        }

        return $this->syncBookingPaymentStatus($bookingId, 'pending', $proofPath);
    }

    public function reject(int $paymentId, ?string $reason = null): bool
    {
        $payment = $this->findById($paymentId);
        if (!$payment) {
            return false;
        }

        $bookingId = (int) $payment['booking_id'];
        $reasonText = $reason ?: 'Payment rejected by admin.';

        if ($this->supportsRejectedStatus()) {
            $set = ['status = ?'];
            $params = ['rejected'];
            if ($this->hasColumn('updated_at')) {
                $set[] = 'updated_at = NOW()';
            }
            if ($this->hasColumn('payment_notes')) {
                $set[] = 'payment_notes = ?';
                $params[] = $reasonText;
            }
            $params[] = $paymentId;
            $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $set) . ' WHERE id = ?';
            if (!$this->db->prepare($sql)->execute($params)) {
                return false;
            }
            return $this->syncBookingPaymentStatus($bookingId, 'unpaid');
        }

        if ($this->hasColumn('payment_notes')) {
            $stmt = $this->db->prepare(
                'UPDATE ' . $this->table . ' SET payment_notes = CONCAT(COALESCE(payment_notes, ""), ?, updated_at = NOW() WHERE id = ?'
            );
            if ($stmt->execute([$reasonText, $paymentId])) {
                return $this->syncBookingPaymentStatus($bookingId, 'unpaid');
            }
        }

        return false;
    }

    private function syncBookingPaymentStatus(int $bookingId, string $status, ?string $proofPath = null): bool
    {
        try {
            $bookingModel = new Booking();
            return $bookingModel->syncPaymentFields($bookingId, $status, $proofPath);
        } catch (Throwable $e) {
            return true;
        }
    }
}
