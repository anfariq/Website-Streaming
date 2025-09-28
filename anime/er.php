<?php
session_start();
$error = $_SESSION['curl_error'] ?? [
    'message' => 'Unknown error occurred',
    'url' => 'N/A',
    'timestamp' => date('Y-m-d H:i:s')
];
unset($_SESSION['curl_error']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Error Page</title>
    <style>
        .error-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            background-color: #fdecea;
        }
        .error-title {
            color: #e74c3c;
        }
        .error-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">⚠️ Error Occurred</h1>
        
        <div class="error-details">
            <p><strong>Message:</strong> <?= htmlspecialchars($error['message']) ?></p>
            <p><strong>URL:</strong> <?= htmlspecialchars($error['url']) ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($error['timestamp']) ?></p>
            <?php if (isset($error['http_code'])): ?>
                <p><strong>HTTP Code:</strong> <?= $error['http_code'] ?></p>
            <?php endif; ?>
        </div>
        
        <p><a href="javascript:history.back()">← Go Back</a> or <a href="/">Return Home</a></p>
    </div>
</body>
</html>