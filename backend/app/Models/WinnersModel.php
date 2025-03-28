<?php

namespace App\Models;

use CodeIgniter\Model;

class WinnersModel extends Model
{
    protected $table = 'winners';
    protected $primaryKey = 'id';
    protected $allowedFields = ['event_id', 'student_id', 'prize_position','group_no'];

/*     public function insertWinners($event_id, $winners)
    {
        $db = \Config\Database::connect();
        $studentsModel = new \App\Models\StudentsModel();

        $db->transStart(); // Start transaction

        foreach ($winners as $index => $input_student_id) {
            // Fetch student by student_id
            $student = $studentsModel->where('student_id', $input_student_id)->first();

            if (!$student) {
                $db->transRollback(); // Rollback transaction on error
                return ['success' => false, 'message' => "Student ID $input_student_id not found"];
            }

            // Insert winner details
            $this->insert([
                'event_id' => $event_id,
                'student_id' => $student['id'], // Use database ID
                'prize_position' => $index + 1
            ]);
        }

        $db->transComplete(); // Commit transaction
        return ['success' => true, 'message' => 'Winners inserted successfully'];
    } */


    
        public function insertWinners($event_id, $winners, $group_participation)
        {
            $db = \Config\Database::connect();
            $studentsModel = new \App\Models\StudentsModel();
            $registrationModel = new \App\Models\EventsRegistrationModel();
        
            $db->transStart(); // Start transaction
        
            if ($group_participation === '1') {
                // Group Participation: Each array within winners represents a group
                foreach ($winners as $group_no => $winnerGroup) {
                    if (!is_array($winnerGroup)) {
                        $winnerGroup = [$winnerGroup]; // Ensure it's always treated as an array
                    }
    
                    // Convert comma-separated student IDs to an array
                    $winnerGroup = explode(',', $winnerGroup[0]);
    
                    $studentIds = [];
                    foreach ($winnerGroup as $input_student_id) {
                        $student = $studentsModel->where('student_id', trim($input_student_id))->first();
                        
                        if (!$student) {
                            $db->transRollback();
                            return ['success' => false, 'message' => "Student ID $input_student_id not found"];
                        }
    
                        // Check if the student is registered for the event
                        $isRegistered = $registrationModel->where([
                            'student_id' => $student['id'],
                            'event_id' => $event_id
                        ])->first();
    
                        if (!$isRegistered) {
                            $db->transRollback();
                            return ['success' => false, 'message' => "Student ID $input_student_id is not registered for event ID $event_id"];
                        }
    
                        $studentIds[] = $student['id'];
                    }
    
                    // Insert each team member with the same prize position and group number
                    foreach ($studentIds as $student_id) {
                        $this->insert([
                            'event_id' => $event_id,
                            'student_id' => $student_id,
                            'prize_position' => $group_no + 1, // Each group has a unique prize position
                            'group_no' => $group_no + 1  // Assign group number (starting from 1)
                        ]);
                    }
                }
            } else {
                // Individual Participation
                foreach ($winners as $index => $input_student_id) {
                    $student = $studentsModel->where('student_id', $input_student_id)->first();
        
                    if (!$student) {
                        $db->transRollback();
                        return ['success' => false, 'message' => "Student ID $input_student_id not found"];
                    }
    
                    // Check if the student is registered for the event
                    $isRegistered = $registrationModel->where([
                        'student_id' => $student['id'],
                        'event_id' => $event_id
                    ])->first();
    
                    if (!$isRegistered) {
                        $db->transRollback();
                        return ['success' => false, 'message' => "Student ID $input_student_id is not registered for event ID $event_id"];
                    }
    
                    $this->insert([
                        'event_id' => $event_id,
                        'student_id' => $student['id'],
                        'prize_position' => $index + 1,
                        'group_no' => 0  // No group number for individual participants
                    ]);
                }
            }
    
            $db->transComplete(); // Commit transaction
            return ['success' => true, 'message' => 'Winners inserted successfully'];
        }
        
    


 public function getWinnersByEventId($event_id)
{
    $db = \Config\Database::connect();

    // Check if the event is a group participation event
    $event = $db->table('events')
        ->select('group_participation')
        ->where('id', $event_id)
        ->get()
        ->getRowArray();

    if (!$event) {
        return ['success' => false, 'message' => 'Event not found'];
    }

    $group_participation = (int) $event['group_participation'];

    // Fetch winners
    $builder = $db->table('winners')
        ->select('
            winners.group_no, 
            students.student_id
        ')
        ->join('students', 'students.id = winners.student_id', 'left')
        ->where('winners.event_id', $event_id)
        ->orderBy('winners.group_no', 'ASC')
        ->orderBy('winners.prize_position', 'ASC')
        ->get()
        ->getResultArray();

    if (empty($builder)) {
        return ['success' => false, 'message' => 'No winners found for this event'];
    }

    $winner_details = [];

    if ($group_participation === 1) {
        // Group winners by group_no
        $grouped_winners = [];
        foreach ($builder as $winner) {
            $group_no = (string) $winner['group_no'];
            $grouped_winners[$group_no][] = (string) $winner['student_id'];
        }
        $winner_details = array_values($grouped_winners);
    } else {
        // Individual winners (group_participation === 0)
        foreach ($builder as $winner) {
            $winner_details[] = [(string) $winner['student_id']];
        }
    }

    return ['success' => true, 'winners' => json_encode($winner_details)];
}




}
