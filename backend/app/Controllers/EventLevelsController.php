<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EventLevelsModel;

class EventLevelsController extends ResourceController
{
    protected $eventLevelsModel;

    public function __construct()
    {
        // Load the EventLevels model
        $this->eventLevelsModel = new EventLevelsModel();
    }

    /**
     * Get all event levels.
     *
     * @return mixed
     */
    public function getEventLevels()
    {
        $eventLevels = $this->eventLevelsModel->getAllEventLevels();

        if ($eventLevels) {
            return $this->respond(['status' => 'success', 'data' => $eventLevels], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No event levels found.'], 404);
        }
    }
}
