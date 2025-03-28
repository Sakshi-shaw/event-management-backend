<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;
use App\Models\EventsModel;
use App\Models\EventsRegistrationModel;
use App\Models\StudentsModel;
use App\Models\DepartmentModel;
use App\Models\RoomsModel;

class EventRegistrationController extends ResourceController
{
    protected $db;
    protected $eventsModel;
    protected $registrationModel;
    protected $studentsModel;
    protected $departmentsModel;
    protected $roomsModel;


    public function __construct()
    {
        $this->db = \Config\Database::connect(); // Connect to the database
        $this->eventsModel = new EventsModel();
        $this->registrationModel = new EventsRegistrationModel();
        $this->studentsModel = new StudentsModel();
        $this->departmentsModel = new \App\Models\DepartmentModel();
        $this->roomsModel = new RoomsModel();  

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



/* public function registerEvent()
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
} */






public function registerEvent()
{
    $data = $this->request->getJSON(true);
    $emails = $data['email']; // Array of emails
    
    if (empty($emails) || !is_array($emails)) {
        return $this->respond(['success' => false, 'message' => 'Invalid email input.'], 400);
    }

    $this->db->transStart(); // Start Transaction

    try {
        // Fetch student IDs from emails
        $students = $this->studentsModel->whereIn('email', $emails)->findAll();
        if (count($students) !== count($emails)) {
            return $this->respond(['success' => false, 'message' => 'One or more students doesnot have account.'], 404);
        }
        //log_message('info', 'student ID: ' . json_encode($students) );
        
        $studentIds = array_column($students, 'id');
        
        // Fetch event details using event name with row locking
        $event = $this->db->query('SELECT * FROM events WHERE event_name = ? FOR UPDATE', [$data['event_name']])->getRowArray();
        if (!$event) {
            return $this->respond(['success' => false, 'message' => 'Event not found.'], 404);
        }

        $eventId = $event['id'];
        $roomId = $event['room_id'];
        $currentRegistrationCount = (int) $event['registration_count'];
        $groupParticipation = (int) $event['group_participation'];
        $minParticipation = (int) $event['min_participation'];
        $maxParticipation = (int) $event['max_participation'];

        // Fetch room details
        $room = $this->roomsModel->where('id', $roomId)->first();
        if (!$room) {
            return $this->respond(['success' => false, 'message' => 'Room not found.'], 404);
        }
        
        $maxLimit = (int) $room['max_limit'];
        if (($currentRegistrationCount + count($studentIds)) > $maxLimit) {
            return $this->respond(['success' => false, 'message' => 'Registration full. Capacity reached.'], 403);
        }
        
        // Check if each student is already registered
        $existingRegistrations = $this->registrationModel->whereIn('student_id', $studentIds)->where('event_id', $eventId)->findAll();
        if (!empty($existingRegistrations)) {
            return $this->respond(['success' => false, 'message' => 'One or more students are already registered.'], 409);
        }
        
        // Check eligibility for each student
        if ($event['eligible_dept'] !== "ALL") {
            $eligibleDepts = array_map('trim', explode(',', trim($event['eligible_dept'])));
            foreach ($students as $student) {
                if (!in_array(trim((string) $student['dept_id']), $eligibleDepts, true)) {
                    return $this->respond(['success' => false, 'message' => 'One or more students are not eligible for this event.'], 403);
                }
            }
        }
        
        // Validate participation count
        $numStudents = count($studentIds);
        if ($groupParticipation == 0) { // Individual Event
            if ($numStudents !== $minParticipation || $numStudents !== $maxParticipation) {
                return $this->respond(['success' => false, 'message' => 'Invalid number of participants for this event.'], 400);
            }
        } else { // Group Event
            if ($numStudents < $minParticipation || $numStudents > $maxParticipation) {
                return $this->respond(['success' => false, 'message' => "Group size must be between $minParticipation and $maxParticipation."], 400);
            }
        }
        
        // Insert each student into registration table
        foreach ($studentIds as $studentId) {
            $this->registrationModel->insert([
                'student_id' => $studentId,
                'event_id' => $eventId,
                'registration_date' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Update registration count
        $this->db->query('UPDATE events SET registration_count = registration_count + ? WHERE id = ?', [count($studentIds), $eventId]);
        
        $this->db->transComplete(); // Commit Transaction

        return $this->respond(['success' => true, 'message' => 'Registration successful.'], 200);
    } catch (\Exception $e) {
        $this->db->transRollback(); // Rollback on error
        return $this->respond(['success' => false, 'message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
    }
}



    



    // Add other methods as needed
}
