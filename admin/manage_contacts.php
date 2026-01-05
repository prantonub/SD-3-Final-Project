<?php
session_start();

// ✅ Admin session check
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// ✅ DB connection
include '../includes/db.php';

// ✅ Fetch contact messages
$result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Contact Messages</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .page-title {
            font-weight: 700;
        }
        .table td {
            vertical-align: middle;
        }
        .message-box {
            max-width: 350px;
            white-space: normal;
        }
    </style>
</head>
<body>

<div class="container-fluid px-4 mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title text-primary">
            <i class="fas fa-envelope-open-text me-2"></i>Contact Messages
        </h3>

        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Name</th>
                            <th width="20%">Email</th>
                            <th width="15%">Subject</th>
                            <th width="30%">Message</th>
                            <th width="15%">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold">
                                    <?= $row['id'] ?>
                                </td>

                                <td>
                                    <span class="fw-semibold">
                                        <?= htmlspecialchars($row['name']) ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="mailto:<?= htmlspecialchars($row['email']) ?>"
                                       class="text-decoration-none text-primary">
                                        <?= htmlspecialchars($row['email']) ?>
                                    </a>
                                </td>

                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars($row['subject']) ?>
                                    </span>
                                </td>

                                <td class="message-box text-muted small">
                                    <?= nl2br(htmlspecialchars($row['message'])) ?>
                                </td>

                                <td class="text-center text-muted small">
                                    <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                No contact messages found
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
