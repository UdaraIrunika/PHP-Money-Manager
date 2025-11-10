<?php
require_once __DIR__ . '/functions.php';
require_login();
$user = get_user();
$totals = get_totals($_SESSION['user_id']);
// Prepare 30-day data
$pdo = get_pdo();
$today = new DateTime();
$start = (clone $today)->modify('-29 days');
$stmt = $pdo->prepare("SELECT date, SUM(CASE WHEN type='income' THEN amount ELSE -amount END) as net FROM transactions WHERE user_id = ? AND date BETWEEN ? AND ? GROUP BY date ORDER BY date");
$stmt->execute([$_SESSION['user_id'], $start->format('Y-m-d'), $today->format('Y-m-d')]);
$rows = $stmt->fetchAll();
$map = [];
foreach ($rows as $r) $map[$r['date']] = (float)$r['net'];
$labels = [];$data = [];$running = 0;
$period = new DatePeriod($start, new DateInterval('P1D'), 30);
foreach ($period as $d) {
    $date = $d->format('Y-m-d');
    $labels[] = $d->format('M j');
    $running += $map[$date] ?? 0;
    $data[] = round($running,2);
}
// category pie
$stmt = $pdo->prepare('SELECT c.name, SUM(t.amount) as total FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? AND t.type = "expense" GROUP BY c.id ORDER BY total DESC');
$stmt->execute([$_SESSION['user_id']]);
$catrows = $stmt->fetchAll();
$catLabels = []; $catData = []; $catColors = [];
$colorsPalette = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'];
$idx=0; foreach ($catrows as $c){ $catLabels[] = $c['name']; $catData[] = (float)$c['total']; $catColors[] = $colorsPalette[$idx%count($colorsPalette)]; $idx++; }
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Money Manager</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 600;
        }

        .nav {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* KPI Cards */
        .kpi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card .small {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 8px;
        }

        /* Headings */
        h2, h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }

        h2 {
            font-size: 1.8rem;
        }

        h3 {
            font-size: 1.4rem;
            margin-top: 30px;
        }

        /* Charts */
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .pie-container {
            display: flex;
            gap: 30px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .pie-legend {
            flex: 1;
            min-width: 250px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 6px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            margin-right: 10px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .nav {
                justify-content: center;
            }
            
            .pie-container {
                flex-direction: column;
            }
            
            .pie-legend {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.6rem;
            }
            
            .nav {
                gap: 8px;
            }
            
            .nav a {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            .kpi {
                grid-template-columns: 1fr;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container > * {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Dashboard</h1>
        <div class="nav">
            Hello, <?=h($user['name'])?> 
            <a href="transactions.php">Transactions</a> 
            <a href="categories.php">Categories</a> 
            <a href="budgets.php">Budgets</a> 
            <a href="goals.php">Goals</a> 
            <a href="reports.php">Reports</a> 
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="kpi">
        <div class="card">
            <div class="small">Total Income</div>
            <div style="font-size:20px;color:#1cc88a">$<?=number_format($totals['income'],2)?></div>
        </div>
        <div class="card">
            <div class="small">Total Expenses</div>
            <div style="font-size:20px;color:#e74a3b">$<?=number_format($totals['expense'],2)?></div>
        </div>
        <div class="card">
            <div class="small">Current Balance</div>
            <div style="font-size:20px;color:#4e73df">$<?=number_format($totals['balance'],2)?></div>
        </div>
    </div>

    <div class="chart-container">
        <h3>30-day balance</h3>
        <canvas id="lineChart" height="80"></canvas>
    </div>

    <div class="chart-container">
        <h3>Expenses by category</h3>
        <div class="pie-container">
            <canvas id="pieChart" width="300" height="300"></canvas>
            <div class="pie-legend">
                <?php foreach ($catLabels as $i => $l): ?>
                    <div class="legend-item">
                        <div class="legend-color" style="background:<?=h($catColors[$i])?>"></div>
                        <?=h($l)?> - $<?=number_format($catData[$i],2)?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
<script>
const labels = <?=json_encode($labels)?>;
const data = <?=json_encode($data)?>;
const catLabels = <?=json_encode($catLabels)?>;
const catData = <?=json_encode($catData)?>;
const catColors = <?=json_encode($catColors)?>;

// Line chart for 30-day balance
if (document.getElementById('lineChart')) {
    const ctx = document.getElementById('lineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Balance',
                data: data,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
}

// Pie chart for expenses by category
if (document.getElementById('pieChart')) {
    const ctx = document.getElementById('pieChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: catColors,
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}
</script>
</body>
</html>