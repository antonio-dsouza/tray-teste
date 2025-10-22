<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda Realizada</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #28a745;
        }

        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 28px;
        }

        .sale-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }

        .sale-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
        }

        .sale-item strong {
            color: #495057;
        }

        .commission-highlight {
            background: #28a745;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Nova Venda Realizada!</h1>
            <p>Parabéns, {{ $seller->name }}!</p>
        </div>

        <p>Olá <strong>{{ $seller->name }}</strong>,</p>

        <p>Temos o prazer de informar que uma nova venda foi registrada em seu nome. Confira os detalhes abaixo:</p>

        <div class="sale-details">
            <h3>📋 Detalhes da Venda</h3>
            <div class="sale-item">
                <span><strong>ID da Venda:</strong></span>
                <span>#{{ $sale->id }}</span>
            </div>
            <div class="sale-item">
                <span><strong>Valor da Venda:</strong></span>
                <span>{{ $formattedAmount }}</span>
            </div>
            <div class="sale-item">
                <span><strong>Data da Venda:</strong></span>
                <span>{{ $sale->sold_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="commission-highlight">
            💰 Sua Comissão: {{ $formattedCommission }}
        </div>

        <p>Sua comissão será processada e creditada conforme as políticas da empresa.</p>

        <p>Continue com o excelente trabalho!</p>

        <div class="footer">
            <p>Este é um email automático. Por favor, não responda.</p>
            <p><small>Teste Tray - Sistema de Comissões © {{ date('Y') }}</small></p>
        </div>
    </div>
</body>

</html>
