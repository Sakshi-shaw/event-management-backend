<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomFacilitiesModel extends Model
{
    protected $table      = 'room_facilities';
    protected $primaryKey = 'id';

    protected $allowedFields = ['room_id', 'facility_id'];
    
    // Method to get facilities based on room_id
    public function getFacilitiesByRoom($room_id)
    {
        // Join room_facilities with facilities table to get facility details
        return $this->select('facilities.*')
                    ->join('facilities', 'room_facilities.facility_id = facilities.id')
                    ->where('room_facilities.room_id', $room_id)
                    ->findAll();
    }
}
