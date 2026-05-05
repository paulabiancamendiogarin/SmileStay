<?php

class Booking {
    private $db;
    private $table = 'bookings';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

 
    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollback() {
        $this->db->rollBack();
    }

   
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (user_id, hotel_id, room_id, check_in, check_out, total_price, guests, special_requests, status, booking_reference, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        
        $bookingRef = generateBookingReference();

        $success = $stmt->execute([
            $data['user_id'],
            $data['hotel_id'],
            $data['room_id'],
            $data['check_in'],
            $data['check_out'],
            $data['total_price'],
            $data['guests'] ?? 1,
            $data['special_requests'] ?? '',
            $data['status'] ?? 'pending',
            $bookingRef
        ]);

        if (!$success) {
            return false;
        }

        return [
            'id' => $this->db->lastInsertId(),
            'reference' => $bookingRef
        ];
    }

   
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByReference($reference) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE booking_reference = ?");
        $stmt->execute([$reference]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getWithDetails($id) {
        $stmt = $this->db->prepare(
            "SELECT b.*, 
                    h.hotel_name, h.location as hotel_location, h.image as hotel_image,
                    r.room_type, r.capacity,
                    u.name as guest_name, u.email as guest_email
             FROM {$this->table} b
             JOIN hotels h ON b.hotel_id = h.id
             JOIN rooms r ON b.room_id = r.id
             JOIN users u ON b.user_id = u.id
             WHERE b.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare(
            "SELECT b.*, 
                    h.hotel_name,
                    r.room_type,
                    pay.status AS payment_status,
                    pay.expires_at AS payment_expires_at
             FROM {$this->table} b
             JOIN hotels h ON b.hotel_id = h.id
             JOIN rooms r ON b.room_id = r.id
             LEFT JOIN payments pay ON pay.booking_id = b.id
             WHERE b.user_id = ?
             ORDER BY b.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->db->query(
            "SELECT 
                b.*,
                u.name AS guest_name,
                u.email AS guest_email,
                h.hotel_name,
                r.room_type
             FROM {$this->table} b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN hotels h ON b.hotel_id = h.id
             LEFT JOIN rooms r ON b.room_id = r.id
             ORDER BY b.created_at DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

 
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?"
        );

        return $stmt->execute($values);
    }

    public function cancel($id) {
        return $this->updateStatus($id, 'cancelled');
    }

   
   public function delete($id) {
    $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
    return $stmt->execute([$id]);
}

  
    public function getCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    public function getPendingCount() {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM {$this->table} WHERE status = 'pending'"
        );
        return $stmt->fetchColumn();
    }

    public function getRecent($limit = 5) {
        $stmt = $this->db->prepare(
            "SELECT b.*, 
                    h.hotel_name,
                    u.name as guest_name
             FROM {$this->table} b
             JOIN hotels h ON b.hotel_id = h.id
             JOIN users u ON b.user_id = u.id
             ORDER BY b.created_at DESC
             LIMIT ?"
        );

        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalRevenue() {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(total_price), 0) 
             FROM {$this->table} 
             WHERE status IN ('confirmed', 'completed')"
        );
        return $stmt->fetchColumn();
    }

    /** Auto-cancel pending bookings whose payment window expired without verified payment */
    public function expireUnpaidBookings(): void
    {
        $sql = "UPDATE bookings b
                INNER JOIN payments p ON p.booking_id = b.id
                SET b.status = 'cancelled'
                WHERE b.status = 'pending'
                  AND p.status = 'pending'
                  AND p.expires_at < NOW()";
        $this->db->exec($sql);
    }
}