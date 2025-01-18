<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de email
$to = "contact@eclatnet.fr"; // Seu email
$from = "contact@eclatnet.fr"; // Seu email

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Prepara o email
    $email_subject = "Nouveau message de contact: " . htmlspecialchars($subject);
    
    $email_content = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .details { margin: 20px 0; }
        </style>
    </head>
    <body>
        <h2>Nouveau message de contact</h2>
        <div class='details'>
            <p><strong>Nom:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Téléphone:</strong> " . htmlspecialchars($phone) . "</p>
            <p><strong>Sujet:</strong> " . htmlspecialchars($subject) . "</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        </div>
    </body>
    </html>";

    // Headers para email HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $from . "\r\n";

    // Envia o email
    try {
        if(mail($to, $email_subject, $email_content, $headers)) {
            echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès']);
        } else {
            throw new Exception('Erreur lors de l\'envoi du message');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>
