<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de email
$to = "eclatnetservices255@gmail.com"; 
$from = "eclatnetservices255@gmail.com";

// Função de log de erros
function logError($error) {
    $log_file = "error_log.txt";
    $message = date('Y-m-d H:i:s') . " - Error: " . $error . "\n";
    error_log($message, 3, $log_file);
}

// Recebe os dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $formData = $_POST;
        
        // Validação básica
        if (!isset($formData['name']) || !isset($formData['email']) || !isset($formData['phone'])) {
            throw new Exception('Informations manquantes dans le formulaire');
        }

        // Calcula o orçamento
        $estimate = calculateEstimate($formData);
        
        // Prepara o email para o cliente
        $subject = "Votre devis ÉclatNet";
        $message = getClientEmailTemplate($formData, $estimate);
        
        // Headers para email HTML
        $headers = getEmailHeaders($from);
        
        // Envia email para o cliente
        if (!mail($formData['email'], $subject, $message, $headers)) {
            throw new Exception('Erreur lors de l\'envoi du mail au client');
        }
        
        // Prepara e envia email para a empresa
        $admin_subject = "Nouvelle demande de devis - ÉclatNet";
        $admin_message = getAdminEmailTemplate($formData, $estimate);
        
        if (!mail($to, $admin_subject, $admin_message, $headers)) {
            throw new Exception('Erreur lors de l\'envoi du mail à l\'administrateur');
        }
        
        // Retorna sucesso
        echo json_encode(['success' => true, 'message' => 'Devis envoyé avec succès']);
        
    } catch (Exception $e) {
        logError($e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}

function calculateEstimate($data) {
    $prices = [
        'immeuble' => [
            'basePrice' => 2.5,
            'frequency' => [
                'quotidien' => 1,
                'hebdomadaire' => 0.8,
                'mensuel' => 0.6
            ]
        ],
        'bureau' => [
            'basePrice' => 3,
            'frequency' => [
                'quotidien' => 1,
                'hebdomadaire' => 0.85,
                'mensuel' => 0.7
            ]
        ]
    ];

    if($data['serviceType'] == 'bureau') {
        // Cálculo para escritórios
        $surface = floatval($data['surface']);
        $baseHours = $surface <= 100 ? 1 : 1 + (($surface - 100) / 100);
        $cost = $baseHours * 50; // €50 por hora de trabalho
    } else {
        // Cálculo para imóveis
        $etages = intval($data['etages']);
        $baseHours = 1.5 * ($etages / 6); // 1h30 para 6 andares
        $cost = $baseHours * 50;
    }

    // Ajuste pela frequência
    $frequencyMultiplier = $prices[$data['serviceType']]['frequency'][$data['frequency']];
    $totalCost = $cost * $frequencyMultiplier;

    // Custos mensais
    if($data['frequency'] == 'quotidien') {
        $totalCost *= 22; // dias úteis por mês
    } else if($data['frequency'] == 'hebdomadaire') {
        $totalCost *= 4; // semanas por mês
    }

    return [
        'min' => round($totalCost * 0.85),
        'max' => round($totalCost * 1.15)
    ];
}

function getEmailHeaders($from) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: ÉclatNet <{$from}>\r\n";
    $headers .= "Reply-To: {$from}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return $headers;
}

function getClientEmailTemplate($data, $estimate) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .estimate { color: #1a73e8; font-weight: bold; margin: 20px 0; }
            .note { color: #666; font-style: italic; }
        </style>
    </head>
    <body>
        <h2>Merci pour votre demande de devis</h2>
        <p>Cher(e) " . htmlspecialchars($data['name']) . ",</p>
        <p>Nous avons bien reçu votre demande de devis et nous vous en remercions.</p>
        
        <div class='estimate'>
            <p>Estimation mensuelle: entre " . $estimate['min'] . "€ et " . $estimate['max'] . "€</p>
        </div>
        
        <p class='note'>Cette estimation est donnée à titre indicatif et peut varier selon les spécificités de votre demande.</p>
        
        <p>Un de nos conseillers vous contactera dans les plus brefs délais pour affiner cette estimation et répondre à toutes vos questions.</p>
        
        <p>Cordialement,<br>L'équipe ÉclatNet</p>
    </body>
    </html>";
}

function getAdminEmailTemplate($data, $estimate) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .details { margin: 20px 0; background: #f9f9f9; padding: 15px; }
            .estimate { color: #1a73e8; font-weight: bold; }
        </style>
    </head>
    <body>
        <h2>Nouvelle demande de devis</h2>
        
        <div class='details'>
            <p><strong>Nom:</strong> " . htmlspecialchars($data['name']) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($data['email']) . "</p>
            <p><strong>Téléphone:</strong> " . htmlspecialchars($data['phone']) . "</p>
            <p><strong>Adresse:</strong> " . htmlspecialchars($data['address']) . "</p>
            <p><strong>Type de service:</strong> " . htmlspecialchars($data['serviceType']) . "</p>
            <p><strong>Fréquence:</strong> " . htmlspecialchars($data['frequency']) . "</p>
        </div>
        
        <div class='estimate'>
            <p>Estimation: entre " . $estimate['min'] . "€ et " . $estimate['max'] . "€</p>
        </div>
    </body>
    </html>";
}
?>
