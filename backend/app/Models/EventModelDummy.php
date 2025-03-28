<?php

namespace App\Models;

use CodeIgniter\Model;

class EventsModel extends Model
{
    protected $table = 'events';
    protected $allowedFields = [
        'teacher_id', 'room_id', 'event_name', 'description', 'dept',
        'start_date', 'end_date', 'event_status', 'event_link','eventType_id','event_level_id','eligible_dept'
    ];

    public function getEvents()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('events');

        $builder->select('events.teacher_id, events.event_name, events.description, events.dept, 
                          events.start_date, events.end_date, events.event_status, events.event_link, events.imagename, events.event_level_id,
                          teachers.firstName as teacher_firstname, teachers.lastName as teacher_lastname, 
                          teachers.phone as teacher_phone, departments.dept_name,
                          GROUP_CONCAT(CONCAT(students.firstName, " ", students.lastName, " (", winners.prize_position, ")") 
                          SEPARATOR ", ") as winner_details,event_levels.event_level');
        $builder->join('teachers', 'events.teacher_id = teachers.id', 'left');
        $builder->join('departments', 'events.dept = departments.id', 'left');
        $builder->join('event_levels', 'events.event_level_id = event_levels.id', 'left');
        $builder->join('winners', 'events.id = winners.event_id', 'left');
        $builder->join('students', 'winners.student_id = students.id', 'left');
        $builder->where('events.event_status', 1);
        $builder->groupBy('events.id');

        $query = $builder->get();
        return $query->getResultArray();
    }

/*     public function getAvailableVenues($filters)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('rooms'); // Select the `rooms` table
    
        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $startTime = $filters['start_time'];
        $endTime = $filters['end_time'];
        $roomType = $filters['room_type'];
    
        // Select both room_no and seating_capacity
        $builder->select('rooms.id,rooms.room_no, rooms.seating_capacity');
        $builder->join('room_type', 'rooms.type = room_type.id', 'inner'); // Match `rooms.type` with `room_type.id`
    
        // Exclude rooms where the dates and times match exactly with any event in the `events` table
        $builder->whereNotIn('rooms.id', function ($subquery) use ($db, $startDate, $endDate, $startTime, $endTime) {
            $subquery->select('room_id')
                     ->from('events')
                     ->where('DATE(start_date)', date('Y-m-d', strtotime($startDate)))
                     ->where('DATE(end_date)', date('Y-m-d', strtotime($endDate)))
                     ->where('TIME(start_date)', $startTime)
                     ->where('TIME(end_date)', $endTime)
                     ->where('event_status', 1) // Add condition for event_status
                     ->getCompiledSelect(false); // Prevent query execution; compile subquery instead
        });
    
        // Optionally filter by room type if provided
        if (!empty($roomType)) {
            $builder->where('room_type.type', $roomType); // Match room type name
        }
    
        $query = $builder->get();
        $result = $query->getResultArray();
    
        // Return both room_no and seating_capacity
        return $result;
    } */



    public function getAvailableVenues($filters)
{
    $db = \Config\Database::connect();
    $builder = $db->table('rooms'); // Select the `rooms` table

    $startDate = date('Y-m-d', strtotime($filters['start_date'])); // Extract only the date
    $endDate = date('Y-m-d', strtotime($filters['end_date'])); // Extract only the date
    $startTime = $filters['start_time']; // Extract only the time
    $endTime = $filters['end_time']; // Extract only the time
    $roomType = $filters['room_type'];

    // Select room_no and seating_capacity
    $builder->select('rooms.id, rooms.room_no, rooms.seating_capacity');
    $builder->join('room_type', 'rooms.type = room_type.id', 'inner'); // Match `rooms.type` with `room_type.id`

    // Exclude rooms that have conflicting events using `whereNotIn()`
    $builder->whereNotIn('rooms.id', function ($subquery) use ($db, $startDate, $endDate, $startTime, $endTime) {
        $subquery->select('room_id')
                 ->from('events')
                 ->where('event_status', 1) // Only consider active events
                 ->groupStart()
                     // Check if the requested start time is inside an event
                     ->where('DATE(start_date)', $startDate)
                     ->where('TIME(start_date) <=', $startTime)
                     ->where('TIME(end_date) >', $startTime)

                     // Check if the requested end time is inside an event
                     ->orWhere('DATE(end_date)', $endDate)
                     ->where('TIME(start_date) <', $endTime)
                     ->where('TIME(end_date) >=', $endTime)

                     // Check if an existing event is fully inside the requested range
                     ->orWhere('DATE(start_date) >=', $startDate)
                     ->where('DATE(end_date) <=', $endDate)
                     ->where('TIME(start_date) >=', $startTime)
                     ->where('TIME(end_date) <=', $endTime)

                     // Check if an existing event completely overlaps the requested range
                     ->orWhere('DATE(start_date) <=', $startDate)
                     ->where('DATE(end_date) >=', $endDate)
                     ->where('TIME(start_date) <=', $startTime)
                     ->where('TIME(end_date) >=', $endTime)
                 ->groupEnd();
    });

    // Optionally filter by room type if provided
    if (!empty($roomType)) {
        $builder->where('room_type.type', $roomType); // Match room type name
    }

    $query = $builder->get();
    return $query->getResultArray();
}

    

// Method to insert a new event
public function insertEvent($data)
{
    return $this->insert($data, true); // Returns the inserted ID on success
}

public function getEventsByTeacher($teacherId)
{
    return $this->db->table($this->table)
        ->select('events.id, events.event_name, events.description, events.start_date, events.end_date, eventtypes.eventType,events.imagename,events.event_status,events.eligible_dept,
                  CONCAT(teachers.firstName, " ", teachers.lastName) as teacher_name, teachers.email as teacher_email, teachers.phone, teachers.teacher_id,
                  rooms.room_no, 
                  departments.dept_name, 
                  GROUP_CONCAT(CONCAT(students.firstName, " ", students.lastName, " (", winners.prize_position, ")") SEPARATOR ", ") as winner_details,events.event_level_id,event_levels.event_level')
        ->join('eventtypes', 'eventtypes.id = events.eventType_id', 'left')
        ->join('teachers', 'teachers.id = events.teacher_id', 'left')
        ->join('event_levels', 'events.event_level_id = event_levels.id', 'left')
        ->join('rooms', 'rooms.id = events.room_id', 'left')
        ->join('departments', 'departments.id = events.dept', 'left')
        ->join('winners', 'events.id = winners.event_id', 'left')
        ->join('students', 'winners.student_id = students.id', 'left')
        ->where('events.teacher_id', $teacherId) // Filter by teacher_id
        ->where('events.event_status', 1)
        ->orderBy('events.start_date', 'desc') // Sort by start_date in descending order
        ->orderBy('events.end_date', 'desc') // Then sort by end_date in descending order
        ->groupBy('events.id') // Group by event ID to use GROUP_CONCAT
        ->get()
        ->getResultArray();
}


public function getRecentPastEvents($currentDate)
{
    return $this->db->table($this->table)
        ->select('events.id, events.event_name, events.description, events.start_date, events.end_date, eventtypes.eventType,events.event_status,events.imagename,events.eligible_dept,
                  CONCAT(teachers.firstName, " ", teachers.lastName) as teacher_name, teachers.email as teacher_email,
                  rooms.room_no, 
                  departments.dept_name, 
                  GROUP_CONCAT(CONCAT(students.firstName, " ", students.lastName) 
                  ORDER BY winners.prize_position ASC SEPARATOR ", ") as winner_details,events.event_level_id,event_levels.event_level')
        ->join('eventtypes', 'eventtypes.id = events.eventType_id', 'left')
        ->join('event_levels', 'events.event_level_id = event_levels.id', 'left')
        ->join('teachers', 'teachers.id = events.teacher_id', 'left')
        ->join('rooms', 'rooms.id = events.room_id', 'left')
        ->join('departments', 'departments.id = events.dept', 'left')        
        ->join('winners', 'events.id = winners.event_id', 'left')
        ->join('students', 'winners.student_id = students.id', 'left')
        ->where('events.start_date <', $currentDate)
        ->where('events.end_date <', $currentDate)
        ->where('events.event_status', 1)
        ->orderBy('events.end_date', 'desc')
        ->groupBy('events.id') // Group by event ID to use GROUP_CONCAT
        ->limit(4) // Limit to the latest 4 past events
        ->get()
        ->getResultArray();
}

public function getUpcomingEvents($currentDate)
{
    return $this->db->table($this->table)
        ->select('events.id, events.event_name, events.description, events.start_date, events.end_date, eventtypes.eventType,events.imagename,events.event_status,events.eligible_dept,
                  CONCAT(teachers.firstName, " ", teachers.lastName) as teacher_name, teachers.email as teacher_email,
                  rooms.room_no, 
                  departments.dept_name,events.event_level_id,event_levels.event_level')
        ->join('eventtypes', 'eventtypes.id = events.eventType_id','left')
        ->join('event_levels', 'events.event_level_id = event_levels.id', 'left')
        ->join('teachers', 'teachers.id = events.teacher_id')
        ->join('rooms', 'rooms.id = events.room_id')
        ->join('departments', 'departments.id = events.dept')
        ->where('events.event_status', 1)
        ->where('events.start_date >=', $currentDate)
        ->where('events.end_date >=', $currentDate)
        ->orderBy('events.end_date','asc')
        ->get()
        ->getResultArray();
}


    // Function to get events based on room ids, event_status, and accepted_rejected NULL in notifications
    public function getEventsByRoomIdsAndStatus($roomIds)
    {
        // Extract only the 'id' values from the roomIds array
        $roomIdsArray = array_map(function($room) {
            return $room['id'];  // Assuming room has an 'id' key
        }, $roomIds);

        // Build the query with the room IDs
        $query = $this->db->table('events')
                        ->select('events.*, notification.accepted_rejected, CONCAT(teachers.firstName, " ", teachers.lastName) as teacher_name,teachers.email as teacher_email,eventtypes.eventType,rooms.room_no,events.event_level_id,event_levels.event_level,events.eligible_dept,departments.dept_name')
                        ->join('notification', 'events.id = notification.event_id', 'left')
                        ->join('event_levels', 'events.event_level_id = event_levels.id', 'left')
                        ->join('eventtypes', 'eventtypes.id = events.eventType_id','left')
                        ->join('departments', 'departments.id = events.dept')
                        ->join('rooms', 'rooms.id = events.room_id')
                        ->join('teachers', 'teachers.id = events.teacher_id')
                        ->whereIn('events.room_id', $roomIdsArray)
                        ->where('events.event_status', 0)
                        ->where('notification.accepted_rejected', null);

        return $query->get()->getResult();
    }


        // Function to get events based on room ids, event_status, and accepted_rejected NULL in notifications
        public function getAcceptedEvents($roomIds,$roomOwnerId)
        {
            // Extract only the 'id' values from the roomIds array
            $roomIdsArray = array_map(function($room) {
                return $room['id'];  // Assuming room has an 'id' key
            }, $roomIds);
    
            // Build the query with the room IDs
            $query = $this->db->table('events')
                            ->select('events.*, notification.accepted_rejected, CONCAT(teachers.firstName, " ", teachers.lastName) as teacher_name,teachers.email as teacher_email,eventtypes.eventType,events.event_level_id,event_levels.event_level,events.eligible_dept,departments.dept_name')
                            ->join('notification', 'events.id = notification.event_id', 'left')
                            ->join('event_levels', 'events.event_level_id = event_levels.id', 'left')
                            ->join('eventtypes', 'eventtypes.id = events.eventType_id','left')
                            ->join('teachers', 'teachers.id = events.teacher_id')
                            ->join('departments', 'departments.id = events.dept')
                            ->join('rooms', 'events.room_id = rooms.id', 'left')  // Join with rooms table
                            ->whereIn('events.room_id', $roomIdsArray)
                            ->where('events.event_status', 1)
                            ->where('notification.accepted_rejected', 1)
                            ->where('rooms.room_owner', $roomOwnerId)  // Room owner condition
                            ->where('notification.accepted_by', $roomOwnerId);  // Accepted by condition in notifications
    
            return $query->get()->getResult();
        }


                // Function to get events based on room ids, event_status, and accepted_rejected NULL in notifications
        public function getRejectedEvents($roomIds,$roomOwnerId)
        {
            // Extract only the 'id' values from the roomIds array
            $roomIdsArray = array_map(function($room) {
                return $room['id'];  // Assuming room has an 'id' key
            }, $roomIds);
    
            // Build the query with the room IDs
            $query = $this->db->table('events')
                            ->select('events.*, notification.accepted_rejected, CONCAT(teachers.firstName, " ", teachers.lastName) as teacher_name,teachers.email as teacher_email,eventtypes.eventType,events.event_level_id,events.eligible_dept,event_levels.event_level,departments.dept_name')
                            ->join('notification', 'events.id = notification.event_id', 'left')
                            ->join('event_levels', 'events.event_level_id = event_levels.id', 'left')
                            ->join('eventtypes', 'eventtypes.id = events.eventType_id','left')
                            ->join('teachers', 'teachers.id = events.teacher_id')
                            ->join('departments', 'departments.id = events.dept')
                            ->join('rooms', 'events.room_id = rooms.id', 'left')  // Join with rooms table
                            ->whereIn('events.room_id', $roomIdsArray)
                            ->where('events.event_status', 0)
                            ->where('notification.accepted_rejected', 0)
                            ->where('rooms.room_owner', $roomOwnerId)  // Room owner condition
                            ->where('notification.accepted_by', $roomOwnerId);  // Accepted by condition in notifications
    
            return $query->get()->getResult();
        }


        public function getPendingEventsByTeacher($teacherId)
        {
            return $this->select('events.*')
                ->join('notification', 'notification.event_id = events.id')
                ->where('events.teacher_id', $teacherId)
                ->where('notification.accepted_rejected', null)
                ->findAll();
        }






        
}
