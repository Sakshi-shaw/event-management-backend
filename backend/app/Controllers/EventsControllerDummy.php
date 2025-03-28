

<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EventsController extends ResourceController
{
    protected $eventsModel;
    protected $teachersModel;
    protected $roomsModel;
    protected $departmentsModel;
    protected $eventTypesModel;
    protected $studentsModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->eventsModel = new \App\Models\EventsModel();   // Load the EventsModel
        $this->teachersModel = new \App\Models\TeachersModel(); // Load the TeachersModel
        $this->roomsModel = new \App\Models\RoomsModel();     // Load the RoomsModel
        $this->departmentsModel = new \App\Models\DepartmentModel();  
        $this->eventTypesModel = new \App\Models\EventTypeModel(); // Load the EventTypesModel
        $this->eventLevelsModel = new \App\Models\EventLevelsModel();
        $this->studentsModel = new \App\Models\StudentsModel();
        $this->notificationModel = new \App\Models\NotificationModel();
    }


    /**
     * Fetch all events.
     */
    public function index()
    {
        try {
            $events = $this->eventsModel->getEvents(); // Call the model's method

            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available venues based on input filters.
     */
    public function getAvailableVenues()
    {
        try {
            // Get input data from the request
            $input = $this->request->getJSON(true); // Accept JSON payload
    
            // Validate input data
            if (
                empty($input['start_date']) ||
                empty($input['end_date']) ||
                empty($input['start_time']) ||
                empty($input['end_time']) ||
                empty($input['room_type'])
            ) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'All filters are required!',
                ], 400); // HTTP 400 Bad Request
            }
    
            // Prepare filters for the model
            $filters = [
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'start_time' => $input['start_time'],
                'end_time' => $input['end_time'],
                'room_type' => $input['room_type'],
            ];
    
            // Call the model method to get available venues
            $availableVenues = $this->eventsModel->getAvailableVenues($filters);
    
            // Check if any venues are available
            if (empty($availableVenues)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No venues available for the given filters.'
                ], 404); // HTTP 404 Not Found
            }
    
            // Return the result as JSON
            return $this->respond([
                'status' => 'success',
                'data' => $availableVenues,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }
    

/*     public function create()
{
    try {
        $input = $this->request->getJSON(true);

        // Log event type to check if it's being passed correctly
        log_message('debug', 'Event Type: ' . $input['eventType']);

        // Fetch teacher details
        $teacher = $this->teachersModel->find($input['teacher_id']);
        if (!$teacher) {
            return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
        }
        $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];

        // Validate room_no and fetch room_id
        $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
        if (!$room) {
            return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
        }
        $roomId = $room['id']; // Extract room ID

        // Validate dept_name and fetch dept_id
        $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
        if (!$department) {
            return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
        }
        $deptId = $department['id']; // Extract department ID

        // Fetch event type and validate
        $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
        if (!$eventType) {
            return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
        }
        $eventTypeId = $eventType['id']; // Extract eventType ID

        $eventLevel = $this->eventLevelsModel->where('event_level',$input['event_level'])->first();
        //log_message('debug', 'Event Level Retrieved: ' . print_r($eventLevel, true));
        if (!$eventLevel) {
            return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
        }
        $eventLevelId = $eventLevel['id']; // Extract eventType ID
        //log_message('debug', 'eventLevelId ID: ' . $eventLevelId);

        // Log eventTypeId to check if it's correct
        //log_message('debug', 'Event Type ID: ' . $eventTypeId);

        // Prepare event data
        $eventData = [
            'teacher_id'   => $input['teacher_id'],
            'room_id'      => $roomId, // Use resolved room_id
            'event_name'   => $input['event_name'],
            'description'  => $input['description'],
            'dept'         => $deptId, // Use resolved department ID
            'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
            'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
            'teacher_name' => $teacherFullName,
            'eventType_id' => $eventTypeId, // Store eventType_id here
            'event_level_id' => $eventLevelId // Store eventType_id here
        ];

        // Log the event data to check before insertion
        //log_message('debug', 'Event Data: ' . print_r($eventData, true));

        // Insert event into database
        $eventId = $this->eventsModel->insertEvent($eventData);

        if ($eventId) {
            return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 201);
        }

        return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

 */








 /* public function create()
 {
     try {
         $input = $this->request->getJSON(true);
 
         // Log event type to check if it's being passed correctly
         //log_message('debug', 'Event Type: ' . $input['eventType']);
 
         // Fetch teacher details
         $teacher = $this->teachersModel->find($input['teacher_id']);
         if (!$teacher) {
             return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
         }
         $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
 
         // Validate room_no and fetch room_id
         $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
         if (!$room) {
             return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
         }
         $roomId = $room['id']; // Extract room ID
 
         // Validate dept_name and fetch dept_id
         $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
         if (!$department) {
             return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
         }
         $deptId = $department['id']; // Extract department ID
 
         // Fetch event type and validate
         $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
         if (!$eventType) {
             return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
         }
         $eventTypeId = $eventType['id']; // Extract eventType ID
 
         $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
         if (!$eventLevel) {
             return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
         }
         $eventLevelId = $eventLevel['id']; // Extract eventLevel ID
 
         // Prepare event data
         $eventData = [
             'teacher_id'   => $input['teacher_id'],
             'room_id'      => $roomId,
             'event_name'   => $input['event_name'],
             'description'  => $input['description'],
             'dept'         => $deptId,
             'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
             'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
             'eventType_id' => $eventTypeId,
             'event_level_id' => $eventLevelId
         ];
 
         // Insert event into the database
         $eventId = $this->eventsModel->insertEvent($eventData);
 
         if ($eventId) {
             // Check if event level is 'National' (event_level_id = 1)
             if ($eventLevelId == 1) {
                 // Fetch students with subscribe = 1
                 $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();
 
                 // Send email to each subscribed student
                 foreach ($subscribedStudents as $student) {
                     $this->sendEmailToStudent($student['email'], $eventData);
                 }
             }
 
             return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 201);
         }
 
         return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
     } catch (\Exception $e) {
         return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
     }
 }

  */


///////////////////////////////********************************* correct code bellow one */

//   public function create()
//   {
//       try {
//           $input = $this->request->getJSON(true);
          
//         // Check for duplicate event
//         $existingEvent = $this->eventsModel
//             ->where('event_name', $input['event_name'])
//             ->where('event_status', 1) // Check if event_status is 1
//             ->first();

//         if ($existingEvent) {
//             return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
//         }
//           // Fetch teacher details
//           $teacher = $this->teachersModel->find($input['teacher_id']);
//           if (!$teacher) {
//               return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
//           }
//           $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
//           $teacherEmail = $teacher['email']; // Fetch teacher's email
  
//           // Validate room_no and fetch room_id and room_owner
//           $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
//           if (!$room) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
//           }
//           $roomId = $room['id']; // Extract room ID
//           $roomOwnerId = $room['room_owner']; // Extract room owner ID
  
//           // Fetch room_owner's email
//           $roomOwner = $this->teachersModel->find($roomOwnerId);
//           if (!$roomOwner) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
//           }
//           $roomOwnerEmail = $roomOwner['email']; // Fetch room owner's email
  
//           // Validate dept_name and fetch dept_id
//           $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
//           if (!$department) {
//               return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
//           }
//           $deptId = $department['id']; // Extract department ID
  
//           // Fetch event type and validate
//           $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
//           if (!$eventType) {
//               return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
//           }
//           $eventTypeId = $eventType['id']; // Extract eventType ID
  
//           // Fetch event level and validate
//           $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
//           if (!$eventLevel) {
//               return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
//           }
//           $eventLevelId = $eventLevel['id']; // Extract eventLevel ID
  
//           // Prepare event data
//           $eventData = [
//               'teacher_id'   => $input['teacher_id'],
//               'room_id'      => $roomId,
//               'event_name'   => $input['event_name'],
//               'description'  => $input['description'],
//               'dept'         => $deptId,
//               'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
//               'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
//               'teacher_name' => $teacherFullName,
//               'eventType_id' => $eventTypeId,
//               'event_level_id' => $eventLevelId,
//               'eligible_dept'=>$input['eligibleDepartments'],
//           ];
  
//           // Insert event into the database
//           $eventId = $this->eventsModel->insertEvent($eventData);

  
//           if ($eventId) {
//             // Check if event level is 'National' (event_level_id = 1)
//             if ($eventLevelId == 1) {
//                 // Fetch students with subscribe = 1
//                 $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

//                 // Send email to each subscribed student
//                 foreach ($subscribedStudents as $student) {
//                     $this->sendEmailToStudent($student['email'], $eventData);
//                 }
//             }

//               // Send email from teacher to room owner
//              //$this->sendEmailFromTeacherToRoomOwner($teacherEmail, $roomOwnerEmail, $eventData,$teacherFullName);

//               // Check if event_status is 0, then create a notification
//               $event = $this->eventsModel->find($eventId);

//         // Check if event_status is 0, then create a notification
//             if ($event && $event['event_status'] == 0) {
//                 $notificationData = [
//                     'requested_by'         => $input['teacher_id'],
//                     'event_id'             => $eventId,
//                     'created_at'           => date('Y-m-d H:i:s'),
                    
//                 ];

//                 // Insert notification
//                 if (!$this->notificationModel->insert($notificationData)) {
//                     return $this->failServerError('Event created, but failed to create notification.');
//                 }
//             }
  
//               return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
//           }
  
//           return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
//       } catch (\Exception $e) {
//           return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
//       }
//   }
  
//   /**
//    * Sends an email from the teacher to the room owner.
//    *
//    * @param string $fromEmail
//    * @param string $toEmail
//    * @param array $eventData
//    * @return void
//    */
//   private function sendEmailFromTeacherToRoomOwner($fromEmail, $toEmail, $eventData)
//   {
//       $mail = new PHPMailer(true);
  
//       try {
//           // SMTP configuration
//           $mail->isSMTP();
//           $mail->Host       = 'smtp.gmail.com';
//           $mail->SMTPAuth   = true;
//           $mail->Username   = 'sakshishaw1375@gmail.com'; // Sender's email
//           $mail->Password   = 'suji ukrf bwtb lcpp'; // Sender's email app password 
//           $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//           $mail->Port       = 587;
  
//           // Email settings
//           $mail->setFrom($fromEmail, 'Event Management System');
//           $mail->addAddress($toEmail);
//           $mail->isHTML(true);
//           $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
//           $mail->Body    = "
//             <h3>New Event Scheduled in Your Room</h3>
//             <p>Event Name: {$eventData['event_name']}</p>
//             <p>Start Date: {$eventData['start_date']}</p>
//             <p>End Date: {$eventData['end_date']}</p>
//             <p>Created By: {$eventData['teacher_name']}</p>
//         ";
  
//           $mail->send();
//       } catch (Exception $e) {
//           //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
//       }
//   }
  
//////********************************************** correct code abpve one */



public function create()
{
    try {
        $input = $this->request->getJSON(true);

        // Check for duplicate event
        $existingEvent = $this->eventsModel
            ->where('event_name', $input['event_name'])
            ->where('event_status', 1) // Check if event_status is 1
            ->first();

        if ($existingEvent) {
            return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
        }

        // Fetch teacher details including passkey
        $teacher = $this->teachersModel->find($input['teacher_id']);
        if (!$teacher) {
            return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
        }
        $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
        $teacherEmail = $teacher['email'];
        $teacherPasskey = $teacher['passkey']; // Fetch teacher's passkey

        // Validate room_no and fetch room_id and room_owner
        $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
        if (!$room) {
            return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
        }
        $roomId = $room['id'];
        $roomOwnerId = $room['room_owner'];

        // Fetch room_owner's email and passkey
        $roomOwner = $this->teachersModel->find($roomOwnerId);
        if (!$roomOwner) {
            return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
        }
        $roomOwnerEmail = $roomOwner['email'];
        $roomOwnerPasskey = $roomOwner['passkey']; // Fetch room owner's passkey

        // Validate dept_name and fetch dept_id
        $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
        if (!$department) {
            return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
        }
        $deptId = $department['id'];

        // Fetch event type and validate
        $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
        if (!$eventType) {
            return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
        }
        $eventTypeId = $eventType['id'];

        // Fetch event level and validate
        $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
        if (!$eventLevel) {
            return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
        }
        $eventLevelId = $eventLevel['id'];

        // Prepare event data
        $eventData = [
            'teacher_id'   => $input['teacher_id'],
            'room_id'      => $roomId,
            'event_name'   => $input['event_name'],
            'description'  => $input['description'],
            'dept'         => $deptId,
            'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
            'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
            'teacher_name' => $teacherFullName,
            'eventType_id' => $eventTypeId,
            'event_level_id' => $eventLevelId,
            'eligible_dept'=> $input['eligibleDepartments'],
        ];

        // Insert event into the database
        $eventId = $this->eventsModel->insertEvent($eventData);

        if ($eventId) {
            // Check if event level is 'National' (event_level_id = 1)
            if ($eventLevelId == 1) {
                // Fetch students with subscribe = 1
                $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

                // Send email to each subscribed student
                foreach ($subscribedStudents as $student) {
                    $this->sendEmailToStudent($student['email'], $eventData);
                }
            }

            // Send email from teacher to room owner with teacher's email & passkey
            if ($teacherEmail !== $roomOwnerEmail) {
                $this->sendEmailFromTeacherToRoomOwner($teacherEmail, $teacherPasskey, $roomOwnerEmail, $eventData);
            } 

            // Check if event_status is 0, then create a notification
            $event = $this->eventsModel->find($eventId);
            if ($event && $event['event_status'] == 0) {
                $notificationData = [
                    'requested_by' => $input['teacher_id'],
                    'event_id'     => $eventId,
                    'created_at'   => date('Y-m-d H:i:s'),
                ];

                // Insert notification
                if (!$this->notificationModel->insert($notificationData)) {
                    return $this->failServerError('Event created, but failed to create notification.');
                }
            }

            return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
        }

        return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

private function sendEmailFromTeacherToRoomOwner($fromEmail, $fromPasskey, $toEmail, $eventData)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $fromEmail; // Sender's email (Teacher's email)
        $mail->Password   = $fromPasskey; // Use teacher's passkey as password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
       // log_message('debug',$mail->Username, " " ,$mail->Password);
       // log_message('info',  'Passkey Type: ' . gettype($fromPasskey));
      // log_message('info', 'Email sent to ' . $toEmail);
        // Email settings
        $mail->setFrom($fromEmail, 'Event Management System');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
        $mail->Body    = "
            <h3>New Event Scheduled in Your Room</h3>
            <p>Event Name: {$eventData['event_name']}</p>
            <p>Start Date: {$eventData['start_date']}</p>
            <p>End Date: {$eventData['end_date']}</p>
            <p>Created By: {$eventData['teacher_name']}</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    }
}














 /**
  * Sends an email to a subscribed student.
  *
  * @param string $email
  * @param array $eventData
  * @return void
  */
 private function sendEmailToStudent($email, $eventData)
 {
     $mail = new PHPMailer(true);
 
     try {
         // SMTP configuration
         $mail->isSMTP();
         $mail->Host       = 'smtp.gmail.com';
         $mail->SMTPAuth   = true;
         $mail->Username   = 'sakshishaw1375@gmail.com'; // Replace with your email
         $mail->Password   = 'suji ukrf bwtb lcpp'; // Replace with your email password use App password
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port       = 587;
 
         // Email settings
         $mail->setFrom('sakshishaw1375@gmail.com', 'Event Management System');
         $mail->addAddress($email);
         $mail->isHTML(true);
         $mail->Subject = 'New National Event: ' . $eventData['event_name'];
         $mail->Body    = "
             <h3>New National Event Alert!</h3>
             <p>Event Name: {$eventData['event_name']}</p>
             <p>Description: {$eventData['description']}</p>
             <p>Start Date: {$eventData['start_date']}</p>
             <p>End Date: {$eventData['end_date']}</p>
             <p>Created By: {$eventData['teacher_name']}</p>
         ";
 
         $mail->send();
         //log_message('info', 'Email sent to ' . $email);
     } catch (Exception $e) {
         //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
     }
 }
























public function uploadEventImage()
{
    try {
        // Get the uploaded image file
        $imageFile = $this->request->getFile('event_image');
        
        // Check if a file is uploaded
        if (!$this->request->getFile('event_image')) {
            return $this->respond([
                'status' => false,
                'message' => 'No image file uploaded.'
            ], Response::HTTP_BAD_REQUEST);
        }
        if (!$imageFile->isValid() || $imageFile->hasMoved()) {
            return $this->respond([
                'status' => false,
                'message' => $imageFile->getErrorString()
            ], Response::HTTP_BAD_REQUEST);
        }

        // Fetch the highest ID from the events table
        $db = \Config\Database::connect();
        $builder = $db->table('events');
        $builder->selectMax('id');
        $query = $builder->get();
        $result = $query->getRow();
        $eventId = $result->id;

        // Fetch the event_name based on the highest event ID
        $builder->select('event_name');
        $builder->where('id', $eventId);
        $query = $builder->get();
        $event = $query->getRow();

        if (!$event) {
            return $this->respond([
                'status' => false,
                'message' => 'Event not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $eventName = $event->event_name;

        // Generate a new name for the image based on the event_name
        $eventNameWords = explode(' ', $eventName);
        $imageNewName = '';
        foreach ($eventNameWords as $word) {
            $imageNewName .= strtoupper(substr($word, 0, 1)); // Take the first letter of each word
        }
        $imageNewName .= '-' . uniqid(); // Add a unique ID to the end

        // Define the upload path
        $uploadPath = FCPATH . 'public/uploads/event_images/';

        // Ensure the directory exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Move the file to the upload directory with the new name
        $imageFile->move($uploadPath, $imageNewName );

        // Store the new image name in the database
        $builder = $db->table('events');
        $builder->set('imagename', $imageNewName );
        $builder->where('id', $eventId);
        $builder->update();

        // Get the file's relative URL for frontend access
        $fileUrl = base_url('public/uploads/event_images/' . $imageNewName);

        // Return success response
        return $this->respond([
            'status' => true,
            'message' => 'Image uploaded and name stored successfully.',
            'file_url' => $fileUrl // Return the file's URL
        ], 200);
    } catch (\Exception $e) {
        // Handle any exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    

public function getRecentPastEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getRecentPastEvents($currentDate);

            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], 404);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], 200);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

public function getEventsByTeacher($teacherId)
{
    try {
        // Call the model method to fetch events by teacher_id
        $events = $this->eventsModel->getEventsByTeacher($teacherId);

        // Check if any events were retrieved
        if (empty($events)) {
            return $this->respond([
                'status' => false,
                'message' => 'No events found for this teacher.'
            ], 404);
        }

         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
    
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
             $deptNames = [];
 
             // Fetch department names for all eligible_dept IDs
             if (!empty($eligibleDeptIds)) {
                 $departments = $this->departmentsModel
                     ->whereIn('id', $eligibleDeptIds)
                     ->findAll();
 
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
 
             // Join the department names with commas
             $event['eligible_dept'] = implode(', ', $deptNames);
         }

        // Return the events as JSON
        return $this->respond([
            'status' => true,
            'data' => $events
        ], 200);
    } catch (\Exception $e) {
        // Handle exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


/*     public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } */




    public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');
    
            // Call the model method to fetch upcoming events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);
    
            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No upcoming events found.'
                ], Response::HTTP_NOT_FOUND);
            }
    
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }
    
            // Return the events with mapped department names
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    




    // get requested events for room admin
    public function getEventByRoomOwner($room_owner_id)
    {


        // Step 1: Get all room ids where room_owner matches the passed room_owner_id
        $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);

        // Step 2: Get events based on room_ids with event_status = 0
        $events = $this->eventsModel->getEventsByRoomIdsAndStatus($roomIds);
         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
        
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
             $deptNames = [];
     
             if (!empty($eligibleDeptIds)) {
                 // Fetch department names
                 $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
     
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
     
             // Replace eligible_dept with department names
             $event->eligible_dept = implode(', ', $deptNames);
         }

        // Return events data
        return $this->response->setJSON($events);
    }


        // get requested events for room admin
        public function getAcceptedEventByRoomOwner($room_owner_id)
        {
    
    
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
    
            // Step 2: Get events based on room_ids with event_status = 1
            $events = $this->eventsModel->getAcceptedEvents($roomIds, $room_owner_id);
             // Initialize the DepartmentModel
             $this->departmentsModel = new \App\Models\DepartmentModel();
        
             // Map the eligible_dept to department names
             foreach ($events as &$event) {
                 $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                 $deptNames = [];
         
                 if (!empty($eligibleDeptIds)) {
                     // Fetch department names
                     $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
         
                     // Extract department names
                     foreach ($departments as $dept) {
                         $deptNames[] = $dept['dept_name'];
                     }
                 }
         
                 // Replace eligible_dept with department names
                 $event->eligible_dept = implode(', ', $deptNames);
             }
    
            // Return events data
            return $this->response->setJSON($events);
        }


        public function getRejectedEvents($room_owner_id)
        {
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
        
            // Step 2: Get events based on room_ids with event_status = 0
            $events = $this->eventsModel->getRejectedEvents($roomIds, $room_owner_id);
        
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
        
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                $deptNames = [];
        
                if (!empty($eligibleDeptIds)) {
                    // Fetch department names
                    $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
        
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
        
                // Replace eligible_dept with department names
                $event->eligible_dept = implode(', ', $deptNames);
            }
        
            // Return events data
            return $this->response->setJSON($events);
        }
         
        
        












        // public function updateRegistrationAccept()
        // {
        //     $request = $this->request->getJSON();
        
        //     // Validate input
        //     if (!isset($request->event_id) || !isset($request->userId)) {
        //         return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
        //     }
        
        //     $eventId = $request->event_id;
        //     $userId = $request->userId;
        //     $eventname= $request->eventname;

        
        //     if (empty($eventId) || empty($userId)) {
        //         return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
        //     }
        
        //     // Update event status
        //     $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);
        
        //     // Update notification table
        //     $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
        //         ->set(['accepted_by' => $userId, 'accepted_rejected' => 1,'message' => "Your event '{$eventname}' was Accepted.",
        //         'created_at' => date('Y-m-d H:i:s') , 'display_status' => 1])
        //         ->update();
        
        //     if ($eventUpdate && $notificationUpdate) {
        //         // Get the email addresses
        //         $teacherEmailFrom = $this->teachersModel->where('id', $userId)->select('email')->first()['email'];
        //         $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        //         $teacherIdTo = $eventDetails['teacher_id'];
        //         $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];
        
        //         if ($teacherEmailFrom && $teacherEmailTo) {
        //             // Send email
        //             //$this->sendEventAcceptedEmail($teacherEmailFrom, $teacherEmailTo, $eventId);
        //         }
        
        //         return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
        //     }
        
        //     return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
        // }
        
        // private function sendEventAcceptedEmail($fromEmail, $toEmail, $eventId)
        // {
        //     // Load PHPMailer library
        //     $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        //     try {
        //         // SMTP configuration
        //         $mail->isSMTP();
        //         $mail->Host       = 'smtp.gmail.com';
        //         $mail->SMTPAuth   = true;
        //         $mail->Username   = $fromEmail; // Authentication email
        //         $mail->Password   = 'suji ukrf bwtb lcpp'; // App password for Gmail
        //         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        //         $mail->Port       = 587;
        
        //         // Sender and recipient settings
        //         $mail->setFrom($fromEmail, 'Event Organizer'); // Set dynamic sender email
        //         $mail->addAddress($toEmail); // Recipient's email
        
        //         // Email content
        //         $mail->isHTML(true); // Set email format to HTML
        //         $mail->Subject = 'Event Accepted'; // Subject
        //         $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong> has been accepted.</p>";
        //         $mail->AltBody = "Your event with ID: $eventId has been accepted."; // Fallback for non-HTML email clients
        
        //         // Send email
        //         $mail->send();
        //         log_message('info', 'Email sent successfully to ' . $toEmail);
        //         return true;
        
        //     } catch (\PHPMailer\PHPMailer\Exception $e) {
        //         // Log error message
        //         log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
        //         return false;
        //     }
        // }
        

        public function updateRegistrationAccept()
{
    $request = $this->request->getJSON();

    // Validate input
    if (!isset($request->event_id) || !isset($request->userId)) {
        return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
    }

    $eventId = $request->event_id;
    $userId = $request->userId;
    $eventname= $request->eventname;

    if (empty($eventId) || empty($userId)) {
        return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
    }

    // Update event status
    $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);

    // Update notification table
    $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
        ->set(['accepted_by' => $userId, 'accepted_rejected' => 1, 'message' => "Your event '{$eventname}' was Accepted.",
        'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
        ->update();

    if ($eventUpdate && $notificationUpdate) {
        // Get the email and passkey addresses
        $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
        $teacherEmailFrom = $teacherDetailsFrom['email'];
        $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
        
        $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        $teacherIdTo = $eventDetails['teacher_id'];
        $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

        // Pass email and passkey to sendEventAcceptedEmail
        if ($teacherEmailFrom !== $teacherEmailTo) {
            $this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId);
        }

        return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
    }

    return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
}

private function sendEventAcceptedEmail($fromEmail, $fromPasskey, $toEmail, $eventId)
{    
    $mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $fromEmail; // Sender's email
    $mail->Password   = $fromPasskey; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender and recipient settings
    $mail->setFrom($fromEmail, 'Event Organizer');
    $mail->addAddress($toEmail);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Event Accepted';
    $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong> has been accepted.</p>";
    $mail->AltBody = "Your event with ID: $eventId has been accepted.";

    // Send email
    $mail->send();
    //log_message('info', 'Email sent successfully to ' . $toEmail);
    return true;

} catch (Exception $e) {
    //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    return false;
}

}

        
        


    public function updateRegistrationReject()
    {
        $request = $this->request->getJSON();

        // Validate input
        if (!isset($request->event_id) || !isset($request->userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
        }

        $eventId = $request->event_id;
        $userId = $request->userId;
        $eventname= $request->eventname;
        $rejectionReason= $request->rejectionReason;

        //log_message('info', 'Received event_id: ' . $eventId);
        //log_message('info', 'Received user_id: ' . $userId);

        if (empty($eventId) || empty($userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
        }

        //$eventModel = new EventsModel();
        //$notificationModel = new NotificationModel();

        // Update event status
        $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 0]);

        // Update notification table
        $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
            ->set(['accepted_by' => $userId, 'accepted_rejected' => 0,'message' => "Your event '{$eventname}' was rejected. Reason: $rejectionReason",
            'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
            ->update();

        if ($eventUpdate && $notificationUpdate) {
              $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
        $teacherEmailFrom = $teacherDetailsFrom['email'];
        $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
        
        $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        $teacherIdTo = $eventDetails['teacher_id'];
        $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

        // Pass email and passkey to sendEventAcceptedEmail
        if ($teacherEmailFrom !== $teacherEmailTo) {
            $this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason);
        }
            return $this->respond(['status' => 'success', 'message' => 'Registration rejected successfully.'], 200);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
    }

        private function sendEventRejectedEmail($fromEmail, $fromPasskey, $toEmail, $eventId, $rejectionReason)
{    
    $mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $fromEmail; // Sender's email
    $mail->Password   = $fromPasskey; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender and recipient settings
    $mail->setFrom($fromEmail, 'Event Organizer');
    $mail->addAddress($toEmail);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Event Rejected';
    $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong> has been rejected due to <strong>$rejectionReason</strong>.</p>";
    $mail->AltBody = "Your event with ID: $eventId has been Rejected.";

    // Send email
    $mail->send();
    //log_message('info', 'Email sent successfully to ' . $toEmail);
    return true;

} catch (Exception $e) {
    //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    return false;
}

}
    
}













/* *********************************************************************************************************************** */













<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EventsController extends ResourceController
{
    protected $eventsModel;
    protected $teachersModel;
    protected $roomsModel;
    protected $departmentsModel;
    protected $eventTypesModel;
    protected $studentsModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->eventsModel = new \App\Models\EventsModel();   // Load the EventsModel
        $this->teachersModel = new \App\Models\TeachersModel(); // Load the TeachersModel
        $this->roomsModel = new \App\Models\RoomsModel();     // Load the RoomsModel
        $this->departmentsModel = new \App\Models\DepartmentModel();  
        $this->eventTypesModel = new \App\Models\EventTypeModel(); // Load the EventTypesModel
        $this->eventLevelsModel = new \App\Models\EventLevelsModel();
        $this->studentsModel = new \App\Models\StudentsModel();
        $this->notificationModel = new \App\Models\NotificationModel();
    }


    /**
     * Fetch all events.
     */
    public function index()
    {
        try {
            $events = $this->eventsModel->getEvents(); // Call the model's method

            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available venues based on input filters.
     */
    public function getAvailableVenues()
    {
        try {
            // Get input data from the request
            $input = $this->request->getJSON(true); // Accept JSON payload
    
            // Validate input data
            if (
                empty($input['start_date']) ||
                empty($input['end_date']) ||
                empty($input['start_time']) ||
                empty($input['end_time']) ||
                empty($input['room_type'])
            ) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'All filters are required!',
                ], 400); // HTTP 400 Bad Request
            }
    
            // Prepare filters for the model
            $filters = [
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'start_time' => $input['start_time'],
                'end_time' => $input['end_time'],
                'room_type' => $input['room_type'],
            ];
    
            // Call the model method to get available venues
            $availableVenues = $this->eventsModel->getAvailableVenues($filters);
    
            // Check if any venues are available
            if (empty($availableVenues)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No venues available for the given filters.'
                ], 404); // HTTP 404 Not Found
            }
    
            // Return the result as JSON
            return $this->respond([
                'status' => 'success',
                'data' => $availableVenues,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }
    

/*     public function create()
{
    try {
        $input = $this->request->getJSON(true);

        // Log event type to check if it's being passed correctly
        log_message('debug', 'Event Type: ' . $input['eventType']);

        // Fetch teacher details
        $teacher = $this->teachersModel->find($input['teacher_id']);
        if (!$teacher) {
            return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
        }
        $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];

        // Validate room_no and fetch room_id
        $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
        if (!$room) {
            return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
        }
        $roomId = $room['id']; // Extract room ID

        // Validate dept_name and fetch dept_id
        $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
        if (!$department) {
            return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
        }
        $deptId = $department['id']; // Extract department ID

        // Fetch event type and validate
        $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
        if (!$eventType) {
            return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
        }
        $eventTypeId = $eventType['id']; // Extract eventType ID

        $eventLevel = $this->eventLevelsModel->where('event_level',$input['event_level'])->first();
        //log_message('debug', 'Event Level Retrieved: ' . print_r($eventLevel, true));
        if (!$eventLevel) {
            return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
        }
        $eventLevelId = $eventLevel['id']; // Extract eventType ID
        //log_message('debug', 'eventLevelId ID: ' . $eventLevelId);

        // Log eventTypeId to check if it's correct
        //log_message('debug', 'Event Type ID: ' . $eventTypeId);

        // Prepare event data
        $eventData = [
            'teacher_id'   => $input['teacher_id'],
            'room_id'      => $roomId, // Use resolved room_id
            'event_name'   => $input['event_name'],
            'description'  => $input['description'],
            'dept'         => $deptId, // Use resolved department ID
            'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
            'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
            'teacher_name' => $teacherFullName,
            'eventType_id' => $eventTypeId, // Store eventType_id here
            'event_level_id' => $eventLevelId // Store eventType_id here
        ];

        // Log the event data to check before insertion
        //log_message('debug', 'Event Data: ' . print_r($eventData, true));

        // Insert event into database
        $eventId = $this->eventsModel->insertEvent($eventData);

        if ($eventId) {
            return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 201);
        }

        return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

 */








 /* public function create()
 {
     try {
         $input = $this->request->getJSON(true);
 
         // Log event type to check if it's being passed correctly
         //log_message('debug', 'Event Type: ' . $input['eventType']);
 
         // Fetch teacher details
         $teacher = $this->teachersModel->find($input['teacher_id']);
         if (!$teacher) {
             return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
         }
         $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
 
         // Validate room_no and fetch room_id
         $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
         if (!$room) {
             return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
         }
         $roomId = $room['id']; // Extract room ID
 
         // Validate dept_name and fetch dept_id
         $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
         if (!$department) {
             return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
         }
         $deptId = $department['id']; // Extract department ID
 
         // Fetch event type and validate
         $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
         if (!$eventType) {
             return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
         }
         $eventTypeId = $eventType['id']; // Extract eventType ID
 
         $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
         if (!$eventLevel) {
             return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
         }
         $eventLevelId = $eventLevel['id']; // Extract eventLevel ID
 
         // Prepare event data
         $eventData = [
             'teacher_id'   => $input['teacher_id'],
             'room_id'      => $roomId,
             'event_name'   => $input['event_name'],
             'description'  => $input['description'],
             'dept'         => $deptId,
             'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
             'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
             'eventType_id' => $eventTypeId,
             'event_level_id' => $eventLevelId
         ];
 
         // Insert event into the database
         $eventId = $this->eventsModel->insertEvent($eventData);
 
         if ($eventId) {
             // Check if event level is 'National' (event_level_id = 1)
             if ($eventLevelId == 1) {
                 // Fetch students with subscribe = 1
                 $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();
 
                 // Send email to each subscribed student
                 foreach ($subscribedStudents as $student) {
                     $this->sendEmailToStudent($student['email'], $eventData);
                 }
             }
 
             return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 201);
         }
 
         return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
     } catch (\Exception $e) {
         return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
     }
 }

  */


///////////////////////////////********************************* correct code bellow one */

//   public function create()
//   {
//       try {
//           $input = $this->request->getJSON(true);
          
//         // Check for duplicate event
//         $existingEvent = $this->eventsModel
//             ->where('event_name', $input['event_name'])
//             ->where('event_status', 1) // Check if event_status is 1
//             ->first();

//         if ($existingEvent) {
//             return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
//         }
//           // Fetch teacher details
//           $teacher = $this->teachersModel->find($input['teacher_id']);
//           if (!$teacher) {
//               return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
//           }
//           $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
//           $teacherEmail = $teacher['email']; // Fetch teacher's email
  
//           // Validate room_no and fetch room_id and room_owner
//           $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
//           if (!$room) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
//           }
//           $roomId = $room['id']; // Extract room ID
//           $roomOwnerId = $room['room_owner']; // Extract room owner ID
  
//           // Fetch room_owner's email
//           $roomOwner = $this->teachersModel->find($roomOwnerId);
//           if (!$roomOwner) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
//           }
//           $roomOwnerEmail = $roomOwner['email']; // Fetch room owner's email
  
//           // Validate dept_name and fetch dept_id
//           $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
//           if (!$department) {
//               return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
//           }
//           $deptId = $department['id']; // Extract department ID
  
//           // Fetch event type and validate
//           $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
//           if (!$eventType) {
//               return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
//           }
//           $eventTypeId = $eventType['id']; // Extract eventType ID
  
//           // Fetch event level and validate
//           $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
//           if (!$eventLevel) {
//               return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
//           }
//           $eventLevelId = $eventLevel['id']; // Extract eventLevel ID
  
//           // Prepare event data
//           $eventData = [
//               'teacher_id'   => $input['teacher_id'],
//               'room_id'      => $roomId,
//               'event_name'   => $input['event_name'],
//               'description'  => $input['description'],
//               'dept'         => $deptId,
//               'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
//               'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
//               'teacher_name' => $teacherFullName,
//               'eventType_id' => $eventTypeId,
//               'event_level_id' => $eventLevelId,
//               'eligible_dept'=>$input['eligibleDepartments'],
//           ];
  
//           // Insert event into the database
//           $eventId = $this->eventsModel->insertEvent($eventData);

  
//           if ($eventId) {
//             // Check if event level is 'National' (event_level_id = 1)
//             if ($eventLevelId == 1) {
//                 // Fetch students with subscribe = 1
//                 $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

//                 // Send email to each subscribed student
//                 foreach ($subscribedStudents as $student) {
//                     $this->sendEmailToStudent($student['email'], $eventData);
//                 }
//             }

//               // Send email from teacher to room owner
//              //$this->sendEmailFromTeacherToRoomOwner($teacherEmail, $roomOwnerEmail, $eventData,$teacherFullName);

//               // Check if event_status is 0, then create a notification
//               $event = $this->eventsModel->find($eventId);

//         // Check if event_status is 0, then create a notification
//             if ($event && $event['event_status'] == 0) {
//                 $notificationData = [
//                     'requested_by'         => $input['teacher_id'],
//                     'event_id'             => $eventId,
//                     'created_at'           => date('Y-m-d H:i:s'),
                    
//                 ];

//                 // Insert notification
//                 if (!$this->notificationModel->insert($notificationData)) {
//                     return $this->failServerError('Event created, but failed to create notification.');
//                 }
//             }
  
//               return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
//           }
  
//           return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
//       } catch (\Exception $e) {
//           return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
//       }
//   }
  
//   /**
//    * Sends an email from the teacher to the room owner.
//    *
//    * @param string $fromEmail
//    * @param string $toEmail
//    * @param array $eventData
//    * @return void
//    */
//   private function sendEmailFromTeacherToRoomOwner($fromEmail, $toEmail, $eventData)
//   {
//       $mail = new PHPMailer(true);
  
//       try {
//           // SMTP configuration
//           $mail->isSMTP();
//           $mail->Host       = 'smtp.gmail.com';
//           $mail->SMTPAuth   = true;
//           $mail->Username   = 'sakshishaw1375@gmail.com'; // Sender's email
//           $mail->Password   = 'suji ukrf bwtb lcpp'; // Sender's email app password 
//           $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//           $mail->Port       = 587;
  
//           // Email settings
//           $mail->setFrom($fromEmail, 'Event Management System');
//           $mail->addAddress($toEmail);
//           $mail->isHTML(true);
//           $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
//           $mail->Body    = "
//             <h3>New Event Scheduled in Your Room</h3>
//             <p>Event Name: {$eventData['event_name']}</p>
//             <p>Start Date: {$eventData['start_date']}</p>
//             <p>End Date: {$eventData['end_date']}</p>
//             <p>Created By: {$eventData['teacher_name']}</p>
//         ";
  
//           $mail->send();
//       } catch (Exception $e) {
//           //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
//       }
//   }
  
//////********************************************** correct code abpve one */



public function create()
{
    try {
        $input = $this->request->getJSON(true);

        // Check for duplicate event
        /* $existingEvent = $this->eventsModel
            ->where('event_name', $input['event_name'])
            ->where('event_status', 1) // Check if event_status is 1
            ->first(); */
        $existingEvent = $this->eventsModel
        ->select('events.id')
        ->join('notification', 'notification.event_id = events.id', 'left')
        ->where('events.event_name', $input['event_name'])
        ->groupStart()
            ->where('notification.accepted_rejected', 1) // Active event
            ->orWhere('notification.accepted_rejected IS NULL') // Pending  event
        ->groupEnd()
        ->first();    

        if ($existingEvent) {
            return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
        }

        // Fetch teacher details including passkey
        $teacher = $this->teachersModel->find($input['teacher_id']);
        if (!$teacher) {
            return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
        }
        $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
        $teacherEmail = $teacher['email'];
        $teacherPasskey = $teacher['passkey']; // Fetch teacher's passkey

        // Validate room_no and fetch room_id and room_owner
        $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
        if (!$room) {
            return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
        }
        $roomId = $room['id'];
        $roomOwnerId = $room['room_owner'];

        // Fetch room_owner's email and passkey
        $roomOwner = $this->teachersModel->find($roomOwnerId);
        if (!$roomOwner) {
            return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
        }
        $roomOwnerEmail = $roomOwner['email'];
        $roomOwnerPasskey = $roomOwner['passkey']; // Fetch room owner's passkey

        // Validate dept_name and fetch dept_id
        $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
        if (!$department) {
            return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
        }
        $deptId = $department['id'];

        // Fetch event type and validate
        $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
        if (!$eventType) {
            return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
        }
        $eventTypeId = $eventType['id'];

        // Fetch event level and validate
        $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
        if (!$eventLevel) {
            return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
        }
        $eventLevelId = $eventLevel['id'];

        // Prepare event data
        $eventData = [
            'teacher_id'   => $input['teacher_id'],
            'room_id'      => $roomId,
            'event_name'   => $input['event_name'],
            'description'  => $input['description'],
            'dept'         => $deptId,
            'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
            'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
            'teacher_name' => $teacherFullName,
            'eventType_id' => $eventTypeId,
            'event_level_id' => $eventLevelId,
            'eligible_dept'=> $input['eligibleDepartments'],
        ];

        // Insert event into the database
        $eventId = $this->eventsModel->insertEvent($eventData);

        if ($eventId) {
            // Check if event level is 'National' (event_level_id = 1)
            if ($eventLevelId == 1) {
                // Fetch students with subscribe = 1
                $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

                // Send email to each subscribed student
                foreach ($subscribedStudents as $student) {
                    $this->sendEmailToStudent($student['email'], $eventData);
                }
            }

            // Send email from teacher to room owner with teacher's email & passkey
            if ($teacherEmail !== $roomOwnerEmail) {
                $this->sendEmailFromTeacherToRoomOwner($teacherEmail, $teacherPasskey, $roomOwnerEmail, $eventData);
            } 

            // Check if event_status is 0, then create a notification
            $event = $this->eventsModel->find($eventId);
            if ($event && $event['event_status'] == 0) {
                $notificationData = [
                    'requested_by' => $input['teacher_id'],
                    'event_id'     => $eventId,
                    'created_at'   => date('Y-m-d H:i:s'),
                ];

                // Insert notification
                if (!$this->notificationModel->insert($notificationData)) {
                    return $this->failServerError('Event created, but failed to create notification.');
                }
            }

            return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
        }

        return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

private function sendEmailFromTeacherToRoomOwner($fromEmail, $fromPasskey, $toEmail, $eventData)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $fromEmail; // Sender's email (Teacher's email)
        $mail->Password   = $fromPasskey; // Use teacher's passkey as password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
       // log_message('debug',$mail->Username, " " ,$mail->Password);
       // log_message('info',  'Passkey Type: ' . gettype($fromPasskey));
      // log_message('info', 'Email sent to ' . $toEmail);
        // Email settings
        $mail->setFrom($fromEmail, 'Event Management System');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
        $mail->Body    = "
            <h3>New Event Scheduled in Your Room</h3>
            <p>Event Name: {$eventData['event_name']}</p>
            <p>Start Date: {$eventData['start_date']}</p>
            <p>End Date: {$eventData['end_date']}</p>
            <p>Created By: {$eventData['teacher_name']}</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    }
}














 /**
  * Sends an email to a subscribed student.
  *
  * @param string $email
  * @param array $eventData
  * @return void
  */
 private function sendEmailToStudent($email, $eventData)
 {
     $mail = new PHPMailer(true);
 
     try {
         // SMTP configuration
         $mail->isSMTP();
         $mail->Host       = 'smtp.gmail.com';
         $mail->SMTPAuth   = true;
         $mail->Username   = 'sakshishaw1375@gmail.com'; // Replace with your email
         $mail->Password   = 'suji ukrf bwtb lcpp'; // Replace with your email password use App password
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port       = 587;
 
         // Email settings
         $mail->setFrom('sakshishaw1375@gmail.com', 'Event Management System');
         $mail->addAddress($email);
         $mail->isHTML(true);
         $mail->Subject = 'New National Event: ' . $eventData['event_name'];
         $mail->Body    = "
             <h3>New National Event Alert!</h3>
             <p>Event Name: {$eventData['event_name']}</p>
             <p>Description: {$eventData['description']}</p>
             <p>Start Date: {$eventData['start_date']}</p>
             <p>End Date: {$eventData['end_date']}</p>
             <p>Created By: {$eventData['teacher_name']}</p>
         ";
 
         $mail->send();
         //log_message('info', 'Email sent to ' . $email);
     } catch (Exception $e) {
         //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
     }
 }




public function uploadEventImage()
{
    try {
        // Get the uploaded image file
        $imageFile = $this->request->getFile('event_image');
        
        // Check if a file is uploaded
        if (!$this->request->getFile('event_image')) {
            return $this->respond([
                'status' => false,
                'message' => 'No image file uploaded.'
            ], Response::HTTP_BAD_REQUEST);
        }
        if (!$imageFile->isValid() || $imageFile->hasMoved()) {
            return $this->respond([
                'status' => false,
                'message' => $imageFile->getErrorString()
            ], Response::HTTP_BAD_REQUEST);
        }

        // Fetch the highest ID from the events table
        $db = \Config\Database::connect();
        $builder = $db->table('events');
        $builder->selectMax('id');
        $query = $builder->get();
        $result = $query->getRow();
        $eventId = $result->id;

        // Fetch the event_name based on the highest event ID
        $builder->select('event_name');
        $builder->where('id', $eventId);
        $query = $builder->get();
        $event = $query->getRow();

        if (!$event) {
            return $this->respond([
                'status' => false,
                'message' => 'Event not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $eventName = $event->event_name;

        // Generate a new name for the image based on the event_name
        $eventNameWords = explode(' ', $eventName);
        $imageNewName = '';
        foreach ($eventNameWords as $word) {
            $imageNewName .= strtoupper(substr($word, 0, 1)); // Take the first letter of each word
        }
        $imageNewName .= '-' . uniqid(); // Add a unique ID to the end

        // Define the upload path
        $uploadPath = FCPATH . 'public/uploads/event_images/';

        // Ensure the directory exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Move the file to the upload directory with the new name
        $imageFile->move($uploadPath, $imageNewName );

        // Store the new image name in the database
        $builder = $db->table('events');
        $builder->set('imagename', $imageNewName );
        $builder->where('id', $eventId);
        $builder->update();

        // Get the file's relative URL for frontend access
        $fileUrl = base_url('public/uploads/event_images/' . $imageNewName);

        // Return success response
        return $this->respond([
            'status' => true,
            'message' => 'Image uploaded and name stored successfully.',
            'file_url' => $fileUrl // Return the file's URL
        ], 200);
    } catch (\Exception $e) {
        // Handle any exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    

public function getRecentPastEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getRecentPastEvents($currentDate);

            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], 404);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], 200);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

public function getEventsByTeacher($teacherId)
{
    try {
        // Call the model method to fetch events by teacher_id
        $events = $this->eventsModel->getEventsByTeacher($teacherId);

        // Check if any events were retrieved
        if (empty($events)) {
            return $this->respond([
                'status' => false,
                'message' => 'No events found for this teacher.'
            ], 404);
        }

         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
    
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
             $deptNames = [];
 
             // Fetch department names for all eligible_dept IDs
             if (!empty($eligibleDeptIds)) {
                 $departments = $this->departmentsModel
                     ->whereIn('id', $eligibleDeptIds)
                     ->findAll();
 
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
 
             // Join the department names with commas
             $event['eligible_dept'] = implode(', ', $deptNames);
         }

        // Return the events as JSON
        return $this->respond([
            'status' => true,
            'data' => $events
        ], 200);
    } catch (\Exception $e) {
        // Handle exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


/*     public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } */




    public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');
    
            // Call the model method to fetch upcoming events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);
    
            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No upcoming events found.'
                ], Response::HTTP_NOT_FOUND);
            }
    
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }
    
            // Return the events with mapped department names
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    




    // get requested events for room admin
    public function getEventByRoomOwner($room_owner_id)
    {


        // Step 1: Get all room ids where room_owner matches the passed room_owner_id
        $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);

        // Step 2: Get events based on room_ids with event_status = 0
        $events = $this->eventsModel->getEventsByRoomIdsAndStatus($roomIds);
         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
        
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
             $deptNames = [];
     
             if (!empty($eligibleDeptIds)) {
                 // Fetch department names
                 $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
     
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
     
             // Replace eligible_dept with department names
             $event->eligible_dept = implode(', ', $deptNames);
         }

        // Return events data
        return $this->response->setJSON($events);
    }


        // get requested events for room admin
        public function getAcceptedEventByRoomOwner($room_owner_id)
        {
    
    
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
    
            // Step 2: Get events based on room_ids with event_status = 1
            $events = $this->eventsModel->getAcceptedEvents($roomIds, $room_owner_id);
             // Initialize the DepartmentModel
             $this->departmentsModel = new \App\Models\DepartmentModel();
        
             // Map the eligible_dept to department names
             foreach ($events as &$event) {
                 $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                 $deptNames = [];
         
                 if (!empty($eligibleDeptIds)) {
                     // Fetch department names
                     $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
         
                     // Extract department names
                     foreach ($departments as $dept) {
                         $deptNames[] = $dept['dept_name'];
                     }
                 }
         
                 // Replace eligible_dept with department names
                 $event->eligible_dept = implode(', ', $deptNames);
             }
    
            // Return events data
            return $this->response->setJSON($events);
        }


        public function getRejectedEvents($room_owner_id)
        {
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
        
            // Step 2: Get events based on room_ids with event_status = 0
            $events = $this->eventsModel->getRejectedEvents($roomIds, $room_owner_id);
        
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
        
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                $deptNames = [];
        
                if (!empty($eligibleDeptIds)) {
                    // Fetch department names
                    $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
        
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
        
                // Replace eligible_dept with department names
                $event->eligible_dept = implode(', ', $deptNames);
            }
        
            // Return events data
            return $this->response->setJSON($events);
        }
         
        
        












        // public function updateRegistrationAccept()
        // {
        //     $request = $this->request->getJSON();
        
        //     // Validate input
        //     if (!isset($request->event_id) || !isset($request->userId)) {
        //         return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
        //     }
        
        //     $eventId = $request->event_id;
        //     $userId = $request->userId;
        //     $eventname= $request->eventname;

        
        //     if (empty($eventId) || empty($userId)) {
        //         return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
        //     }
        
        //     // Update event status
        //     $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);
        
        //     // Update notification table
        //     $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
        //         ->set(['accepted_by' => $userId, 'accepted_rejected' => 1,'message' => "Your event '{$eventname}' was Accepted.",
        //         'created_at' => date('Y-m-d H:i:s') , 'display_status' => 1])
        //         ->update();
        
        //     if ($eventUpdate && $notificationUpdate) {
        //         // Get the email addresses
        //         $teacherEmailFrom = $this->teachersModel->where('id', $userId)->select('email')->first()['email'];
        //         $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        //         $teacherIdTo = $eventDetails['teacher_id'];
        //         $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];
        
        //         if ($teacherEmailFrom && $teacherEmailTo) {
        //             // Send email
        //             //$this->sendEventAcceptedEmail($teacherEmailFrom, $teacherEmailTo, $eventId);
        //         }
        
        //         return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
        //     }
        
        //     return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
        // }
        
        // private function sendEventAcceptedEmail($fromEmail, $toEmail, $eventId)
        // {
        //     // Load PHPMailer library
        //     $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        //     try {
        //         // SMTP configuration
        //         $mail->isSMTP();
        //         $mail->Host       = 'smtp.gmail.com';
        //         $mail->SMTPAuth   = true;
        //         $mail->Username   = $fromEmail; // Authentication email
        //         $mail->Password   = 'suji ukrf bwtb lcpp'; // App password for Gmail
        //         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        //         $mail->Port       = 587;
        
        //         // Sender and recipient settings
        //         $mail->setFrom($fromEmail, 'Event Organizer'); // Set dynamic sender email
        //         $mail->addAddress($toEmail); // Recipient's email
        
        //         // Email content
        //         $mail->isHTML(true); // Set email format to HTML
        //         $mail->Subject = 'Event Accepted'; // Subject
        //         $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong> has been accepted.</p>";
        //         $mail->AltBody = "Your event with ID: $eventId has been accepted."; // Fallback for non-HTML email clients
        
        //         // Send email
        //         $mail->send();
        //         log_message('info', 'Email sent successfully to ' . $toEmail);
        //         return true;
        
        //     } catch (\PHPMailer\PHPMailer\Exception $e) {
        //         // Log error message
        //         log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
        //         return false;
        //     }
        // }
        

    public function updateRegistrationAccept()
        {
            $request = $this->request->getJSON();

            // Validate input
            if (!isset($request->event_id) || !isset($request->userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
            }

            $eventId = $request->event_id;
            $userId = $request->userId;
            $eventname= $request->eventname;

            if (empty($eventId) || empty($userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
            }

            // Update event status
            $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);

            // Update notification table
            $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
                ->set(['accepted_by' => $userId, 'accepted_rejected' => 1, 'message' => "Your event '{$eventname}' was Accepted.",
                'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
                ->update();

            if ($eventUpdate && $notificationUpdate) {
                // Get the email and passkey addresses
                $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
                $teacherEmailFrom = $teacherDetailsFrom['email'];
                $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
                
                $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
                $teacherIdTo = $eventDetails['teacher_id'];
                $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

                 // Pass email and passkey to sendEventAcceptedEmail
                if ($teacherEmailFrom &&$teacherEmailTo) {
                    $this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId);
                } 
                //$this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId);

                return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
            }

            return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
        }

private function sendEventAcceptedEmail($fromEmail, $fromPasskey, $toEmail, $eventId)
{    
    $mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $fromEmail; // Sender's email
    $mail->Password   = $fromPasskey; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender and recipient settings
    $mail->setFrom($fromEmail, 'Event Organizer');
    $mail->addAddress($toEmail);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Event Accepted';
    $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong> has been accepted.</p>";
    $mail->AltBody = "Your event with ID: $eventId has been accepted.";

    // Send email
    $mail->send();
    //log_message('info', 'Email sent successfully to ' . $toEmail);
    return true;

} catch (Exception $e) {
    //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    return false;
}

}

        
        


    public function updateRegistrationReject()
    {
        $request = $this->request->getJSON();

        // Validate input
        if (!isset($request->event_id) || !isset($request->userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
        }

        $eventId = $request->event_id;
        $userId = $request->userId;
        $eventname= $request->eventname;
        $rejectionReason= $request->rejectionReason;

        //log_message('info', 'Received event_id: ' . $eventId);
        //log_message('info', 'Received user_id: ' . $userId);

        if (empty($eventId) || empty($userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
        }

        //$eventModel = new EventsModel();
        //$notificationModel = new NotificationModel();

        // Update event status
        $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 0]);

        // Update notification table
        $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
            ->set(['accepted_by' => $userId, 'accepted_rejected' => 0,'message' => "Your event '{$eventname}' was rejected. Reason: $rejectionReason",
            'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
            ->update();

        if ($eventUpdate && $notificationUpdate) {
              $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
        $teacherEmailFrom = $teacherDetailsFrom['email'];
        $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
        
        $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        $teacherIdTo = $eventDetails['teacher_id'];
        $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

        // Pass email and passkey to sendEventAcceptedEmail
        if ($teacherEmailFrom && $teacherEmailTo) {
            $this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason);
        } 
        //$this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason);

            return $this->respond(['status' => 'success', 'message' => 'Registration rejected successfully.'], 200);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
    }

        private function sendEventRejectedEmail($fromEmail, $fromPasskey, $toEmail, $eventId, $rejectionReason)
{    
    $mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $fromEmail; // Sender's email
    $mail->Password   = $fromPasskey; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender and recipient settings
    $mail->setFrom($fromEmail, 'Event Organizer');
    $mail->addAddress($toEmail);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Event Rejected';
    $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong> has been rejected due to <strong>$rejectionReason</strong>.</p>";
    $mail->AltBody = "Your event with ID: $eventId has been Rejected.";

    // Send email
    $mail->send();
    //log_message('info', 'Email sent successfully to ' . $toEmail);
    return true;

} catch (Exception $e) {
    //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    return false;
}

}
    
}












/* ************************************************************************************************************************* */












<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EventsController extends ResourceController
{
    protected $eventsModel;
    protected $teachersModel;
    protected $roomsModel;
    protected $departmentsModel;
    protected $eventTypesModel;
    protected $studentsModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->eventsModel = new \App\Models\EventsModel();   // Load the EventsModel
        $this->teachersModel = new \App\Models\TeachersModel(); // Load the TeachersModel
        $this->roomsModel = new \App\Models\RoomsModel();     // Load the RoomsModel
        $this->departmentsModel = new \App\Models\DepartmentModel();  
        $this->eventTypesModel = new \App\Models\EventTypeModel(); // Load the EventTypesModel
        $this->eventLevelsModel = new \App\Models\EventLevelsModel();
        $this->studentsModel = new \App\Models\StudentsModel();
        $this->notificationModel = new \App\Models\NotificationModel();
    }


    /**
     * Fetch all events.
     */
    public function index()
    {
        try {
            $events = $this->eventsModel->getEvents(); // Call the model's method

            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available venues based on input filters.
     */
    public function getAvailableVenues()
    {
        try {
            // Get input data from the request
            $input = $this->request->getJSON(true); // Accept JSON payload
    
            // Validate input data
            if (
                empty($input['start_date']) ||
                empty($input['end_date']) ||
                empty($input['start_time']) ||
                empty($input['end_time']) ||
                empty($input['room_type'])
            ) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'All filters are required!',
                ], 400); // HTTP 400 Bad Request
            }
    
            // Prepare filters for the model
            $filters = [
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'start_time' => $input['start_time'],
                'end_time' => $input['end_time'],
                'room_type' => $input['room_type'],
            ];
    
            // Call the model method to get available venues
            $availableVenues = $this->eventsModel->getAvailableVenues($filters);
    
            // Check if any venues are available
            if (empty($availableVenues)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No venues available for the given filters.'
                ], 404); // HTTP 404 Not Found
            }
    
            // Return the result as JSON
            return $this->respond([
                'status' => 'success',
                'data' => $availableVenues,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }
    






///////////////////////////////********************************* correct code bellow one */

//   public function create()
//   {
//       try {
//           $input = $this->request->getJSON(true);
          
//         // Check for duplicate event
//         $existingEvent = $this->eventsModel
//             ->where('event_name', $input['event_name'])
//             ->where('event_status', 1) // Check if event_status is 1
//             ->first();

//         if ($existingEvent) {
//             return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
//         }
//           // Fetch teacher details
//           $teacher = $this->teachersModel->find($input['teacher_id']);
//           if (!$teacher) {
//               return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
//           }
//           $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
//           $teacherEmail = $teacher['email']; // Fetch teacher's email
  
//           // Validate room_no and fetch room_id and room_owner
//           $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
//           if (!$room) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
//           }
//           $roomId = $room['id']; // Extract room ID
//           $roomOwnerId = $room['room_owner']; // Extract room owner ID
  
//           // Fetch room_owner's email
//           $roomOwner = $this->teachersModel->find($roomOwnerId);
//           if (!$roomOwner) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
//           }
//           $roomOwnerEmail = $roomOwner['email']; // Fetch room owner's email
  
//           // Validate dept_name and fetch dept_id
//           $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
//           if (!$department) {
//               return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
//           }
//           $deptId = $department['id']; // Extract department ID
  
//           // Fetch event type and validate
//           $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
//           if (!$eventType) {
//               return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
//           }
//           $eventTypeId = $eventType['id']; // Extract eventType ID
  
//           // Fetch event level and validate
//           $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
//           if (!$eventLevel) {
//               return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
//           }
//           $eventLevelId = $eventLevel['id']; // Extract eventLevel ID
  
//           // Prepare event data
//           $eventData = [
//               'teacher_id'   => $input['teacher_id'],
//               'room_id'      => $roomId,
//               'event_name'   => $input['event_name'],
//               'description'  => $input['description'],
//               'dept'         => $deptId,
//               'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
//               'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
//               'teacher_name' => $teacherFullName,
//               'eventType_id' => $eventTypeId,
//               'event_level_id' => $eventLevelId,
//               'eligible_dept'=>$input['eligibleDepartments'],
//           ];
  
//           // Insert event into the database
//           $eventId = $this->eventsModel->insertEvent($eventData);

  
//           if ($eventId) {
//             // Check if event level is 'National' (event_level_id = 1)
//             if ($eventLevelId == 1) {
//                 // Fetch students with subscribe = 1
//                 $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

//                 // Send email to each subscribed student
//                 foreach ($subscribedStudents as $student) {
//                     $this->sendEmailToStudent($student['email'], $eventData);
//                 }
//             }

//               // Send email from teacher to room owner
//              //$this->sendEmailFromTeacherToRoomOwner($teacherEmail, $roomOwnerEmail, $eventData,$teacherFullName);

//               // Check if event_status is 0, then create a notification
//               $event = $this->eventsModel->find($eventId);

//         // Check if event_status is 0, then create a notification
//             if ($event && $event['event_status'] == 0) {
//                 $notificationData = [
//                     'requested_by'         => $input['teacher_id'],
//                     'event_id'             => $eventId,
//                     'created_at'           => date('Y-m-d H:i:s'),
                    
//                 ];

//                 // Insert notification
//                 if (!$this->notificationModel->insert($notificationData)) {
//                     return $this->failServerError('Event created, but failed to create notification.');
//                 }
//             }
  
//               return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
//           }
  
//           return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
//       } catch (\Exception $e) {
//           return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
//       }
//   }
  
//   /**
//    * Sends an email from the teacher to the room owner.
//    *
//    * @param string $fromEmail
//    * @param string $toEmail
//    * @param array $eventData
//    * @return void
//    */
//   private function sendEmailFromTeacherToRoomOwner($fromEmail, $toEmail, $eventData)
//   {
//       $mail = new PHPMailer(true);
  
//       try {
//           // SMTP configuration
//           $mail->isSMTP();
//           $mail->Host       = 'smtp.gmail.com';
//           $mail->SMTPAuth   = true;
//           $mail->Username   = 'sakshishaw1375@gmail.com'; // Sender's email
//           $mail->Password   = 'suji ukrf bwtb lcpp'; // Sender's email app password 
//           $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//           $mail->Port       = 587;
  
//           // Email settings
//           $mail->setFrom($fromEmail, 'Event Management System');
//           $mail->addAddress($toEmail);
//           $mail->isHTML(true);
//           $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
//           $mail->Body    = "
//             <h3>New Event Scheduled in Your Room</h3>
//             <p>Event Name: {$eventData['event_name']}</p>
//             <p>Start Date: {$eventData['start_date']}</p>
//             <p>End Date: {$eventData['end_date']}</p>
//             <p>Created By: {$eventData['teacher_name']}</p>
//         ";
  
//           $mail->send();
//       } catch (Exception $e) {
//           //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
//       }
//   }
  
//////********************************************** correct code abpve one */



public function create()
{
    try {
        $input = $this->request->getJSON(true);

        // Check for duplicate event
        /* $existingEvent = $this->eventsModel
            ->where('event_name', $input['event_name'])
            ->where('event_status', 1) // Check if event_status is 1
            ->first(); */
        $existingEvent = $this->eventsModel
        ->select('events.id')
        ->join('notification', 'notification.event_id = events.id', 'left')
        ->where('events.event_name', $input['event_name'])
        ->groupStart()
            ->where('notification.accepted_rejected', 1) // Active event
            ->orWhere('notification.accepted_rejected IS NULL') // Pending  event
        ->groupEnd()
        ->first();    

        if ($existingEvent) {
            return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
        }

        // Fetch teacher details including passkey
        $teacher = $this->teachersModel->find($input['teacher_id']);
        if (!$teacher) {
            return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
        }
        $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
        $teacherEmail = $teacher['email'];
        $teacherPasskey = $teacher['passkey']; // Fetch teacher's passkey

        // Validate room_no and fetch room_id and room_owner
        $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
        if (!$room) {
            return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
        }
        $roomId = $room['id'];
        $roomOwnerId = $room['room_owner'];

        // Fetch room_owner's email and passkey
        $roomOwner = $this->teachersModel->find($roomOwnerId);
        if (!$roomOwner) {
            return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
        }
        $roomOwnerEmail = $roomOwner['email'];
        $roomOwnerPasskey = $roomOwner['passkey']; // Fetch room owner's passkey

        // Validate dept_name and fetch dept_id
        $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
        if (!$department) {
            return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
        }
        $deptId = $department['id'];

        // Fetch event type and validate
        $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
        if (!$eventType) {
            return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
        }
        $eventTypeId = $eventType['id'];

        // Fetch event level and validate
        $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
        if (!$eventLevel) {
            return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
        }
        $eventLevelId = $eventLevel['id'];

        // Prepare event data
        $eventData = [
            'teacher_id'   => $input['teacher_id'],
            'room_id'      => $roomId,
            'event_name'   => $input['event_name'],
            'description'  => $input['description'],
            'dept'         => $deptId,
            'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
            'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
            'teacher_name' => $teacherFullName,
            'eventType_id' => $eventTypeId,
            'event_level_id' => $eventLevelId,
            'eligible_dept'=> $input['eligibleDepartments'],
        ];

        // Insert event into the database
        $eventId = $this->eventsModel->insertEvent($eventData);

        if ($eventId) {
            // Check if event level is 'National' (event_level_id = 1)
            if ($eventLevelId == 1) {
                // Fetch students with subscribe = 1
                $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

                // Send email to each subscribed student
                foreach ($subscribedStudents as $student) {
                    $this->sendEmailToStudent($student['email'], $eventData);
                }
            }

            // Send email from teacher to room owner with teacher's email & passkey
            if ($teacherEmail && $roomOwnerEmail) {
                $this->sendEmailFromTeacherToRoomOwner($teacherEmail, $teacherPasskey, $roomOwnerEmail, $eventData);
            } 

            // Check if event_status is 0, then create a notification
            $event = $this->eventsModel->find($eventId);
            if ($event && $event['event_status'] == 0) {
                $notificationData = [
                    'requested_by' => $input['teacher_id'],
                    'event_id'     => $eventId,
                    'created_at'   => date('Y-m-d H:i:s'),
                ];

                // Insert notification
                if (!$this->notificationModel->insert($notificationData)) {
                    return $this->failServerError('Event created, but failed to create notification.');
                }
            }

            return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
        }

        return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

private function sendEmailFromTeacherToRoomOwner($fromEmail, $fromPasskey, $toEmail, $eventData)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $fromEmail; // Sender's email (Teacher's email)
        $mail->Password   = $fromPasskey; // Use teacher's passkey as password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
       // log_message('debug',$mail->Username, " " ,$mail->Password);
       // log_message('info',  'Passkey Type: ' . gettype($fromPasskey));
      // log_message('info', 'Email sent to ' . $toEmail);
        // Email settings
        $mail->setFrom($fromEmail, 'Event Management System');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
        $mail->Body    = "
            <h3>New Event Scheduled in Your Room</h3>
            <p>Event Name: {$eventData['event_name']}</p>
            <p>Start Date: {$eventData['start_date']}</p>
            <p>End Date: {$eventData['end_date']}</p>
            <p>Created By: {$eventData['teacher_name']}</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    }
}




 /**
  * Sends an email to a subscribed student.
  *
  * @param string $email
  * @param array $eventData
  * @return void
  */
 private function sendEmailToStudent($email, $eventData)
 {
     $mail = new PHPMailer(true);
 
     try {
         // SMTP configuration
         $mail->isSMTP();
         $mail->Host       = 'smtp.gmail.com';
         $mail->SMTPAuth   = true;
         $mail->Username   = 'sakshishaw1375@gmail.com'; // Replace with your email
         $mail->Password   = 'eekx rmku xarr hkab'; // Replace with your email password use App password
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port       = 587;
 
         // Email settings
         $mail->setFrom('sakshishaw1375@gmail.com', 'Event Management System');
         $mail->addAddress($email);
         $mail->isHTML(true);
         $mail->Subject = 'New National Event: ' . $eventData['event_name'];
         $mail->Body    = "
             <h3>New National Event Alert!</h3>
             <p>Event Name: {$eventData['event_name']}</p>
             <p>Description: {$eventData['description']}</p>
             <p>Start Date: {$eventData['start_date']}</p>
             <p>End Date: {$eventData['end_date']}</p>
             <p>Created By: {$eventData['teacher_name']}</p>
         ";
 
         $mail->send();
         //log_message('info', 'Email sent to ' . $email);
     } catch (Exception $e) {
         //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
     }
 }




public function uploadEventImage()
{
    try {
        // Get the uploaded image file
        $imageFile = $this->request->getFile('event_image');
        
        // Check if a file is uploaded
        if (!$this->request->getFile('event_image')) {
            return $this->respond([
                'status' => false,
                'message' => 'No image file uploaded.'
            ], Response::HTTP_BAD_REQUEST);
        }
        if (!$imageFile->isValid() || $imageFile->hasMoved()) {
            return $this->respond([
                'status' => false,
                'message' => $imageFile->getErrorString()
            ], Response::HTTP_BAD_REQUEST);
        }

        // Fetch the highest ID from the events table
        $db = \Config\Database::connect();
        $builder = $db->table('events');
        $builder->selectMax('id');
        $query = $builder->get();
        $result = $query->getRow();
        $eventId = $result->id;

        // Fetch the event_name based on the highest event ID
        $builder->select('event_name');
        $builder->where('id', $eventId);
        $query = $builder->get();
        $event = $query->getRow();

        if (!$event) {
            return $this->respond([
                'status' => false,
                'message' => 'Event not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $eventName = $event->event_name;

        // Generate a new name for the image based on the event_name
        $eventNameWords = explode(' ', $eventName);
        $imageNewName = '';
        foreach ($eventNameWords as $word) {
            $imageNewName .= strtoupper(substr($word, 0, 1)); // Take the first letter of each word
        }
        $imageNewName .= '-' . uniqid(); // Add a unique ID to the end

        // Define the upload path
        $uploadPath = FCPATH . 'public/uploads/event_images/';

        // Ensure the directory exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Move the file to the upload directory with the new name
        $imageFile->move($uploadPath, $imageNewName );

        // Store the new image name in the database
        $builder = $db->table('events');
        $builder->set('imagename', $imageNewName );
        $builder->where('id', $eventId);
        $builder->update();

        // Get the file's relative URL for frontend access
        $fileUrl = base_url('public/uploads/event_images/' . $imageNewName);

        // Return success response
        return $this->respond([
            'status' => true,
            'message' => 'Image uploaded and name stored successfully.',
            'file_url' => $fileUrl // Return the file's URL
        ], 200);
    } catch (\Exception $e) {
        // Handle any exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    

public function getRecentPastEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getRecentPastEvents($currentDate);

            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], 404);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], 200);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

public function getEventsByTeacher($teacherId)
{
    try {
        // Call the model method to fetch events by teacher_id
        $events = $this->eventsModel->getEventsByTeacher($teacherId);

        // Check if any events were retrieved
        if (empty($events)) {
            return $this->respond([
                'status' => false,
                'message' => 'No events found for this teacher.'
            ], 404);
        }

         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
    
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
             $deptNames = [];
 
             // Fetch department names for all eligible_dept IDs
             if (!empty($eligibleDeptIds)) {
                 $departments = $this->departmentsModel
                     ->whereIn('id', $eligibleDeptIds)
                     ->findAll();
 
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
 
             // Join the department names with commas
             $event['eligible_dept'] = implode(', ', $deptNames);
         }

        // Return the events as JSON
        return $this->respond([
            'status' => true,
            'data' => $events
        ], 200);
    } catch (\Exception $e) {
        // Handle exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


/*     public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } */




    public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');
    
            // Call the model method to fetch upcoming events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);
    
            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No upcoming events found.'
                ], Response::HTTP_NOT_FOUND);
            }
    
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }
    
            // Return the events with mapped department names
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    




    // get requested events for room admin
    public function getEventByRoomOwner($room_owner_id)
    {


        // Step 1: Get all room ids where room_owner matches the passed room_owner_id
        $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);

        // Step 2: Get events based on room_ids with event_status = 0
        $events = $this->eventsModel->getEventsByRoomIdsAndStatus($roomIds);
         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
        
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
             $deptNames = [];
     
             if (!empty($eligibleDeptIds)) {
                 // Fetch department names
                 $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
     
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
     
             // Replace eligible_dept with department names
             $event->eligible_dept = implode(', ', $deptNames);
         }

        // Return events data
        return $this->response->setJSON($events);
    }


        // get requested events for room admin
        public function getAcceptedEventByRoomOwner($room_owner_id)
        {
    
    
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
    
            // Step 2: Get events based on room_ids with event_status = 1
            $events = $this->eventsModel->getAcceptedEvents($roomIds, $room_owner_id);
             // Initialize the DepartmentModel
             $this->departmentsModel = new \App\Models\DepartmentModel();
        
             // Map the eligible_dept to department names
             foreach ($events as &$event) {
                 $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                 $deptNames = [];
         
                 if (!empty($eligibleDeptIds)) {
                     // Fetch department names
                     $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
         
                     // Extract department names
                     foreach ($departments as $dept) {
                         $deptNames[] = $dept['dept_name'];
                     }
                 }
         
                 // Replace eligible_dept with department names
                 $event->eligible_dept = implode(', ', $deptNames);
             }
    
            // Return events data
            return $this->response->setJSON($events);
        }


        public function getRejectedEvents($room_owner_id)
        {
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
        
            // Step 2: Get events based on room_ids with event_status = 0
            $events = $this->eventsModel->getRejectedEvents($roomIds, $room_owner_id);
        
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
        
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                $deptNames = [];
        
                if (!empty($eligibleDeptIds)) {
                    // Fetch department names
                    $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
        
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
        
                // Replace eligible_dept with department names
                $event->eligible_dept = implode(', ', $deptNames);
            }
        
            // Return events data
            return $this->response->setJSON($events);
        }






        // public function updateRegistrationAccept()
        // {
        //     $request = $this->request->getJSON();
        
        //     // Validate input
        //     if (!isset($request->event_id) || !isset($request->userId)) {
        //         return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
        //     }
        
        //     $eventId = $request->event_id;
        //     $userId = $request->userId;
        //     $eventname= $request->eventname;

        
        //     if (empty($eventId) || empty($userId)) {
        //         return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
        //     }
        
        //     // Update event status
        //     $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);
        
        //     // Update notification table
        //     $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
        //         ->set(['accepted_by' => $userId, 'accepted_rejected' => 1,'message' => "Your event '{$eventname}' was Accepted.",
        //         'created_at' => date('Y-m-d H:i:s') , 'display_status' => 1])
        //         ->update();
        
        //     if ($eventUpdate && $notificationUpdate) {
        //         // Get the email addresses
        //         $teacherEmailFrom = $this->teachersModel->where('id', $userId)->select('email')->first()['email'];
        //         $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        //         $teacherIdTo = $eventDetails['teacher_id'];
        //         $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];
        
        //         if ($teacherEmailFrom && $teacherEmailTo) {
        //             // Send email
        //             //$this->sendEventAcceptedEmail($teacherEmailFrom, $teacherEmailTo, $eventId);
        //         }
        
        //         return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
        //     }
        
        //     return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
        // }
        
        // private function sendEventAcceptedEmail($fromEmail, $toEmail, $eventId)
        // {
        //     // Load PHPMailer library
        //     $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        //     try {
        //         // SMTP configuration
        //         $mail->isSMTP();
        //         $mail->Host       = 'smtp.gmail.com';
        //         $mail->SMTPAuth   = true;
        //         $mail->Username   = $fromEmail; // Authentication email
        //         $mail->Password   = 'suji ukrf bwtb lcpp'; // App password for Gmail
        //         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        //         $mail->Port       = 587;
        
        //         // Sender and recipient settings
        //         $mail->setFrom($fromEmail, 'Event Organizer'); // Set dynamic sender email
        //         $mail->addAddress($toEmail); // Recipient's email
        
        //         // Email content
        //         $mail->isHTML(true); // Set email format to HTML
        //         $mail->Subject = 'Event Accepted'; // Subject
        //         $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong> has been accepted.</p>";
        //         $mail->AltBody = "Your event with ID: $eventId has been accepted."; // Fallback for non-HTML email clients
        
        //         // Send email
        //         $mail->send();
        //         log_message('info', 'Email sent successfully to ' . $toEmail);
        //         return true;
        
        //     } catch (\PHPMailer\PHPMailer\Exception $e) {
        //         // Log error message
        //         log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
        //         return false;
        //     }
        // }
        

    public function updateRegistrationAccept()
        {
            $request = $this->request->getJSON();

            // Validate input
            if (!isset($request->event_id) || !isset($request->userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
            }

            $eventId = $request->event_id;
            $userId = $request->userId;
            $eventname= $request->eventname;

            if (empty($eventId) || empty($userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
            }

            // Update event status
            $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);

            // Update notification table
            $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
                ->set(['accepted_by' => $userId, 'accepted_rejected' => 1, 'message' => "Your event '{$eventname}' was Accepted.",
                'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
                ->update();

            if ($eventUpdate && $notificationUpdate) {
                // Get the email and passkey addresses
                $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
                $teacherEmailFrom = $teacherDetailsFrom['email'];
                $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
                
                $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
                $teacherIdTo = $eventDetails['teacher_id'];
                $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

                 // Pass email and passkey to sendEventAcceptedEmail
                 if ($teacherEmailFrom && $teacherEmailTo) {
                    $this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $eventname);
                }  
                //$this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId);

                return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
            }

            return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
        }

private function sendEventAcceptedEmail($fromEmail, $fromPasskey, $toEmail, $eventId,$eventname)
{    
    $mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $fromEmail; // Sender's email
    $mail->Password   = $fromPasskey; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender and recipient settings
    $mail->setFrom($fromEmail, 'Event Organizer');
    $mail->addAddress($toEmail);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Event Accepted';
     $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong>.</p>
     <p>Event Name : $eventname has been accepted.</p>";
     //$mail->AltBody = "<p>Your event : $eventname has been accepted.</p>";
    //$mail->Body= "<p>Event Name: {$eventname['event_name']}</p>";

    // Send email
    $mail->send();
    //log_message('info', 'Email sent successfully to ' . $toEmail);
    return true;

} catch (Exception $e) {
    //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    return false;
}

}


    public function updateRegistrationReject()
    {
        $request = $this->request->getJSON();

        // Validate input
        if (!isset($request->event_id) || !isset($request->userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
        }

        $eventId = $request->event_id;
        $userId = $request->userId;
        $eventname= $request->eventname;
        $rejectionReason= $request->rejectionReason;

        //log_message('info', 'Received event_id: ' . $eventId);
        //log_message('info', 'Received user_id: ' . $userId);

        if (empty($eventId) || empty($userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
        }

        //$eventModel = new EventsModel();
        //$notificationModel = new NotificationModel();

        // Update event status
        $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 0]);

        // Update notification table
        $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
            ->set(['accepted_by' => $userId, 'accepted_rejected' => 0,'message' => "Your event '{$eventname}' was rejected. Reason: $rejectionReason",
            'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
            ->update();

        if ($eventUpdate && $notificationUpdate) {
              $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
        $teacherEmailFrom = $teacherDetailsFrom['email'];
        $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
        
        $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        $teacherIdTo = $eventDetails['teacher_id'];
        $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

        // Pass email and passkey to sendEventAcceptedEmail
        if ($teacherEmailFrom && $teacherEmailTo) {
            $this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason,$eventname);
        } 
        //$this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason);

            return $this->respond(['status' => 'success', 'message' => 'Registration rejected successfully.'], 200);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
    }

    private function sendEventRejectedEmail($fromEmail, $fromPasskey, $toEmail, $eventId, $rejectionReason,$eventname)
    {    
        $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $fromEmail; // Sender's email
        $mail->Password   = $fromPasskey; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient settings
        $mail->setFrom($fromEmail, 'Event Organizer');
        $mail->addAddress($toEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Event Rejected';
        $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong><p/>
        <p>Event name: <strong>$eventname</strong> has been rejected due to <strong>$rejectionReason</strong>.</p>";
        //$mail->AltBody = "Your event with ID: $eventId has been Rejected.";

        // Send email
        $mail->send();
        //log_message('info', 'Email sent successfully to ' . $toEmail);
        return true;

    } catch (Exception $e) {
        //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
        return false;
    }

    }


    public function pendingEvents($teacherId)
    {
        $events = $this->eventsModel->getPendingEventsByTeacher($teacherId);
    
        if (empty($events)) {
            return $this->respond(['success' => false, 'message' => 'No pending events found'], 404);
        }
        return $this->respond([
            'success' => true,
            'data' => $events,
        ], 200);
    
        
    }
    

    
}




/* ********************************************************************************************************************* */















<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EventsController extends ResourceController
{
    protected $eventsModel;
    protected $teachersModel;
    protected $roomsModel;
    protected $departmentsModel;
    protected $eventTypesModel;
    protected $studentsModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->eventsModel = new \App\Models\EventsModel();   // Load the EventsModel
        $this->teachersModel = new \App\Models\TeachersModel(); // Load the TeachersModel
        $this->roomsModel = new \App\Models\RoomsModel();     // Load the RoomsModel
        $this->departmentsModel = new \App\Models\DepartmentModel();  
        $this->eventTypesModel = new \App\Models\EventTypeModel(); // Load the EventTypesModel
        $this->eventLevelsModel = new \App\Models\EventLevelsModel();
        $this->studentsModel = new \App\Models\StudentsModel();
        $this->notificationModel = new \App\Models\NotificationModel();
    }


    /**
     * Fetch all events.
     */
    public function index()
    {
        try {
            $events = $this->eventsModel->getEvents(); // Call the model's method

            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available venues based on input filters.
     */
    public function getAvailableVenues()
    {
        try {
            // Get input data from the request
            $input = $this->request->getJSON(true); // Accept JSON payload
    
            // Validate input data
            if (
                empty($input['start_date']) ||
                empty($input['end_date']) ||
                empty($input['start_time']) ||
                empty($input['end_time']) ||
                empty($input['room_type'])
            ) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'All filters are required!',
                ], 400); // HTTP 400 Bad Request
            }
    
            // Prepare filters for the model
            $filters = [
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'start_time' => $input['start_time'],
                'end_time' => $input['end_time'],
                'room_type' => $input['room_type'],
            ];
    
            // Call the model method to get available venues
            $availableVenues = $this->eventsModel->getAvailableVenues($filters);
    
            // Check if any venues are available
            if (empty($availableVenues)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No venues available for the given filters.'
                ], 404); // HTTP 404 Not Found
            }
    
            // Return the result as JSON
            return $this->respond([
                'status' => 'success',
                'data' => $availableVenues,
            ], 200); // HTTP 200 OK
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }
    






///////////////////////////////********************************* correct code bellow one */

//   public function create()
//   {
//       try {
//           $input = $this->request->getJSON(true);
          
//         // Check for duplicate event
//         $existingEvent = $this->eventsModel
//             ->where('event_name', $input['event_name'])
//             ->where('event_status', 1) // Check if event_status is 1
//             ->first();

//         if ($existingEvent) {
//             return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
//         }
//           // Fetch teacher details
//           $teacher = $this->teachersModel->find($input['teacher_id']);
//           if (!$teacher) {
//               return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
//           }
//           $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
//           $teacherEmail = $teacher['email']; // Fetch teacher's email
  
//           // Validate room_no and fetch room_id and room_owner
//           $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
//           if (!$room) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
//           }
//           $roomId = $room['id']; // Extract room ID
//           $roomOwnerId = $room['room_owner']; // Extract room owner ID
  
//           // Fetch room_owner's email
//           $roomOwner = $this->teachersModel->find($roomOwnerId);
//           if (!$roomOwner) {
//               return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
//           }
//           $roomOwnerEmail = $roomOwner['email']; // Fetch room owner's email
  
//           // Validate dept_name and fetch dept_id
//           $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
//           if (!$department) {
//               return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
//           }
//           $deptId = $department['id']; // Extract department ID
  
//           // Fetch event type and validate
//           $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
//           if (!$eventType) {
//               return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
//           }
//           $eventTypeId = $eventType['id']; // Extract eventType ID
  
//           // Fetch event level and validate
//           $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
//           if (!$eventLevel) {
//               return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
//           }
//           $eventLevelId = $eventLevel['id']; // Extract eventLevel ID
  
//           // Prepare event data
//           $eventData = [
//               'teacher_id'   => $input['teacher_id'],
//               'room_id'      => $roomId,
//               'event_name'   => $input['event_name'],
//               'description'  => $input['description'],
//               'dept'         => $deptId,
//               'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
//               'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
//               'teacher_name' => $teacherFullName,
//               'eventType_id' => $eventTypeId,
//               'event_level_id' => $eventLevelId,
//               'eligible_dept'=>$input['eligibleDepartments'],
//           ];
  
//           // Insert event into the database
//           $eventId = $this->eventsModel->insertEvent($eventData);

  
//           if ($eventId) {
//             // Check if event level is 'National' (event_level_id = 1)
//             if ($eventLevelId == 1) {
//                 // Fetch students with subscribe = 1
//                 $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

//                 // Send email to each subscribed student
//                 foreach ($subscribedStudents as $student) {
//                     $this->sendEmailToStudent($student['email'], $eventData);
//                 }
//             }

//               // Send email from teacher to room owner
//              //$this->sendEmailFromTeacherToRoomOwner($teacherEmail, $roomOwnerEmail, $eventData,$teacherFullName);

//               // Check if event_status is 0, then create a notification
//               $event = $this->eventsModel->find($eventId);

//         // Check if event_status is 0, then create a notification
//             if ($event && $event['event_status'] == 0) {
//                 $notificationData = [
//                     'requested_by'         => $input['teacher_id'],
//                     'event_id'             => $eventId,
//                     'created_at'           => date('Y-m-d H:i:s'),
                    
//                 ];

//                 // Insert notification
//                 if (!$this->notificationModel->insert($notificationData)) {
//                     return $this->failServerError('Event created, but failed to create notification.');
//                 }
//             }
  
//               return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
//           }
  
//           return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
//       } catch (\Exception $e) {
//           return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
//       }
//   }
  
//   /**
//    * Sends an email from the teacher to the room owner.
//    *
//    * @param string $fromEmail
//    * @param string $toEmail
//    * @param array $eventData
//    * @return void
//    */
//   private function sendEmailFromTeacherToRoomOwner($fromEmail, $toEmail, $eventData)
//   {
//       $mail = new PHPMailer(true);
  
//       try {
//           // SMTP configuration
//           $mail->isSMTP();
//           $mail->Host       = 'smtp.gmail.com';
//           $mail->SMTPAuth   = true;
//           $mail->Username   = 'sakshishaw1375@gmail.com'; // Sender's email
//           $mail->Password   = 'suji ukrf bwtb lcpp'; // Sender's email app password 
//           $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//           $mail->Port       = 587;
  
//           // Email settings
//           $mail->setFrom($fromEmail, 'Event Management System');
//           $mail->addAddress($toEmail);
//           $mail->isHTML(true);
//           $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
//           $mail->Body    = "
//             <h3>New Event Scheduled in Your Room</h3>
//             <p>Event Name: {$eventData['event_name']}</p>
//             <p>Start Date: {$eventData['start_date']}</p>
//             <p>End Date: {$eventData['end_date']}</p>
//             <p>Created By: {$eventData['teacher_name']}</p>
//         ";
  
//           $mail->send();
//       } catch (Exception $e) {
//           //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
//       }
//   }
  
//////********************************************** correct code abpve one */



public function create()
{
    try {
        $input = $this->request->getJSON(true);

        // Check for duplicate event
        /* $existingEvent = $this->eventsModel
            ->where('event_name', $input['event_name'])
            ->where('event_status', 1) // Check if event_status is 1
            ->first(); */
        $existingEvent = $this->eventsModel
        ->select('events.id')
        ->join('notification', 'notification.event_id = events.id', 'left')
        ->where('events.event_name', $input['event_name'])
        ->groupStart()
            ->where('notification.accepted_rejected', 1) // Active event
            ->orWhere('notification.accepted_rejected IS NULL') // Pending  event
        ->groupEnd()
        ->first();    

        if ($existingEvent) {
            return $this->respond(['status' => false, 'message' => 'Event already exists with an active status.'], 400);
        }

        // Fetch teacher details including passkey
        $teacher = $this->teachersModel->find($input['teacher_id']);
        if (!$teacher) {
            return $this->respond(['status' => false, 'message' => 'Invalid teacher ID.'], 404);
        }
        $teacherFullName = $teacher['firstName'] . ' ' . $teacher['lastName'];
        $teacherEmail = $teacher['email'];
        $teacherPasskey = $teacher['passkey']; // Fetch teacher's passkey

        // Validate room_no and fetch room_id and room_owner
        $room = $this->roomsModel->where('room_no', $input['room_no'])->first();
        if (!$room) {
            return $this->respond(['status' => false, 'message' => 'Invalid room number.'], 404);
        }
        $roomId = $room['id'];
        $roomOwnerId = $room['room_owner'];

        // Fetch room_owner's email and passkey
        $roomOwner = $this->teachersModel->find($roomOwnerId);
        if (!$roomOwner) {
            return $this->respond(['status' => false, 'message' => 'Invalid room owner.'], 404);
        }
        $roomOwnerEmail = $roomOwner['email'];
        $roomOwnerPasskey = $roomOwner['passkey']; // Fetch room owner's passkey

        // Validate dept_name and fetch dept_id
        $department = $this->departmentsModel->where('dept_name', $input['dept_name'])->first();
        if (!$department) {
            return $this->respond(['status' => false, 'message' => 'Invalid department name.'], 404);
        }
        $deptId = $department['id'];

        // Fetch event type and validate
        $eventType = $this->eventTypesModel->where('eventType', $input['eventType'])->first();
        if (!$eventType) {
            return $this->respond(['status' => false, 'message' => 'Event type not found.'], 404);
        }
        $eventTypeId = $eventType['id'];

        // Fetch event level and validate
        $eventLevel = $this->eventLevelsModel->where('event_level', $input['event_level'])->first();
        if (!$eventLevel) {
            return $this->respond(['status' => false, 'message' => 'Event level not found.'], 404);
        }
        $eventLevelId = $eventLevel['id'];

        // Prepare event data
        $eventData = [
            'teacher_id'   => $input['teacher_id'],
            'room_id'      => $roomId,
            'event_name'   => $input['event_name'],
            'description'  => $input['description'],
            'dept'         => $deptId,
            'start_date'   => $input['start_date'] . ' ' . $input['start_time'],
            'end_date'     => $input['end_date'] . ' ' . $input['end_time'],
            'teacher_name' => $teacherFullName,
            'eventType_id' => $eventTypeId,
            'event_level_id' => $eventLevelId,
            'eligible_dept'=> $input['eligibleDepartments'],
        ];

        // Insert event into the database
        $eventId = $this->eventsModel->insertEvent($eventData);

        if ($eventId) {
            // Check if event level is 'National' (event_level_id = 1)
            if ($eventLevelId == 1) {
                // Fetch students with subscribe = 1
                $subscribedStudents = $this->studentsModel->where('subscribe', 1)->findAll();

                // Send email to each subscribed student
                foreach ($subscribedStudents as $student) {
                    $this->sendEmailToStudent($student['email'], $eventData);
                }
            }

            // Send email from teacher to room owner with teacher's email & passkey
            if ($teacherEmail && $roomOwnerEmail) {
                $this->sendEmailFromTeacherToRoomOwner($teacherEmail, $teacherPasskey, $roomOwnerEmail, $eventData);
            } 

            // Check if event_status is 0, then create a notification
            $event = $this->eventsModel->find($eventId);
            if ($event && $event['event_status'] == 0) {
                $notificationData = [
                    'requested_by' => $input['teacher_id'],
                    'event_id'     => $eventId,
                    'created_at'   => date('Y-m-d H:i:s'),
                ];

                // Insert notification
                if (!$this->notificationModel->insert($notificationData)) {
                    return $this->failServerError('Event created, but failed to create notification.');
                }
            }

            return $this->respond(['status' => true, 'message' => 'Event created successfully.', 'event_id' => $eventId], 200);
        }

        return $this->respond(['status' => false, 'message' => 'Failed to create event.'], 500);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

private function sendEmailFromTeacherToRoomOwner($fromEmail, $fromPasskey, $toEmail, $eventData)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $fromEmail; // Sender's email (Teacher's email)
        $mail->Password   = $fromPasskey; // Use teacher's passkey as password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
       // log_message('debug',$mail->Username, " " ,$mail->Password);
       // log_message('info',  'Passkey Type: ' . gettype($fromPasskey));
      // log_message('info', 'Email sent to ' . $toEmail);
        // Email settings
        $mail->setFrom($fromEmail, 'Event Management System');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'New Event Created: ' . $eventData['event_name'];
        $mail->Body    = "
            <h3>New Event Scheduled in Your Room</h3>
            <p>Event Name: {$eventData['event_name']}</p>
            <p>Start Date: {$eventData['start_date']}</p>
            <p>End Date: {$eventData['end_date']}</p>
            <p>Created By: {$eventData['teacher_name']}</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
    }
}




 /**
  * Sends an email to a subscribed student.
  *
  * @param string $email
  * @param array $eventData
  * @return void
  */
 private function sendEmailToStudent($email, $eventData)
 {
     $mail = new PHPMailer(true);
 
     try {
         // SMTP configuration
         $mail->isSMTP();
         $mail->Host       = 'smtp.gmail.com';
         $mail->SMTPAuth   = true;
         $mail->Username   = 'sakshishaw1375@gmail.com'; // Replace with your email
         $mail->Password   = 'eekx rmku xarr hkab'; // Replace with your email password use App password
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port       = 587;
 
         // Email settings
         $mail->setFrom('sakshishaw1375@gmail.com', 'Event Management System');
         $mail->addAddress($email);
         $mail->isHTML(true);
         $mail->Subject = 'New National Event: ' . $eventData['event_name'];
         $mail->Body    = "
             <h3>New National Event Alert!</h3>
             <p>Event Name: {$eventData['event_name']}</p>
             <p>Description: {$eventData['description']}</p>
             <p>Start Date: {$eventData['start_date']}</p>
             <p>End Date: {$eventData['end_date']}</p>
             <p>Created By: {$eventData['teacher_name']}</p>
         ";
 
         $mail->send();
         //log_message('info', 'Email sent to ' . $email);
     } catch (Exception $e) {
         //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
     }
 }




public function uploadEventImage()
{
    try {
        // Get the uploaded image file
        $imageFile = $this->request->getFile('event_image');
        
        // Check if a file is uploaded
        if (!$this->request->getFile('event_image')) {
            return $this->respond([
                'status' => false,
                'message' => 'No image file uploaded.'
            ], Response::HTTP_BAD_REQUEST);
        }
        if (!$imageFile->isValid() || $imageFile->hasMoved()) {
            return $this->respond([
                'status' => false,
                'message' => $imageFile->getErrorString()
            ], Response::HTTP_BAD_REQUEST);
        }

        // Fetch the highest ID from the events table
        $db = \Config\Database::connect();
        $builder = $db->table('events');
        $builder->selectMax('id');
        $query = $builder->get();
        $result = $query->getRow();
        $eventId = $result->id;

        // Fetch the event_name based on the highest event ID
        $builder->select('event_name');
        $builder->where('id', $eventId);
        $query = $builder->get();
        $event = $query->getRow();

        if (!$event) {
            return $this->respond([
                'status' => false,
                'message' => 'Event not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $eventName = $event->event_name;

        // Generate a new name for the image based on the event_name
        $eventNameWords = explode(' ', $eventName);
        $imageNewName = '';
        foreach ($eventNameWords as $word) {
            $imageNewName .= strtoupper(substr($word, 0, 1)); // Take the first letter of each word
        }
        $imageNewName .= '-' . uniqid(); // Add a unique ID to the end

        // Define the upload path
        $uploadPath = FCPATH . 'public/uploads/event_images/';

        // Ensure the directory exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Move the file to the upload directory with the new name
        $imageFile->move($uploadPath, $imageNewName );

        // Store the new image name in the database
        $builder = $db->table('events');
        $builder->set('imagename', $imageNewName );
        $builder->where('id', $eventId);
        $builder->update();

        // Get the file's relative URL for frontend access
        $fileUrl = base_url('public/uploads/event_images/' . $imageNewName);

        // Return success response
        return $this->respond([
            'status' => true,
            'message' => 'Image uploaded and name stored successfully.',
            'file_url' => $fileUrl // Return the file's URL
        ], 200);
    } catch (\Exception $e) {
        // Handle any exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    

public function getRecentPastEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getRecentPastEvents($currentDate);

            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], 404);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], 200);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

public function getEventsByTeacher($teacherId)
{
    try {
        // Call the model method to fetch events by teacher_id
        $events = $this->eventsModel->getEventsByTeacher($teacherId);

        // Check if any events were retrieved
        if (empty($events)) {
            return $this->respond([
                'status' => false,
                'message' => 'No events found for this teacher.'
            ], 404);
        }

         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
    
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
             $deptNames = [];
 
             // Fetch department names for all eligible_dept IDs
             if (!empty($eligibleDeptIds)) {
                 $departments = $this->departmentsModel
                     ->whereIn('id', $eligibleDeptIds)
                     ->findAll();
 
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
 
             // Join the department names with commas
             $event['eligible_dept'] = implode(', ', $deptNames);
         }

        // Return the events as JSON
        return $this->respond([
            'status' => true,
            'data' => $events
        ], 200);
    } catch (\Exception $e) {
        // Handle exceptions
        return $this->respond([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


/*     public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');

            // Call the model method to fetch past events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);

            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No past events found.'
                ], Response::HTTP_NOT_FOUND);
            }

            // Return the events as JSON
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {   
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } */




    public function getUpcomingEvents()
    {
        try {
            // Current date and time
            $currentDate = date('Y-m-d H:i:s');
    
            // Call the model method to fetch upcoming events
            $events = $this->eventsModel->getUpcomingEvents($currentDate);
    
            // Check if any events were retrieved
            if (empty($events)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No upcoming events found.'
                ], Response::HTTP_NOT_FOUND);
            }
    
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
    
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event['eligible_dept']); // Split the eligible_dept string into an array of IDs
                $deptNames = [];
    
                // Fetch department names for all eligible_dept IDs
                if (!empty($eligibleDeptIds)) {
                    $departments = $this->departmentsModel
                        ->whereIn('id', $eligibleDeptIds)
                        ->findAll();
    
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
    
                // Join the department names with commas
                $event['eligible_dept'] = implode(', ', $deptNames);
            }
    
            // Return the events with mapped department names
            return $this->respond([
                'status' => true,
                'data' => $events
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle exceptions
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    




    // get requested events for room admin
    public function getEventByRoomOwner($room_owner_id)
    {


        // Step 1: Get all room ids where room_owner matches the passed room_owner_id
        $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);

        // Step 2: Get events based on room_ids with event_status = 0
        $events = $this->eventsModel->getEventsByRoomIdsAndStatus($roomIds);
         // Initialize the DepartmentModel
         $this->departmentsModel = new \App\Models\DepartmentModel();
        
         // Map the eligible_dept to department names
         foreach ($events as &$event) {
             $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
             $deptNames = [];
     
             if (!empty($eligibleDeptIds)) {
                 // Fetch department names
                 $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
     
                 // Extract department names
                 foreach ($departments as $dept) {
                     $deptNames[] = $dept['dept_name'];
                 }
             }
     
             // Replace eligible_dept with department names
             $event->eligible_dept = implode(', ', $deptNames);
         }

        // Return events data
        return $this->response->setJSON($events);
    }


        // get requested events for room admin
        public function getAcceptedEventByRoomOwner($room_owner_id)
        {
    
    
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
    
            // Step 2: Get events based on room_ids with event_status = 1
            $events = $this->eventsModel->getAcceptedEvents($roomIds, $room_owner_id);
             // Initialize the DepartmentModel
             $this->departmentsModel = new \App\Models\DepartmentModel();
        
             // Map the eligible_dept to department names
             foreach ($events as &$event) {
                 $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                 $deptNames = [];
         
                 if (!empty($eligibleDeptIds)) {
                     // Fetch department names
                     $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
         
                     // Extract department names
                     foreach ($departments as $dept) {
                         $deptNames[] = $dept['dept_name'];
                     }
                 }
         
                 // Replace eligible_dept with department names
                 $event->eligible_dept = implode(', ', $deptNames);
             }
    
            // Return events data
            return $this->response->setJSON($events);
        }


        public function getRejectedEvents($room_owner_id)
        {
            // Step 1: Get all room ids where room_owner matches the passed room_owner_id
            $roomIds = $this->roomsModel->getRoomIdsByOwner($room_owner_id);
        
            // Step 2: Get events based on room_ids with event_status = 0
            $events = $this->eventsModel->getRejectedEvents($roomIds, $room_owner_id);
        
            // Initialize the DepartmentModel
            $this->departmentsModel = new \App\Models\DepartmentModel();
        
            // Map the eligible_dept to department names
            foreach ($events as &$event) {
                $eligibleDeptIds = explode(',', $event->eligible_dept); // Convert eligible_dept to an array of IDs
                $deptNames = [];
        
                if (!empty($eligibleDeptIds)) {
                    // Fetch department names
                    $departments = $this->departmentsModel->whereIn('id', $eligibleDeptIds)->findAll();
        
                    // Extract department names
                    foreach ($departments as $dept) {
                        $deptNames[] = $dept['dept_name'];
                    }
                }
        
                // Replace eligible_dept with department names
                $event->eligible_dept = implode(', ', $deptNames);
            }
        
            // Return events data
            return $this->response->setJSON($events);
        }

        

/*     public function updateRegistrationAccept()
        {
            $request = $this->request->getJSON();

            // Validate input
            if (!isset($request->event_id) || !isset($request->userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
            }

            $eventId = $request->event_id;
            $userId = $request->userId;
            $eventname= $request->eventname;

            if (empty($eventId) || empty($userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
            }

            // Update event status
            $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);

            // Update notification table
            $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
                ->set(['accepted_by' => $userId, 'accepted_rejected' => 1, 'message' => "Your event '{$eventname}' was Accepted.",
                'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
                ->update();

            if ($eventUpdate && $notificationUpdate) {
                // Get the email and passkey addresses
                $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
                $teacherEmailFrom = $teacherDetailsFrom['email'];
                $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
                
                $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
                $teacherIdTo = $eventDetails['teacher_id'];
                $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

                 // Pass email and passkey to sendEventAcceptedEmail
                 if ($teacherEmailFrom && $teacherEmailTo) {
                    $this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $eventname);
                }  
                //$this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId);

                return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
            }

            return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
        } */


        public function updateRegistrationAccept()
        {
            
            $request = $this->request->getJSON();
        
            // Validate input
            if (!isset($request->event_id) || !isset($request->userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
            }
        
            $eventId = $request->event_id;
            $userId = $request->userId;
            $eventname = $request->eventname;
        
            if (empty($eventId) || empty($userId)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
            }
        
            // Get event details
            $event = $this->eventsModel->where('id', $eventId)->first();
            if (!$event) {
                return $this->respond(['status' => 'error', 'message' => 'Event not found.'], 404);
            }
        
            $startDate = $event['start_date'];
            $endDate = $event['end_date'];
            $roomId = $event['room_id'];
        
// Debugging: Log event details
log_message('debug', 'Checking for conflicts with event: ' . json_encode($event));

$conflictingEvents = $this->eventsModel
    ->where('room_id', $roomId)
    ->groupStart()
        ->where("start_date <=", $startDate)  // Check for overlap with start date
        ->where("end_date >=", $startDate)  
        ->orWhere("start_date <=", $endDate)  // Check for overlap with end date
        ->where("end_date >=", $endDate)  
        ->orWhere("start_date >=", $startDate)  // Fully contained within
        ->where("end_date <=", $endDate)
    ->groupEnd()
    ->where('id !=', $eventId) // Exclude the current event
    ->findAll();

// Debugging: Log conflicting events
log_message('debug', 'Conflicting Events Found After Fix: ' . json_encode($conflictingEvents));

        


    // Reject each conflicting event and send rejection mail
    foreach ($conflictingEvents as $conflictingEvent) {
        $rejectionReason = "Conflicts with another accepted event.";

        // Update event status to rejected
        $this->eventsModel->update($conflictingEvent['id'], ['event_status' => 0]);

        // Update notification table for rejection
        $this->notificationModel->where('event_id', $conflictingEvent['id'])
            ->set([
                'accepted_by' => $userId,
                'accepted_rejected' => 0,
                'message' => "Your event '{$conflictingEvent['event_name']}' was rejected by Admin due to an Event on same date is already scheduled.",
                'created_at' => date('Y-m-d H:i:s'),
                'display_status' => 1
            ])
            ->update();
            $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
            $teacherEmailFrom = $teacherDetailsFrom['email'];
            $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];    

        // Get teacher details for rejection email
        $teacherDetails = $this->teachersModel->where('id', $conflictingEvent['teacher_id'])->select('email, passkey')->first();
        if ($teacherDetails) {
            $teacherEmail = $teacherDetails['email'];
            $teacherPasskey = $teacherDetails['passkey'];

            // Debugging: Log rejection mail details
            log_message('debug', "Sending rejection mail to: {$teacherEmail}");

            // Send rejection email
            $this->sendEventRejectedEmail($teacherEmailFrom,$teacherPasskeyFrom,$teacherEmail, $teacherPasskey,$eventId,$rejectionReason, $conflictingEvent['event_name']);
        }
    }

            // Accept the event
            $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 1]);
        
            // Update notification table
            $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
                ->set([
                    'accepted_by' => $userId,
                    'accepted_rejected' => 1,
                    'message' => "Your event '{$eventname}' was Accepted.",
                    'created_at' => date('Y-m-d H:i:s'),
                    'display_status' => 1
                ])
                ->update();
        
            if ($eventUpdate && $notificationUpdate) {
                // Get the email and passkey
                $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
                $teacherEmailFrom = $teacherDetailsFrom['email'];
                $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
        
                $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
                $teacherIdTo = $eventDetails['teacher_id'];
                $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];
        
                // Send email notification
                if ($teacherEmailFrom && $teacherEmailTo) {
                    $this->sendEventAcceptedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $eventname);
                }
        
                return $this->respond(['status' => 'success', 'message' => 'Registration accepted successfully.'], 200);
            }
        
            return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
        }
        





        private function sendEventAcceptedEmail($fromEmail, $fromPasskey, $toEmail, $eventId,$eventname)
        {    
            $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $fromEmail; // Sender's email
            $mail->Password   = $fromPasskey; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender and recipient settings
            $mail->setFrom($fromEmail, 'Event Organizer');
            $mail->addAddress($toEmail);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Event Accepted';
            $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong>.</p>
            <p>Event Name : $eventname has been accepted.</p>";
            //$mail->AltBody = "<p>Your event : $eventname has been accepted.</p>";
            //$mail->Body= "<p>Event Name: {$eventname['event_name']}</p>";

            // Send email
            $mail->send();
            //log_message('info', 'Email sent successfully to ' . $toEmail);
            return true;

        } catch (Exception $e) {
            //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
            return false;
        }

        }


public function updateRegistrationReject()
{
    log_message('debug', 'updateRegistrationReject() called.');
    $request = $this->request->getJSON();

    // Debugging: Log received request data
    log_message('debug', 'Received Data in updateRegistrationReject: ' . json_encode($request));

    $eventId = $request->event_id;
    $userId = $request->userId;
    $eventname = $request->eventname ?? 'Event'; // Default value
    $rejectionReason = $request->rejectionReason ?? null;

    
    // Validate input
    if (!isset($request->event_id) || !isset($request->userId)) {
        return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
    }

    if (!isset($request->rejectionReason)) { 
        return $this->respond(['status' => 'error', 'message' => 'Missing rejection reason.'], 400);
    }




    // Update event status
    $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 0]);

    // Update notification table
    $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
        ->set([
            'accepted_by' => $userId,
            'accepted_rejected' => 0,
            'message' => "Your event '{$eventname}' was rejected. Reason: $rejectionReason",
            'created_at' => date('Y-m-d H:i:s'),
            'display_status' => 1
        ])
        ->update();

    if ($eventUpdate && $notificationUpdate) {
        $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
        $teacherEmailFrom = $teacherDetailsFrom['email'] ?? null;
        $teacherPasskeyFrom = $teacherDetailsFrom['passkey'] ?? null;

        $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        $teacherIdTo = $eventDetails['teacher_id'] ?? null;
        $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'] ?? null;

        if ($teacherEmailFrom && $teacherEmailTo) {
            $this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason, $eventname);
        }

        return $this->respond(['status' => 'success', 'message' => 'Registration rejected successfully.'], 200);
    }

    return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
}


/* 
    public function updateRegistrationReject()
    {
        $request = $this->request->getJSON();

        // Validate input
        if (!isset($request->event_id) || !isset($request->userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Missing event_id or userId in request.'], 400);
        }

        $eventId = $request->event_id;
        $userId = $request->userId;
        $eventname= $request->eventname;
        $rejectionReason= $request->rejectionReason;
        

        //log_message('info', 'Received event_id: ' . $eventId);
        //log_message('info', 'Received user_id: ' . $userId);

        if (empty($eventId) || empty($userId)) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid event_id or userId provided.'], 400);
        }

        //$eventModel = new EventsModel();
        //$notificationModel = new NotificationModel();

        // Update event status
        $eventUpdate = $this->eventsModel->update($eventId, ['event_status' => 0]);

        // Update notification table
        $notificationUpdate = $this->notificationModel->where('event_id', $eventId)
            ->set(['accepted_by' => $userId, 'accepted_rejected' => 0,'message' => "Your event '{$eventname}' was rejected. Reason: $rejectionReason",
            'created_at' => date('Y-m-d H:i:s'), 'display_status' => 1])
            ->update();

        if ($eventUpdate && $notificationUpdate) {
              $teacherDetailsFrom = $this->teachersModel->where('id', $userId)->select('email, passkey')->first();
        $teacherEmailFrom = $teacherDetailsFrom['email'];
        $teacherPasskeyFrom = $teacherDetailsFrom['passkey'];
        
        $eventDetails = $this->eventsModel->where('id', $eventId)->select('teacher_id')->first();
        $teacherIdTo = $eventDetails['teacher_id'];
        $teacherEmailTo = $this->teachersModel->where('id', $teacherIdTo)->select('email')->first()['email'];

        // Pass email and passkey to sendEventAcceptedEmail
        if ($teacherEmailFrom && $teacherEmailTo) {
            $this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason,$eventname);
        } 
        //$this->sendEventRejectedEmail($teacherEmailFrom, $teacherPasskeyFrom, $teacherEmailTo, $eventId, $rejectionReason);

            return $this->respond(['status' => 'success', 'message' => 'Registration rejected successfully.'], 200);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update registration.'], 500);
    }
 */

    private function sendEventRejectedEmail($fromEmail, $fromPasskey, $toEmail, $eventId, $rejectionReason,$eventname)
    {    
        $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $fromEmail; // Sender's email
        $mail->Password   = $fromPasskey; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient settings
        $mail->setFrom($fromEmail, 'Event Organizer');
        $mail->addAddress($toEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Event Rejected';
        $mail->Body    = "<p>Your event with ID: <strong>$eventId</strong><p/>
        <p>Event name: <strong>$eventname</strong> has been rejected due to <strong>$rejectionReason</strong>.</p>";
        //$mail->AltBody = "Your event with ID: $eventId has been Rejected.";

        // Send email
        $mail->send();
        //log_message('info', 'Email sent successfully to ' . $toEmail);
        return true;

    } catch (Exception $e) {
        //log_message('error', 'Email could not be sent. Error: ' . $mail->ErrorInfo);
        return false;
    }

    }


    public function pendingEvents($teacherId)
    {
        $events = $this->eventsModel->getPendingEventsByTeacher($teacherId);
    
        if (empty($events)) {
            return $this->respond(['success' => false, 'message' => 'No pending events found'], 404);
        }
        return $this->respond([
            'success' => true,
            'data' => $events,
        ], 200);
    
        
    }
    

    
}







