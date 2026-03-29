<?php
require_once 'config.php';

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Kérjük, adjon meg egy érvényes email címet!';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $response['message'] = 'Ez az email cím már fel van iratkozva hírlevelünkre!';
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)");
                $insertStmt->execute([$email]);
                
                $response['success'] = true;
                $response['message'] = 'Sikeresen feliratkozott hírlevelünkre!';
            }
        } catch (PDOException $e) {
            error_log("Hírlevél feliratkozási hiba: " . $e->getMessage());
            $response['message'] = 'Technikai hiba történt. Kérjük, próbálja később!';
        }
    }
} else {
    $response['message'] = 'Érvénytelen kérés!';
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$redirect .= (strpos($redirect, '?') === false ? '?' : '&') . 'newsletter=' . ($response['success'] ? 'success' : 'error');
header('Location: ' . $redirect);
exit;
?>