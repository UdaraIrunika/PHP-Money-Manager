<?php
require_once __DIR__ . '/functions.php';
require_login();
$pdo = get_pdo(); $user_id = $_SESSION['user_id'];
$month = $_GET['month'] ?? null;
$sql = 'SELECT t.*, c.name as category_name FROM transactions t LEFT JOIN categories c ON t.category_id = c.id WHERE t.user_id = ?';
$params = [$user_id];
if ($month) {
    $start = $month . '-01'; $end = date('Y-m-t', strtotime($start));
    $sql .= ' AND date BETWEEN ? AND ?'; $params[] = $start; $params[] = $end;
}
$sql .= ' ORDER BY date DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
export_transactions_csv($user_id, $rows);
?>