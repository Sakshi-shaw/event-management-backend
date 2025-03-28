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
   /*  public function getRegistrationsByStudent($studentId)
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
     */

     public function getRegistrationsByStudent($studentId)
{
    return $this->db->table($this->table)
        ->select('
            registration.*, 
            events.id as event_id, 
            events.teacher_id, 
            events.event_name, 
            events.description, 
            events.start_date, 
            events.end_date, 
            events.imagename, 
            events.event_status, 
            events.eligible_dept, 
            events.registration_limit, 
            events.registration_count, 
            events.group_participation, 
            events.max_participation, 
            eventtypes.eventType, 
            CONCAT(teachers.firstName, " ", teachers.lastName) as teacher_name, 
            teachers.email as teacher_email, 
            rooms.room_no, 
            departments.dept_name, 
            events.event_level_id, 
            event_levels.event_level,
            CASE 
                WHEN events.group_participation = 1 
                THEN 
                    CONCAT(
                        "[", 
                        GROUP_CONCAT(
                            DISTINCT 
                            CONCAT(
                                \'{"group_no":"\', winners.group_no, \'","students": [\', 
                                (SELECT GROUP_CONCAT(CONCAT(\'"\', students.firstName, \' \', students.lastName, \'"\')) 
                                 FROM students 
                                 WHERE students.id = winners.student_id),
                                "]}"
                            ) SEPARATOR ","
                        ), 
                        "]"
                    ) 
                ELSE 
                    CONCAT("[", GROUP_CONCAT(DISTINCT CONCAT("\"", students.firstName, " ", students.lastName, "\"") ORDER BY winners.prize_position ASC SEPARATOR ", "), "]")
            END AS winner_details
        ')
        ->join('events', 'registration.event_id = events.id', 'left') // Join events
        ->join('eventtypes', 'eventtypes.id = events.eventType_id', 'left') // Join eventtypes
        ->join('event_levels', 'events.event_level_id = event_levels.id', 'left') // Join event_levels
        ->join('teachers', 'teachers.id = events.teacher_id', 'left') // Join teachers
        ->join('rooms', 'rooms.id = events.room_id', 'left') // Join rooms
        ->join('departments', 'departments.id = events.dept', 'left') // Join departments
        ->join('winners', 'events.id = winners.event_id', 'left') // Join winners
        ->join('students', 'winners.student_id = students.id', 'left') // Join students
        ->where('registration.student_id', $studentId) // Filter by student ID
        ->orderBy('events.start_date', 'DESC') // Order by event start date
        ->orderBy('events.end_date', 'DESC') // Then order by event end date
        ->groupBy('events.id') // Group by event ID
        ->get()
        ->getResultArray(); // Fetch results as an array
}

}
