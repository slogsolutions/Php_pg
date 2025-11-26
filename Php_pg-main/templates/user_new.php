<?php include __DIR__ . '/_header.php'; ?>
<div class="container">
  <div class="headerbar">
    <div class="logo">Slog Solutions Pvt Ltd.</div>
    <div style="display: flex; align-items: center; gap: 10px;">
      <a class="btn" href="index.php?action=list">← Back to Proposals</a>
      <a class="btn" href="/logout.php">⏻&nbsp;Sign out</a>
    </div>
  </div>

  <div class="card" style="max-width: 450px; margin: 40px auto; padding: 30px;">
    <h1 style="margin:0 0 20px; font-size: 24px;">Create New Employee User</h1>
    
    <?php if (!empty($errors)): ?>
      <div class="error" style="background:#7f1d1d; color:#fecaca; padding:10px 12px; border-radius:8px; margin:8px 0 16px;">
        <?php foreach ($errors as $error): ?>
          <p style="margin: 0;"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="index.php?action=user_create" autocomplete="off">
      <label for="username" style="display:block; margin: 12px 0 6px; font-weight: 600;">Username</label>
      <input 
        id="username" 
        name="username" 
        value="<?= htmlspecialchars($old['username'] ?? '') ?>"
        required 
        style="width:100%; padding:12px 14px; border-radius:10px; border:1px solid #374151; background:#0b1220; color:#e5e7eb;"
      >

      <label for="password" style="display:block; margin: 12px 0 6px; font-weight: 600;">Password</label>
      <input 
        id="password" 
        name="password" 
        type="password" 
        required 
        style="width:100%; padding:12px 14px; border-radius:10px; border:1px solid #374151; background:#0b1220; color:#e5e7eb;"
      >

      <button 
        type="submit" 
        style="margin-top:24px; width:100%; padding:12px 14px; border-radius:10px; border:0; background:#2563eb; color:white; font-weight:700; cursor: pointer;"
      >
        Create User
      </button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
