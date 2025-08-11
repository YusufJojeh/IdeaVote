<?php
ob_start();
include 'includes/navbar.php';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_admin();

// Handle add user
$user_feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!csrf_verify()) { $user_feedback = '<div class="alert alert-danger">CSRF verification failed.</div>'; }
    else {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] === 'admin' ? 1 : 0;
    if (!validate_username($username) || !validate_email($email) || strlen($password) < 6) {
        $user_feedback = '<div class="alert alert-danger">Invalid input. Please check all fields.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username=? OR email=?");
        mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $user_feedback = '<div class="alert alert-danger">Username or email already exists.</div>';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = mysqli_prepare($conn, "INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt2, 'sssi', $username, $email, $hash, $role);
            if (mysqli_stmt_execute($stmt2)) {
                $user_feedback = '<div class="alert alert-success">User added successfully!</div>';
            } else {
                $user_feedback = '<div class="alert alert-danger">Failed to add user.</div>';
            }
            mysqli_stmt_close($stmt2);
        }
        mysqli_stmt_close($stmt);
    }
}
}
// Handle edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!csrf_verify()) { $user_feedback = '<div class="alert alert-danger">CSRF verification failed.</div>'; }
    else {
    $edit_id = intval($_POST['edit_id']);
    $username = trim($_POST['edit_username']);
    $email = trim($_POST['edit_email']);
    $password = $_POST['edit_password'];
    $role = $_POST['edit_role'] === 'admin' ? 1 : 0;
    if (!validate_username($username) || !validate_email($email)) {
        $user_feedback = '<div class="alert alert-danger">Invalid input. Please check all fields.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE (username=? OR email=?) AND id!=?");
        mysqli_stmt_bind_param($stmt, 'ssi', $username, $email, $edit_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $user_feedback = '<div class="alert alert-danger">Username or email already exists.</div>';
        } else {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, password=?, is_admin=? WHERE id=?");
                mysqli_stmt_bind_param($stmt2, 'sssii', $username, $email, $hash, $role, $edit_id);
            } else {
                $stmt2 = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, is_admin=? WHERE id=?");
                mysqli_stmt_bind_param($stmt2, 'ssii', $username, $email, $role, $edit_id);
            }
            if (mysqli_stmt_execute($stmt2)) {
                $user_feedback = '<div class="alert alert-success">User updated successfully!</div>';
            } else {
                $user_feedback = '<div class="alert alert-danger">Failed to update user.</div>';
            }
            mysqli_stmt_close($stmt2);
        }
        mysqli_stmt_close($stmt);
    }
}
}
// Handle delete user
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header('Location: admin.php?tab=users&msg=deleted'); exit();
}
$tab = $_GET['tab'] ?? 'dashboard';
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $user_feedback = '<div class="alert alert-success">User deleted successfully.</div>';
}
// Fetch users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
// Fix: Fetch ideas with username
$ideas = mysqli_query($conn, "SELECT ideas.*, users.username FROM ideas JOIN users ON ideas.user_id = users.id ORDER BY ideas.created_at DESC");
$votes = mysqli_query($conn, "SELECT v.*, u.username, i.title FROM votes v JOIN users u ON v.user_id=u.id JOIN ideas i ON v.idea_id=i.id ORDER BY v.created_at DESC");
$comments = mysqli_query($conn, "SELECT c.*, u.username, i.title FROM comments c JOIN users u ON c.user_id=u.id JOIN ideas i ON c.idea_id=i.id ORDER BY c.created_at DESC");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");

// Handle add/edit/delete for ideas
$idea_feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_idea'])) {
    if (!csrf_verify()) { $idea_feedback = '<div class="alert alert-danger">CSRF verification failed.</div>'; }
    else {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $user_id = intval($_POST['user_id']);
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    if (strlen($title) < 3 || strlen($description) < 10 || $category_id <= 0 || $user_id <= 0) {
        $idea_feedback = '<div class="alert alert-danger">Invalid input. Please check all fields.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO ideas (user_id, category_id, title, description, is_public) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iissi', $user_id, $category_id, $title, $description, $is_public);
        if (mysqli_stmt_execute($stmt)) {
            $idea_feedback = '<div class="alert alert-success">Idea added successfully!</div>';
        } else {
            $idea_feedback = '<div class="alert alert-danger">Failed to add idea.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_idea'])) {
    if (!csrf_verify()) { $idea_feedback = '<div class="alert alert-danger">CSRF verification failed.</div>'; }
    else {
    $edit_id = intval($_POST['edit_id']);
    $title = trim($_POST['edit_title']);
    $description = trim($_POST['edit_description']);
    $category_id = intval($_POST['edit_category_id']);
    $user_id = intval($_POST['edit_user_id']);
    $is_public = isset($_POST['edit_is_public']) ? 1 : 0;
    if (strlen($title) < 3 || strlen($description) < 10 || $category_id <= 0 || $user_id <= 0) {
        $idea_feedback = '<div class="alert alert-danger">Invalid input. Please check all fields.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE ideas SET user_id=?, category_id=?, title=?, description=?, is_public=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'iissii', $user_id, $category_id, $title, $description, $is_public, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            $idea_feedback = '<div class="alert alert-success">Idea updated successfully!</div>';
        } else {
            $idea_feedback = '<div class="alert alert-danger">Failed to update idea.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
}
if (isset($_GET['delete_idea'])) {
    $id = intval($_GET['delete_idea']);
    mysqli_query($conn, "DELETE FROM ideas WHERE id=$id");
    header('Location: admin.php?tab=ideas&msg=deleted'); exit();
}
if ($tab == 'ideas' && isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $idea_feedback = '<div class="alert alert-success">Idea deleted successfully.</div>';
}
// Handle add/edit/delete for categories
$cat_feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (!csrf_verify()) { $cat_feedback = '<div class="alert alert-danger">CSRF verification failed.</div>'; }
    else {
    $name_en = trim($_POST['name_en']);
    if ($name_en === '') {
        $cat_feedback = '<div class="alert alert-danger">Category name required.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name_en, name_ar) VALUES (?, '')");
        mysqli_stmt_bind_param($stmt, 's', $name_en);
        if (mysqli_stmt_execute($stmt)) {
            $cat_feedback = '<div class="alert alert-success">Category added successfully!</div>';
        } else {
            $cat_feedback = '<div class="alert alert-danger">Failed to add category.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    if (!csrf_verify()) { $cat_feedback = '<div class="alert alert-danger">CSRF verification failed.</div>'; }
    else {
    $edit_id = intval($_POST['edit_id']);
    $name_en = trim($_POST['edit_name_en']);
    if ($name_en === '') {
        $cat_feedback = '<div class="alert alert-danger">Category name required.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE categories SET name_en=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'si', $name_en, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            $cat_feedback = '<div class="alert alert-success">Category updated successfully!</div>';
        } else {
            $cat_feedback = '<div class="alert alert-danger">Failed to update category.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
}
if (isset($_GET['delete_category'])) {
    $id = intval($_GET['delete_category']);
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    header('Location: admin.php?tab=categories&msg=deleted'); exit();
}
if ($tab == 'categories' && isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $cat_feedback = '<div class="alert alert-success">Category deleted successfully.</div>';
}
// Handle edit/delete for comments
$comment_feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'])) {
    if (!csrf_verify()) { $comment_feedback = '<div class="alert alert-danger">CSRF verification failed.</div>'; }
    else {
    $edit_id = intval($_POST['edit_id']);
    $comment = trim($_POST['edit_comment']);
    if (strlen($comment) < 2) {
        $comment_feedback = '<div class="alert alert-danger">Comment is too short.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE comments SET comment=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'si', $comment, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            $comment_feedback = '<div class="alert alert-success">Comment updated successfully!</div>';
        } else {
            $comment_feedback = '<div class="alert alert-danger">Failed to update comment.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
}
if (isset($_GET['delete_comment'])) {
    $id = intval($_GET['delete_comment']);
    mysqli_query($conn, "DELETE FROM comments WHERE id=$id");
    header('Location: admin.php?tab=comments&msg=deleted'); exit();
}
if ($tab == 'comments' && isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $comment_feedback = '<div class="alert alert-success">Comment deleted successfully.</div>';
}
// Handle delete for votes
if (isset($_GET['delete_vote'])) {
    $id = intval($_GET['delete_vote']);
    mysqli_query($conn, "DELETE FROM votes WHERE id=$id");
    header('Location: admin.php?tab=votes&msg=deleted'); exit();
}

// Fetch stats
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_ideas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM ideas"))['c'];
$total_votes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM votes"))['c'];
$total_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM comments"))['c'];
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM categories"))['c'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - IdeaVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        .gold-gradient { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: #FFD700; }
        .admin-glass { background: rgba(255,255,255,0.97); box-shadow: 0 8px 32px 0 rgba(24,24,24,0.08); border-radius: 32px; border: 1.5px solid #eee; padding: 2.5rem 2rem; }
        .icon-gold { color: #FFD700; }
        .tab-content { margin-top: 2rem; }
        .nav-pills .nav-link.active { background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%); color: #181818 !important; font-weight: bold; }
        .nav-pills .nav-link { color: #FFD700; font-weight: 500; }
        .stat-card { background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(24,24,24,0.06); border: 1.5px solid #eee; padding: 2rem 1.5rem; text-align: center; }
        .stat-icon { font-size: 2.5rem; color: #FFD700; margin-bottom: 0.5rem; }
        .btn-gold {
            background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%);
            color: #181818 !important;
            font-weight: bold;
            border: none;
            box-shadow: 0 2px 12px rgba(255,215,0,0.10);
        }
        .btn-gold:hover {
            background: linear-gradient(90deg, #FFEF8E 0%, #FFD700 100%);
            color: #181818 !important;
        }
        .table thead th { color: #FFD700; }
        .badge-admin { background: #FFD700; color: #181818; font-weight: 500; }
        .badge-user { background: #eee; color: #888; font-weight: 500; }
        .form-control, .form-select { background: #fff; border-radius: 12px; border: 1.5px solid #eee; }
        .btn-outline-gold {
            background: transparent;
            color: #FFD700 !important;
            border: 2px solid #FFD700;
            font-weight: bold;
            box-shadow: none;
            transition: background 0.2s, color 0.2s;
        }
        .btn-outline-gold:hover, .btn-outline-gold:focus {
            background: linear-gradient(90deg, #FFD700 0%, #FFEF8E 100%);
            color: #181818 !important;
            border-color: #FFD700;
        }
    </style>
</head>
<body>
<?php
ob_end_flush();
?>
<div class="container py-4">
    <div class="admin-glass shadow mb-4">
        <h2 class="mb-3 gold-gradient"><i class="bi bi-shield-lock icon-gold"></i> Admin Panel</h2>
        <ul class="nav nav-pills mb-3" id="adminTabs">
            <li class="nav-item"><a class="nav-link <?= $tab=='dashboard'?'active':'' ?>" href="?tab=dashboard">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab=='users'?'active':'' ?>" href="?tab=users">Users</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab=='ideas'?'active':'' ?>" href="?tab=ideas">Ideas</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab=='votes'?'active':'' ?>" href="?tab=votes">Votes</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab=='comments'?'active':'' ?>" href="?tab=comments">Comments</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab=='categories'?'active':'' ?>" href="?tab=categories">Categories</a></li>
        </ul>
        <div class="tab-content">
            <?php if ($tab == 'dashboard'): ?>
                <div class="row g-4">
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="fs-3 fw-bold gold-gradient"><?= $total_users ?></div>
                            <div>Users</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-lightbulb"></i></div>
                            <div class="fs-3 fw-bold gold-gradient"><?= $total_ideas ?></div>
                            <div>Ideas</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-hand-thumbs-up-fill"></i></div>
                            <div class="fs-3 fw-bold gold-gradient"><?= $total_votes ?></div>
                            <div>Votes</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-chat-dots"></i></div>
                            <div class="fs-3 fw-bold gold-gradient"><?= $total_comments ?></div>
                            <div>Comments</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-tags"></i></div>
                            <div class="fs-3 fw-bold gold-gradient"><?= $total_categories ?></div>
                            <div>Categories</div>
                        </div>
                    </div>
                </div>
            <?php elseif ($tab == 'users'): ?>
                <h4 class="gold-gradient mb-3">Users
                    <button class="btn btn-outline-gold float-end" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-plus-circle"></i> Add User</button>
                </h4>
                <?= $user_feedback ?>
                <div class="table-responsive"><table class="table table-striped align-middle">
                    <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while ($u = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><img src="<?= htmlspecialchars($u['image_url'] ?? 'https://api.dicebear.com/6.x/initials/svg?seed=' . urlencode($u['username'])) ?>" alt="avatar" class="rounded-circle me-2" width="36" height="36"><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['is_admin'] ? '<span class="badge badge-admin">Admin</span>' : '<span class="badge badge-user">User</span>' ?></td>
                            <td><?= htmlspecialchars($u['created_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>"><i class="bi bi-pencil"></i></button>
                                <?php if (!$u['is_admin']): ?>
                                    <a href="?tab=users&delete_user=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?= $u['id'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                               <form method="POST">
                                 <?= csrf_field(); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title gold-gradient" id="editUserModalLabel<?= $u['id'] ?>">Edit User</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="edit_id" value="<?= $u['id'] ?>">
                                  <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="edit_username" required value="<?= htmlspecialchars($u['username']) ?>">
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="edit_email" required value="<?= htmlspecialchars($u['email']) ?>">
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Image URL (optional)</label>
                                    <input type="text" class="form-control" name="edit_image_url" value="<?= htmlspecialchars($u['image_url'] ?? '') ?>" placeholder="Leave blank for default avatar">
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">New Password <span class="text-muted" style="font-weight:400;">(leave blank to keep current)</span></label>
                                    <input type="password" class="form-control" name="edit_password">
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" name="edit_role">
                                      <option value="user" <?= !$u['is_admin'] ? 'selected' : '' ?>>User</option>
                                      <option value="admin" <?= $u['is_admin'] ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" name="edit_user" class="btn btn-gold">Save Changes</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endwhile; ?>
                    </tbody>
                </table></div>
                <!-- Add User Modal -->
                <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                               <form method="POST">
                                 <?= csrf_field(); ?>
                        <div class="modal-header">
                          <h5 class="modal-title gold-gradient" id="addUserModalLabel">Add User</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role">
                              <option value="user">User</option>
                              <option value="admin">Admin</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="add_user" class="btn btn-gold">Add User</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php elseif ($tab == 'ideas'): ?>
                <h4 class="gold-gradient mb-3">Ideas
                    <button class="btn btn-outline-gold float-end" data-bs-toggle="modal" data-bs-target="#addIdeaModal"><i class="bi bi-plus-circle"></i> Add Idea</button>
                </h4>
                <?= $idea_feedback ?>
                <div class="table-responsive"><table class="table table-striped align-middle">
                    <thead><tr><th>ID</th><th>Title</th><th>User</th><th>Category</th><th>Public</th><th>Created</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while ($i = mysqli_fetch_assoc($ideas)): ?>
                        <tr>
                            <td><?= $i['id'] ?></td>
                            <td><?= htmlspecialchars($i['title']) ?></td>
                            <td><?= htmlspecialchars($i['username']) ?></td>
                            <td><?= htmlspecialchars(get_category_name($i['category_id'], 'en')) ?></td>
                            <td><?= $i['is_public'] ? 'Yes' : 'No' ?></td>
                            <td><?= htmlspecialchars($i['created_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#editIdeaModal<?= $i['id'] ?>"><i class="bi bi-pencil"></i></button>
                                <a href="?tab=ideas&delete_idea=<?= $i['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete idea?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <!-- Edit Idea Modal -->
                        <div class="modal fade" id="editIdeaModal<?= $i['id'] ?>" tabindex="-1" aria-labelledby="editIdeaModalLabel<?= $i['id'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                       <form method="POST">
                         <?= csrf_field(); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title gold-gradient" id="editIdeaModalLabel<?= $i['id'] ?>">Edit Idea</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="edit_id" value="<?= $i['id'] ?>">
                                  <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="edit_title" required value="<?= htmlspecialchars($i['title']) ?>">
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="edit_description" rows="3"><?= htmlspecialchars($i['description']) ?></textarea>
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="edit_category_id">
                                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                            <option value="<?= $cat['id'] ?>" <?= $i['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name_en']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Public</label>
                                    <select class="form-select" name="edit_is_public">
                                        <option value="1" <?= $i['is_public'] ? 'selected' : '' ?>>Yes</option>
                                        <option value="0" <?= !$i['is_public'] ? 'selected' : '' ?>>No</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" name="edit_idea" class="btn btn-gold">Save Changes</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endwhile; ?>
                    </tbody>
                </table></div>
                <!-- Add Idea Modal -->
                <div class="modal fade" id="addIdeaModal" tabindex="-1" aria-labelledby="addIdeaModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                               <form method="POST">
                                 <?= csrf_field(); ?>
                        <div class="modal-header">
                          <h5 class="modal-title gold-gradient" id="addIdeaModalLabel">Add Idea</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?= $cat['id'] ?>">
                                        <?= htmlspecialchars($cat['name_en']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Public</label>
                            <select class="form-select" name="is_public">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="add_idea" class="btn btn-gold">Add Idea</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php elseif ($tab == 'votes'): ?>
                <h4 class="gold-gradient mb-3">Votes</h4>
                <div class="table-responsive"><table class="table table-striped align-middle">
                    <thead><tr><th>ID</th><th>User</th><th>Idea</th><th>Type</th><th>Created</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while ($v = mysqli_fetch_assoc($votes)): ?>
                        <tr>
                            <td><?= $v['id'] ?></td>
                            <td><?= htmlspecialchars($v['username']) ?></td>
                            <td><?= htmlspecialchars($v['title']) ?></td>
                            <td><?= ucfirst($v['vote_type']) ?></td>
                            <td><?= htmlspecialchars($v['created_at']) ?></td>
                            <td><a href="?tab=votes&delete_vote=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete vote?')"><i class="bi bi-trash"></i></a></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table></div>
            <?php elseif ($tab == 'comments'): ?>
                <h4 class="gold-gradient mb-3">Comments
                    <button class="btn btn-outline-gold float-end" data-bs-toggle="modal" data-bs-target="#addCommentModal"><i class="bi bi-plus-circle"></i> Add Comment</button>
                </h4>
                <?= $comment_feedback ?>
                <div class="table-responsive"><table class="table table-striped align-middle">
                    <thead><tr><th>ID</th><th>User</th><th>Idea</th><th>Comment</th><th>Created</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while ($c = mysqli_fetch_assoc($comments)): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= htmlspecialchars($c['username']) ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td><?= htmlspecialchars($c['comment']) ?></td>
                            <td><?= htmlspecialchars($c['created_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#editCommentModal<?= $c['id'] ?>"><i class="bi bi-pencil"></i></button>
                                <a href="?tab=comments&delete_comment=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete comment?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <!-- Edit Comment Modal -->
                        <div class="modal fade" id="editCommentModal<?= $c['id'] ?>" tabindex="-1" aria-labelledby="editCommentModalLabel<?= $c['id'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                       <form method="POST">
                         <?= csrf_field(); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title gold-gradient" id="editCommentModalLabel<?= $c['id'] ?>">Edit Comment</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="edit_id" value="<?= $c['id'] ?>">
                                  <div class="mb-3">
                                    <label class="form-label">Comment</label>
                                    <textarea class="form-control" name="edit_comment" rows="3"><?= htmlspecialchars($c['comment']) ?></textarea>
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" name="edit_comment" class="btn btn-gold">Save Changes</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endwhile; ?>
                    </tbody>
                </table></div>
                <!-- Add Comment Modal -->
                <div class="modal fade" id="addCommentModal" tabindex="-1" aria-labelledby="addCommentModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title gold-gradient" id="addCommentModalLabel">Add Comment</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label">Comment</label>
                            <textarea class="form-control" name="comment" rows="3" required></textarea>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="add_comment" class="btn btn-gold">Add Comment</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php elseif ($tab == 'categories'): ?>
                <h4 class="gold-gradient mb-3">Categories
                    <button class="btn btn-outline-gold float-end" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-circle"></i> Add Category</button>
                </h4>
                <?= $cat_feedback ?>
                <div class="table-responsive"><table class="table table-striped align-middle">
                    <thead><tr><th>ID</th><th>Name (EN)</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><?= htmlspecialchars($cat['name_en']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $cat['id'] ?>"><i class="bi bi-pencil"></i></button>
                                <a href="?tab=categories&delete_category=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete category?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <!-- Edit Category Modal -->
                        <div class="modal fade" id="editCategoryModal<?= $cat['id'] ?>" tabindex="-1" aria-labelledby="editCategoryModalLabel<?= $cat['id'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="POST">
                                <div class="modal-header">
                                  <h5 class="modal-title gold-gradient" id="editCategoryModalLabel<?= $cat['id'] ?>">Edit Category</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="edit_id" value="<?= $cat['id'] ?>">
                                  <div class="mb-3">
                                    <label class="form-label">Name (EN)</label>
                                    <input type="text" class="form-control" name="edit_name_en" required value="<?= htmlspecialchars($cat['name_en']) ?>">
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" name="edit_category" class="btn btn-gold">Save Changes</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endwhile; ?>
                    </tbody>
                </table></div>
                <!-- Add Category Modal -->
                <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title gold-gradient" id="addCategoryModalLabel">Add Category</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label">Name (EN)</label>
                            <input type="text" class="form-control" name="name_en" required>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="add_category" class="btn btn-gold">Add Category</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Bootstrap JS for modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 