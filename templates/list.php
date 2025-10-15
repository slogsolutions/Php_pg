
<?php include __DIR__ . '/_header.php'; ?>
<div class="container">
  <div class="headerbar">
    <div class="logo">Slog Solutions Pvt Ltd.</div>
    <div>
      <a class="btn" href="index.php?action=logout">⏻&nbsp;Sign out</a>
      <a class="btn primary" href="index.php?action=new">✨&nbsp;Create New Proposal</a>
    </div>
  </div>

  <div class="toolbar">
    <form method="get" action="index.php" style="display:contents">
      <input type="hidden" name="action" value="list">
      <input class="search" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Search proposals (e.g. dit, soft)" />
      <select name="kind">
        <option value="">All</option>
        <option value="training" <?= (($_GET['kind'] ?? '')==='training')?'selected':'' ?>>Training</option>
        <option value="soft" <?= (($_GET['kind'] ?? '')==='soft')?'selected':'' ?>>Soft Skill</option>
      </select>
      <button class="btn">Search</button>
      <a class="btn ghost" href="index.php?action=list">Clear</a>
    </form>
  </div>

  <div class="card-grid">
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
