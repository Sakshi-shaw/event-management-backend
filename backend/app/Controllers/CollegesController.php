<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Database\Config;

class CollegesController extends ResourceController
{
    protected $db;

    public function __construct()
    {
        // Get the database connection instance
        $this->db = Config::connect();
    }

    // Method to fetch all colleges
    public function getColleges()
    {
        // Perform the database query directly in the controller
        $builder = $this->db->table('colleges');
        $colleges = $builder->get()->getResult();

        if ($colleges) {
            return $this->respond(['status' => 'success', 'data' => $colleges], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No colleges found.'], 404);
        }
    }

    // Method to insert a new college
    public function insertCollege()
    {
        $input = $this->request->getJSON(true); // Get input as an associative array

        if (!isset($input['college']) || empty(trim($input['college']))) {
            return $this->respond(['status' => 'error', 'message' => 'College name is required.'], 400);
        }

        $builder = $this->db->table('colleges');

        try {
            // Insert data into the table
            $builder->insert(['college' => strtoupper(trim($input['college']))]);

            // Get the ID of the inserted record
            $insertedId = $this->db->insertID();
            
            return $this->respond(['status' => 'success', 'message' => 'College added successfully.', 'college_id' => $insertedId], 201);
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to add college.', 'error' => $e->getMessage()], 500);
        }
    }
}
