

<?php

class HotelController {
    private $hotelModel;
    private $roomModel;

    public function __construct() {
        $this->hotelModel = new Hotel();
        $this->roomModel = new Room();
    }

   
    public function home() {
        
        $featuredHotels = $this->hotelModel->getFeatured(6);
        $totalHotels = $this->hotelModel->getCount();
        
        include APP_PATH . '/views/home.php';
    }

   
    public function index() {
     
        $search = sanitize($_GET['search'] ?? '');
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;

        
        if (!empty($search)) {
            $hotels = $this->hotelModel->search($search);
        } elseif ($minPrice && $maxPrice) {
            $hotels = $this->hotelModel->getByPriceRange($minPrice, $maxPrice);
        } else {
            $hotels = $this->hotelModel->getAll();
        }

        include APP_PATH . '/views/hotels/index.php';
    }

    public function show($id = null) {
       
        $hotelId = $id ?? ($_GET['id'] ?? null);

        if (!$hotelId) {
            redirect('/hotels');
        }

        $hotel = $this->hotelModel->findById($hotelId);

        if (!$hotel) {
            setFlashMessage('error', 'Hotel not found.');
            redirect('/hotels');
        }

       
        $rooms = $this->roomModel->getAvailableByHotelId($hotelId);

        $amenities = !empty($hotel['amenities']) ? explode(',', $hotel['amenities']) : [];

        include APP_PATH . '/views/hotels/details.php';
    }

    public function search() {
        $keyword = sanitize($_GET['q'] ?? '');
        
        if (empty($keyword)) {
            $hotels = $this->hotelModel->getAll();
        } else {
            $hotels = $this->hotelModel->search($keyword);
        }

     
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($hotels);
            exit;
        }

        $search = $keyword;
        include APP_PATH . '/views/hotels/index.php';
    }

    public function map() {
        $hotels = $this->hotelModel->getForMap();
        $hotelsJson = json_encode($hotels);
        
        include APP_PATH . '/views/hotels/map.php';
    }
}
