<?php

namespace App\Models;

use CodeIgniter\Model;

class EventTypeModel extends Model
{
    protected $table = 'eventtypes'; // Table name
    protected $primaryKey = 'id'; // Primary key
    protected $allowedFields = ['eventType']; // Fields that are allowed for operations

    // Disable automatic timestamps if not used
    public $timestamps = false;
}
