<?php
require_once __DIR__ . '/functions.php';
require_login();
$pdo = get_pdo(); $user_id = $_SESSION['user_id'];
// Add budget
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $month = $_POST['month'];
    $amount = (float)$_POST['amount'];
    if ($category_id && $month && $amount>0) {
        $stmt = $pdo->prepare('INSERT INTO budgets (user_id, category_id, month, amount) VALUES (?,?,?,?)');
        $stmt->execute([$user_id,$category_id,$month,$amount]);
        flash_set('success','Budget created.'); header('Location: budgets.php'); exit;
    } else { flash_set('error','Invalid data.'); }
}
// fetch budgets
$stmt = $pdo->prepare('SELECT b.*, c.name FROM budgets b JOIN categories c ON b.category_id=c.id WHERE b.user_id=? ORDER BY b.month DESC');
$stmt->execute([$user_id]); $budgets = $stmt->fetchAll();
$categories = get_categories($user_id);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Budgets - Money Manager</title>
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
            max-width: 1000px;
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

        /* Messages */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .message-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
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

        /* Form styles */
        form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .col {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #4a5568;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
            width: 100%;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Button */
        button[type="submit"] {
            padding: 14px 30px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button[type="submit"]:hover {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        button[type="submit"]:active {
            transform: translateY(0);
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
            
            .form-row {
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

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #718096;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Budgets</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a> 
            <a href="transactions.php">Transactions</a> 
            <a href="categories.php">Categories</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($m=flash_get('success')): ?>
        <div class="message message-success"><?=$m?></div>
    <?php endif; ?>
    
    <?php if ($e=flash_get('error')): ?>
        <div class="message message-error"><?=$e?></div>
    <?php endif; ?>

    <h3>Create Monthly Budget</h3>
    <form method="post">
        <div class="form-row">
            <div class="col">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?=$c['id']?>"><?=h($c['name'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="col">
                <label>Month (YYYY-MM)</label>
                <input type="text" name="month" placeholder="2025-01" required>
            </div>
            <div class="col">
                <label>Amount ($)</label>
                <input type="number" step="0.01" name="amount" min="0.01" required>
            </div>
        </div>
        <div>
            <button type="submit">Create Budget</button>
        </div>
    </form>

    <h3>Your Budgets</h3>
    <?php if (empty($budgets)): ?>
        <div class="empty-state">
            <p>No budgets created yet. Create your first budget above!</p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Category</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budgets as $b): ?>
                    <tr>
                        <td><?=h($b['month'])?></td>
                        <td><?=h($b['name'])?></td>
                        <td>$<?=number_format($b['amount'],2)?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>