<?php
require_once __DIR__ . '/db.php';

function proposal_create($data, $items) {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO proposals (title, for_whom, recipient, date, intro_text, signatory_name, signatory_title, signatory_phone, signatory_email, include_about, include_technologies) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
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
  if (trim((string)($it['label'] ?? '')) === '' && trim((string)($it['body'] ?? '')) === '') continue;
  $stmtItem->execute([
    $proposal_id,
    $it['label'] ?? '',
    $it['type'] ?? 'content',   // <-- ensure type is set
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

?>
