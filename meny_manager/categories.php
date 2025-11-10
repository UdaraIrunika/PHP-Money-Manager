<?php
require_once __DIR__ . '/functions.php';
require_login();
$pdo = get_pdo();
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '#4e73df');
    if (empty($name)) { flash_set('error','Name required.'); }
    else {
        if (!empty($_POST['id'])) {
            $stmt = $pdo->prepare('UPDATE categories SET name=?, color=? WHERE id=? AND user_id=?');
            $stmt->execute([$name,$color,$_POST['id'],$user_id]);
            flash_set('success','Category updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (user_id,name,color) VALUES (?,?,?)');
            $stmt->execute([$user_id,$name,$color]);
            flash_set('success','Category added.');
        }
        header('Location: categories.php'); exit;
    }
}

if ($action === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id=? AND user_id=?');
    $stmt->execute([$_GET['id'],$user_id]);
    flash_set('success','Category deleted.'); header('Location: categories.php'); exit;
}

$edit = null;
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id=? AND user_id=?');
    $stmt->execute([$_GET['id'],$user_id]); $edit = $stmt->fetch();
}

$stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = ? ORDER BY name'); $stmt->execute([$user_id]); $cats = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Categories - Money Manager</title>
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

        /* Color preview */
        .color-preview {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 2px solid #e2e8f0;
            display: inline-block;
            vertical-align: middle;
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

        /* Color input wrapper */
        .color-input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .color-input-wrapper input[type="color"] {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: none;
        }

        .color-input-wrapper input[type="text"] {
            flex: 1;
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
        <h1>Categories</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a> 
            <a href="transactions.php">Transactions</a> 
            <a href="budgets.php">Budgets</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($msg = flash_get('success')): ?>
        <div class="message message-success"><?=$msg?></div>
    <?php endif; ?>
    <?php if ($err = flash_get('error')): ?>
        <div class="message message-error"><?=$err?></div>
    <?php endif; ?>

    <h3><?= $edit ? 'Edit' : 'Add' ?> Category</h3>
    <form method="post">
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?=h($edit['id'])?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="col">
                <label>Name</label>
                <input type="text" name="name" value="<?=h($edit['name'] ?? '')?>" required placeholder="Enter category name">
            </div>
            <div class="col">
                <label>Color</label>
                <div class="color-input-wrapper">
                    <input type="color" name="color" value="<?=h($edit['color'] ?? '#4e73df')?>" title="Choose color">
                    <input type="text" name="color" value="<?=h($edit['color'] ?? '#4e73df')?>" placeholder="#4e73df" pattern="^#[0-9A-Fa-f]{6}$">
                </div>
            </div>
        </div>
        <div>
            <button type="submit"><?= $edit ? 'Save Changes' : 'Add Category' ?></button>
            <?php if ($edit): ?>
                <a href="categories.php" style="margin-left: 12px; color: #718096; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <h3>Your Categories</h3>
    <?php if (empty($cats)): ?>
        <div class="empty-state">
            <p>No categories created yet. Create your first category above!</p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Color</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cats as $c): ?>
                    <tr>
                        <td><?=h($c['name'])?></td>
                        <td>
                            <div class="color-preview" style="background:<?=h($c['color'])?>"></div>
                            <span style="margin-left: 8px; font-family: monospace; font-size: 0.9rem;"><?=h($c['color'])?></span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="categories.php?action=edit&id=<?=$c['id']?>" class="edit">Edit</a>
                                <a href="categories.php?action=delete&id=<?=$c['id']?>" class="delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>