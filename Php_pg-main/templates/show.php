<?php include __DIR__ . '/_header.php'; ?>
<?php if (function_exists('current_user') && is_logged_in()): ?>
<nav style="display:flex;justify-content:flex-end;gap:12px;padding:10px 14px;background:#0b1220;">
  <div style="margin-right:auto;color:#e5e7eb;">👋 <?= htmlspecialchars(current_user()['username'] ?? '') ?></div>
  <a href="/index.php" style="color:#e5e7eb;text-decoration:none;">Home</a>
  <a href="/logout.php" style="color:#fca5a5;text-decoration:none;">Sign out</a>
</nav>
<?php endif; ?>

<p><a class="btn" href="index.php?action=edit&id=<?php echo $proposal['id']; ?>">Edit</a> <a class="btn" href="index.php?action=download&id=<?php echo $proposal['id']; ?>">Download PDF</a></p>
<h2>Proposal #<?php echo htmlspecialchars($proposal['id']); ?></h2>
<p><strong><?php echo htmlspecialchars($proposal['title']); ?></strong> for <em><?php echo htmlspecialchars($proposal['for_whom']); ?></em></p>
<p>Date: <?php echo htmlspecialchars($proposal['date']); ?></p>
<p><a class="btn" href="index.php?action=download&id=<?php echo $proposal['id']; ?>">Download PDF</a></p>
<h3>Sections</h3>
<ol>
<?php foreach ($items as $it): ?>
  <li><strong><?php echo htmlspecialchars($it['label']); ?></strong><br><?php echo nl2br(htmlspecialchars($it['body'])); ?></li>
<?php endforeach; ?>
</ol>
<?php include __DIR__ . '/_footer.php'; ?>
