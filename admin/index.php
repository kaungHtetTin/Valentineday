<?php
/**
 * Admin – login and approve/reject payment screenshots
 */
session_start();
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/config.php';

$baseUrl = baseUrl();
$adminBase = rtrim($baseUrl, '/') . '/admin';

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: ' . $adminBase . '/index.php');
    exit;
}

// POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ' . $adminBase . '/index.php');
        exit;
    }
    $loginError = 'Wrong password.';
}

// POST approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        if ($_POST['action'] === 'approve') {
            setUnlockRequestStatus($pdo, $id, 'approved');
        } elseif ($_POST['action'] === 'reject') {
            setUnlockRequestStatus($pdo, $id, 'rejected');
        }
        header('Location: ' . $adminBase . '/index.php');
        exit;
    }
}

$loggedIn = !empty($_SESSION['admin_logged_in']);
$pending = $loggedIn ? getPendingUnlockRequests($pdo) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – LoveFun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light-pink">

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 fw-bold font-heading mb-0">LoveFun Admin</h1>
            <?php if ($loggedIn): ?>
                <a href="<?= $adminBase ?>/index.php?logout=1" class="btn btn-outline-secondary btn-sm">Logout</a>
            <?php endif; ?>
        </div>

        <?php if (!$loggedIn): ?>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="glass-card p-4">
                        <h5 class="fw-bold font-heading mb-3">Admin login</h5>
                        <?php if (!empty($loginError)): ?>
                            <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($loginError) ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required autofocus style="border-radius: 14px;">
                            </div>
                            <button type="submit" class="btn btn-pink w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="glass-card p-4">
                <h5 class="fw-bold font-heading mb-3">Pending payments</h5>
                <?php if (empty($pending)): ?>
                    <p class="text-muted mb-0">No pending requests. New submissions will appear here.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Story key</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Payment screenshot</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $r): ?>
                                    <tr>
                                        <td class="text-nowrap small"><?= htmlspecialchars(date('M j, Y H:i', strtotime($r['created_at']))) ?></td>
                                        <td><code class="small"><?= htmlspecialchars(substr($r['story_key'], 0, 8)) ?>…</code></td>
                                        <td><?= htmlspecialchars($r['name']) ?></td>
                                        <td><?= htmlspecialchars($r['email']) ?></td>
                                        <td><?= htmlspecialchars($r['phone']) ?></td>
                                        <td>
                                            <a href="<?= $baseUrl . '/' . htmlspecialchars($r['screenshot_url']) ?>" target="_blank" class="d-inline-block" title="Open full size">
                                                <img src="<?= $baseUrl . '/' . htmlspecialchars($r['screenshot_url']) ?>" alt="Screenshot" style="max-width:120px;max-height:80px;object-fit:cover;border-radius:8px;">
                                            </a>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Approve this payment?');">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="post" class="d-inline ms-1" onsubmit="return confirm('Reject this request?');">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <p class="small text-muted mt-3">
                <strong>Approve</strong> marks the story as paid and lets the customer see the view link on their status page.
                <a href="<?= $baseUrl ?>/index.php">← Back to app</a>
            </p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
