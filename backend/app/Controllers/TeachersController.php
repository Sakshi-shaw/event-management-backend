<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class TeachersController extends ResourceController
{
    protected $TeachersModel;

    public function __construct()
    {
        $this->teachersModel = new \App\Models\TeachersModel(); // Load the StudentModel
    }

    // Method to fetch all student details
    
    public function getTeacherById($id)
    {
        try {
            $teacher = $this->teachersModel->getTeacherById($id);

            if (empty($teacher)) {
                return $this->respond(['status' => false, 'message' => 'teacher not found.'], 404);
            }

            return $this->respond(['status' => true, 'data' => $teacher], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function updateData( )
{
    $data = $this->request->getJSON(true);
    $id = $data['id'];
    // If $id is not provided in the URL, check the JSON data for the ID
    if ($id === null) {
        //$data = $this->request->getJSON(true);
        if (empty($data['id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Teacher ID is required']);
        }
        $id = $data['id']; // Assign the ID from the data
    }

    // Call the model method to update student data
    $result = $this->teachersModel->updateTeacher($data);

    // Handle the result based on the update status
     if ($result === false) {
         return $this->response->setJSON(['success' => false, 'message' => 'Teacher update failed or Teacherz not found']);
     }

   return $this->response->setJSON(['success' => true, 'message' => 'Teacher updated successfully'],200);
   //return $this->result;
}



}
