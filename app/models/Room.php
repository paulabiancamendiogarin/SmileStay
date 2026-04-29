<?php

class Room {
    private $db;
    private $table = 'rooms';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

  

    // Get all rooms
    public function getAll() {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY id DESC"
        );
        return $stmt->fetchAll();
    }


    public function getByHotelId($hotelId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE hotel_id = ? 
             ORDER BY price ASC"
        );
        $stmt->execute([$hotelId]);
        return $stmt->fetchAll();
    }

    
    public function getAvailableByHotelId($hotelId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE hotel_id = ? AND available > 0 
             ORDER BY price ASC"
        );
        $stmt->execute([$hotelId]);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }


    public function findWithHotel($id) {
        $stmt = $this->db->prepare(
            "SELECT r.*, h.hotel_name, h.location, h.image as hotel_image 
             FROM {$this->table} r 
             JOIN hotels h ON r.hotel_id = h.id 
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    
    public function getAllWithHotel() {
        $stmt = $this->db->query(
            "SELECT r.*, h.hotel_name 
             FROM {$this->table} r 
             JOIN hotels h ON r.hotel_id = h.id 
             ORDER BY h.hotel_name, r.room_type"
        );
        return $stmt->fetchAll();
    }

  

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (hotel_id, room_type, capacity, price, description, available) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['hotel_id'],
            $data['room_type'],
            $data['capacity'],
            $data['price'],
            $data['description'] ?? '',
            $data['available'] ?? 1
        ]);

        return $this->db->lastInsertId();
    }


    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET hotel_id = ?, 
                 room_type = ?, 
                 capacity = ?, 
                 price = ?, 
                 description = ?, 
                 available = ?
             WHERE id = ?"
        );

        return $stmt->execute([
            $data['hotel_id'],
            $data['room_type'],
            $data['capacity'],
            $data['price'],
            $data['description'] ?? '',
            $data['available'] ?? 1,
            $id
        ]);
    }


    public function delete($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

   

    public function decreaseAvailability($id) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET available = available - 1 
             WHERE id = ? AND available > 0"
        );
        return $stmt->execute([$id]);
    }

    public function increaseAvailability($id) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET available = available + 1 
             WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    

    public function isAvailableForDates($roomId, $checkIn, $checkOut) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM bookings 
             WHERE room_id = ? 
             AND status NOT IN ('cancelled') 
             AND (
                 (check_in <= ? AND check_out > ?) 
                 OR (check_in < ? AND check_out >= ?) 
                 OR (check_in >= ? AND check_out <= ?)
             )"
        );

        $stmt->execute([
            $roomId,
            $checkIn, $checkIn,
            $checkOut, $checkOut,
            $checkIn, $checkOut
        ]);

        $count = $stmt->fetchColumn();
        $room = $this->findById($roomId);

        return $count < $room['available'];
    }
}