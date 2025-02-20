<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Database\Exceptions\DataException;

class RoomsController extends ResourceController
{
    protected $db;
    protected $table = 'rooms'; // Name of the database table

    public function __construct()
    {
        $this->db = \Config\Database::connect(); // Connect to the database
    }

    // Fetch all rooms
    public function index()
    {
        try {
            $query = $this->db->table($this->table)->get();
            $rooms = $query->getResultArray();

            if (empty($rooms)) {
                return $this->respond(['status' => false, 'message' => 'No rooms found.'], 404);
            }

            return $this->respond(['status' => true, 'data' => $rooms], 200);
        } catch (DataException $e) {
            return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
