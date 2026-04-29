

<?php

class Hotel {
    private $db;
    private $table = 'hotels';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    
    public function getAll() {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY rating DESC, hotel_name ASC"
        );
        return $stmt->fetchAll();
    }

    
    public function getAllAdmin() {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
    }

  
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search($keyword) {
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE status = 'active' 
             AND (hotel_name LIKE ? OR location LIKE ? OR description LIKE ?)
             ORDER BY rating DESC"
        );
        $stmt->execute([$keyword, $keyword, $keyword]);
        return $stmt->fetchAll();
    }

   
    public function getByPriceRange($minPrice, $maxPrice) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE status = 'active' 
             AND price_per_night BETWEEN ? AND ?
             ORDER BY price_per_night ASC"
        );
        $stmt->execute([$minPrice, $maxPrice]);
        return $stmt->fetchAll();
    }

    
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
             (hotel_name, location, latitude, longitude, description, price_per_night, image, amenities, rating, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->execute([
            $data['hotel_name'],
            $data['location'],
            $data['latitude'],
            $data['longitude'],
            $data['description'],
            $data['price_per_night'],
            $data['image'] ?? 'default_hotel.jpg',
            $data['amenities'] ?? '',
            $data['rating'] ?? 0,
            $data['status'] ?? 'active'
        ]);
        
        return $this->db->lastInsertId();
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

    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }


    public function getFeatured($limit = 6) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE status = 'active' 
             ORDER BY rating DESC 
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        return $stmt->fetchColumn();
    }

    
    public function getForMap() {
        $stmt = $this->db->query(
            "SELECT id, hotel_name, location, latitude, longitude, price_per_night, image, rating 
             FROM {$this->table} 
             WHERE status = 'active'"
        );
        return $stmt->fetchAll();
    }
}
