<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de email
$to = "contact@eclatnet.fr"; // Seu email
$from = "contact@eclatnet.fr"; // Seu email

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe e limpa os dados do formulário
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $subject = htmlspecialchars($_POST['subject'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    // Prepara o assunto do email
    $email_subject = "Nouveau message de contact - " . $subject;
    
    // Prepara o conteúdo do email em HTML
    $email_content = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .details { margin: 20px 0; background: #f9f9f9; padding: 15px; border-radius: 5px; }
            h2 { color: #1a73e8; }
            .info-item { margin-bottom: 10px; }
            .highlight { font-weight: bold; color: #1a73e8; }
        </style>
    </head>
    <body>
        <h2>Nouveau message de contact</h2>
        
        <div class='details'>
            <div class='info-item'>
                <span class='highlight'>Nom:</span> {$name}
            </div>
            <div class='info-item'>
                <span class='highlight'>Email:</span> {$email}
            </div>
            <div class='info-item'>
                <span class='highlight'>Téléphone:</span> {$phone}
            </div>
            <div class='info-item'>
                <span class='highlight'>Sujet:</span> {$subject}
            </div>
            <div class='info-item'>
                <span class='highlight'>Message:</span><br>
                " . nl2br($message) . "
            </div>
        </div>
        
        <p style='color: #666; font-size: 0.9em;'>
            Ce message a été envoyé depuis le formulaire de contact du site web ÉclatNet.
        </p>
    </body>
    </html>";

    // Headers para email HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$from}\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    try {
        // Envia o email
        if(mail($to, $email_subject, $email_content, $headers)) {
            // Email de confirmação para o cliente
            $client_subject = "Confirmation de votre message - ÉclatNet";
            $client_message = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #1a73e8;'>Merci de nous avoir contacté</h2>
                <p>Cher(e) {$name},</p>
                <p>Nous avons bien reçu votre message et nous vous en remercions.</p>
                <p>Notre équipe vous répondra dans les plus brefs délais.</p>
                <p>Cordialement,<br>L'équipe ÉclatNet</p>
            </body>
            </html>";
            
            mail($email, $client_subject, $client_message, $headers);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Message envoyé avec succès'
            ]);
        } else {
            throw new Exception('Erreur lors de l\'envoi du message');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Méthode non autorisée'
    ]);
}
?>
