<?php
require_once __DIR__ . '/db.php';

function proposal_create($data, $items) {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO proposals (user_id, title, for_whom, recipient, date, intro_text, signatory_name, signatory_title, signatory_phone, signatory_email, include_about, include_technologies) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $data['user_id'], // New: user_id
            $data['title'], $data['for_whom'], $data['recipient'], $data['date'], $data['intro_text'],
            $data['signatory_name'], $data['signatory_title'], $data['signatory_phone'], $data['signatory_email'],
            !empty($data['include_about']) ? 1 : 0, !empty($data['include_technologies']) ? 1 : 0
        ]);
        $proposal_id = (int)$pdo->lastInsertId();

        $stmtItem = $pdo->prepare(
            'INSERT INTO proposal_items (proposal_id, label, type, body, position) VALUES (?,?,?,?,?)'
        );

        $pos = 0;
        foreach ($items as $it) {
            // Use 'type' property from the controller's item data (e.g., 'course_details', 'table', 'content')
            $item_type = $it['type'] ?? 'content';

            // No strict filtering on label/body content is needed here as filtering is done in Controller.php
            $stmtItem->execute([
                $proposal_id,
                $it['label'] ?? '',
                $item_type,
                $it['body'] ?? '',
                $pos++,
            ]);
        }

        $pdo->commit();
        return $proposal_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function proposal_all() {
    $pdo = db();
    return $pdo->query('SELECT * FROM proposals ORDER BY id DESC')->fetchAll();
}

function proposal_get($id) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM proposals WHERE id = ?');
    $stmt->execute([$id]);
    $proposal = $stmt->fetch();
    if (!$proposal) return null;
    $stmt2 = $pdo->prepare('SELECT * FROM proposal_items WHERE proposal_id = ? ORDER BY position ASC');
    $stmt2->execute([$id]);
    $items = $stmt2->fetchAll();

    foreach ($items as &$item) {
        if (!empty($item['body']) && is_string($item['body'])) {
            $decoded = json_decode($item['body'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $item['body'] = $decoded;
            }
        }
    }

    return [$proposal, $items];

}

function proposal_get_raw($id) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM proposals WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function proposal_set_pdf($id, $filename) {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE proposals SET generated_pdf = ? WHERE id = ?');
    $stmt->execute([$filename, $id]);
}
function proposal_find($id) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM proposals WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function proposal_delete($id) {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Remove items first (FK safety)
        $stmt = $pdo->prepare('DELETE FROM proposal_items WHERE proposal_id = ?');
        $stmt->execute([$id]);

        // Capture PDF filename before deleting proposal row
        $proposal = proposal_find($id);
        $pdf = $proposal && !empty($proposal['generated_pdf']) ? $proposal['generated_pdf'] : null;

        $stmt2 = $pdo->prepare('DELETE FROM proposals WHERE id = ?');
        $stmt2->execute([$id]);

        $pdo->commit();

        // Delete PDF file on disk (after commit)
        if ($pdf) {
            $path = STORAGE_PATH . '/' . basename($pdf);
            if (is_file($path)) { @unlink($path); }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
function proposal_search(array $opts = []) {
    $pdo = db();

    $q           = trim($opts['q'] ?? '');
    $dateFilter  = $opts['date_filter'] ?? 'all';  // all|yesterday|on|month|range
    $dateOn      = trim($opts['date'] ?? '');      // YYYY-MM-DD
    $month       = trim($opts['month'] ?? '');     // YYYY-MM
    $from        = trim($opts['from'] ?? '');      // YYYY-MM-DD
    $to          = trim($opts['to'] ?? '');        // YYYY-MM-DD
    $user_id     = $opts['user_id'] ?? null;       // New: user_id for filtering

    $sql = "SELECT * FROM proposals WHERE 1=1";
    $params = [];

    // New: Filter by user_id if provided (for employees)
    if ($user_id !== null) {
        $sql .= " AND user_id = ?";
        $params[] = $user_id;
    }

    // text search: title, for_whom, recipient, intro_text
    if ($q !== '') {
        $sql .= " AND (title LIKE ? OR for_whom LIKE ? OR recipient LIKE ? OR intro_text LIKE ?)";
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like, $like);
    }

    // date filters
    if ($dateFilter === 'yesterday') {
        $sql .= " AND DATE(`date`) = DATE(DATE_SUB(CURDATE(), INTERVAL 1 DAY))";
    } elseif ($dateFilter === 'on' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOn)) {
        $sql .= " AND DATE(`date`) = ?";
        $params[] = $dateOn;
    } elseif ($dateFilter === 'month' && preg_match('/^\d{4}-\d{2}$/', $month)) {
        // first and last day of that month
        $sql .= " AND DATE(`date`) >= ? AND DATE(`date`) < DATE_ADD(DATE(CONCAT(?, '-01')), INTERVAL 1 MONTH)";
        array_push($params, $month . '-01', $month);
    } elseif ($dateFilter === 'range' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $sql .= " AND DATE(`date`) BETWEEN ? AND ?";
        array_push($params, $from, $to);
    }
    // else 'all' -> no constraint

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
// Added proposal_get_raw for Controller.php to use for authorization checks
// Added user_id to proposal_create
// Added user_id filtering to proposal_search
?>
