<?php

namespace App\Controllers;

use App\Models\EventTypeModel; // Load the EventTypeModel

class EventTypesController extends BaseController
{
    public function index()
    {
        
        // Create an instance of the EventTypeModel
        $eventTypeModel = new EventTypeModel();

        // Fetch all data from the 'eventtypes' table
        $eventTypes = $eventTypeModel->findAll();

        // Return the data as JSON for simplicity (you can also pass it to a view)
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $eventTypes
        ], 200);
    }
}
