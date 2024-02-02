<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class TestController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('TestModel');
    }

    public function index() {
        // Load roles from TestModel and display the test form
        $data['roles'] = $this->TestModel->getRoles();
        $this->load->view('user_view/test_form', $data);
    }

    public function processForm() {
        // Validate form input
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'required|regex_match[/^[6-9]\d{9}$/]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|strtolower');
        $this->form_validation->set_rules('role', 'Role', 'required');
        $this->form_validation->set_rules('qualification', 'Qualification', 'required');
    
        if ($this->form_validation->run() == FALSE) {
            // If validation fails, reload the test form
            $this->index();
        } else {
            // Form validation passed
            $role = $this->input->post('role');
            $email = $this->input->post('email');
    
            // Check if the user already applied for the same role within 90 days
            $userData = array(
                'email' => $email,
                'role' => $role,
            );
            $isUserExistsForRole = $this->TestModel->checkUserExists($userData);
    
            // Check if the user is allowed to apply
            $isUserAllowed = $this->TestModel->checkUserAllowed($email, $role);
    
            if ($isUserExistsForRole) {
                // User already applied for the same role within 90 days
                $this->session->set_flashdata('error', 'You have already applied for this role try after 90 days. Please contact admin if page refresh issue.');
                $this->index();
            } elseif (!$isUserAllowed) {
                // User already applied for 2 roles within 90 days
                $this->session->set_flashdata('error', 'You have already applied for 2 roles try after 90 days.');
                $this->index();
            } else {
                // New user or applying for a different role, proceed
                $userData = array(
                    'name' => $this->input->post('name'),
                    'mobile' => $this->input->post('mobile'),
                    'email' => $email,
                    'role' => $role,
                    'qualification' => $this->input->post('qualification'),
                );
    
                $this->TestModel->addUser($userData);
                $this->session->set_userdata('user_email', $email);
                $this->session->set_userdata('user_role', $role);
                $this->session->set_userdata('userData', $userData);
                $data['questions'] = $this->getQuestions($role);
                $this->load->view('user_view/quiz_form', $data);
            }
        }
    }
    
    public function submit() {
        // Process user's submitted answers and update score
        $score = 0;
        $userRole = $this->session->userdata('user_role');
        $usersData = $this->session->userdata('userData');
        
        foreach ($this->getQuestions($userRole) as $question) {
            $user_answer = $this->input->post("q" . $question['q_id']);
            if ($user_answer === $question['correct_ans']) {
                $score++;
            }
        }
    
        $grade = $this->calculateGrade($score);
        $userEmail = $this->session->userdata('user_email');
    
        if (!empty($userEmail)) {
            // Update user's score in the database
            $this->TestModel->update_UserScoreByEmail($userEmail, $score, $grade);
            $data['score'] = $score;
            $data['grade'] = $grade; // Make sure $grade is defined
            // At the end of your submit() function
            unset($_SESSION['quiz_progress']);
            $this->load->view('user_view/result', $data);
        } else {
            echo "User email not found in the session. Unable to update the score.";
        }
    }
    
    public function getQuestions($role) {
        // Retrieve questions from the database based on user's role
        $table_name = strtolower(str_replace(' ', '_', $role)) . '_table';

        // Retrieve questions from the database based on user's role
        $query = $this->db->get($table_name);
        return $query->result_array();
       
    }

    public function calculateGrade($score) {
        if ($score <= 60 && $score>=48) { //above  85%
            return 'A';
        } elseif ($score<48 && $score >= 39) {//85% to 65%
            return 'B';
        } elseif ($score<39 && $score >=21 ){ //65% to 35%
            return 'C';
        }else{ //less tha 35%
            return 'Fail';
        }
    }

    public function logout() {
        // Destroy session and redirect to the test form
        $this->session->sess_destroy();
        redirect('TestController');
    }
}