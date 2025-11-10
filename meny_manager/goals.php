<?php
require_once __DIR__ . '/functions.php';
require_login();
$pdo = get_pdo(); $user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); $target = (float)$_POST['target']; $deadline = $_POST['deadline'] ?: null;
    if ($name && $target>0) {
        $stmt = $pdo->prepare('INSERT INTO goals (user_id,name,target,deadline) VALUES (?,?,?,?)');
        $stmt->execute([$user_id,$name,$target,$deadline]); flash_set('success','Goal added.'); header('Location: goals.php'); exit;
    } else { flash_set('error','Invalid data.'); }
}
// update saved via quick form
if (!empty($_GET['add_saved']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id']; $add = (float)($_GET['add_saved']);
    $stmt = $pdo->prepare('UPDATE goals SET saved = saved + ? WHERE id=? AND user_id=?'); $stmt->execute([$add,$id,$user_id]); flash_set('success','Progress updated.'); header('Location: goals.php'); exit;
}
$stmt = $pdo->prepare('SELECT * FROM goals WHERE user_id=? ORDER BY created_at DESC'); $stmt->execute([$user_id]); $goals = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Goals - Money Manager</title>
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
        input[type="date"],
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
        input[type="date"]:focus,
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

        /* Progress bar */
        .progress-container {
            width: 100%;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            height: 12px;
            margin: 8px 0;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #1cc88a 0%, #36b9cc 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
            position: relative;
        }

        .progress-bar.complete {
            background: linear-gradient(90deg, #1cc88a 0%, #0f9d58 100%);
        }

        .progress-bar.overdue {
            background: linear-gradient(90deg, #e74a3b 0%, #c53030 100%);
        }

        /* Progress text */
        .progress-text {
            font-size: 0.85rem;
            color: #718096;
            text-align: center;
            margin-top: 4px;
        }

        /* Quick update form */
        .quick-update-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .quick-update-form input[type="number"] {
            width: 100px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .quick-update-form button {
            padding: 8px 16px;
            background: #1cc88a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .quick-update-form button:hover {
            background: #17a673;
            transform: translateY(-1px);
        }

        /* Goal status indicators */
        .goal-complete {
            color: #1cc88a;
            font-weight: 600;
        }

        .goal-overdue {
            color: #e74a3b;
            font-weight: 600;
        }

        .goal-urgent {
            color: #f6c23e;
            font-weight: 600;
        }

        /* Goal cards for mobile */
        .goal-cards {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .goal-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #3498db;
        }

        .goal-card.complete {
            border-left-color: #1cc88a;
        }

        .goal-card.overdue {
            border-left-color: #e74a3b;
        }

        .goal-card-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .goal-card-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .goal-card-meta {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 10px;
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
                display: none;
            }
            
            .goal-cards {
                display: grid;
            }
            
            .quick-update-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .quick-update-form input[type="number"] {
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
            
            .goal-cards {
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

        /* Currency formatting */
        .currency {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .currency.target {
            color: #2c3e50;
        }

        .currency.saved {
            color: #1cc88a;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Savings Goals</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a> 
            <a href="transactions.php">Transactions</a> 
            <a href="categories.php">Categories</a>
            <a href="budgets.php">Budgets</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($m=flash_get('success')): ?>
        <div class="message message-success"><?=$m?></div>
    <?php endif; ?>
    <?php if ($e=flash_get('error')): ?>
        <div class="message message-error"><?=$e?></div>
    <?php endif; ?>

    <h3>Create Goal</h3>
    <form method="post">
        <div class="form-row">
            <div class="col">
                <label>Goal Name</label>
                <input type="text" name="name" required placeholder="e.g., New Car, Vacation, Emergency Fund">
            </div>
            <div class="col">
                <label>Target Amount ($)</label>
                <input type="number" step="0.01" name="target" min="0.01" required placeholder="1000.00">
            </div>
            <div class="col">
                <label>Deadline (Optional)</label>
                <input type="date" name="deadline">
            </div>
        </div>
        <div>
            <button type="submit">Add Goal</button>
        </div>
    </form>

    <h3>Your Goals</h3>
    
    <?php if (empty($goals)): ?>
        <div class="empty-state">
            <p>No goals created yet. Create your first savings goal above!</p>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Target</th>
                    <th>Saved</th>
                    <th>Deadline</th>
                    <th>Progress</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($goals as $g): 
                    $perc = $g['target']>0 ? min(100,($g['saved']/$g['target'])*100) : 0;
                    $isComplete = $g['saved'] >= $g['target'];
                    $isOverdue = $g['deadline'] && strtotime($g['deadline']) < time() && !$isComplete;
                ?>
                    <tr>
                        <td><?=h($g['name'])?></td>
                        <td class="currency target">$<?=number_format($g['target'],2)?></td>
                        <td class="currency saved">$<?=number_format($g['saved'],2)?></td>
                        <td><?=h($g['deadline'] ? date('M j, Y', strtotime($g['deadline'])) : 'No deadline')?></td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar <?= $isComplete ? 'complete' : ($isOverdue ? 'overdue' : '') ?>" style="width: <?=$perc?>%"></div>
                            </div>
                            <div class="progress-text"><?=round($perc,1)?>%</div>
                        </td>
                        <td>
                            <form class="quick-update-form" method="get">
                                <input type="hidden" name="id" value="<?=$g['id']?>">
                                <input type="number" step="0.01" name="add_saved" placeholder="+ Amount" min="0.01" required>
                                <button type="submit">Add</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Mobile Card View -->
        <div class="goal-cards">
            <?php foreach ($goals as $g): 
                $perc = $g['target']>0 ? min(100,($g['saved']/$g['target'])*100) : 0;
                $isComplete = $g['saved'] >= $g['target'];
                $isOverdue = $g['deadline'] && strtotime($g['deadline']) < time() && !$isComplete;
                $cardClass = $isComplete ? 'complete' : ($isOverdue ? 'overdue' : '');
            ?>
                <div class="goal-card <?=$cardClass?>">
                    <div class="goal-card-header">
                        <div>
                            <div class="goal-card-name"><?=h($g['name'])?></div>
                            <div class="goal-card-meta">
                                Saved: <span class="currency saved">$<?=number_format($g['saved'],2)?></span> / 
                                Target: <span class="currency target">$<?=number_format($g['target'],2)?></span>
                            </div>
                            <?php if ($g['deadline']): ?>
                                <div class="goal-card-meta">
                                    Deadline: <?=date('M j, Y', strtotime($g['deadline']))?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar <?= $isComplete ? 'complete' : ($isOverdue ? 'overdue' : '') ?>" style="width: <?=$perc?>%"></div>
                    </div>
                    <div class="progress-text"><?=round($perc,1)?>% Complete</div>
                    
                    <form class="quick-update-form" method="get" style="margin-top: 15px;">
                        <input type="hidden" name="id" value="<?=$g['id']?>">
                        <input type="number" step="0.01" name="add_saved" placeholder="Add amount" min="0.01" required>
                        <button type="submit">Add to Goal</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>