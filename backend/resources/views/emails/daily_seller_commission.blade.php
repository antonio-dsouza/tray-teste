<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo Diário de Vendas</title>
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

        .daily-stats {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-item strong {
            color: #495057;
        }

        .stat-value {
            color: #28a745;
            font-weight: bold;
        }

        .commission-highlight {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }

        .greeting {
            font-size: 18px;
            color: #495057;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Resumo Diário de Vendas</h1>
            <p>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
        </div>

        <div class="greeting">
            Olá <strong>{{ $seller->name }}</strong>,
        </div>

        <p>Aqui está o resumo das suas vendas do dia {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}:</p>

        <div class="daily-stats">
            <h3>📈 Performance do Dia</h3>
            <div class="stat-item">
                <span><strong>📦 Total de Vendas:</strong></span>
                <span class="stat-value">{{ $count }} {{ $count == 1 ? 'venda' : 'vendas' }}</span>
            </div>
            <div class="stat-item">
                <span><strong>💵 Valor Total Vendido:</strong></span>
                <span class="stat-value">R$ {{ number_format($totalAmount, 2, ',', '.') }}</span>
            </div>
            <div class="stat-item">
                <span><strong>📊 Taxa de Comissão:</strong></span>
                <span class="stat-value">8,5%</span>
            </div>
        </div>

        <div class="commission-highlight">
            💰 Sua Comissão do Dia: R$ {{ number_format($totalCommission, 2, ',', '.') }}
        </div>

        @if ($count > 0)
            <p>🎉 <strong>Parabéns pelo excelente trabalho!</strong> Continue assim e alcance metas ainda maiores.</p>
        @else
            <p>📅 Não foram registradas vendas hoje. Amanhã é uma nova oportunidade!</p>
        @endif

        <p>Suas comissões serão processadas e creditadas conforme as políticas da empresa.</p>

        <div class="footer">
            <p>Este é um email automático. Por favor, não responda.</p>
            <p><small>Teste Tray - Sistema de Comissões © {{ date('Y') }}</small></p>
        </div>
    </div>
</body>

</html>
