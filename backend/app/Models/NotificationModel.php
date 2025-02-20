<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table      = 'notification';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'requested_by',
        'event_id',
        'accepted_by',	'status',	'accepted_rejected','message','created_at','display_status','read_status'

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
    
        
}
