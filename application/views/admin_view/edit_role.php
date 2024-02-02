<?php $this->load->view('header'); ?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f8f8;
    }

    .content {
        text-align: center;
        margin: 20px auto;
    }

    h2 {
        color: #333;
        margin: auto;
    }

    form {
        width: 50%;
        margin: auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #333;
    }

    input {
        width: 50%;
        padding: 10px;
        margin-bottom: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    button {
        background-color: #3498db;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 5px;
    }

    button:hover {
        background-color: #2980b9;
    }
</style>

<div class="content">
    <h2>Edit Role</h2>
    <form action="<?php echo base_url('index.php/AdminController/updateRole'); ?>" method="post">
        <input type="hidden" name="role_id" value="<?php echo isset($role['role_id']) ? $role['role_id'] : ''; ?>">

        <label for="role_name">Role Name:</label>
        <input type="text" id="role_name" name="role_name" value="<?php echo isset($role['role_name']) ? $role['role_name'] : ''; ?>" required>

        <button type="submit">Update Role</button>
    </form>
</div>

<?php $this->load->view('footer'); ?>
