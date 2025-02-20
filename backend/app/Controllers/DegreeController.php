<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Database\Config;

class DegreeController extends ResourceController
{
    protected $db;

    public function __construct()
    {
        // Get the database connection instance
        $this->db = Config::connect();
    }

    // Method to fetch all degrees
    public function getDegrees()
    {
        // Perform the database query directly in the controller
        $builder = $this->db->table('degree');
        $degrees = $builder->get()->getResult();

        if ($degrees) {
            return $this->respond(['status' => 'success', 'data' => $degrees], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No degrees found.'], 404);
        }
    }

        // Method to insert a new college
        public function insertDegree()
        {
            $input = $this->request->getJSON(true); // Get input as an associative array
    
            if (!isset($input['degree']) || empty(trim($input['degree']))) {
                return $this->respond(['status' => 'error', 'message' => 'Degree name is required.'], 400);
            }
    
            $builder = $this->db->table('degree');
    
            try {
                // Insert data into the table
                $builder->insert(['degree' => strtoupper(trim($input['degree']))]);
    
                // Get the ID of the inserted record
                $insertedId = $this->db->insertID();
                
                return $this->respond(['status' => 'success', 'message' => 'Degree added successfully.', 'degree_id' => $insertedId], 201);
            } catch (\Exception $e) {
                return $this->respond(['status' => 'error', 'message' => 'Failed to add Degree.', 'error' => $e->getMessage()], 500);
            }
        }

}
