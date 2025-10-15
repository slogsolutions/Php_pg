<?php include __DIR__ . '/_header.php'; ?>
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
