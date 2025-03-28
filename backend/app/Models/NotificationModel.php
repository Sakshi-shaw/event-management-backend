<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table      = 'notification';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'requested_by','event_id', 'accepted_by','accepted_rejected','message','created_at','display_status','read_status'
    ];

    public function getRejectedNotifications()
    {
        return $this->where('accepted_rejected', 0)
                    ->orderBy('created_at', 'DESC') // Sort by most recent notifications
                    ->findAll();
    }

    public function getNotificationsByRequestedBy($requested_by)
    {
        // Join notifications with events to fetch event details
        return $this->select('notification.*, events.event_name')
                    ->join('events', 'events.id = notification.event_id')
                    ->where('notification.requested_by', $requested_by)
                    ->where('notification.display_status', 1)
                    ->orderBy('notification.created_at', 'DESC') // Order by created_at in descending order (most recent first)
                    ->findAll();
    }

public function hideNotification($id, $data)
{
        // Check if the notification with the given ID exists
        $existingRecord = $this->find($id);
        if (!$existingRecord) {
            throw new \Exception("Notification with ID $id not found.");
        }
    
        // Proceed with the update if record exists
        return $this->update($id, $data);
}

public function markAllAsRead($userId)
{
    return $this->where('read_status', 0)
                ->where('requested_by', $userId)
                ->set(['read_status' => 1])
                ->update();
}


    public function notifyRoomOwnerForUpcomingEvents($room_owner_id)
    {
        $db = \Config\Database::connect();
        
        $twoDaysLater = date('Y-m-d', strtotime('+2 days'));

        // Query to fetch events that are 2 days away and not yet accepted/rejected
        $query = $db->table('events')
        ->select('events.id AS event_id, events.event_name, events.start_date, rooms.room_owner') 
        ->join('rooms', 'rooms.id = events.room_id') 
        ->where('DATE(events.start_date)', $twoDaysLater)
        ->join('notification', 'notification.event_id = events.id')
        ->where('notification.accepted_rejected IS NULL') 
        ->where('rooms.room_owner', $room_owner_id) 
        ->groupBy('events.id') // Ensures each event appears only once
        ->get();
    
        
        $events = $query->getResultArray();

        $notifications = [];

        if (!empty($events)) {
            foreach ($events as $event) {
                // Prepare notification data
                $notificationData = [
                    'requested_by'    => $event['room_owner'], // Notify specific room owner
                    'event_name'     => $event['event_name'],
                    'event_id'        => $event['event_id'],
                    'start_date'        => $event['start_date'],
                    'status'          => 0, // Pending status
                    'accepted_rejected' => NULL, // Not accepted/rejected yet
                    'message'         => "Reminder: Event '{$event['event_name']}' is scheduled for {$event['start_date']}. Please review.",
                    'created_at'      => date('Y-m-d H:i:s'),
                    // 'display_status'  => 1, // Visible
                     'read_status'     => 0  // Unread notification
                ];

                // Add to the notifications array
                $notifications[] = $notificationData;
            }

            return $notifications; // Return the array of notifications
        }

        return []; // Return empty array if no notifications
    }


    
        
}
