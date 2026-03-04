<?php
// newsletter.php
require_once 'config.php';

$response = [
    'success' => false,
    'message' => ''
];

// Ellenőrizzük, hogy POST kérés érkezett-e
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Email cím validálása
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Kérjük, adjon meg egy érvényes email címet!';
    } else {
        try {
            // Ellenőrizzük, hogy létezik-e már ez az email
            $checkStmt = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $response['message'] = 'Ez az email cím már fel van iratkozva hírlevelünkre!';
            } else {
                // Beszúrjuk az új email címet
                $insertStmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)");
                $insertStmt->execute([$email]);
                
                $response['success'] = true;
                $response['message'] = 'Sikeresen feliratkozott hírlevelünkre! Hamarosan küldjük az első ajánlatainkat.';
                
                // Opcionális: küldhetünk egy visszaigazoló emailt is
                // sendConfirmationEmail($email);
            }
        } catch (PDOException $e) {
            // Naplózzuk a hibát, de a felhasználónak ne jelenítsük meg a részleteket
            error_log("Hírlevél feliratkozási hiba: " . $e->getMessage());
            $response['message'] = 'Technikai hiba történt. Kérjük, próbálja később!';
        }
    }
} else {
    $response['message'] = 'Érvénytelen kérés!';
}

// Ha AJAX kérés (fetch), akkor JSON választ küldünk
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Ha nem AJAX, akkor átirányítjuk a referrer oldalra (vagy főoldalra)
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$redirect .= (strpos($redirect, '?') === false ? '?' : '&') . 'newsletter=' . ($response['success'] ? 'success' : 'error');
header('Location: ' . $redirect);
exit;
?>