<?php

namespace App\Models;

use CodeIgniter\Model;

class TeachersModel extends Model
{
    protected $table = 'teachers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id','firstName', 'lastName','teacher_id', 'email', 'password', 'phone', 
        'gender', 'role_id', 'dept_id'
    ];

    public function getTeacherById($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $builder->select('
            teachers.*,
            departments.dept_name,
            colleges.college, degree.degree');
        $builder->join('departments', 'teachers.dept_id = departments.id', 'left');
        $builder->join('colleges', 'teachers.college_id = colleges.id', 'left');
        $builder->join('degree', 'departments.degree_id = degree.id', 'left');
        $builder->where('teachers.id', $id);

        $query = $builder->get();
        return $query->getRowArray(); // Return a single row as an associative array
    }

    public function updateTeacher($data)
    {
        if (empty($data['id'])) {
            return false; // Return false if ID is missing
        }

        $teacher = $this->find($data['id']);
        if (!$teacher) {
            return false; // Return false if student not found
        }

        return $this->update($data['id'], $data); // Update the student record
    }

}
