<?php

class Payment
{
    private $db;
    private $table = 'payments';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): int|false
    {
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
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET proof_image = ? WHERE booking_id = ? AND status = 'pending'"
        );

        return $stmt->execute([$relativePath, $bookingId]);
    }

    public function verify(int $paymentId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 'verified', updated_at = NOW() WHERE id = ? AND status = 'pending'"
        );

        return $stmt->execute([$paymentId]);
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
}
