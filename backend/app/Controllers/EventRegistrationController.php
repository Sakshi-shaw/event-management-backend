<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;
use App\Models\EventsModel;
use App\Models\EventsRegistrationModel;
use App\Models\StudentsModel;
use App\Models\DepartmentModel;


class EventRegistrationController extends ResourceController
{
    protected $eventsModel;
    protected $registrationModel;
    protected $studentsModel;
    protected $departmentsModel;


    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->registrationModel = new EventsRegistrationModel();
        $this->studentsModel = new StudentsModel();
        $this->departmentsModel = new \App\Models\DepartmentModel();  

        // Initialize other models as needed
    }

    public function getAllRegistrations()
    {
        try {
            $registrations = $this->registrationModel->getAllRegistrations();

            if (empty($registrations)) {
                return $this->respond(['status' => false, 'message' => 'No registrations found.'], 404);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Registrations fetched successfully.',
                'data' => $registrations
            ], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }

/*     public function getRegistrationsByStudent($studentId)
    {
        try {
            $registrations = $this->registrationModel->getRegistrationsByStudent($studentId);

            

            if (empty($registrations)) {
                return $this->respond(['status' => true, 'message' => 'No registrations found for this student.'], 404);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Registrations fetched successfully.',
                'data' => $registrations
            ], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    } */



    public function getRegistrationsByStudent($studentId)
{
    try {
        $registrations = $this->registrationModel->getRegistrationsByStudent($studentId);

        // Initialize the DepartmentModel
        $this->departmentsModel = new \App\Models\DepartmentModel();

        // Map the eligible_dept to department names
        foreach ($registrations as &$event) { // Use reference & to modify the array element directly
            $eligibleDeptIds = explode(',', $event['eligible_dept']); // Convert string to array
            $deptNames = [];

            // Fetch department names for all eligible_dept IDs
            if (!empty($eligibleDeptIds) && $eligibleDeptIds[0] !== "") {
                $departments = $this->departmentsModel
                    ->whereIn('id', $eligibleDeptIds)
                    ->findAll();

                // Extract department names
                foreach ($departments as $dept) {
                    $deptNames[] = $dept['dept_name'];
                }
            }

            // Join the department names with commas and assign it back to the current event
            $event['eligible_dept'] = implode(', ', $deptNames);
        }
        unset($event); // Unset reference to avoid side effects

        if (empty($registrations)) {
            return $this->respond(['status' => true, 'message' => 'No registrations found for this student.'], 404);
        }

        return $this->respond([
            'status' => true,
            'message' => 'Registrations fetched successfully.',
            'data' => $registrations
        ], 200);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
    }
}



/*     public function registerEvent()
    {
        $data = $this->request->getJSON(true);
        $studentId = $data['student_id'];
        $eventName = $data['event_name'];

        try {
            // Fetch event ID using event name
            $event = $this->eventsModel->where('event_name', $eventName)->first();
            if (!$event) {
                return $this->respond(['status' => false, 'message' => 'Event not found.'], 404);
            }

            $eventId = $event['id'];

            // Insert new registration record
            $registrationData = [
                'student_id' => $studentId,
                'event_id' => $eventId,
                'registration_date' => date('Y-m-d H:i:s')
            ];
            $this->registrationModel->insert($registrationData);

            return $this->respond([
                'status' => true,
                'message' => 'Registration successful.',
                'data' => $registrationData
            ], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    } */


/*     public function registerEvent()
    {
        $data = $this->request->getJSON(true);
        $studentId = $data['student_id'];
        $eventName = $data['event_name'];
    
        try {
            // Fetch event details using event name
            $event = $this->eventsModel->where('event_name', $eventName)->first();
            if (!$event) {
                return $this->respond(['status' => false, 'message' => 'Event not found.'], 404);
            }            
            
            $eventId = $event['id'];
            $eligibleDepts = array_map('intval', array_filter(explode(',', trim($event['eligible_dept'])))); // Trim and filter empty values
 
            // Fetch student details including dept_id from StudentsModel
            $studentData = $this->studentsModel->getStudentById($studentId);
            if (!$studentData) {
                return $this->respond(['status' => false, 'message' => 'Student not found.'], 404);
            }
    
            $deptId = (int) ($studentData['dept_id'] ?? 0); // Ensure dept_id is an integer
           
    
            // Check if student's department is eligible
            if (!in_array($deptId, $eligibleDepts,true)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'You are not eligible for this event.'
                ], 403);
            }
    
            // Insert new registration record
            $registrationData = [
                'student_id' => $studentId,
                'event_id' => $eventId,
                'registration_date' => date('Y-m-d H:i:s')
            ];
            $this->registrationModel->insert($registrationData);
    
            return $this->respond([
                'status' => true,
                'message' => 'Registration successful.',
                'data' => $registrationData
            ], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    } */




    public function registerEvent()
{
    $data = $this->request->getJSON(true);
    $studentId = $data['student_id'];
    $eventName = $data['event_name'];

    try {
        // Fetch event details using event name
        $event = $this->eventsModel->where('event_name', $eventName)->first();
        if (!$event) {
            return $this->respond(['status' => false, 'message' => 'Event not found.'], 404);
        }            
        
        $eventId = $event['id'];
        
        // Fetch student details including dept_id from StudentsModel
        $studentData = $this->studentsModel->getStudentById($studentId);
        if (!$studentData) {
            return $this->respond(['status' => false, 'message' => 'Student not found.'], 404);
        }

        // Only check department eligibility if eligible_dept is not "ALL"
        if ($event['eligible_dept'] !== "ALL") {
            $eligibleDepts = array_map('intval', array_filter(explode(',', trim($event['eligible_dept'])))); // Trim and filter empty values
            $deptId = (int) ($studentData['dept_id'] ?? 0); // Ensure dept_id is an integer
            
            // Check if student's department is eligible
            if (!in_array($deptId, $eligibleDepts, true)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'You are not eligible for this event.'
                ], 403);
            }
        }

        // Insert new registration record
        $registrationData = [
            'student_id' => $studentId,
            'event_id' => $eventId,
            'registration_date' => date('Y-m-d H:i:s')
        ];
        $this->registrationModel->insert($registrationData);

        return $this->respond([
            'status' => true,
            'message' => 'Registration successful.',
            'data' => $registrationData
        ], 200);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
    }
}
    



    // Add other methods as needed
}
