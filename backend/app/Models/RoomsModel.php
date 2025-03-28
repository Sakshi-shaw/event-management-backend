<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomsModel extends Model
{
    protected $table = 'rooms'; // Your rooms table name
    protected $primaryKey = 'id';
    protected $allowedFields = ['id','room_no','type','seating_capacity','dept_id','active','status','floor_no','room_owner','min_limit','max_limit',]; // Update fields as needed


    // Function to get room ids where the room_owner matches
    public function getRoomIdsByOwner($room_owner_id)
    {
        return $this->select('id')
                    ->where('room_owner', $room_owner_id)
                    ->findAll();
    }
}