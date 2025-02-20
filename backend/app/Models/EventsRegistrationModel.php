<?php

namespace App\Models;

use CodeIgniter\Model;

class EventsRegistrationModel extends Model
{
    protected $table = 'registration';
    protected $primaryKey = 'id';
    protected $allowedFields = ['student_id', 'event_id', 'registration_date'];

    // Additional configurations if needed
    protected $useTimestamps = false; // Since we handle 'registration_date' manually

    public function getAllRegistrations()
    {
        return $this->findAll();
    }


    // public function getRegistrationsByStudentId($studentId)
    // {
    //     return $this->where('student_id', $studentId)->findAll();
    // }

    public function getRegistrationsByStudent($studentId)
    {
        // Join the registration table with the events and eventtypes tables
        $builder = $this->db->table($this->table);
        $builder->select('registration.*, events.*, eventtypes.eventType'); // Include eventType from eventtypes table
        $builder->join('events', 'registration.event_id = events.id', 'left'); // Join on event_id
        $builder->join('eventtypes', 'eventtypes.id = events.eventType_id', 'left'); // Join eventtypes on eventType_id
        $builder->where('registration.student_id', $studentId); // Filter by student ID
        $builder->orderBy('events.start_date', 'DESC'); // Order by start_date descending
        $builder->orderBy('events.end_date', 'DESC'); // Order by end_date descending
    
        return $builder->get()->getResultArray(); // Fetch results as an array
    }
    
}
