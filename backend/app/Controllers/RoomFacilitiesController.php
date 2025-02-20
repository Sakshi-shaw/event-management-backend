<?php

namespace App\Controllers;

use App\Models\RoomFacilitiesModel;

class RoomFacilitiesController extends BaseController
{
    public function index($room_id)
    {
        // Load the model
        $roomFacilitiesModel = new RoomFacilitiesModel();
        
        // Get the facilities data by room_id
        $facilities = $roomFacilitiesModel->getFacilitiesByRoom($room_id);

        // Check if data is found
        if (!empty($facilities)) {
            return $this->response->setJSON(['status' => 'success', 'data' => $facilities],200);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No facilities found for this room']);
        }
    }
}
