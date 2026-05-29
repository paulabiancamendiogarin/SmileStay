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
                    COALESCE(pay.status, b.payment_status) AS payment_record_status,
                    b.payment_status,
                    b.payment_proof,
                    pay.proof_image AS payment_proof_image,
                    pay.id AS payment_id,
                    pay.reference_code AS payment_reference,
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
        return $this->searchAdminHistory([]);
    }

    public function searchAdminHistory(array $filters): array
    {
        $sql = "SELECT 
                b.*,
                u.id AS user_id,
                u.name AS guest_name,
                u.email AS guest_email,
                u.is_approved AS user_is_approved,
                u.qr_verified AS user_qr_verified,
                u.approved_at AS user_approved_at,
                h.hotel_name,
                h.location AS hotel_location,
                r.room_type,
                pay.status AS payment_record_status,
                pay.proof_image AS payment_proof_image,
                pay.reference_code AS payment_reference,
                pay.amount AS payment_amount,
                pay.id AS payment_id
             FROM {$this->table} b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN hotels h ON b.hotel_id = h.id
             LEFT JOIN rooms r ON b.room_id = r.id
             LEFT JOIN payments pay ON pay.booking_id = b.id
             WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (
                b.booking_reference LIKE ?
                OR u.name LIKE ?
                OR u.email LIKE ?
                OR h.hotel_name LIKE ?
            )";
            $term = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND b.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (isset($filters['user_approved']) && $filters['user_approved'] !== '') {
            $sql .= " AND u.is_approved = ?";
            $params[] = (int) $filters['user_approved'];
        }

        $sortColumn = match ($filters['sort'] ?? 'created_at') {
            'check_in' => 'b.check_in',
            'total_price' => 'b.total_price',
            'guest_name' => 'u.name',
            'hotel_name' => 'h.hotel_name',
            default => 'b.created_at',
        };

        $sortDir = strtoupper($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY {$sortColumn} {$sortDir}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAdminHistoryPaged(array $filters, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = " WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (
                b.booking_reference LIKE ?
                OR u.name LIKE ?
                OR u.email LIKE ?
                OR h.hotel_name LIKE ?
            )";
            $term = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($filters['status'])) {
            $where .= " AND b.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $where .= " AND b.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (isset($filters['user_approved']) && $filters['user_approved'] !== '') {
            $where .= " AND u.is_approved = ?";
            $params[] = (int) $filters['user_approved'];
        }

        $from = " FROM {$this->table} b
                  LEFT JOIN users u ON b.user_id = u.id
                  LEFT JOIN hotels h ON b.hotel_id = h.id
                  LEFT JOIN rooms r ON b.room_id = r.id
                  LEFT JOIN payments pay ON pay.booking_id = b.id";

        $countStmt = $this->db->prepare("SELECT COUNT(*) " . $from . $where);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sortColumn = match ($filters['sort'] ?? 'created_at') {
            'check_in' => 'b.check_in',
            'total_price' => 'b.total_price',
            'guest_name' => 'u.name',
            'hotel_name' => 'h.hotel_name',
            default => 'b.created_at',
        };
        $sortDir = strtoupper($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT 
                b.*,
                u.id AS user_id,
                u.name AS guest_name,
                u.email AS guest_email,
                u.is_approved AS user_is_approved,
                u.qr_verified AS user_qr_verified,
                u.approved_at AS user_approved_at,
                h.hotel_name,
                h.location AS hotel_location,
                r.room_type,
                pay.status AS payment_record_status,
                pay.proof_image AS payment_proof_image,
                pay.reference_code AS payment_reference,
                pay.amount AS payment_amount,
                pay.id AS payment_id
                " . $from . $where . " ORDER BY {$sortColumn} {$sortDir} LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function getReportSummary(array $filters): array
    {
        $params = [];
        $where = " WHERE 1=1";

        if (!empty($filters['from'])) {
            $where .= " AND b.created_at >= ?";
            $params[] = $filters['from'] . " 00:00:00";
        }
        if (!empty($filters['to'])) {
            $where .= " AND b.created_at <= ?";
            $params[] = $filters['to'] . " 23:59:59";
        }
        if (!empty($filters['hotel_id'])) {
            $where .= " AND b.hotel_id = ?";
            $params[] = (int) $filters['hotel_id'];
        }
        if (!empty($filters['booking_status'])) {
            $where .= " AND b.status = ?";
            $params[] = $filters['booking_status'];
        }
        if (!empty($filters['payment_status'])) {
            $where .= " AND b.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        $sql = "SELECT
                    COUNT(*) AS total_bookings,
                    COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_price ELSE 0 END), 0) AS total_revenue,
                    COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN 1 ELSE 0 END), 0) AS paid_count,
                    COALESCE(SUM(CASE WHEN b.payment_status <> 'paid' THEN 1 ELSE 0 END), 0) AS unpaid_count
                FROM bookings b" . $where;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_bookings' => (int) ($row['total_bookings'] ?? 0),
            'total_revenue' => (float) ($row['total_revenue'] ?? 0),
            'paid_count' => (int) ($row['paid_count'] ?? 0),
            'unpaid_count' => (int) ($row['unpaid_count'] ?? 0),
        ];
    }

    public function getMostBookedHotels(array $filters, int $limit = 5): array
    {
        $params = [];
        $where = " WHERE 1=1";
        if (!empty($filters['from'])) {
            $where .= " AND b.created_at >= ?";
            $params[] = $filters['from'] . " 00:00:00";
        }
        if (!empty($filters['to'])) {
            $where .= " AND b.created_at <= ?";
            $params[] = $filters['to'] . " 23:59:59";
        }

        $sql = "SELECT h.id, h.hotel_name, COUNT(*) AS bookings_count
                FROM bookings b
                JOIN hotels h ON h.id = b.hotel_id
                " . $where . "
                GROUP BY h.id, h.hotel_name
                ORDER BY bookings_count DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $i => $val) {
            $stmt->bindValue($i + 1, $val);
        }
        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyBookings(int $year, array $filters): array
    {
        $params = [$year];
        $where = " WHERE YEAR(b.created_at) = ?";

        if (!empty($filters['hotel_id'])) {
            $where .= " AND b.hotel_id = ?";
            $params[] = (int) $filters['hotel_id'];
        }
        if (!empty($filters['booking_status'])) {
            $where .= " AND b.status = ?";
            $params[] = $filters['booking_status'];
        }
        if (!empty($filters['payment_status'])) {
            $where .= " AND b.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        $sql = "SELECT MONTH(b.created_at) AS m, COUNT(*) AS c
                FROM bookings b
                {$where}
                GROUP BY MONTH(b.created_at)
                ORDER BY m ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = array_fill(1, 12, 0);
        foreach ($rows as $r) {
            $out[(int) $r['m']] = (int) $r['c'];
        }
        return $out;
    }

    public function syncPaymentFields(int $bookingId, string $paymentStatus, ?string $proofPath = null): bool
    {
        $data = ['payment_status' => $paymentStatus];

        if ($proofPath !== null) {
            $data['payment_proof'] = $proofPath;
        }

        return $this->update($bookingId, $data);
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

        $allowed = [
            'check_in', 'check_out', 'guests', 'special_requests', 'status',
            'payment_status', 'payment_proof', 'total_price',
        ];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        if (empty($fields)) {
            return false;
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