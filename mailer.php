<?php
// Configurações de email
$to = "eclatnetservices255@gmail.com"; // Coloque seu email aqui
$smtp_host = "smtp.gmail.com"; // Configure seu servidor SMTP
$smtp_port = 587;
$smtp_user = "seu-email@gmail.com"; // Seu email para envio
$smtp_pass = "sua-senha"; // Sua senha

// Recebe os dados do formulário
$formData = $_POST;

// Calcula o orçamento (mesma lógica do JavaScript)
function calculateEstimate($formData) {
    $prices = [
        'immeuble' => [
            'basePrice' => 2.5,
            'frequency' => [
                'quotidien' => 1,
                'hebdomadaire' => 0.8,
                'mensuel' => 0.6
            ],
            'services' => [
                'vitrerie' => 150,
                'sols' => 200,
                'sanitaires' => 100,
                'desinfection' => 180
            ]
        ],
        'bureau' => [
            'basePrice' => 3,
            'frequency' => [
                'quotidien' => 1,
                'hebdomadaire' => 0.85,
                'mensuel' => 0.7
            ],
            'services' => [
                'vitrerie' => 200,
                'sols' => 250,
                'sanitaires' => 150,
                'desinfection' => 200
            ]
        ]
    ];

    $serviceType = $formData['serviceType'];
    $surface = floatval($formData['surface']);
    $frequency = $formData['frequency'];
    $etages = intval($formData['etages']);

    // Cálculo base
    $basePrice = $prices[$serviceType]['basePrice'] * $surface;
    $frequencyMultiplier = $prices[$serviceType]['frequency'][$frequency];
    $etageMultiplier = 1 + (($etages - 1) * 0.05);

    // Serviços adicionais
    $servicesTotal = 0;
    if (isset($formData['services'])) {
        foreach ($formData['services'] as $service) {
            $servicesTotal += $prices[$serviceType]['services'][$service];
        }
    }

    // Total
    $total = ($basePrice * $frequencyMultiplier * $etageMultiplier) + $servicesTotal;

    return [
        'min' => round($total * 0.85),
        'max' => round($total * 1.15)
    ];
}

// Calcula o orçamento
$estimate = calculateEstimate($formData);

// Prepara o email para o cliente
$subject = "Votre devis ÉclatNet";
$message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .estimate { color: #1a73e8; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Merci pour votre demande de devis</h2>
    <p>Cher(e) {$formData['name']},</p>
    <p>Voici une estimation pour vos besoins de nettoyage :</p>
    <p class='estimate'>Estimation : entre {$estimate['min']}€ et {$estimate['max']}€</p>
    <p><strong>Note importante :</strong> Cette estimation est donnée à titre indicatif et peut varier selon les spécificités de votre demande.</p>
    <p>Un de nos conseillers vous contactera dans les plus brefs délais pour affiner cette estimation.</p>
    <br>
    <p>Cordialement,</p>
    <p>L'équipe ÉclatNet</p>
</body>
</html>
";

// Headers para envio de email HTML
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: ÉclatNet <noreply@eclatnet.fr>\r\n";

// Tenta enviar o email
try {
    // Envia email para o cliente
    mail($formData['email'], $subject, $message, $headers);
    
    // Envia notificação para a empresa
    $admin_message = "Nouvelle demande de devis de {$formData['name']}\n";
    $admin_message .= "Email: {$formData['email']}\n";
    $admin_message .= "Téléphone: {$formData['phone']}\n";
    $admin_message .= "Estimation: {$estimate['min']}€ - {$estimate['max']}€";
    
    mail($to, "Nouvelle demande de devis", $admin_message, $headers);
    
    // Retorna sucesso
    http_response_code(200);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Retorna erro
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>