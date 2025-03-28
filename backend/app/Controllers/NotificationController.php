<?php

namespace App\Controllers;
use Config\Database;

use CodeIgniter\RESTful\ResourceController;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationController extends ResourceController
{
    protected $eventModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->eventModel = new \App\Models\EventsModel(); // Load EventModel
        $this->notificationModel = new \App\Models\NotificationModel(); // Load NotificationModel
    }

    public function index()
    {
        try {
        // Fetch rejected notifications
        $notifications = $this->notificationModel->getRejectedNotifications();

        if (empty($notifications)) {
            return $this->respond(['status' => false, 'message' => 'No notifications found.'], 404);
        }
        // Return notifications as a response
        return $this->respond([
            'status'  => true,
            'message' => 'Notifications retrieved successfully.',
            'data'    => $notifications,
        ],200);
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
    }

    /**
     * Create a notification for an event
     *
     * @param int $event_id Event ID for which notification is created
     * @return \CodeIgniter\HTTP\Response
     */
    public function createNotificationForEvent($event_id)
    {
        // Fetch event details from the events table
        $event = $this->eventModel->find($event_id);

        // Check if the event exists and if its status is 0
        if ($event && $event['event_status'] == 0) {
            $notificationData = [
                'requested_by'         => $event['teacher_id'], // Teacher creating the event
                'event_id'             => $event['id'],        // Event ID
            ];

            // Insert notification into the database
            if ($this->notificationModel->insert($notificationData)) {
                return $this->respondCreated([
                    'status'  => true,
                    'message' => 'Notification created successfully.',
                    'data'    => $notificationData,
                ]);
            } else {
                return $this->failServerError('Failed to create notification.');
            }
        }

        // If the event doesn't exist or status is not 0
        return $this->failNotFound('Invalid event ID or event status is not 0.');
    }

    /**
     * Get all notifications
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getAllNotifications()
    {
        $notifications = $this->notificationModel->findAll();

        return $this->respond([
            'status'  => true,
            'message' => 'Notifications retrieved successfully.',
            'data'    => $notifications,
        ]);
    }

    public function getNotificationByRequestedBy($requested_by)
    {
        try {
        // Call the model method to fetch notifications
        $notifications = $this->notificationModel->getNotificationsByRequestedBy($requested_by);
        if (empty($notifications)) {
            return $this->respond(['status' => false, 'message' => 'No Notifications found.'], 404);
        }
        return $this->respond([
            'status'  => true,
            'message' => 'Notifications retrieved successfully.',
            'data'    => $notifications,
        ],200);
    
    } catch (\Exception $e) {
        return $this->respond(['status' => false, 'message' => $e->getMessage()], 500);
    }
    }

    public function hideNotification($id)
    {
        $data = [
            'display_status' => 0  // Or any other value you want to update
        ];

        $result = $this->notificationModel->hideNotification($id, $data);

        if ($result) {
            return $this->respond(['status' => 'success', 'message' => 'Notification updated successfully.']);
        } else {
            return $this->fail('Failed to update notification.');
        }
    }
   // Mark notifications as read
    public function markAllAsRead($userId)
    {
        $this->notificationModel->markAllAsRead($userId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'All notifications marked as read.'
        ],200);
    }




/*     public function sendNotificationsForRoomOwner($room_owner_id)
    {
        try {
            $result = $this->notificationModel->notifyRoomOwnerForUpcomingEvents($room_owner_id);
    
            if (!empty($result)) {
                return $this->respond([
                    'status'  => true,
                    'message' => "Notifications Found",
                    'data'    => $result,
                ], 200);
            } else {
                return $this->respond([
                    'status'  => false,
                    'message' => "No upcoming events requiring notifications",
                    'data'    => [],
                ], 404);
            }
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => "Error: " . $e->getMessage(),
            ], 500);
        }
    }
     */





     public function sendNotificationsForRoomOwner($room_owner_id)
     {
         try {
             $result = $this->notificationModel->notifyRoomOwnerForUpcomingEvents($room_owner_id);
     
             if (!empty($result)) {
                 // Send email notifications for each event
                 foreach ($result as $notification) {
                     $this->sendEmailNotification($room_owner_id, $notification);
                 }
                 log_message('info', "Notifications Found and Emails Sent for Room Owner: $room_owner_id");
                 return $this->respond([
                     'status'  => true,
                     'message' => "Notifications Found and Emails Sent",
                     'data'    => $result,
                 ], 200);
             } else {
                log_message('info', "No upcoming events requiring notifications for Room Owner: $room_owner_id");
                 return $this->respond([
                     'status'  => false,
                     'message' => "No upcoming events requiring notifications",
                     'data'    => [],
                 ], 404);
             }
         } catch (\Exception $e) {
             return $this->respond([
                 'status' => false,
                 'message' => "Error: " . $e->getMessage(),
             ], 500);
         }
     }


     public function sendNotificationsCron()
     {
  
         $db = Database::connect();
         $roomOwners = $db->table('rooms')
             ->select('room_owner')
             ->distinct()
             ->get()
             ->getResultArray();
 
         log_message('info', 'Cron Job Executed. Found Room Owners: ' . json_encode($roomOwners));
         log_message('info', 'Cron Job Executed: ' . date('Y-m-d H:i:s'));

 
         foreach ($roomOwners as $roomOwner) {
             $this->sendNotificationsForRoomOwner($roomOwner['room_owner']);
         }
 
         return $this->respond([
             'status'  => true,
             'message' => "Email notifications sent successfully.",
         ]);
     }
     
     public function sendEmailNotification($room_owner_id, $notification)
     {
         $db = \Config\Database::connect();
         
         // Fetch room owner's email from the teachers table
         $query = $db->table('teachers')->select('email')->where('id', $room_owner_id)->get();
         $user = $query->getRowArray();
         
         if (!$user || empty($user['email'])) {
             return false; // No valid email found
         }
         
         $mail = new PHPMailer(true);
     
         try {
             $mail->isSMTP();
             $mail->Host       = 'smtp.gmail.com'; // Set SMTP server
             $mail->SMTPAuth   = true;
             $mail->Username   = 'sakshishaw1375@gmail.com'; // Replace with your email
             $mail->Password   = 'eekx rmku xarr hkab'; // SMTP password
             $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
             $mail->Port       = 587;
     
             $mail->setFrom('sakshishaw1375@gmail.com', 'Campus Connect');
             $mail->addAddress($user['email']);
             
             $mail->isHTML(true);
             $mail->Subject = 'Upcoming Event Notification';
             $mail->Body    = "
                <p>Dear Room Owner,\n\nYou have an upcoming event that requires your attention:\n\n</p>
                 <p>Event Name: {$notification['event_name']}</p>
                 <p>Start Date: {$notification['start_date']}</p>
             ";
             $mail->send();
             return true;
         } catch (Exception $e) {
             log_message('error', "Email sending failed: {$mail->ErrorInfo}");
             return false;
         }
     } 
     
     
     public function sendAutomaticNotifications()
     {
         $db = \Config\Database::connect();
        // Get unique room owners from the rooms table
        $roomOwners = $db->table('rooms')
        ->select('room_owner')
        ->distinct()
        ->get()
        ->getResultArray();
     
         foreach ($roomOwners as $owner) {
             $this->sendNotificationsForRoomOwner($owner['room_owner']);
         }
     
         return $this->respond([
             'status'  => true,
             'message' => "Automatic notifications processed.",
         ], 200);
     }

     



    public function resetReadStatusByEventId()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['event_id'])) {
            return $this->response->setJSON(["status" => false, "message" => "Event ID is required"],404);
        }

        $event_id = $data['event_id'];

        // Update the read_status to 0
        $updated = $this->notificationModel
            ->where('event_id', $event_id)
            ->set(['read_status' => '0'])
            ->update();

        if ($updated) {
            return $this->response->setJSON(["status" => true, "message" => "Read status reset successfully"],200);
        } else {
            return $this->response->setJSON(["status" => false, "message" => "No records updated"],500);
        }
    }


    
}
