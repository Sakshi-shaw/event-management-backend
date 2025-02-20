<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table = 'departments'; // Your rooms table name
    protected $primaryKey = 'id';
    protected $allowedFields = ['id','dept_name','degree_id','dept_code','active','floor_no','wing']; // Update fields as needed
}