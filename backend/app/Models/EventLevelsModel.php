<?php
namespace App\Models;

use CodeIgniter\Model;

class EventLevelsModel extends Model
{
    protected $table = 'event_levels'; // Specify the table name
    protected $primaryKey = 'id';      // Define the primary key
    protected $allowedFields = ['id','event_level']; // Define the allowed fields for insert/update

    /**
     * Fetch all event levels.
     *
     * @return array
     */
    public function getAllEventLevels()
    {
        return $this->findAll(); // Use Model's `findAll()` to get all records
    }
}
