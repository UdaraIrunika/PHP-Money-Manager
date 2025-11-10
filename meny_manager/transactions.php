<?php
require_once __DIR__ . '/functions.php';
require_login();
$pdo = get_pdo();
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? null;

// Add or edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = (float)$_POST['amount'];
    $date = $_POST['date'];
    $notes = $_POST['notes'] ?? null;
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    if (!in_array($type,['income','expense']) || $amount<=0) {
        flash_set('error','Invalid data.');
    } else {
        if (!empty($_POST['id'])) {
            // update
            $stmt = $pdo->prepare('UPDATE transactions SET type=?, amount=?, date=?, notes=?, category_id=? WHERE id=? AND user_id=?');
            $stmt->execute([$type,$amount,$date,$notes,$category_id,$_POST['id'],$user_id]);
            flash_set('success','Transaction updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO transactions (user_id, category_id, type, amount, date, notes) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$user_id,$category_id,$type,$amount,$date,$notes]);
            flash_set('success','Transaction added.');
        }
        header('Location: transactions.php'); exit;
    }
}

// Delete
if ($action === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM transactions WHERE id=? AND user_id=?');
    $stmt->execute([$_GET['id'],$user_id]);
    flash_set('success','Transaction deleted.');
    header('Location: transactions.php'); exit;
}

// Edit form
$edit = null;
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM transactions WHERE id=? AND user_id=?');
    $stmt->execute([$_GET['id'],$user_id]);
    $edit = $stmt->fetch();
}

// Filters
$filters = [];
if (!empty($_GET['type'])) $filters['type'] = $_GET['type'];
if (!empty($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
if (!empty($_GET['start_date'])) $filters['start_date'] = $_GET['start_date'];
if (!empty($_GET['end_date'])) $filters['end_date'] = $_GET['end_date'];
$transactions = get_transactions($user_id, $filters);
$categories = get_categories($user_id);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transactions - Money Manager</title>
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
            max-width: 1400px;
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

        /* Filter form */
        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filters .col {
            margin-bottom: 0;
        }

        .filters button {
            padding: 12px 20px;
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            height: fit-content;
        }

        .filters button:hover {
            background: linear-gradient(135deg, #5a6268 0%, #6c757d 100%);
            transform: translateY(-1px);
        }

        /* Table */
        .table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border-collapse: collapse;
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

        /* Type badges */
        .type-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-income {
            background: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
            border: 1px solid rgba(28, 200, 138, 0.2);
        }

        .type-expense {
            background: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
            border: 1px solid rgba(231, 74, 59, 0.2);
        }

        /* Amount styling */
        .amount-income {
            color: #1cc88a;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .amount-expense {
            color: #e74a3b;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        /* Action links */
        .actions {
            display: flex;
            gap: 12px;
        }

        .actions a {
            color: #3498db;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .actions a.edit {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.2);
        }

        .actions a.delete {
            background: rgba(231, 74, 59, 0.1);
            border: 1px solid rgba(231, 74, 59, 0.2);
            color: #e74a3b;
        }

        .actions a.edit:hover {
            background: rgba(52, 152, 219, 0.2);
        }

        .actions a.delete:hover {
            background: rgba(231, 74, 59, 0.2);
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

        /* Category color indicator */
        .category-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .category-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
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

        /* Responsive design */
        @media (max-width: 1024px) {
            .table {
                display: block;
                overflow-x: auto;
            }
        }

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
            
            .filters {
                grid-template-columns: 1fr;
            }
            
            .table th,
            .table td {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            .actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .actions a {
                text-align: center;
                padding: 8px;
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

        /* Section titles */
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
        <h1>Transactions</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a> 
            <a href="categories.php">Categories</a>
            <a href="budgets.php">Budgets</a>
            <a href="goals.php">Goals</a>
            <a href="reports.php">Reports</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($msg = flash_get('success')): ?>
        <div class="message message-success"><?=$msg?></div>
    <?php endif; ?>
    <?php if ($err = flash_get('error')): ?>
        <div class="message message-error"><?=$err?></div>
    <?php endif; ?>

    <div class="section-title">
        <h3><?= $edit ? 'Edit Transaction' : 'Add New Transaction' ?></h3>
    </div>

    <form method="post">
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?=h($edit['id'])?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="col">
                <label>Type</label>
                <select name="type" required>
                    <option value="income" <?=($edit && $edit['type']=='income')?'selected':''?>>Income</option>
                    <option value="expense" <?=($edit && $edit['type']=='expense')?'selected':''?>>Expense</option>
                </select>
            </div>
            <div class="col">
                <label>Amount ($)</label>
                <input type="number" step="0.01" name="amount" value="<?=h($edit['amount'] ?? '')?>" required min="0.01" placeholder="0.00">
            </div>
            <div class="col">
                <label>Date</label>
                <input type="date" name="date" value="<?=h($edit['date'] ?? date('Y-m-d'))?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="col">
                <label>Category</label>
                <select name="category_id">
                    <option value="">-- No Category --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?=$c['id']?>" <?=($edit && $edit['category_id']==$c['id'])?'selected':''?>>
                            <?=h($c['name'])?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label>Notes</label>
                <input type="text" name="notes" value="<?=h($edit['notes'] ?? '')?>" placeholder="Optional description">
            </div>
        </div>
        <div>
            <button type="submit"><?= $edit ? 'Save Changes' : 'Add Transaction' ?></button>
            <?php if ($edit): ?>
                <a href="transactions.php" style="margin-left: 12px; color: #718096; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="section-title">
        <h3>Filter Transactions</h3>
    </div>

    <form method="get" class="filters">
        <div class="col">
            <label>Type</label>
            <select name="type">
                <option value="">All Types</option>
                <option value="income" <?=(!empty($_GET['type']) && $_GET['type']=='income')?'selected':''?>>Income</option>
                <option value="expense" <?=(!empty($_GET['type']) && $_GET['type']=='expense')?'selected':''?>>Expense</option>
            </select>
        </div>
        <div class="col">
            <label>Category</label>
            <select name="category_id">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?=$c['id']?>" <?=(!empty($_GET['category_id']) && $_GET['category_id']==$c['id'])?'selected':''?>>
                        <?=h($c['name'])?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label>Start Date</label>
            <input type="date" name="start_date" value="<?=h($_GET['start_date'] ?? '')?>">
        </div>
        <div class="col">
            <label>End Date</label>
            <input type="date" name="end_date" value="<?=h($_GET['end_date'] ?? '')?>">
        </div>
        <div class="col">
            <button type="submit">Apply Filters</button>
        </div>
    </form>

    <div class="section-title">
        <h3>Transaction History</h3>
    </div>

    <?php if (empty($transactions)): ?>
        <div class="empty-state">
            <p>No transactions found. <?=!empty($filters) ? 'Try adjusting your filters.' : 'Add your first transaction above!'?></p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?=date('M j, Y', strtotime($t['date']))?></td>
                        <td>
                            <span class="type-badge type-<?=$t['type']?>">
                                <?=ucfirst($t['type'])?>
                            </span>
                        </td>
                        <td>
                            <?php if ($t['category_name']): ?>
                                <div class="category-indicator">
                                    <?php if (!empty($t['category_color'])): ?>
                                        <span class="category-color" style="background: <?=h($t['category_color'])?>"></span>
                                    <?php endif; ?>
                                    <?=h($t['category_name'])?>
                                </div>
                            <?php else: ?>
                                <span style="color: #718096; font-style: italic;">Uncategorized</span>
                            <?php endif; ?>
                        </td>
                        <td class="amount-<?=$t['type']?>">
                            $<?=number_format($t['amount'],2)?>
                        </td>
                        <td><?=h($t['notes'])?></td>
                        <td>
                            <div class="actions">
                                <a href="transactions.php?action=edit&id=<?=$t['id']?>" class="edit">Edit</a>
                                <a href="transactions.php?action=delete&id=<?=$t['id']?>" class="delete" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="export-section">
            <a href="export_csv.php" class="export-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Export CSV of Current Transactions
            </a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>