<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo Executivo Diário</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 3px solid #28a745;
        }

        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }

        .header .subtitle {
            color: #6c757d;
            font-size: 16px;
            margin-top: 8px;
        }

        .executive-summary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
            border-left: 5px solid #28a745;
            border-right: 5px solid #28a745;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }

        .metric-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-top: 3px solid #28a745;
        }

        .metric-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            margin: 5px 0;
        }

        .metric-label {
            color: #6c757d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .total-highlight {
            background: linear-gradient(135deg, #28a745, #495057);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
            font-size: 22px;
            font-weight: bold;
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
        }

        .insights {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .insights h4 {
            color: #856404;
            margin-top: 0;
        }

        .insights p {
            color: #856404;
            margin-bottom: 0;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }

        .date-badge {
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📋 Resumo Executivo</h1>
            <div class="subtitle">Relatório Diário de Vendas</div>
            <div style="margin-top: 15px;">
                <span class="date-badge">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
            </div>
        </div>

        <p style="font-size: 18px; color: #495057; margin-bottom: 30px;">
            Prezado(a) Administrador(a),
        </p>

        <p>Segue abaixo o resumo consolidado das vendas realizadas em
            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}:</p>

        <div class="executive-summary">
            <h3 style="color: #28a745; margin-top: 0;">📊 Métricas do Dia</h3>

            <div class="summary-grid">
                <div class="metric-card">
                    <div class="metric-icon">🛒</div>
                    <div class="metric-value">{{ $totalSales }}</div>
                    <div class="metric-label">Total de Vendas</div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">💰</div>
                    <div class="metric-value">R$ {{ number_format($totalAmount, 2, ',', '.') }}</div>
                    <div class="metric-label">Faturamento Total</div>
                </div>
            </div>
        </div>

        <div class="total-highlight">
            🎯 Performance Geral: {{ $totalSales }} {{ $totalSales == 1 ? 'venda realizada' : 'vendas realizadas' }}
            <br>
            💵 Receita do Dia: R$ {{ number_format($totalAmount, 2, ',', '.') }}
        </div>

        @if ($totalSales > 0)
            <div class="insights">
                <h4>💡 Insights do Dia</h4>
                <p>
                    ✅ <strong>Meta alcançada!</strong> Foram registradas {{ $totalSales }}
                    {{ $totalSales == 1 ? 'venda' : 'vendas' }} com faturamento total de R$
                    {{ number_format($totalAmount, 2, ',', '.') }}.
                    <br><br>
                    📈 Continue monitorando o desempenho da equipe para manter o crescimento sustentável.
                </p>
            </div>
        @else
            <div class="insights">
                <h4>📊 Análise do Dia</h4>
                <p>
                    ⚠️ <strong>Atenção:</strong> Não foram registradas vendas hoje.
                    <br><br>
                    💼 Considere revisar as estratégias de vendas e motivar a equipe para os próximos dias.
                </p>
            </div>
        @endif

        <p style="margin-top: 30px;">
            Para relatórios mais detalhados, acesse o painel administrativo do sistema.
        </p>

        <div class="footer">
            <p><strong>Sistema de Gestão de Vendas e Comissões</strong></p>
            <p>Este é um email automático enviado diariamente. Por favor, não responda.</p>
            <p><small>Teste Tray - Sistema de Comissões © {{ date('Y') }}</small></p>
        </div>
    </div>
</body>

</html>
