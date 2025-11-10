<?php
require_once __DIR__ . '/functions.php';
require_login();
$pdo = get_pdo(); $user_id = $_SESSION['user_id'];
// monthly summary
$month = $_GET['month'] ?? date('Y-m');
$start = $month . '-01'; $end = date('Y-m-t', strtotime($start));
$stmt = $pdo->prepare("SELECT type, SUM(amount) as total FROM transactions WHERE user_id=? AND date BETWEEN ? AND ? GROUP BY type");
$stmt->execute([$user_id,$start,$end]); $sums = $stmt->fetchAll(); $income=0;$expense=0;
foreach ($sums as $s){ if ($s['type']=='income') $income=$s['total']; else $expense=$s['total']; }
// category breakdown
$stmt = $pdo->prepare('SELECT c.name, SUM(t.amount) as total FROM transactions t JOIN categories c ON t.category_id=c.id WHERE t.user_id=? AND t.date BETWEEN ? AND ? AND t.type="expense" GROUP BY c.id');
$stmt->execute([$user_id,$start,$end]); $cat = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports - Money Manager</title>
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

        /* Month selector form */
        .month-selector {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .month-selector .form-group {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 200px;
        }

        .month-selector label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #4a5568;
            font-size: 0.95rem;
        }

        .month-selector input[type="month"] {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .month-selector input[type="month"]:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .month-selector button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .month-selector button:hover {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        /* Summary cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card.income {
            border-top: 4px solid #1cc88a;
        }

        .summary-card.expense {
            border-top: 4px solid #e74a3b;
        }

        .summary-card.balance {
            border-top: 4px solid #3498db;
        }

        .summary-card .label {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .summary-card .amount {
            font-size: 1.8rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .summary-card.income .amount {
            color: #1cc88a;
        }

        .summary-card.expense .amount {
            color: #e74a3b;
        }

        .summary-card.balance .amount {
            color: #3498db;
        }

        /* Table */
        .table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .table thead {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
        }

        .table th {
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .table td {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        /* Progress bars for category breakdown */
        .category-progress {
            width: 100%;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            height: 8px;
            margin: 8px 0;
        }

        .category-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e74a3b 0%, #f6c23e 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        /* Export section */
        .export-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
            margin-bottom: 30px;
        }

        .export-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .export-button:hover {
            background: linear-gradient(135deg, #17a673 0%, #1cc88a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(28, 200, 138, 0.3);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #718096;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        /* Chart container */
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
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
            
            .month-selector {
                flex-direction: column;
                align-items: stretch;
            }
            
            .month-selector .form-group {
                min-width: auto;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .table {
                display: block;
                overflow-x: auto;
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
            
            .table th,
            .table td {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            .summary-card .amount {
                font-size: 1.5rem;
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

        /* Additional styling for better readability */
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #3498db;
            border-radius: 2px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Financial Reports</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a> 
            <a href="transactions.php">Transactions</a> 
            <a href="categories.php">Categories</a>
            <a href="budgets.php">Budgets</a>
            <a href="goals.php">Goals</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="section-title">
        <h3>Monthly Summary</h3>
    </div>

    <div class="month-selector">
        <div class="form-group">
            <label for="month">Select Month</label>
            <input type="month" name="month" id="month" value="<?=h($month)?>">
        </div>
        <button type="submit">Generate Report</button>
    </div>

    <?php if (empty($sums) && empty($cat)): ?>
        <div class="empty-state">
            <p>No transaction data found for <?=date('F Y', strtotime($start))?>. Try selecting a different month.</p>
        </div>
    <?php else: ?>
        <div class="summary-cards">
            <div class="summary-card income">
                <div class="label">Total Income</div>
                <div class="amount">$<?=number_format($income,2)?></div>
            </div>
            <div class="summary-card expense">
                <div class="label">Total Expenses</div>
                <div class="amount">$<?=number_format($expense,2)?></div>
            </div>
            <div class="summary-card balance">
                <div class="label">Net Balance</div>
                <div class="amount">$<?=number_format($income-$expense,2)?></div>
            </div>
        </div>

        <div class="section-title">
            <h3>Expense Breakdown by Category</h3>
        </div>

        <?php if (empty($cat)): ?>
            <div class="empty-state">
                <p>No expense data found for <?=date('F Y', strtotime($start))?>.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalExpenses = $expense > 0 ? $expense : 1; // Avoid division by zero
                    foreach ($cat as $c): 
                        $percentage = ($c['total'] / $totalExpenses) * 100;
                    ?>
                        <tr>
                            <td><?=h($c['name'])?></td>
                            <td>$<?=number_format($c['total'],2)?></td>
                            <td>
                                <div class="category-progress">
                                    <div class="category-progress-bar" style="width: <?=$percentage?>%"></div>
                                </div>
                                <?=round($percentage,1)?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="export-section">
            <a href="export_csv.php?month=<?=urlencode($month)?>" class="export-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Export CSV for <?=date('F Y', strtotime($start))?>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// Simple form submission for month selector
document.querySelector('.month-selector').addEventListener('submit', function(e) {
    e.preventDefault();
    const month = document.getElementById('month').value;
    window.location.href = `reports.php?month=${month}`;
});
</script>
</body>
</html>