<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

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
    
}
