<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentsModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'firstName', 'lastName','student_id', 'email', 'password', 'phone', 
        'gender', 'dept_id', 'skills', 'interest', 'college_id'
    ];

    public function getAllStudents()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('students');

        $builder->select('
            students.*,
            departments.dept_name,
            colleges.college');
        $builder->join('departments', 'students.dept_id = departments.id', 'left');
        $builder->join('colleges', 'students.college_id = colleges.id', 'left');

        $query = $builder->get();
        return $query->getResultArray(); // Return the data as an associative array
    }


    public function getStudentById($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $builder->select('
            students.*,
            departments.dept_name,
            colleges.college, degree.degree');
            $builder->join('departments', 'students.dept_id = departments.id', 'left');
        $builder->join('colleges', 'students.college_id = colleges.id', 'left');
        $builder->join('degree', 'departments.degree_id = degree.id', 'left');        
        $builder->where('students.id', $id);

        $query = $builder->get();
        return $query->getRowArray(); // Return a single row as an associative array
    }

 /*    public function subscribeByEmail($email)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('students');
    
        // Update the 'subscribe' attribute to 1 where the email matches
        $builder->set('subscribe', 1);
        $builder->where('email', $email);
        $builder->update();
    
        // Check if the row was updated
        if ($db->affectedRows() > 0) {
            return true; // Subscription successful
        } else {
            return false; // Email not found or already subscribed
        }
    } */

    public function subscribeByEmail($email)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('students');
    
        // Update the 'subscribe' attribute to 1 where the email matches
        $builder->set('subscribe', 1);
        $builder->where('email', $email);
        $builder->update();
    
        // Log the query to check for any errors
        if ($db->affectedRows() > 0) {
            // If email is updated, prepare and send the email
            $subject = "Subscription Successful!";
            $message = "Thank you for subscribing to our newsletter.";
    
            // Use PHPMailer or mail() to send the email
            $mail = new \PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sakshishaw1375@gmail.com'; // Your SMTP username
            $mail->Password   = 'suji ukrf bwtb lcpp';   // Your SMTP password use App password
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
    
            $mail->setFrom('shawsakshi1375@gmail.com', 'Your Website');
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body    = $message;
    
            if ($mail->send()) {
                return true; // Subscription successful and email sent
            } else {
                // Log the error if email sending fails
                return false; // Email sending failed
            }
        } else {
            // Log why the update failed (e.g., email not found or already subscribed)
            return false; // Email not found or already subscribed
        }
    }
    







    public function updateStudent($data)
    {
        if (empty($data['id'])) {
            return false; // Return false if ID is missing
        }

        $student = $this->find($data['id']);
        if (!$student) {
            return false; // Return false if student not found
        }

        return $this->update($data['id'], $data); // Update the student record
    }


    // Check if email exists
    public function isEmailExist($email)
    {
        return $this->where('email', $email)->first();
    }

    // Update password
    public function updatePassword($email, $newPassword)
    {
        return $this->where('email', $email)->set('password', $newPassword)->update();
    }
}
