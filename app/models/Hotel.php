

<?php

class Hotel {
    private $db;
    private $table = 'hotels';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function getUploadedHotelImages(): array
    {
        static $files = null;
        if ($files !== null) {
            return $files;
        }

        $dir = PUBLIC_PATH . '/uploads/hotels/';
        if (!is_dir($dir)) {
            $files = [];
            return $files;
        }

        $matched = glob($dir . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
        $files = [];
        foreach ($matched ?: [] as $path) {
            $files[] = basename($path);
        }

        return $files;
    }

    private function normalizeKey(string $value): string
    {
        $value = strtolower($value);
        return preg_replace('/[^a-z0-9]+/', '', $value);
    }

    private function normalizeConsonants(string $value): string
    {
        $key = $this->normalizeKey($value);
        return preg_replace('/[aeiou]/', '', $key);
    }

    private function resolveImageFilename(?string $image, string $hotelName = ''): string
    {
        $fallback = 'westown.jpg';
        $value = trim((string) $image);
        $files = $this->getUploadedHotelImages();

        if (empty($files)) {
            return $fallback;
        }

        if ($value === '') {
            $value = $hotelName;
        }

        $value = str_replace('\\', '/', $value);
        $value = basename($value);
        $exact = $this->normalizeKey(pathinfo($value, PATHINFO_FILENAME));

        // 1) exact filename match
        if (in_array($value, $files, true)) {
            return $value;
        }

        // 2) exact stem/normalized match
        foreach ($files as $file) {
            $stem = pathinfo($file, PATHINFO_FILENAME);
            if ($exact !== '' && $this->normalizeKey($stem) === $exact) {
                return $file;
            }
        }

        // 3) typo-friendly consonant match, e.g. lfsher -> lfisher
        $consonants = $this->normalizeConsonants(pathinfo($value, PATHINFO_FILENAME));
        foreach ($files as $file) {
            $stemConsonants = $this->normalizeConsonants(pathinfo($file, PATHINFO_FILENAME));
            if ($consonants !== '' && $stemConsonants === $consonants) {
                return $file;
            }
        }

        // 4) close levenshtein match by stem
        $best = null;
        $bestScore = PHP_INT_MAX;
        foreach ($files as $file) {
            $stem = $this->normalizeKey(pathinfo($file, PATHINFO_FILENAME));
            if ($stem === '') {
                continue;
            }
            $score = levenshtein($exact, $stem);
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $file;
            }
        }
        if ($best !== null && $bestScore <= 3) {
            return $best;
        }

        if (in_array($fallback, $files, true)) {
            return $fallback;
        }

        return $files[0];
    }

    private function hydrateImageRow($row) {
        if (!$row) {
            return $row;
        }

        $row['image'] = $this->resolveImageFilename($row['image'] ?? '', (string) ($row['hotel_name'] ?? ''));
        return $row;
    }

    private function hydrateImageRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $row = $this->hydrateImageRow($row);
        }
        unset($row);

        return $rows;
    }

    
    public function getAll() {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY rating DESC, hotel_name ASC"
        );
        return $this->hydrateImageRows($stmt->fetchAll());
    }

    
    public function getAllAdmin() {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC"
        );
        return $this->hydrateImageRows($stmt->fetchAll());
    }

  
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $this->hydrateImageRow($stmt->fetch());
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
        return $this->hydrateImageRows($stmt->fetchAll());
    }

   
    public function getByPriceRange($minPrice, $maxPrice) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE status = 'active' 
             AND price_per_night BETWEEN ? AND ?
             ORDER BY price_per_night ASC"
        );
        $stmt->execute([$minPrice, $maxPrice]);
        return $this->hydrateImageRows($stmt->fetchAll());
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
        return $this->hydrateImageRows($stmt->fetchAll());
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
        return $this->hydrateImageRows($stmt->fetchAll());
    }
}
