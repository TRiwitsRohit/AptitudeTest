<?php $this->load->view('header'); ?>

<link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css') ?>">

<!-- Fixed header div -->
<div class="fixed-header">
    <?php $this->load->view('header'); ?>
</div>

<div class="content content-with-fixed-header">
    <div class="table-responsive table-container">
        <table class="table table-bordered">
            <thead class="fixed-header">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Mobile Number</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Qualification</th>
                    <th>Date</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($result)) : foreach ($result as $data) : ?>
                    <tr>
                        <td><?php echo $data['id'] ?></td>
                        <td><?php echo $data['name'] ?></td>
                        <td><?php echo $data['mobile'] ?></td>
                        <td><?php echo $data['email'] ?></td>
                        <td><?php echo $data['role'] ?></td>
                        <td><?php echo $data['qualification'] ?></td>
                        <td><?php echo $data['date'] ?></td>
                        <td><?php echo $data['score'] ?></td>
                        <td><?php echo $data['grade'] ?></td>
                        <td>
                            <!-- Add data for the new sub-columns -->
                            1:<?php echo !empty($data['followup_1']) ? $data['followup_1'] : 'No status'; ?><br>
                            2: <?php echo !empty($data['followup_2']) ? $data['followup_2'] : 'No status'; ?><br>
                            3: <?php echo !empty($data['status']) ? $data['status'] : 'No status'; ?>
                        </td>
                        <td>
                            <form action="<?php echo base_url('index.php/AdminController/updateStatus'); ?>" method="post">
                                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                <button type="submit" class="update-button">Update Status</button>
                            </form>
                            <form action="<?php echo base_url('index.php/AdminController/deleteUser'); ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else : ?>
                    <tr>
                        <td colspan="11">No records found in the database!!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="pagination-links">
        <?php echo $this->pagination->create_links(); ?>
    </div>
</div>

<?php $this->load->view('footer'); ?>
