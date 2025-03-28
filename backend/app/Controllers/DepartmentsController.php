<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Database\Config;

class DepartmentsController extends ResourceController
{
    protected $db;

    public function __construct()
    {
        // Get the database connection instance
        $this->db = Config::connect();
    }

    // Method to fetch all departments
    public function getDepartments()
    {
        // Perform the database query directly in the controller
        $builder = $this->db->table('departments');
        $departments = $builder->get()->getResult();
 
        if ($departments) {
            return $this->respond(['status' => 'success', 'data' => $departments], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No departments found.'], 404);
        }
    }

    // Method to fetch departments matching a degree using degree name
    public function getDepartmentsByDegree()
    {
        // Get the degree name from the POST body
        $degree_name = $this->request->getJSON(true);  // Using POST request to get degree name

        if (!$degree_name) {
            return $this->respond(['status' => 'error', 'message' => 'Degree name is required.'], 400);
        }

        // Fetch the degree_id from the degree table using the degree name
        $builder = $this->db->table('degree');
        $builder->select('id');
        $builder->where('degree', $degree_name);  // Assuming 'name' is the column in degree table
        $degree = $builder->get()->getRow();

        if (!$degree) {
            return $this->respond(['status' => 'error', 'message' => 'Degree not found.'], 404);
        }

        // Now use the fetched degree_id to get departments
        $degree_id = $degree->id;
        $builder = $this->db->table('departments');
        $builder->where('degree_id', $degree_id);  // Assuming degree_id is the foreign key in departments table
        $departments = $builder->get()->getResult();

        if ($departments) {
            return $this->respond(['status' => 'success', 'data' => $departments], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No departments found for this degree.'], 404);
        }
    }

    public function insertDepartment()
    {
        $input = $this->request->getJSON(true); // Get input as an associative array

        if (!isset($input['dept_name']) || empty(trim($input['dept_name']))) {
            return $this->respond(['status' => 'error', 'message' => 'deptName  is required.'], 400);
        }

        $builder = $this->db->table('departments');

        try {
            // Insert data into the table
            $deptName = strtoupper(trim($input['dept_name']));
            $builder->insert(['dept_name' => $deptName]);

            return $this->respond(['status' => 'success', 'message' => 'Department added successfully.', 'dept_name' => $deptName], 201);
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to add department.', 'error' => $e->getMessage()], 500);
        }
    }

}
