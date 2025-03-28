<?php

namespace App\Controllers;
use Config\Database;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\NotificationModel;
use App\Models\EventsModel;
use App\Models\TeachersModel;


class CronController extends ResourceController
{
    
    protected $notificationModel;
    protected $eventsModel;
    protected $teachersModel;
    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->eventsModel = new EventsModel();
        $this->teachersModel = new TeachersModel();
    }
    public function autoRejectEvents()
    {
/*         if (!is_cli()) {
            exit('This script can only be run from the command line.');
        } */
        
        $tomorrowDate = date('Y-m-d', strtotime('+1 day')); 

        $db = Database::connect();
        
    
        try {
            $db->transStart(); 
            log_message('info', "Corn job started");
            // Fetch all pending events
            $eventsQuery = $db->table('events')
                ->select('events.id, events.event_name, events.teacher_id, rooms.room_owner')
                ->join('notification', 'events.id = notification.event_id', 'left')
                ->join('rooms', 'events.room_id = rooms.id', 'left')
                ->where('DATE(events.start_date)', $tomorrowDate)
                ->where('events.event_status', 0)
                ->where('notification.accepted_rejected IS NULL');
        
            $eventsResult = $eventsQuery->get();
            //log_message('info', 'Cron Job Executed. Found Room Owners: ' . json_encode($eventsResult));
        
            if (!$eventsResult) {
                return $this->respond(['success' => false, 'message' => 'Database query failed.'], 500);
            }
        
            $events = $eventsResult->getResultArray();
        
            if (empty($events)) {
                return $this->respond(['success' => true, 'message' => 'No pending events found for rejection.'], 200);
            }
            log_message('info', 'Cron Job Executed. Found Room Owners: ' . json_encode($events));
        
            foreach ($events as $event) {
                $eventId = $event['id'];
                $eventName = $event['event_name'] ?? null;
                $teacherId = $event['teacher_id'];
    
                // Update notification
                $db->table('notification')
                    ->where('event_id', $eventId)
                    ->update([
                        'accepted_by' => $event['room_owner'],
                        'accepted_rejected' => 0,
                        'message' => "Your event '{$eventName}' was rejected. Reason: Not accepted in time.",
                        'created_at' => date('Y-m-d H:i:s'),
                        'display_status' => 1
                    ]);
                    $roomOwnerId = $event['room_owner'];

                // Fetch emails
                $teacherData = $this->teachersModel->find($teacherId);
                $roomOwnerData = $this->teachersModel->find($roomOwnerId);
    
                if ($teacherData && $roomOwnerData) {
                    $this->sendEventRejectedEmail(
                        $roomOwnerData['email'],
                        $roomOwnerData['passkey'],
                        $teacherData['email'],
                        $eventId,
                        "Not accepted in time",
                        $eventName
                    );
                    log_message('info', "Event ID {$eventId} - Rejection email sent.");
                }
            }
    
            $db->transComplete(); 
    
            if ($db->transStatus() === false) {
                throw new \Exception("Transaction failed.");
            }
    
            return $this->respond(['success' => true, 'message' => 'Pending events rejected successfully.', 'data' => $events], 200);
        } catch (\Exception $e) {
            $db->transRollback(); 
            return $this->respond(['success' => false, 'message' => 'Failed to process event rejections.'], 500);
        }
    }

    public function sendEventRejectedEmail($fromEmail, $fromPasskey, $toEmail, $eventId, $rejectionReason,$eventname)
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


    public function sendNotificationsCron()
    {
        /* if (!is_cli()) {
            exit('This script can only be run from the command line.');
        } */
    
        $db = Database::connect();
        
        // Manually load NotificationController
       // $notificationController = new \App\Controllers\NotificationController(); 
    
        $roomOwners = $db->table('rooms')
            ->select('room_owner')
            ->distinct()
            ->get()
            ->getResultArray();
    
        //log_message('info', 'Cron Job Executed. Found Room Owners: ' . json_encode($roomOwners));
        //log_message('info', 'Cron Job Executed: ' . date('Y-m-d H:i:s'));
    
        foreach ($roomOwners as $roomOwner) {
            // Call sendNotificationsForRoomOwner from NotificationController
            log_message('info', 'Cron Job Executed. Found Room Owners: ' . $roomOwner['room_owner']);
            // $notificationController->sendNotificationsForRoomOwner($roomOwner['room_owner']);
            $result = $this->notificationModel->notifyRoomOwnerForUpcomingEvents($roomOwner['room_owner']);

            if (!empty($result)) {
                foreach ($result as $notification) {
                    $this->sendEmailNotification($roomOwner['room_owner'], $notification);
                }
                log_message('info', "Notifications sent for Room Owner: {$roomOwner['room_owner']}");
            } else {
                log_message('info', "No notifications needed for Room Owner: {$roomOwner['room_owner']}");
            }

        }
    
        return $this->respond([
            'success'  => true,
            'message' => "Email notifications sent successfully.",
        ],200);
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
    
}
