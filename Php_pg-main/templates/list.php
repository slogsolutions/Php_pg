<?php
require_once __DIR__ . '/../src/auth.php'; // Ensure auth functions are available
$user = current_user();
$is_admin = is_admin();
?>
<?php include __DIR__ . '/_header.php'; ?>
<div class="container">
  <div class="headerbar">
    <div class="logo">Slog Solutions Pvt Ltd.</div>
    <div style="display: flex; align-items: center; gap: 10px;">
      <?php if ($is_admin): ?>
        <a class="btn primary" href="index.php?action=user_new">👤&nbsp;Create User</a>
      <?php endif; ?>
      <a class="btn primary" href="index.php?action=new">✨&nbsp;Create New Proposal</a>
      <a class="btn" href="/logout.php">⏻&nbsp;Sign out</a>
    </div>
  </div>
  <nav style="display:flex;justify-content:flex-end;gap:12px;padding:10px 14px;background:#0b1220;">
    <div style="margin-right:auto;color:#e5e7eb;">
      👋 <?= htmlspecialchars($user['username'] ?? '') ?> (Role: <?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?>)
    </div>
    <a href="/index.php" style="color:#e5e7eb;text-decoration:none;">Home</a>
    <a href="/logout.php" style="color:#fca5a5;text-decoration:none;">Sign out</a>
  </nav>

 <div class="toolbar">
  <form method="get" action="index.php" style="display:contents">
    <input type="hidden" name="action" value="list">

    <!-- Text search -->
    <input
      class="search"
      name="q"
      value="<?= htmlspecialchars($view['q'] ?? '') ?>"
      placeholder="Search proposals (e.g. client name, keywords)" />

    <!-- Date filter selector -->
    <select name="date_filter" onchange="toggleDateFields(this.value)" style="margin-left:8px;">
      <?php
        $df = $view['date_filter'] ?? 'all';
        $opts = [
          'all'       => 'All',
          'yesterday' => 'Yesterday',
          'on'        => 'On date…',
          'month'     => 'In month…',
          'range'     => 'Date range…',
        ];
        foreach ($opts as $k => $label):
      ?>
        <option value="<?= $k ?>" <?= $df===$k ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>

    <!-- Dynamic date inputs -->
    <span id="f-on"   style="display:none;margin-left:8px;">
      <input type="date" name="date" value="<?= htmlspecialchars($view['date'] ?? '') ?>">
    </span>

    <span id="f-month" style="display:none;margin-left:8px;">
      <input type="month" name="month" value="<?= htmlspecialchars($view['month'] ?? '') ?>">
    </span>

    <span id="f-range" style="display:none;margin-left:8px;">
      <input type="date" name="from" value="<?= htmlspecialchars($view['from'] ?? '') ?>">
      <span style="margin:0 6px;">to</span>
      <input type="date" name="to"   value="<?= htmlspecialchars($view['to'] ?? '') ?>">
    </span>

    <button type="submit" class="btn" style="margin-left:10px;">Search</button>
    <a class="btn" href="index.php?action=list" style="margin-left:6px;">Clear</a>
  </form>
</div>

<script>
  function toggleDateFields(mode) {
    document.getElementById('f-on').style.display    = (mode === 'on')    ? '' : 'none';
    document.getElementById('f-month').style.display = (mode === 'month') ? '' : 'none';
    document.getElementById('f-range').style.display = (mode === 'range') ? '' : 'none';
  }
  // init on load using current value
  (function(){
    var sel = document.querySelector('select[name="date_filter"]');
    toggleDateFields(sel ? sel.value : 'all');
  })();
</script>


  <div class="card-grid">
    <?php if (empty($proposals)): ?>
      <p style="grid-column: 1 / -1; text-align: center; color: #9ca3af;">No proposals found.</p>
    <?php endif; ?>
    <?php foreach ($proposals as $p): ?>
      <article class="proposal-card">
        <header>
          <h3><span class="muted"><?= htmlspecialchars($p['title']) ?></span></h3>
          <div class="for"><?= htmlspecialchars($p['for_whom']) ?></div>
        </header>
        <footer>
          <a class="btn" href="index.php?action=edit&id=<?= $p['id'] ?>">Edit</a>
          <a class="btn" href="index.php?action=download&id=<?= $p['id'] ?>">PDF</a>
          <form action="index.php?action=delete&id=<?= (int)$p['id'] ?>" method="post"
      onsubmit="return confirm('Delete this proposal?');" style="display:inline">
  <button type="submit" class="btn btn-danger">Delete Proposal</button>
</form>

        </footer>
      </article>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
