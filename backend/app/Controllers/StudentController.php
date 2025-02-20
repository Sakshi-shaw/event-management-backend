<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class StudentController extends ResourceController
{
    protected $studentModel;

    public function __construct()
    {
        $this->studentModel = new \App\Models\StudentsModel(); // Load the StudentModel
    }

    // Method to fetch all student details
    public function index()
    {
        try {
            $students = $this->studentModel->getAllStudents();

            if (empty($students)) {
                return $this->respond(['status' => false, 'message' => 'No students found.'], 404);
            }

            return $this->respond(['status' => true, 'data' => $students], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStudentById($id)
    {
        try {
            $student = $this->studentModel->getStudentById($id);

            if (empty($student)) {
                return $this->respond(['status' => false, 'message' => 'Student not found.'], 404);
            }

            return $this->respond(['status' => true, 'data' => $student], 200);
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
            return $this->response->setJSON(['success' => false, 'message' => 'Student ID is required']);
        }
        $id = $data['id']; // Assign the ID from the data
    }

    // Call the model method to update student data
    $result = $this->studentModel->updateStudent($data);

    // Handle the result based on the update status
     if ($result === false) {
         return $this->response->setJSON(['success' => false, 'message' => 'Student update failed or student not found']);
     }

   return $this->response->setJSON(['success' => true, 'message' => 'Student updated successfully'],200);
   //return $this->result;
}



    // Method to fetch events details
    public function getEvents()
    {
        try {
            $events = $this->studentModel->getEvents(); // Call the model's method

            if (empty($events)) {
                return $this->respond(['status' => false, 'message' => 'No events found.'], 404);
            }

            return $this->respond(['status' => true, 'data' => $events], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


/*     public function subscribe()
    {
        // Get the email from the POST request
        $email = $this->request->getJSON('email');

        // Validate the email input
            // $studentModel = new StudentsModel();

            // Call the model function to update the subscribe column
            $result = $this->studentModel->subscribeByEmail($email);

            if ($result) {
                return $this->response->setJSON(['success'=>'true','message' => 'Subscription successful.']);
            } else {
                return $this->response->setJSON(['success'=>'false','message' => 'Email not found or already subscribed.']);
            }
    
    } */

    public function subscribe()
{
    // Get the email from the POST request
    $email = $this->request->getJSON('email');

    // Call the model function to update the subscribe column and send email
    $result = $this->studentModel->subscribeByEmail($email);

    if ($result) {
        return $this->response->setJSON(['success' => 'true', 'message' => 'Subscription successful and email sent.']);
    } else {
        return $this->response->setJSON(['success' => 'false', 'message' => 'Email not found, already subscribed, or email sending failed.']);
    }
}

 // Forget Password: Check email existence
 public function forgetPassword()
 {
     $email = $this->request->getJSON('email');

     if (!$email) {
         return $this->response->setJSON(['status' => 'error', 'message' => 'Email is required'])->setStatusCode(400);
     }

     $student = $this->studentModel->isEmailExist($email);

     if ($student) {
         return $this->response->setJSON(['status' => 'success', 'message' => 'Email exists, proceed to reset password'])->setStatusCode(200);
     } else {
         return $this->response->setJSON(['status' => 'error', 'message' => 'Email not found'])->setStatusCode(404);
     }
 }

 public function resetPassword()
 {
     $input = $this->request->getJSON();
  
     $email = $input->email ?? null;
     $newPassword = $input->newPassword ?? null;
 
 
     if (!$email || !$newPassword) {
         return $this->response->setJSON(['status' => 'error', 'message' => 'Email and password are required'])->setStatusCode(400);
     }
 
     // Hash and update password
     $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
     $updated = $this->studentModel->updatePassword($email, $hashedPassword);
 
     if ($updated) {
         return $this->response->setJSON(['status' => 'success', 'message' => 'Password updated successfully'])->setStatusCode(200);
     } else {
         return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update password'])->setStatusCode(500);
     }
 }
 



}
