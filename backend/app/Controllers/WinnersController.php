<?php

namespace App\Controllers;

use App\Models\WinnersModel;
use CodeIgniter\RESTful\ResourceController;

class WinnersController extends ResourceController
{
    /**
     * Inserts winners for an event
     * @return \CodeIgniter\HTTP\Response
     */
    public function insertWinners()
    {
        $winnersModel = new WinnersModel();    
        // Get JSON data from the request
        $data = $this->request->getJSON(true);
        $event_id = $data['event_id'] ?? null;
        $winners = $data['winners'] ?? [];  
        $group_participation = $data['group_participation'] ?? '0';  
        // Log the extracted values for debugging
        //log_message('debug', "Extracted event_id: " . json_encode($event_id));
        //log_message('debug', "Extracted winners: " . json_encode($winners));    
        // Validate input
        if (empty($event_id) || empty($winners) || !is_array($winners)) {
            return $this->respond([
                'success' => false,
                'message' => 'All fields are required!',
            ], 400);
        }    
        // Call model function to insert winners
        $result = $winnersModel->insertWinners($event_id, $winners, $group_participation);    
        if ($result['success'] === false) {
            return $this->respond([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }    
        return $this->respond([
            'success' => true,
            'message' => 'Winners inserted successfully',
        ], 200);
    }




public function getWinners($event_id)
{
    $winnersModel = new WinnersModel();
    $result = $winnersModel->getWinnersByEventId($event_id);

    if ($result['success'] === false) {
        return $this->respond([
            'success' => false,
            'message' => $result['message']
        ], 404);
    }

    return $this->respond([
        'success' => true,
        'winners' => $result['winners']
    ], 200);
}

    
}
