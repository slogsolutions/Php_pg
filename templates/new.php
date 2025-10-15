<?php include __DIR__ . '/_header.php'; ?>
<link rel="stylesheet" href="/assets/app.css">
<?php if (function_exists('current_user') && is_logged_in()): ?>
<nav style="display:flex;justify-content:flex-end;gap:12px;padding:10px 14px;background:#0b1220;">
  <div style="margin-right:auto;color:#e5e7eb;">👋 <?= htmlspecialchars(current_user()['username'] ?? '') ?></div>
  <a href="/index.php" style="color:#e5e7eb;text-decoration:none;">Home</a>
  <a href="/logout.php" style="color:#fca5a5;text-decoration:none;">Sign out</a>
</nav>
<?php endif; ?>

<div class="container">
  <div class="headerbar">
    <div class="logo">Proposal Editor</div>
    
  </div>

  <form id="editor-form" action="index.php" method="post">
    <input type="hidden" name="action" value="<?= isset($proposal)?'update':'create' ?>">
    <?php if(isset($proposal)): ?>
      <input type="hidden" name="id" value="<?= (int)$proposal['id'] ?>">
    <?php endif; ?>
    <input type="hidden" name="items" id="items-json">

    <div class="two-col">
      <aside class="pages-rail" id="pages-rail"></aside>

      <section class="editor">
        <!-- ========== COVER & INTRODUCTION (page 1 only) ========== -->
        <div id="cover-section">
          <div class="field">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($proposal['title'] ?? 'IT TRAINING PROGRAM') ?>">
          </div>

          <div class="field">
            <label>Recipient Details</label>
            <input type="text" name="for_whom" value="<?= htmlspecialchars($proposal['for_whom'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($proposal['date'] ?? date('Y-m-d')) ?>">
          </div>

          <div class="field">
            <label>Recipient</label>
            <input type="text" name="recipient" value="<?= htmlspecialchars($proposal['recipient'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Introduction Text</label>
            <?php
$default_intro = "Respected Sir/Ma'am,\n"
. "Accept our heartfelt greetings!!\n\n"
. "As a longtime admirer of the outstanding work done by Ministry of Defence for army personnel and their families, this proposal is our attempt to teach Army Personnel and AOR with the latest technologies at their doorstep. It is our pleasure to introduce SLOG as a leading certified organization in the field of Employability Skill Training programs, Computer Literacy Programs, Motivational Speaker Programs and Engineers Training Programs for budding engineers.\n\n"
. "SLOG is certified by Ministry of MSME, Government of India, approved by Ministry of Corporate Affairs, recognized by Startup India & partner of Institution of Engineers (India) — a 100-year-old organization. SLOG is also collaborated with IIT Roorkee Alumni Association DC and many more Government and Prestigious Private Organizations.\n\n"
. "We provide Technical Workshop Programs, Corporate Training Programs, Vocational Training Programs, Summer Programs & 6-Months Training Programs on various technologies like Digital Marketing, Python, Machine Learning, Java, PHP, CCNA, Oracle, Data Science, Mean Stack, Joomla, Software Testing, Cloud Computing, Ethical Hacking, MATLAB, CATIA, AUTOCAD, CREO (PRO-E), STAAD.PRO, Embedded Systems, VHDL, Wireless & Telecom, PLC & SCADA, Internet of Things (IoT) and many more.\n\n"
. "With reference to the same, SLOG wishes to conduct an IT Training Program for Army Personnel.\n\n"
. "We look forward to this mutually beneficial association and your kind cooperation in this endeavor. SLOG will be glad to receive your positive reply.\n\n"
. "Thanking you.";
?>

<textarea name="intro_text"><?= htmlspecialchars($proposal['intro_text'] ?? $default_intro) ?></textarea>

          </div>

          <div class="section-title">Signatory Information</div>
          <div class="field">
            <label>Name</label>
            <input type="text" name="signatory_name" value="<?= htmlspecialchars($proposal['signatory_name'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Title</label>
            <input type="text" name="signatory_title" value="<?= htmlspecialchars($proposal['signatory_title'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Phone</label>
            <input type="text" name="signatory_phone" value="<?= htmlspecialchars($proposal['signatory_phone'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Email</label>
            <input type="text" name="signatory_email" value="<?= htmlspecialchars($proposal['signatory_email'] ?? '') ?>">
          </div>
        </div>
        <!-- ========== END COVER ========== -->
        <!-- === Optional pages toggles === -->
<div class="toggle-row">
  <label class="pill-toggle">
    <input type="checkbox" name="include_about"
      <?= !empty($proposal['include_about']) ? 'checked' : '' ?>>
    <span>Include About</span>
  </label>

  <label class="pill-toggle">
    <input type="checkbox" name="include_technologies"
      <?= !empty($proposal['include_technologies']) ? 'checked' : '' ?>>
    <span>Include Technologies</span>
  </label>
</div>

        <hr class="sep" />
        <!-- dynamic blocks for page 2+ -->
        <div id="blocks-host"></div>

        <!-- hide bottom “Add Page”; sidebar one stays -->
        <div class="controls">
          <button class="btn" type="button" id="add-page" style="display:none">+ Add Page</button>
          <button class="btn" type="button" id="add-table">+ Add Table</button>
          <button class="btn" type="button" id="add-content">+ Add Course Content</button>
        </div>

        <div class="controls">
          <button class="btn primary">Save</button>
          <a class="btn" href="index.php?action=list">Cancel</a>
        </div>
      </section>
    </div>
  </form>
</div>


<script>
  window.__INITIAL_ITEMS__ = <?= json_encode($items ?? $default_items ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="/assets/editor.js?v=2"></script>
<?php include __DIR__ . '/_footer.php'; ?>
