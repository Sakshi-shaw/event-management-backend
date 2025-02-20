<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\StudentsModel;
use App\Models\TeachersModel;
use App\Models\SessionsModel;

class RegistrationController extends ResourceController
{
    private $storeSession_id="";
    public function register()
{
    // Get data from POST request (Frontend)
    $data = $this->request->getJSON(true);
    $email = $data['email'];
    $password = $data['password'];
    $firstName = $data['firstName'];
    $registerNumber = $data['registerNumber'];
    $lastName = $data['lastName'];
    $dept_id = $data['dept_id'];
    $phone = $data['phone'];
    $gender = $data['gender'];
    $college_id = $data['college_id'];
    $hashedPassword = "";
    
    try {
        // Password validation
        if (!$this->isValidPassword($password)) {
            
            return $this->respond([
                'message' => 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.'
            ], 400); // HTTP 400 Bad Request
           
        }
        else{
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        }

        // Model for Students
        $studentModel = new StudentsModel();

        // Check if the email already exists in the students table
        $studentExists = $studentModel->where('email', $email)->first();

        if ($studentExists) {
            return $this->respond([
                'message' => 'This email is already registered.'
            ], 409); // HTTP 409 Conflict
        }

        // Insert the student's details into the database
        $studentModel->insert([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'student_id' => $registerNumber,
            'email' => $email,
            'password' => $hashedPassword,
            'dept_id' => $dept_id,
            'phone' => $phone,
            'gender' => $gender,
            'college_id' => $college_id
        ]);
        return $this->respondCreated(['success' => "true", 'message' => 'Student registered successfully.'],200);
        //return $this->respondCreated(['success' => "true", 'message' => 'Student registered successfully.']);
    } catch (\Exception $e) {
        return $this->respond([
            'message' => 'Registration failed.',
            'error' => $e->getMessage()
        ], 500); // HTTP 500 Internal Server Error
    }
}

/**
 * Validate password based on criteria:
 * - At least 8 characters long
 * - At least one uppercase letter
 * - At least one lowercase letter
 * - At least one number
 * - At least one special character
 */
private function isValidPassword($password)
{
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}



//login

/* public function login()
{
    $data = $this->request->getJSON(true);
    $email = $data['email'];
    $password = $data['password'];  // Remove any leading or trailing spaces

    $studentModel = new StudentsModel();
    $teacherModel = new TeachersModel();
    $sessionModel = new SessionsModel();

    $firstName = '';
    $lastName = '';
    $uicon='';

    try {
        // Check Teachers Table
        $teacher = $teacherModel->where('email', $email)->first();
        if ($teacher) {
            $firstName = $teacher['firstName']; // Fetch first name
            $lastName = $teacher['lastName']; 
            $uicon = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));
            //$verify = password_verify($password, $teacher['password']); 
            if ($data['password'] !== $teacher['password']) {
                return $this->respond(['status' => false, 'message' => 'Invalid username or password.'], 401);
            }
            
            else{
                return $this->respond([
                    'success'=>"true",
                    'message' => 'Login successful.',
                    'role' => 'teacher',
                    'id' => $teacher['id'],
                    'data' => [
                        'id' => $teacher['id'],
                        'firstName' => $teacher['firstName'],
                        'lastName' => $teacher['lastName'],
                        'email' => $teacher['email'],
                        'role_id' => $teacher['role_id'],
                        'dept_id' => $teacher['dept_id'],
                        'role' => 'teacher',
                        'uicon' => $uicon
                    ]
                ], 200);
            }
            // if ($teacher['password']===$password) {  // Compare plain text passwords
            //     return $this->respond([
            //         'success'=>"true",
            //         'message' => 'Login successful.',
            //         'role' => 'teacher',
            //         'data' => [
            //             'id' => $teacher['id'],
            //             'firstName' => $teacher['firstName'],
            //             'lastName' => $teacher['lastName'],
            //             'email' => $teacher['email'],
            //             'role_id' => $teacher['role_id'],
            //             'dept_id' => $teacher['dept_id'],
            //             'uicon' => $uicon
            //         ]
            //     ], 200);
            // } else {
            //     return $this->respond(['message' => 'Invalid credentials.'], 401);
            // }
        }

        // Check Students Table
        $student = $studentModel->where('email', $email)->first();
        
        if ($student) {
            $firstName = $student['firstName']; // Fetch first name
            $lastName = $student['lastName']; 
            $uicon = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));

            $verify = password_verify($password, $student['password']);
            if ($verify) {  // Compare plain text passwords
                return $this->respond([
                    'success'=>"true",
                    'message' => 'Login successful.',
                    'role' => 'student',
                    'id' => $student['id'],
                    'data' => [
                        'id' => $student['id'],
                        'firstName' => $student['firstName'],
                        'lastName' => $student['lastName'],
                        'email' => $student['email'],
                        'dept_id' => $student['dept_id'],
                        'uicon' => $uicon,
                        'role' => 'student'
                    ]
                ], 200);
            } else {
                return $this->respond(['message' => 'Invalid credentials.'], 401);
            }
        }

        return $this->respond(['message' => 'User not found.'], 404);
    } catch (\Exception $e) {
        return $this->respond([
            'message' => 'Login failed.',
            'error' => $e->getMessage()
        ], 500);
    }
} */


public function login()
    {
        $data = $this->request->getJSON(true);
        $email = $data['email'];
        $password = $data['password'];

        $studentModel = new StudentsModel();
        $teacherModel = new TeachersModel();
        $sessionModel = new SessionsModel();

        try {
            // Check Teachers Table
            $teacher = $teacherModel->where('email', $email)->first();
            if ($teacher) {
                $firstName = $teacher['firstName']; // Fetch first name
                $lastName = $teacher['lastName']; 
                $uicon = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));

                if ($password !== $teacher['password']) {
                    return $this->respond(['status' => false, 'message' => 'Invalid credentials.'], 401);
                }

                $session_id = bin2hex(random_bytes(32));

                $sessionModel->insert([
                    'user_id' => $teacher['teacher_id'],
                    'role_id' => $teacher['role_id'], // Reference to roles table
                    'session_id' => $session_id
                ]);

                setcookie('session_id', $session_id, time() + 3600, '/', 'localhost', false, true);

                return $this->respond([
                    'success' => "true",
                    'message' => 'Login successful.',
                    'role' => 'teacher',
                    'id' => $teacher['id'],
                    'session_id' => $session_id,
                    'data' => [
                        'id' => $teacher['id'],
                        'firstName' => $teacher['firstName'],
                        'lastName' => $teacher['lastName'],
                        'email' => $teacher['email'],
                        'role_id' => $teacher['role_id'],
                        'dept_id' => $teacher['dept_id'],
                        'role' => 'teacher',
                        'uicon' => $uicon
                    ]

                ], 200);
            }

            // Check Students Table
            $student = $studentModel->where('email', $email)->first();
            if ($student) {
                $firstName = $student['firstName']; // Fetch first name
            $lastName = $student['lastName']; 
            $uicon = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));
                if (!password_verify($password, $student['password'])) {
                    return $this->respond(['message' => 'Invalid credentials.'], 401);
                }

                $session_id = bin2hex(random_bytes(32));
                $this->storeSession_id=$session_id;

                $sessionModel->insert([
                    'user_id' => $student['student_id'],
                    'role_id' => 3, // Assuming '2' is the role ID for students
                    'session_id' => $session_id
                ]);
                //log_message('info', 'Login user_id: ' . $session_id);
                //log_message('info', 'Login user_id1: ' . $this->storeSession_id);

                setcookie('session_id', $session_id, time() + 3600, '/', 'localhost', false, true);
                
                // log_message('info', 'Login user_id2: ' . $session_id);
                //log_message('info', 'Cookies after setting: ' . json_encode($_COOKIE));

                return $this->respond([
                    'success' => "true",
                    'message' => 'Login successful.',
                    'role' => 'student',
                    'id' => $student['id'],
                    'session_id' => $session_id,
                    'data' => [
                        'id' => $student['id'],
                        'firstName' => $student['firstName'],
                        'lastName' => $student['lastName'],
                        'email' => $student['email'],
                        'dept_id' => $student['dept_id'],
                        'uicon' => $uicon,
                        'role' => 'student'
                    ]
                ], 200);
            }

            return $this->respond(['message' => 'User not found.'], 404);
        } catch (\Exception $e) {
            return $this->respond([
                'message' => 'Login failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

/*     public function logout()
    {
        //log_message('info', 'Logout user_id: ' . $session_id);
        //log_message('info', 'Logout user_id: ' . $this->storeSession_id);

        $session_id = $_COOKIE['session_id'] ?? null;
        log_message('info', 'Before Logout Cookies after setting: ' . json_encode($_COOKIE));
        log_message('info', 'Logout session_id: ' . ($session_id ?? 'Not Found'));
        if ($session_id) {
            log_message('info', 'Logout session_id: ' );
            $sessionModel = new SessionsModel();
            $sessionModel->where('session_id', $session_id)->delete();
            setcookie('session_id', '', time() - 3600, '/', 'localhost', false, true);
        }
        session()->destroy();
        log_message('info', 'After logout Cookies after setting: ' . json_encode($_COOKIE));
        return $this->respond(['message' => 'Logged out successfully.'], 200);
    } */

    public function logout()
    {
        $sessionModel = new SessionsModel();
        $data = $this->request->getJSON(true);
    
        // Check if session_id is provided
        if (isset($data['session_id'])) {
            $session_id = $data['session_id'];
    
            // Fetch the session from the database
            $session = $sessionModel->where('session_id', $session_id)->first();
    
            if ($session) {
                // Check if logout_time is NULL (session is active)
                if ($session['logout_time'] === NULL) {
                    // Update logout_time to current timestamp
                    $updateSuccess = $sessionModel->set('logout_time', date('Y-m-d H:i:s'))
                              ->where('session_id', $session_id) // Use the session_id field here
                              ->update();

    
                    // If the update was successful, proceed with logout
                    if ($updateSuccess) {
                        // Expire the session_id cookie properly
                        setcookie('session_id', '', [
                            'expires' => time() - 3600,
                            'path' => '/',
                            'domain' => 'localhost',
                            'secure' => false,
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
    
                        // Unset the session_id in the request data
                        unset($data['session_id']);
    
                        return $this->respond([
                            'success' => true,
                            'message' => 'Logout successful.'
                        ], 200);
                    } else {
                        return $this->respond([
                            'success' => false,
                            'message' => 'Failed to log out. Please try again later.'
                        ], 500);
                    }
                } else {
                    // If logout_time is already set, the session has expired
                    return $this->respond([
                        'success' => false,
                        'message' => 'Session has already expired.'
                    ], 400);
                }
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Session not found.'
                ], 404);
            }
        }
    
        // If no session_id is provided
        return $this->respond([
            'success' => false,
            'message' => 'No active session found.'
        ], 400);
    }
    
    

    public function checkAuth()
    {
        $sessionModel = new SessionsModel();
        $studentModel = new StudentsModel();
        $teacherModel = new TeachersModel();
        $data = $this->request->getJSON(true);
    
        // Check if session_id is provided
        if (!isset($data['session_id'])) {
            return $this->respond([
                'success' => false,
                'message' => 'No active session found.'
            ], 200);
        }
    
        log_message('info', 'Before checkAuth Cookies after setting: ' . json_encode($data));
        $session_id = $data['session_id'];
        $session = $sessionModel->where('session_id', $session_id)->first();
    
        if (!$session) {
            return $this->respond([
                'success' => false,
                'message' => 'Invalid session. Please log in again.'
            ], 200);
        }
    
        // Check if logout_time is NULL (session is still active)
        if ($session['logout_time'] !== NULL) {
            return $this->respond([
                'success' => false,
                'message' => 'Session has expired. Please log in again.'
            ], 200);
        }
    
        // Determine if the user is a teacher or student
        $user = $teacherModel->where('teacher_id', $session['user_id'])->first() ?? $studentModel->where('student_id', $session['user_id'])->first();
    
        if (!$user) {
            return $this->respond([
                'success' => false,
                'message' => 'User not found. Please log in again.'
            ], 200);
        }
    
        return $this->respond([
            'success' => true,
            'message' => 'User authenticated.',
            'user' => [
                'id' => $user['id'],
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
                'email' => $user['email'],
                'role_id' => $session['role_id'],
            ]
        ], 200);
    }
    


/*     public function checkAuth()
    {
        //$session_id = $this->request->getCookie('session_id');
        $session_id = $_COOKIE['session_id'];
        log_message('info', 'Before checkAuth Cookies after setting: ' . json_encode($_COOKIE));
        if (!$session_id) {
            return $this->respond(['authenticated' => false, 'message' => 'Unauthorized access.'], 401);
        }
    
        $sessionModel = new SessionsModel();
        $session = $sessionModel->where('session_id', $session_id)->first();
    
        if (!$session) {
            return $this->respond(['authenticated' => false, 'message' => 'Invalid session.'], 401);
        }
    
        // Regenerate session ID for security
        session()->regenerate();
    
        log_message('info', 'After checkAuth Cookies after setting: ' . json_encode($_COOKIE));
        return $this->respond([
            'authenticated' => true,
            'user_id' => $session['user_id'],
            'role_id' => $session['role_id']
        ], 200); 
    } */
    


}
