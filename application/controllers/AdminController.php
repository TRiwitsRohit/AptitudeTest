<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
class AdminController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->model('AdminModel');
    }

	public function index(){
		$this->load->view('admin_login');
	}
	
	public function login(){

		// Set validation rules
		$this->form_validation->set_rules('admin_name', 'Admin Name', 'required');
		$this->form_validation->set_rules('admin_password', 'Admin Password', 'required');
		if ($this->form_validation->run() === FALSE) {
			// Validation failed, show login form with errors
			$this->load->view('admin_login');
		} else {
			// Validation passed, process the login
			$admin_name = $this->input->post('admin_name');
			$admin_password = $this->input->post('admin_password');
		
			// Example: Check admin credentials in the database
			$admin = $this->AdminModel->get_AdminByCredentials($admin_name, $admin_password);
			if ($admin) {
				// Admin login successful, redirect to the admin dashboard or any other page
				redirect('AdminController/adminView');
			} else {
				// Admin login failed, show error message
				$data['login_error'] = 'Invalid admin credentials. Please try again.';
				$this->load->view('admin_login', $data);
			}
		}
	}

	public function adminView() {
		$this->load->library('pagination');
		
		// Pagination configuration
		$config['base_url'] = base_url('index.php/AdminController/adminView');
		$config['total_rows'] = $this->AdminModel->count_AllUserData();
		$config['per_page'] = 10; // Number of records per page
		$config['uri_segment'] = 3; // The segment number containing the offset
	
		$this->pagination->initialize($config);
	
		// Get the current offset from the URL segment
		$offset = $this->uri->segment(3) ? $this->uri->segment(3) : 0;
	
		// Load data from the model using the pagination parameters
		$data['result'] = $this->AdminModel->get_AllUserData($config['per_page'], $offset);
		$data['active_tab'] = 'home';
	
		// Load the view with pagination data
		$this->load->view('admin_view/admin_dashboard', $data);
	}
		
		
	public function viewRole(){
		// Fetch all roles from the database
		$data['active_tab'] = 'view_roles';
        $data['roles'] = $this->AdminModel->get_All_Roles();

        // Load the view to display the roles
		$this->load->view('admin_view/view_role', $data);
	}

	public function addRole(){
	
		// Set validation rules
		$this->form_validation->set_rules('role_id', 'Role ID', 'required');
		$this->form_validation->set_rules('role_name', 'Role Name', 'required');
	
		if ($this->form_validation->run() === FALSE) {
			// Validation failed, load the view with error messages
			$data['active_tab'] = 'view_roles';
			$this->load->view('admin_view/add_role', $data);
		} else {
			// Validation passed, proceed with adding the role
			$roleData = array(
				'role_id' => $this->input->post('role_id'),
				'role_name' => $this->input->post('role_name')
				// Add other fields if necessary
			);
	
			// Call the addRole method to insert the role into the database
			$roleId = $this->AdminModel->add_Role($roleData);
	
			// You can add further logic or redirect as needed
			if ($roleId) {
				// Role added successfully, redirect or show a success message
				redirect('AdminController/viewRole');
			} else {
				// Error handling, redirect or show an error message
				echo "Error adding role";
			}
		}
	}

	public function editRole($role_id) {
		if ($this->form_validation->run() === FALSE) {
			// Validation failed, load the view with error messages
			$data['active_tab'] = 'view_roles';
			$data['role'] = $this->AdminModel->get_Role($role_id);
			$this->load->view('admin_view/edit_role', $data);
		} else {
			// Validation passed, update the role data in the database
			$newRoleName = $this->input->post('role_name');
			// Get the existing role details
			$existingRole = $this->AdminModel->get_Role($role_id);
	
			if (!$existingRole) {
				// Role not found, handle accordingly (e.g., show an error message)
				echo "Role not found";
				return;
			}
			$role_name = $existingRole['role_name'];
			// Check if the table exists before attempting to delete it
			if ($this->tableExists($role_name . '_table')) {
				// Rename the table to deleted_role_name_table
				$original_table_name = $role_name . '_table';
				$new_table_name = $newRoleName. '_table';
	
				// Rename the table using a SQL query
				$this->db->query("RENAME TABLE $original_table_name TO $new_table_name");
			}
			// Update the role name in the database
			$roleData = array(
				'role_name' => $newRoleName,
				// Add other fields if necessary
			);
	
			// Call the updateRole method to update the role in the database
			$result = $this->AdminModel->update_Role($role_id, $roleData);
	
			// Check the result and redirect accordingly
			if ($result) {
				// Role updated successfully, redirect or show a success message
				redirect('AdminController/viewRole');
			} else {
				// Error handling, redirect or show an error message
				echo "Error updating role";
			}
		}
	}
	
	public function deleteRole($role_id) {
		// Check if the role with the given ID exists
		$existingRole = $this->AdminModel->get_Role($role_id);
	
		if (!$existingRole) {
			// Role not found, handle accordingly (e.g., show an error message)
			echo "Role not found";
			return;
		}
	
		// Get the role name directly from the existingRole object
		$role_name = $existingRole['role_name'];
		$original_table_name = $this->limitTableNameLength(strtolower(str_replace(' ', '_', $role_name)) . '_table');
	
		// Check if the table exists before attempting to delete it
		if ($this->tableExists($original_table_name)) {
			// Rename the table to deleted_role_name_table
			$deleted_table_name = 'deleted_' . $original_table_name;
	
			// Rename the table using a SQL query
			$this->db->query("RENAME TABLE $original_table_name TO $deleted_table_name");
	
			// Insert the role into the second database
			$this->db->insert('deleted_role', array('role_id' => $role_id, 'role_name' => $role_name));
		}
	
		// Proceed with role deletion from the original database
		$result = $this->AdminModel->delete_Role($role_id);
	
		if ($result) {
			// Role deleted successfully, redirect or show a success message
			redirect('AdminController/viewRole');
		} else {
			// Error handling, redirect or show an error message
			echo "Error deleting role";
		}
	}
	
	public function limitTableNameLength($tableName, $maxLength = 64) {
		return substr($tableName, 0, $maxLength);
	}

	public function tableExists($table) {
		$query = $this->db->query("SHOW TABLES LIKE '$table'");
		return $query->num_rows() > 0;
	}
	

	public function updateRole() {
		// Validate the form data
		$this->form_validation->set_rules('role_id', 'Role ID', 'required');
		$this->form_validation->set_rules('role_name', 'Role Name', 'required');
	
		if ($this->form_validation->run() === FALSE) {
			// Validation failed, load the view with error messages
			$data['active_tab'] = 'view_roles';
			$this->load->view('admin_view/edit_role', $data);
		} else {
			// Validation passed, update the role
			$role_id = $this->input->post('role_id');
			$role_name = $this->input->post('role_name');
	
			$roleData = array(
				'role_name' => $role_name,
				// Add other fields if necessary
			);
	
			// Get the existing role data to check the current role name
			$existingRole = $this->AdminModel->get_Role($role_id);
	
			if (!$existingRole) {
				// Role not found, handle accordingly (e.g., show an error message)
				echo "Role not found";
				return;
			}
	
			// Check if the role name has been changed
			if ($role_name !== $existingRole['role_name']) {
				$original_table_name = $this->limitTableNameLength(strtolower(str_replace(' ', '_', $existingRole['role_name'])) . '_table');
				$new_table_name = $this->limitTableNameLength(strtolower(str_replace(' ', '_', $role_name)) . '_table');
	
				// Check if the table exists before attempting to rename it
				if ($this->tableExists($original_table_name)) {
					// Rename the table to the new table name
					$this->db->query("RENAME TABLE $original_table_name TO $new_table_name");
				}
			}
	
			// Call the updateRole method to update the role in the database
			$result = $this->AdminModel->update_Role($role_id, $roleData);
	
			if ($result) {
				// Role updated successfully, redirect or show a success message
				redirect('AdminController/viewRole');
			} else {
				// Error handling, redirect or show an error message
				echo "Error updating role";
			}
		}
	}
	
	public function addQuestionPaper() {
		// Load necessary models or libraries if needed
		$this->form_validation->set_rules('role', 'Role', 'required');
		$this->form_validation->set_rules('question_paper_file', 'Question Paper File', 'callback_file_upload');
	
		if ($this->form_validation->run() === FALSE) {
			echo validation_errors();
			$data['active_tab'] = 'add_question_paper';
			$data['roles'] = $this->AdminModel->get_All_Roles();
			$this->load->view('admin_view/add_que_paper', $data);
		} else {
	
			// Form validation passed, process the form data
			$role_id = $this->input->post('role');
	
			// Get the role name
			$role = $this->AdminModel->get_RoleNameById($role_id);
			$role_name = $role->role_name;
	
			// Create a unique table name for the role
			$table_name = strtolower(str_replace(' ', '_', $role_name)) . '_table';
	
			// Create a new table for the role if it doesn't exist
			$this->db->query("CREATE TABLE IF NOT EXISTS $table_name (
				q_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    			q_text VARCHAR(255) NOT NULL,
    			option_1 VARCHAR(60) NOT NULL,
    			option_2 VARCHAR(60) NOT NULL,
    			option_3 VARCHAR(60) NOT NULL,
    			option_4 VARCHAR(60) NOT NULL,
    			correct_ans VARCHAR(60)
			)");
	
			// Example: Upload the question paper file
			$config['upload_path'] = 'public/files/';
			$config['allowed_types'] = 'pdf|doc|docx|xls|xlsx|csv';
			$config['max_size'] = 2048; // 2MB
			$this->load->library('upload', $config);
	
			if ($this->upload->do_upload('question_paper_file')) {
				// File uploaded successfully, you can handle the file details here
				$fileData = $this->upload->data();
	
				// Read the Excel file using PhpSpreadsheet
				$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileData['full_path']);
				$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
	
				// Assuming the Excel columns are in a specific order (adjust as needed)
				foreach ($sheetData as $row) {
					$dataToInsert = array(
						'q_text' => $row['A'], // Replace 'column1', 'column2', etc., with actual column names
						'option_1' => $row['B'],
						'option_2' => $row['C'],
						'option_3' => $row['D'],
						'option_4' => $row['E'],
						'correct_ans' => $row['F'],
						// Add more columns as needed
					);
	
					// Insert data into the dynamically created table
					$this->db->insert($table_name, $dataToInsert);
				}
	
				// Get the file extension
				$file_ext = pathinfo($fileData['file_name'], PATHINFO_EXTENSION);
	
				// Generate a new file name based on role name and extension
				$new_file_name = $role_name . '.' . $file_ext;
				$new_file_path = $config['upload_path'] . $new_file_name;
				rename($fileData['full_path'], $new_file_path);
	
				// Now you can use $new_file_name and $new_file_path as needed
	
				// Redirect or show a success message
				redirect('AdminController/adminView');
			} else {
				// File upload failed, show an error message
				$data['upload_error'] = $this->upload->display_errors();
				echo $this->upload->display_errors();
				$this->load->view('admin_view/add_que_paper', $data);
			}
		}
	}
	
	// callback function for file upload validation
	public function file_upload($str) {
		// Example: Check if the file type is allowed
		$allowed_types = array('xls', 'xlsx', 'csv');
		$file_ext = pathinfo($_FILES['question_paper_file']['name'], PATHINFO_EXTENSION);	
	
		if (!in_array($file_ext, $allowed_types)) {
			$this->form_validation->set_message('file_upload', 'Invalid file type. Allowed types are: ' . implode(', ', $allowed_types));
			return FALSE;
		}
		// You can add more validation logic based on your requirements
	
		return TRUE;
	}

	// AdminController.php
public function viewResult() {
    $this->load->library('pagination');
    
    // Pagination configuration
    $config['base_url'] = base_url('index.php/AdminController/viewResult');
    $config['total_rows'] = $this->AdminModel->count_AllUserData();
    $config['per_page'] = 10; // Number of records per page
    $config['uri_segment'] = 3; // The segment number containing the offset

    $this->pagination->initialize($config);

    // Get the current offset from the URL segment
    $offset = $this->uri->segment(3) ? $this->uri->segment(3) : 0;

    // Load data from the model using the pagination parameters
    $data['result'] = $this->AdminModel->get_AllUserData($config['per_page'], $offset);
    $data['active_tab'] = 'view_result';

    // Load the view with pagination data
    $this->load->view('admin_view/view_result', $data);
}


    // AdminController.php

public function updateStatus() {
    $this->form_validation->set_rules('id', 'ID', 'required');
    $this->form_validation->set_rules('step', 'Update Type', 'required');
	$this->form_validation->set_rules('status', 'Status', 'required');

    if ($this->form_validation->run() === FALSE) {
        // Validation failed
        $data['active_tab'] = 'view_result';
        $this->load->view('admin_view/add_status', $data);
    } else {
        $id = $this->input->post('id');
		$step = $this->input->post('step');
        $status = $this->input->post('status');

        $this->load->model('AdminModel');
        $this->AdminModel->update_Status($id, $status, $step);

        // Redirect to the method where you display the data or to the desired page
        redirect('AdminController/viewResult');
    }
}

	public function deleteUser() {
    	$id = $this->input->post('id');

    	// Add logic to delete the user with the specified ID
    	// For example, assuming you have a model method for deleting:
    	$this->AdminModel->delete_UserById($id);

		// Redirect or load the view as needed
    	redirect('AdminController/viewResult');
	}

	public function fetch() {
		// Get the selected order from the form
		$order = $this->input->post('order');
		
		// Default order if not provided or invalid
		if ($order !== 'asc' && $order !== 'desc') {
		$order = 'asc';
		}
		$config['base_url'] = base_url('index.php/AdminController/viewResult');
    	$config['total_rows'] = $this->AdminModel->count_AllUserData();
    	$config['per_page'] = 10; // Number of records per page
    	$config['uri_segment'] = 3; // The segment number containing the offset

    	$this->pagination->initialize($config);

    	// Get the current offset from the URL segment
    	$offset = $this->uri->segment(3) ? $this->uri->segment(3) : 0;

    	// Load data from the model using the pagination parameters
    	$data['result'] = $this->AdminModel->get_AllUserData($config['per_page'], $offset);
		$data['active_tab'] = 'view_result';
		// Call the function from the model to get sorted records with the selected order
		$data['result'] = $this->AdminModel->fetch($order);

		// Load the view and pass the data
		$this->load->view('admin_view/view_result', $data);
		}



    // Other controller methods...


	public function logout() {

        // Redirect to the login page or any other page after logout
        redirect('AdminController');
    }

	
}
