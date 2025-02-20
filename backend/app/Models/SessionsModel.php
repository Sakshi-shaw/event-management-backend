<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionsModel extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'role_id', 'session_id', 'login_time','logout_time'];
}
