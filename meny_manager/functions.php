<?php
// functions.php - common helpers
require_once __DIR__ . '/config.php';

function flash_set($key, $msg)
{
    $_SESSION['flash'][$key] = $msg;
}
function flash_get($key)
{
    if (!empty($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}

function is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function get_user()
{
    if (!is_logged_in()) return null;
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Get categories for current user
function get_categories($user_id)
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = ? ORDER BY name');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Totals for dashboard
function get_totals($user_id)
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT
        SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense
        FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    $income = $row['total_income'] ?? 0;
    $expense = $row['total_expense'] ?? 0;
    return ['income' => (float)$income, 'expense' => (float)$expense, 'balance' => (float)$income - (float)$expense];
}

// Transactions list with simple filters
function get_transactions($user_id, $filters = [])
{
    $pdo = get_pdo();
    $sql = 'SELECT t.*, c.name as category_name FROM transactions t LEFT JOIN categories c ON t.category_id = c.id WHERE t.user_id = ?';
    $params = [$user_id];
    if (!empty($filters['type'])) {
        $sql .= ' AND t.type = ?';
        $params[] = $filters['type'];
    }
    if (!empty($filters['category_id'])) {
        $sql .= ' AND t.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['start_date'])) {
        $sql .= ' AND t.date >= ?';
        $params[] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= ' AND t.date <= ?';
        $params[] = $filters['end_date'];
    }
    $sql .= ' ORDER BY t.date DESC, t.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Simple CSV exporter
function export_transactions_csv($user_id, $transactions)
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Date','Type','Category','Amount','Notes']);
    foreach ($transactions as $t) {
        fputcsv($out, [$t['id'], $t['date'], $t['type'], $t['category_name'] ?? '', $t['amount'], $t['notes']]);
    }
    fclose($out);
    exit;
}

?>