<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Relatórios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            margin-bottom: 20px;
        }
        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-body canvas {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Dashboard de Relatórios</h1>
        <div class="row">
            <!-- Cartões de Informações -->
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div>
                            <h2 class="card-title" id="totalVendas">0 vendas</h2>
                            <p class="card-text">Quantidade de vendas realizadas hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div>
                            <h2 class="card-title" id="valorTotalVendido">R$ 0,00</h2>
                            <p class="card-text">Valor total vendido hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div>
                            <h2 class="card-title" id="ticketMedio">R$ 0,00</h2>
                            <p class="card-text">Ticket médio hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <div>
                            <h2 class="card-title" id="vendasCanceladas">0 canceladas</h2>
                            <p class="card-text">Vendas canceladas hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <div>
                            <h2 class="card-title" id="entregasDia">0 entregas</h2>
                            <p class="card-text">Quantidade de entregas realizadas hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Relatório de Vendas Diárias -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Relatório de Vendas Diárias (Comparação Mensal)</div>
                    <div class="card-body">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Relatório de Vendas Mensais -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Relatório de Vendas Mensais (Comparação Anual)</div>
                    <div class="card-body">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Relatório de Entregas Diárias -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Relatório de Entregas Diárias (Comparação Mensal)</div>
                    <div class="card-body">
                        <canvas id="dailyDeliveriesChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Alerta de Estoque Baixo -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Alerta de Estoque Baixo</div>
                    <div class="card-body">
                        <ul id="estoqueBaixo"></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Lucro Aproximado -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Lucro Aproximado</div>
                    <div class="card-body">
                        <h4 id="lucroAproximado">R$ 0,00</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function loadSummaryData() {
            $.ajax({
                url: 'api/summary_data.php',
                method: 'GET',
                success: function (data) {
                    $('#totalVendas').text(data.total_vendas + ' vendas');
                    $('#valorTotalVendido').text('R$ ' + data.valor_total_vendido);
                    $('#ticketMedio').text('R$ ' + data.ticket_medio);
                    $('#vendasCanceladas').text(data.vendas_canceladas + ' canceladas');
                    $('#entregasDia').text(data.entregas_dia + ' entregas');
                },
                error: function (xhr, status, error) {
                    console.error('Error loading summary data:', status, error);
                }
            });
        }

        function loadEstoqueBaixo() {
            $.ajax({
                url: 'api/alerta_estoque_baixo.php',
                method: 'GET',
                success: function (data) {
                    var estoqueBaixo = $('#estoqueBaixo');
                    estoqueBaixo.empty();
                    data.forEach(function (produto) {
                        estoqueBaixo.append('<li>' + produto.nome + ' - Estoque: ' + produto.estoque + ' (Mínimo: ' + produto.estoque_minimo + ')</li>');
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error loading low stock data:', status, error);
                }
            });
        }

        function loadLucroAproximado() {
            $.ajax({
                url: 'api/lucro_aproximado.php',
                method: 'GET',
                success: function (data) {
                    if (data.error) {
                        console.error('Erro do servidor:', data.error);
                        return;
                    }
                    $('#lucroAproximado').text('R$ ' + data.lucro_aproximado);
                },
                error: function (xhr, status, error) {
                    console.error('Erro ao carregar dados de lucro aproximado:', status, error);
                    console.error('Resposta completa do servidor:', xhr.responseText);
                }
            });
        }

        // Função para carregar dados e renderizar gráficos
        function loadChartData(chartId, apiEndpoint) {
            $.ajax({
                url: apiEndpoint,
                method: 'GET',
                success: function (data) {
                    console.log('Data received for ' + chartId + ':', data); // Adicionar mensagem de depuração
                    var ctx = document.getElementById(chartId).getContext('2d');
                    new Chart(ctx, {
                        type: 'bar', // Tipo de gráfico alterado para barras
                        data: data,
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error loading data for ' + chartId + ':', status, error); // Adicionar mensagem de erro
                }
            });
        }

        $(document).ready(function() {
            loadSummaryData();
            loadEstoqueBaixo();
            loadLucroAproximado();
            loadChartData('dailySalesChart', 'api/relatorio_vendas_diarias.php');
            loadChartData('monthlySalesChart', 'api/relatorio_vendas_mensais.php');
            loadChartData('dailyDeliveriesChart', 'api/relatorio_entregas_diarias.php');
        });
    </script>
</body>
</html>
