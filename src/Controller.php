<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php'; // Added auth.php
require_once __DIR__ . '/ProposalModel.php';
require_once __DIR__ . '/PdfService.php';

function _section_count_from_request($default = 10) {
    $n = isset($_GET['n']) ? intval($_GET['n']) : $default;
    if ($n < 1) $n = 1;
    if ($n > 50) $n = 50;
    return $n;
}

// =================================================================================================
// User Management Functions
// =================================================================================================

function route_user_new_form($errors = [], $old = []) {
    if (!is_admin()) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
    include __DIR__ . '/../templates/user_new.php';
}

function route_user_create() {
    if (!is_admin()) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    
    if (count($errors) > 0) {
        return route_user_new_form($errors, ['username' => $username]);
    }

    $db = db();
    
    // Check if user already exists
    $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = 'User with this username already exists.';
        return route_user_new_form($errors, ['username' => $username]);
    }

    // Hash the password and insert the new user as 'employee'
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $stmt->execute([$username, $hashed_password, 'employee']);

    header('Location: index.php?action=list'); // Redirect to proposal list after creation
    exit;
}

// =================================================================================================
// Proposal Management Functions
// =================================================================================================

function route_delete($id) {
    // Authorization check: Only admin or the proposal owner can delete
    $proposal = proposal_get_raw($id);
    $user = current_user();

    if (!$proposal) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }

    if (!is_admin() && $user['id'] !== (int)$proposal['user_id']) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }

    proposal_delete($id);
    header("Location: index.php");
    exit();
}

function route_list() {
    // Read filters from GET
    $filters = [
        'q'           => $_GET['q']           ?? '',
        'date_filter' => $_GET['date_filter'] ?? 'all', // all|yesterday|on|month|range
        'date'        => $_GET['date']        ?? '',    // YYYY-MM-DD
        'month'       => $_GET['month']       ?? '',    // YYYY-MM
        'from'        => $_GET['from']        ?? '',    // YYYY-MM-DD
        'to'          => $_GET['to']          ?? '',    // YYYY-MM-DD
    ];

    // Add user-specific filtering
    $user = current_user();
    if (!is_admin()) {
        $filters['user_id'] = $user['id'];
    }

    $proposals = proposal_search($filters);

    // pass filters to view so inputs can stay filled
    $view = $filters;

    include __DIR__ . '/../templates/list.php';
}


function route_new_form($errors = [], $old = []) {
    // Allow admins and employees to create proposals.
    // The previous restriction that prevented a hardcoded admin (id=0) from creating proposals was removed.
    $section_count = _section_count_from_request(10);
    include __DIR__ . '/../templates/new.php';
}


/**
 * Build proposal_items array from POST.
 * Supports modern JSON payload and legacy item_label[]/item_body[].
 *
 * NOTE:
 * - Reads from POST['items'] OR POST['items_json'] (both supported).
 * - Persists structured JSON in `body` so the editor can reconstruct pages/tables/content.
 */
function build_items_from_post(): array {
    // Accept both names from the form
    $rawJson = $_POST['items'] ?? $_POST['items_json'] ?? '';

    if (is_string($rawJson) && $rawJson !== '') {
        $raw = json_decode($rawJson, true);
        if (is_array($raw)) {
            $out = [];
            $pos = 0;

            foreach ($raw as $it) {
                $type  = $it['type']  ?? 'content';
                $label = trim((string)($it['label'] ?? ''));

                // PAGE
                if ($type === 'page') {
                    $title = $label !== '' ? $label : 'Page';
                    $out[] = [
                        'type'     => 'page',  // <-- ensure type is persisted
                        'label'    => $title,
                        'body'     => json_encode([
                            '__kind' => 'page',
                            'title'  => $title,
                        ], JSON_UNESCAPED_UNICODE),
                        'position' => $pos++,
                    ];
                    continue;
                }

                // TABLE
                if ($type === 'table') {
                    $b = is_array($it['body'] ?? null) ? $it['body'] : [];
                    $title  = trim((string)($b['title'] ?? ($label !== '' ? $label : 'Table')));
                    $cols   = array_values((array)($b['columns'] ?? []));
                    $rowsIn = $b['rows'] ?? [];

                    $rows = [];
                    foreach ($rowsIn as $r) {
                        if (!is_array($r)) $r = [$r];
                        // Ensure each row element is string (to handle the dropdown value which may be "Other" but content is in the input)
                        $rows[] = array_map(static function ($c) { return (string)$c; }, $r);
                    }

                    $out[] = [
                        'type'     => 'table', // <-- ensure type is persisted
                        'label'    => $title !== '' ? $title : 'Table',
                        $is_key_value = ($cols[0] ?? '') === 'label' && ($cols[1] ?? '') === 'content' && count($cols) === 2,
                        'body'     => json_encode([
                            '__kind'  => $is_key_value ? 'key_value_table' : 'table', // Differentiate if it's the standard 2-col key-value table
                            'title'   => $title,
                            'columns' => $cols,
                            'rows'    => $rows,
                        ], JSON_UNESCAPED_UNICODE),
                        'position' => $pos++,
                    ];
                    continue;
                }
                
                // CONTENT (default)
                $b    = is_array($it['body'] ?? null) ? $it['body'] : [];
                $sub  = trim((string)($b['subTitle'] ?? ($label !== '' ? $label : 'Course Content')));
                $text = (string)($b['richText'] ?? '');

                $out[] = [
                    'type'     => 'content', // <-- ensure type is persisted
                    'label'    => $sub !== '' ? $sub : 'Course Content',
                    'body'     => json_encode([
                        '__kind'   => 'content',
                        'subTitle' => $sub,
                        'richText' => $text,
                    ], JSON_UNESCAPED_UNICODE),
                    'position' => $pos++,
                ];
            }
            return $out;
        }
    }

    // Legacy fallback: parallel arrays item_label[] / item_body[]
    $items = [];
    if (isset($_POST['item_label']) && is_array($_POST['item_label'])) {
        $count = count($_POST['item_label']);
        $pos = 0;
        for ($i = 0; $i < $count; $i++) {
            $label = $_POST['item_label'][$i] ?? '';
            $body  = $_POST['item_body'][$i] ?? '';
            if (trim($label) === '' && trim($body) === '') continue;

            $items[] = [
                'type'     => 'content', // <-- ensure type in legacy path
                'label'    => $label,
                'body'     => json_encode([
                    '__kind'   => 'content',
                    'subTitle' => $label,
                    'richText' => $body,
                ], JSON_UNESCAPED_UNICODE),
                'position' => $pos++,
            ];
        }
    }
    return $items;
}

function route_create() {
    $user = current_user();
    // Allow admins and employees to create proposals.
    $data = [
        'user_id' => $user['id'], // Assign proposal to the logged-in user
        'title' => $_POST['title'] ?? 'IT TRAINING PROGRAM',
        'for_whom' => $_POST['for_whom'] ?? '',
        'recipient' => $_POST['recipient'] ?? '',
        'date' => $_POST['date'] ?? date('Y-m-d'),
        'intro_text' => $_POST['intro_text'] ?? '',
        'signatory_name' => $_POST['signatory_name'] ?? '',
        'signatory_title' => $_POST['signatory_title'] ?? '',
        'signatory_phone' => $_POST['signatory_phone'] ?? '',
        'signatory_email' => $_POST['signatory_email'] ?? '',
        'include_about' => isset($_POST['include_about']) ? 1 : 0,
        'include_technologies' => isset($_POST['include_technologies']) ? 1 : 0,
    ];

    $items = build_items_from_post();

    $id = proposal_create($data, $items);
    list($proposal, $its) = proposal_get($id);
    $pdf_filename = render_proposal_pdf($proposal, $its);
    proposal_set_pdf($id, $pdf_filename);

    header('Location: index.php?action=list');
    exit;
}


function route_show($id) {
    list($proposal, $items) = proposal_get($id);
    if (!$proposal) { http_response_code(404); echo 'Not found'; return; }

    // Authorization check: Only admin or the proposal owner can view
    $user = current_user();
    if (!is_admin() && $user['id'] !== (int)$proposal['user_id']) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }

    include __DIR__ . '/../templates/show.php';
}

function route_download_pdf($id) {
    list($proposal, $items) = proposal_get($id);
    if (!$proposal || !$proposal['generated_pdf']) { http_response_code(404); echo 'PDF not found'; return; }

    // Authorization check: Only admin or the proposal owner can download
    $user = current_user();
    if (!is_admin() && $user['id'] !== (int)$proposal['user_id']) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }

    $filepath = STORAGE_PATH . '/' . $proposal['generated_pdf'];
    if (!is_file($filepath)) { http_response_code(404); echo 'File missing'; return; }
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    readfile($filepath);
    exit;
}

function route_edit_form($id) {
    list($proposal, $items) = proposal_get($id);
    if (!$proposal) { http_response_code(404); echo 'Not found'; return; }

    // Authorization check: Only admin or the proposal owner can edit
    $user = current_user();
    if (!is_admin() && $user['id'] !== (int)$proposal['user_id']) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }

    // Decode the JSON bodies so the editor can rebuild pages/tables/content
    $items_for_editor = [];
    foreach ($items as $r) {
        $body    = $r['body'] ?? null;
        $decoded = is_string($body) ? json_decode($body, true) : (is_array($body) ? $body : null);

        if (is_array($decoded) && isset($decoded['__kind'])) {
            if ($decoded['__kind'] === 'page') {
                $items_for_editor[] = [
                    'type'  => 'page',
                    'label' => $decoded['title'] ?? ($r['label'] ?? 'Page'),
                    'body'  => ['title' => $decoded['title'] ?? ($r['label'] ?? 'Page')],
                ];
                continue;
            }
            // MODIFIED: 'key_value_table' now uses type 'table' for editing
            if ($decoded['__kind'] === 'table' || $decoded['__kind'] === 'key_value_table') {
                $items_for_editor[] = [
                    'type'  => 'table',
                    'label' => $decoded['title'] ?? ($r['label'] ?? 'Table'),
                    'body'  => [
                        'title'   => $decoded['title'] ?? ($r['label'] ?? 'Table'),
                        'columns' => $decoded['columns'] ?? ['label', 'content'], // Default columns
                        'rows'    => $decoded['rows'] ?? [],
                    ],
                ];
                continue;
            }
            
            // content
            $items_for_editor[] = [
                'type'  => 'content',
                'label' => $decoded['subTitle'] ?? ($r['label'] ?? 'Course Content'),
                'body'  => [
                    'subTitle' => $decoded['subTitle'] ?? ($r['label'] ?? 'Course Content'),
                    'richText' => $decoded['richText'] ?? '',
                ],
            ];
            continue;
        }

        // Legacy plain-text fallback
        $items_for_editor[] = [
            'type'  => 'content',
            'label' => $r['label'] ?? 'Course Content',
            'body'  => [
                'subTitle' => $r['label'] ?? 'Course Content',
                'richText' => is_string($body) ? $body : '',
            ],
        ];
    }

    $items = $items_for_editor;
    $section_count = _section_count_from_request(max(10, count($items)));
    include __DIR__ . '/../templates/edit.php';
}

function route_update($id) {
    list($proposal, $itemsExisting) = proposal_get($id);
    if (!$proposal) { http_response_code(404); echo 'Not found'; return; }

    // Authorization check: Only admin or the proposal owner can update
    $user = current_user();
    if (!is_admin() && $user['id'] !== (int)$proposal['user_id']) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }

    $data = [
        'title' => $_POST['title'] ?? $proposal['title'],
        'for_whom' => $_POST['for_whom'] ?? $proposal['for_whom'],
        'recipient' => $_POST['recipient'] ?? $proposal['recipient'],
        'date' => $_POST['date'] ?? $proposal['date'],
        'intro_text' => $_POST['intro_text'] ?? $proposal['intro_text'],
        'signatory_name' => $_POST['signatory_name'] ?? $proposal['signatory_name'],
        'signatory_title' => $_POST['signatory_title'] ?? $proposal['signatory_title'],
        'signatory_phone' => $_POST['signatory_phone'] ?? $proposal['signatory_phone'],
        'signatory_email' => $_POST['signatory_email'] ?? $proposal['signatory_email'],
        'include_about' => isset($_POST['include_about']) ? 1 : 0,
        'include_technologies' => isset($_POST['include_technologies']) ? 1 : 0,
    ];

    $items = build_items_from_post();

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('UPDATE proposals SET title=?, for_whom=?, recipient=?, date=?, intro_text=?, signatory_name=?, signatory_title=?, signatory_phone=?, signatory_email=?, include_about=?, include_technologies=? WHERE id=?');
        $stmt->execute([
            $data['title'], $data['for_whom'], $data['recipient'], $data['date'], $data['intro_text'],
            $data['signatory_name'], $data['signatory_title'], $data['signatory_phone'], $data['signatory_email'],
            $data['include_about'], $data['include_technologies'], $id
        ]);

        $pdo->prepare('DELETE FROM proposal_items WHERE proposal_id=?')->execute([$id]);
        $stmtItem = $pdo->prepare('INSERT INTO proposal_items (proposal_id, label, type, body, position) VALUES (?,?,?,?,?)');

        foreach ($items as $it) {
            $position = isset($it['position']) ? (int)$it['position'] : 0;
            $stmtItem->execute([$id, $it['label'], $it['type'] ?? 'content', $it['body'], $position]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    list($proposal2, $its2) = proposal_get($id);
    $pdf_filename = render_proposal_pdf($proposal2, $its2);
    proposal_set_pdf($id, $pdf_filename);

    header('Location: index.php?action=list');
    exit;
}
