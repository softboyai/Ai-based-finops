<?php
require_once __DIR__ . '/../config/db.php';
requireRole('admin');
$pageTitle = 'User Management';

$message = '';
$error = '';
$errors = [];

// Validation functions
function validateName($name) {
    if (empty($name)) return 'Full name is required.';
    if (strlen($name) < 3) return 'Name must be at least 3 characters.';
    if (strlen($name) > 100) return 'Name must not exceed 100 characters.';
    if (!preg_match('/^[a-zA-Z\s\-]+$/', $name)) return 'Name must contain only letters, spaces, and hyphens.';
    return '';
}

function validateEmail($email) {
    if (empty($email)) return 'Email address is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Please enter a valid email address.';
    if (strlen($email) > 100) return 'Email must not exceed 100 characters.';
    return '';
}

function validateUsername($username) {
    if (empty($username)) return 'Username is required.';
    if (strlen($username) < 3) return 'Username must be at least 3 characters.';
    if (strlen($username) > 50) return 'Username must not exceed 50 characters.';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) return 'Username must contain only letters, numbers, and underscores.';
    return '';
}

function validatePassword($password, $isNew = true) {
    if ($isNew && empty($password)) return 'Password is required for new users.';
    if (!empty($password)) {
        if (strlen($password) < 6) return 'Password must be at least 6 characters.';
        if (strlen($password) > 50) return 'Password must not exceed 50 characters.';
        if (!preg_match('/[A-Za-z]/', $password)) return 'Password must contain at least one letter.';
        if (!preg_match('/[0-9]/', $password)) return 'Password must contain at least one number.';
    }
    return '';
}

function validateRole($role) {
    $validRoles = ['admin', 'finance_officer', 'management'];
    if (empty($role)) return 'Role is required.';
    if (!in_array($role, $validRoles)) return 'Invalid role selected.';
    return '';
}

// Handle add/edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $userId = $_POST['user_id'] ?? '';

    $isEditing = ($action === 'edit' && $userId);

    // Validate all fields
    $nameErr = validateName($name);
    $emailErr = validateEmail($email);
    $usernameErr = validateUsername($username);
    $passwordErr = validatePassword($password, !$isEditing);
    $roleErr = validateRole($role);

    if ($nameErr) $errors[] = $nameErr;
    if ($emailErr) $errors[] = $emailErr;
    if ($usernameErr) $errors[] = $usernameErr;
    if ($passwordErr) $errors[] = $passwordErr;
    if ($roleErr) $errors[] = $roleErr;

    if (empty($errors)) {
        if ($action === 'add') {
            // Check unique username
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Username already exists. Choose a different one.';
            }
            // Check unique email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already in use by another user.';
            }

            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $username, $hashedPassword, $role, $status]);
                $message = 'User created successfully.';
                // Reset form values
                $name = $email = $username = $password = $role = '';
            }
        } elseif ($isEditing) {
            // Check unique username (exclude current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                $errors[] = 'Username already exists.';
            }
            // Check unique email (exclude current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already in use by another user.';
            }

            if (empty($errors)) {
                $updateFields = "name = ?, email = ?, username = ?, role = ?, status = ?";
                $params = [$name, $email, $username, $role, $status];

                if (!empty($password)) {
                    $updateFields .= ", password = ?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $params[] = $userId;

                $stmt = $pdo->prepare("UPDATE users SET $updateFields WHERE id = ?");
                $stmt->execute($params);
                $message = 'User updated successfully.';
            }
        }
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}

// Handle deactivate
if (isset($_GET['deactivate'])) {
    $id = (int)$_GET['deactivate'];
    if ($id !== (int)$_SESSION['user_id']) {
        $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?")->execute([$id]);
        $message = 'User deactivated.';
    } else {
        $error = 'You cannot deactivate your own account.';
    }
}

// Handle activate
if (isset($_GET['activate'])) {
    $id = (int)$_GET['activate'];
    $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
    $message = 'User activated.';
}

// Get all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// Get user for editing
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editUser = $stmt->fetch();
}

include __DIR__ . '/../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- Role Descriptions -->
<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:25px;">
    <h3 style="margin-bottom:12px;">User Roles & Permissions</h3>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:var(--light-gray);">
                <th style="padding:10px 15px;text-align:left;color:var(--primary);">Role</th>
                <th style="padding:10px 15px;text-align:left;color:var(--primary);">What They Can Do</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px 15px;"><strong>Administrator</strong></td>
                <td style="padding:10px 15px;color:var(--gray);font-size:0.9rem;">
                    Full system access — manage users, system settings, view all reports, review AI risk alerts, 
                    manage customers & transactions. Has control over the entire FinOps MIS platform.
                </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px 15px;"><strong>Finance Officer</strong></td>
                <td style="padding:10px 15px;color:var(--gray);font-size:0.9rem;">
                    Day-to-day operations — <strong>process payments</strong> (deposits, withdrawals, loan repayments), 
                    manage customer accounts, handle loan schedules, <strong>generate reports</strong> (transaction summaries, 
                    income/expense, loan status), and <strong>update financial reports</strong> with observations and analysis.
                </td>
            </tr>
            <tr>
                <td style="padding:10px 15px;"><strong>Management Staff</strong></td>
                <td style="padding:10px 15px;color:var(--gray);font-size:0.9rem;">
                    Oversight and decision-making — view financial reports, AI insights, risk alerts. 
                    <strong>Monitor performance</strong>: track KPIs (customer growth, transaction volume, loan repayment rates), 
                    review finance officer productivity, portfolio distribution, and receive AI-generated health assessments.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Add/Edit User Form -->
<div class="form-container" style="max-width:100%; margin-bottom:30px;">
    <h3><?= $editUser ? 'Edit User' : 'Add New User' ?></h3>
    <form method="POST" id="userForm" novalidate>
        <input type="hidden" name="action" value="<?= $editUser ? 'edit' : 'add' ?>">
        <?php if ($editUser): ?>
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Full Name <span style="color:var(--danger);">*</span></label>
                <input type="text" name="name" id="nameField" required 
                       pattern="^[a-zA-Z\s\-]{3,100}$"
                       title="Only letters, spaces, and hyphens. Min 3 characters."
                       value="<?= sanitize($editUser['name'] ?? $name ?? '') ?>"
                       placeholder="e.g. Jean Baptiste Uwimana">
                <small style="color:var(--gray);font-size:0.75rem;">Letters, spaces, hyphens only. Min 3 characters.</small>
            </div>
            <div class="form-group">
                <label>Email Address <span style="color:var(--danger);">*</span></label>
                <input type="email" name="email" id="emailField" required 
                       title="Enter a valid email address"
                       value="<?= sanitize($editUser['email'] ?? $email ?? '') ?>"
                       placeholder="e.g. jean@goshenfinance.rw">
                <small style="color:var(--gray);font-size:0.75rem;">Valid email address required.</small>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Username <span style="color:var(--danger);">*</span></label>
                <input type="text" name="username" id="usernameField" required 
                       pattern="^[a-zA-Z0-9_]{3,50}$"
                       title="Letters, numbers, underscores only. Min 3 characters."
                       value="<?= sanitize($editUser['username'] ?? $username ?? '') ?>"
                       placeholder="e.g. jbaptiste">
                <small style="color:var(--gray);font-size:0.75rem;">Letters, numbers, underscores. Min 3 characters.</small>
            </div>
            <div class="form-group">
                <label>Password <span style="color:var(--danger);"><?= $editUser ? '' : '*' ?></span></label>
                <input type="password" name="password" id="passwordField" 
                       <?= $editUser ? '' : 'required' ?>
                       minlength="6"
                       title="Min 6 characters, must include at least one letter and one number."
                       placeholder="<?= $editUser ? 'Leave blank to keep current' : 'Min 6 chars, letter + number' ?>">
                <small style="color:var(--gray);font-size:0.75rem;">Min 6 characters. Must contain a letter and a number.</small>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Role <span style="color:var(--danger);">*</span></label>
                <select name="role" id="roleField" required>
                    <option value="">— Select Role —</option>
                    <option value="admin" <?= ($editUser['role'] ?? $role ?? '') === 'admin' ? 'selected' : '' ?>>Administrator (Full Access)</option>
                    <option value="finance_officer" <?= ($editUser['role'] ?? $role ?? '') === 'finance_officer' ? 'selected' : '' ?>>Finance Officer (Operations)</option>
                    <option value="management" <?= ($editUser['role'] ?? $role ?? '') === 'management' ? 'selected' : '' ?>>Management Staff (Read-Only)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= ($editUser['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($editUser['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div id="validationMessage" style="display:none;margin-bottom:15px;" class="alert alert-danger"></div>

        <button type="submit" class="btn btn-primary" style="width:auto;">
            <?= $editUser ? 'Update User' : 'Create User' ?>
        </button>
        <?php if ($editUser): ?>
            <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-warning" style="width:auto;">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- Users Table -->
<div class="table-container">
    <div class="table-header">
        <h3>All Users (<?= count($users) ?>)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Role</th>
                <th>What They Do</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= sanitize($user['name']) ?></td>
                <td><?= sanitize($user['email'] ?? '—') ?></td>
                <td><strong><?= sanitize($user['username']) ?></strong></td>
                <td><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></td>
                <td style="font-size:0.8rem;color:var(--gray);max-width:200px;">
                    <?php
                    switch ($user['role']) {
                        case 'admin':
                            echo 'Full access: users, settings, alerts, all operations';
                            break;
                        case 'finance_officer':
                            echo 'Process payments, manage customers, generate & update reports';
                            break;
                        case 'management':
                            echo 'Monitor performance, view reports & AI insights (read-only)';
                            break;
                    }
                    ?>
                </td>
                <td><span class="badge badge-<?= $user['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($user['status']) ?></span></td>
                <td><?= formatDate($user['created_at']) ?></td>
                <td>
                    <a href="?edit=<?= $user['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    <?php if ($user['status'] === 'active' && $user['id'] !== $_SESSION['user_id']): ?>
                        <a href="?deactivate=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this user?')">Deactivate</a>
                    <?php elseif ($user['status'] === 'inactive'): ?>
                        <a href="?activate=<?= $user['id'] ?>" class="btn btn-sm btn-success">Activate</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Client-side validation -->
<script>
document.getElementById('userForm').addEventListener('submit', function(e) {
    var errors = [];
    var name = document.getElementById('nameField').value.trim();
    var email = document.getElementById('emailField').value.trim();
    var username = document.getElementById('usernameField').value.trim();
    var password = document.getElementById('passwordField').value;
    var role = document.getElementById('roleField').value;
    var isEdit = document.querySelector('input[name="action"]').value === 'edit';

    // Validate name
    if (name.length < 3) {
        errors.push('Name must be at least 3 characters.');
    } else if (!/^[a-zA-Z\s\-]+$/.test(name)) {
        errors.push('Name must contain only letters, spaces, and hyphens. No numbers or special characters.');
    }

    // Validate email
    if (!email) {
        errors.push('Email address is required.');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push('Please enter a valid email address (e.g. user@example.com).');
    }

    // Validate username
    if (username.length < 3) {
        errors.push('Username must be at least 3 characters.');
    } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        errors.push('Username must contain only letters, numbers, and underscores. No spaces or special characters.');
    }

    // Validate password
    if (!isEdit && !password) {
        errors.push('Password is required.');
    }
    if (password) {
        if (password.length < 6) {
            errors.push('Password must be at least 6 characters.');
        }
        if (!/[A-Za-z]/.test(password)) {
            errors.push('Password must contain at least one letter.');
        }
        if (!/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number.');
        }
    }

    // Validate role
    if (!role) {
        errors.push('Please select a role.');
    }

    var msgDiv = document.getElementById('validationMessage');
    if (errors.length > 0) {
        e.preventDefault();
        msgDiv.innerHTML = errors.join('<br>');
        msgDiv.style.display = 'block';
        window.scrollTo({top: msgDiv.offsetTop - 100, behavior: 'smooth'});
    } else {
        msgDiv.style.display = 'none';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
