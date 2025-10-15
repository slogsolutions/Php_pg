<?php
// Template expects $proposal_data and $proposal_items

// Build a reliable file:// asset helper pointed at /public
$publicRoot = realpath(__DIR__ . '/../public');
if ($publicRoot === false) {
  throw new RuntimeException('Public folder not found at ../public');
}
$asset = function (string $rel) use ($publicRoot): string {
  return 'file://' . $publicRoot . '/' . ltrim($rel, '/');
};
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($proposal_data["title"]) ?></title>
  <link rel="stylesheet" href="<?= $asset('assets/pdf.css') ?>">
</head>
<body>

  <!-- ===== COVER PAGE ===== -->
  <section class="page cover">
    <div class="cover-inner">
      <!-- Header/title block -->
      <header class="cover-header">
        <div class="title-block">
          <div class="line1">PROPOSAL ON</div>
          <div class="program program-underline"><?= htmlspecialchars($proposal_data["title"]) ?></div>
          <div class="for-line">FOR</div>
          <div class="for-where"><?= htmlspecialchars($proposal_data["for_whom"]) ?></div>
        </div>
        <div class="delivered-by">
          <div class="db-small"><strong>Delivered by</strong></div>
          <div class="db-company"><strong>SLOG SOLUTIONS PRIVATE LIMITED</strong></div>
        </div>
        <div class="top-strip">
          <img src="<?= $asset('assets/strip.png') ?>" alt="top strip" class="strip-top"/>
        </div>
      </header>
      <div class="banner-wrap">
        <img src="<?= $asset('assets/banner.png') ?>" alt="banner" class="banner"/>
      </div>
      <div class="bottom-strip">
        <img src="<?= $asset('assets/strip2.jpg') ?>" alt="bottom strip" class="strip-bottom"/>
      </div>
      <footer class="cover-footer">
        <div class="footer-content">
          <span class="footer-left">SLOG- A MSME CERTIFIED ENTERPRISES</span>
          <span class="footer-right">DATE: <?= htmlspecialchars($proposal_data["date"]) ?></span>
        </div>
      </footer>
    </div>
  </section>

  <!-- ===== Content pages: intro ===== -->
  <section class="page content">
    <div class="content-header">
      <div class="content-title">
        PROPOSAL FOR <?= htmlspecialchars($proposal_data["title"]) ?>
      </div>
      <div class="content-subheader">
        <div class="content-for">
          FOR <?= htmlspecialchars($proposal_data["recipient"]) ?>
        </div>
        <div class="content-date">
          Date: <?= htmlspecialchars($proposal_data["date"]) ?>
        </div>
      </div>
      <div class="content-by">
        BY SLOG SOLUTIONS PVT. LTD.
      </div>
    </div>

    <h4 class="intro-title">
      INTRODUCTION:
    </h4>

    <p class="intro-body">
<?php if (!empty($proposal_data["intro_text"])): ?>
<?= nl2br(htmlspecialchars($proposal_data["intro_text"])) ?>
<?php else: ?>
Respected Sir/Ma'am,<br>
Accept our heartfelt greetings!!<br><br>
As a longtime admirer of the outstanding work done by Ministry of Defence for army personnel and their families, this proposal is our attempt to teach Army Personnel and AOR with the latest technologies at their doorstep. It is our pleasure to introduce SLOG as a leading certified organization in the field of Employability Skill Training programs, Computer Literacy Programs, Motivational Speaker Programs and Engineers Training Programs for budding engineers.<br><br>
SLOG is certified by Ministry of MSME, Government of India, approved by Ministry of Corporate Affairs, recognized by Startup India &amp; partner of Institution of Engineers (India) — a 100-year-old organization. SLOG is also collaborated with IIT Roorkee Alumni Association DC and many more Government and Prestigious Private Organizations.<br><br>
We provide Technical Workshop Programs, Corporate Training Programs, Vocational Training Programs, Summer Programs &amp; 6-Months Training Programs on various technologies like Digital Marketing, Python, Machine Learning, Java, PHP, CCNA, Oracle, Data Science, Mean Stack, Joomla, Software Testing, Cloud Computing, Ethical Hacking, MATLAB, CATIA, AUTOCAD, CREO (PRO-E), STAAD.PRO, Embedded Systems, VHDL, Wireless &amp; Telecom, PLC &amp; SCADA, Internet of Things (IoT) and many more.<br><br>
With reference to the same, SLOG wishes to conduct an IT Training Program for Army Personnel.<br><br>
We look forward to this mutually beneficial association and your kind cooperation in this endeavor. SLOG will be glad to receive your positive reply.<br><br>
Thanking you.
<?php endif; ?>
    </p>

    <div class="signature">
      <p>
        <strong><?= htmlspecialchars($proposal_data["signatory_name"]) ?></strong><br/>
        <strong><?= htmlspecialchars($proposal_data["signatory_title"]) ?></strong><br/>
        Mob: <?= htmlspecialchars($proposal_data["signatory_phone"]) ?><br/>
        Email: <a href="mailto:<?= htmlspecialchars($proposal_data["signatory_email"]) ?>"><?= htmlspecialchars($proposal_data["signatory_email"]) ?></a><br/>
        slog.doon@gmail.com
      </p>
    </div>
  </section>

  <!-- ===== DYNAMIC CONTENT PAGES ===== -->
  <?php 
  // Group items by page
  $currentPageItems = [];
  foreach ($proposal_items as $item) {
      if (($item['type'] ?? 'content') === 'page') {
          // Render previous page items if any
          if (!empty($currentPageItems)) {
              echo '<section class="page program-structure">';
              echo renderPageItems($currentPageItems);
              echo '</section>';
          }
          $currentPageItems = [];
      } else {
          $currentPageItems[] = $item;
      }
  }
  // Render remaining items
  if (!empty($currentPageItems)) {
      echo '<section class="page program-structure">';
      echo renderPageItems($currentPageItems);
      echo '</section>';
  }
  
  function renderPageItems($items) {
      $output = '';
      $hasPageTitle = false;
      
      foreach ($items as $item) {
          $label = trim((string)($item['label'] ?? ''));
          $rawBody = $item['body'] ?? '';
          $body = is_array($rawBody) ? $rawBody : (is_string($rawBody) ? json_decode($rawBody, true) : null);
          if (is_null($body) && is_string($rawBody)) $body = $rawBody;
          $kind = is_array($body) ? ($body['__kind'] ?? 'content') : null;
          
          // Render page title if it exists and we haven't rendered one yet
          if (!$hasPageTitle && !empty($label)) {
              $unwantedTitles = ['Cover', 'AI', 'ML'];
              if (!in_array($label, $unwantedTitles)) {
                  $output .= '<div class="page-title">' . htmlspecialchars($label) . '</div>';
                  $hasPageTitle = true;
              }
          }
          
          if ($kind === 'table' || $kind === 'key_value_table') {
              $output .= renderTable($body, $label);
          } elseif ($kind === 'content' || $kind === 'legacy') {
              $output .= renderContent($body, $label);
          }
      }
      return $output;
  }
  
  function renderTable($body, $label) {
      $cols = $body['columns'] ?? [];
      $rows = $body['rows'] ?? [];
      $title = $body['title'] ?? $label ?: 'Table';
      
      $output = '';
      
      if (!empty($rows)) {
          $output .= '<table class="proposal-table">';
          
          // Add table caption for title with green background
          if (!empty($title)) {
              $output .= '<caption class="table-caption">' . htmlspecialchars($title) . '</caption>';
          }
          
          if (count($cols) !== 2) {
              $output .= '<thead><tr>';
              foreach ($cols as $c) {
                  $output .= '<th>' . htmlspecialchars((string)$c) . '</th>';
              }
              $output .= '</tr></thead><tbody>';
              foreach ($rows as $r) {
                  $output .= '<tr>';
                  foreach ($r as $c) {
                      $output .= '<td>' . nl2br(htmlspecialchars((string)$c)) . '</td>';
                  }
                  $output .= '</tr>';
              }
              $output .= '</tbody>';
          } else {
              $output .= '<tbody>';
              foreach ($rows as $r) {
                  if (!empty($r[0]) || !empty($r[1])) {
                      $output .= '<tr>';
                      $output .= '<td>' . htmlspecialchars((string)$r[0]) . '</td>';
                      $output .= '<td>' . nl2br(htmlspecialchars((string)$r[1])) . '</td>';
                      $output .= '</tr>';
                  }
              }
              $output .= '</tbody>';
          }
          $output .= '</table>';
      }
      return $output;
  }
  
  function renderContent($body, $label) {
      $subTitle = trim((string)($body['subTitle'] ?? $label ?: 'Course Content'));
      $richText = (string)($body['richText'] ?? '');
      
      $output = '';
      
      // Only show subtitle if it's not unwanted text and we haven't used it as page title
      $unwantedSubtitles = ['AI', 'ML', 'Cover'];
      if (!in_array($subTitle, $unwantedSubtitles) && !empty($subTitle) && $subTitle !== $label) {
          $output .= '<h4 class="content-subtitle">' . htmlspecialchars($subTitle) . '</h4>';
      }
      
      // Convert text to bullet points like Django version
      $lines = array_filter(array_map('trim', explode("\n", $richText)));
      if (count($lines) > 1) {
          $output .= '<ul>';
          foreach ($lines as $line) {
              if (!empty(trim($line))) {
                  $output .= '<li>' . htmlspecialchars($line) . '</li>';
              }
          }
          $output .= '</ul>';
      } else if (!empty(trim($richText))) {
          $output .= '<div class="content-text">' . nl2br(htmlspecialchars($richText)) . '</div>';
      }
      
      return $output;
  }
  ?>

  <?php if (!empty($proposal_data['include_about'])): ?>
    <section class="page about">
      <div class="about-header">
        ABOUT SLOG SOLUTIONS PVT. LTD.
      </div>
      <ul class="about-list">
        <li>SLOG Solutions Pvt. Ltd. is incorporated on September 2018 having 7+ year of experience in services.</li>
        <li>SLOG is an ISO 9001 : 2015 certified Company</li>
        <li>SLOG is a MSME Certified Organization</li>
        <li>SLOG is approved by Ministry of Corporate Affairs, Government of India</li>
        <li>SLOG provides Technical Training program, corporate training programs, Faculty development program, Capacity building program, Outbound Training Program.</li>
        <li>SLOG is recognized by Start-up India.</li>
        <li>SLOG is Collaborated with E&amp;ICT Academy IIT Roorkee For Corporate Trainings</li>
        <li>SLOG is collaborated with IIT Roorkee Alumni Association DC</li>
        <li>SLOG is also collaborated with 100 year old Institution of Engineering, India.</li>
        <li>SLOG is Certiport Authorized Testing Centre (CATC) for BRAND CERTIFICATION of <span class="highlight-red">Autodesk, QuickBooks, Microsoft, Apple and Adobe.</span></li>
        <li>SLOG Deliver more than <span class="highlight-red">70 + Technical programs to Ministry of Defense (Indian Army, Indian Navy)</span> in <span class="highlight-red">PAN India</span> Includes: Southern Command (Pune), Western Command (Chandigarh), MCEME Schendrabad, Jammu &amp; Kashmir, Assam, Telangana, Amritsar, Dehradun, Haridwar, Bangalore, Schendrabad and many more.</li>
        <li>SLOG Deliver more than 200+ Corporate / Professional Trainings / Faculty development programs include organizations like DRDO, ORDNANCE (OFIL), KV faculties, UBTER, UTU, World Bank projects and many more.</li>
        <li>SLOG Deliver more than 800+ Student Development program, benefited more than 30,000 students.</li>
        <li>SLOG deliver Virtual training program in online mode to 15,000+ polytechnic students at a time.</li>
        <li>SLOG has a group of 100+ Industry Experts and collaborated with IIT Roorkee Alumni Association DC for Expert Guest Lecture by <span class="highlight-red">IITians</span></li>
        <li>SLOG provide technical training program, Personality development program, Corporate trainings, Student development programs in more than 100+ latest trending technologies.</li>
      </ul>
    </section>
  <?php endif; ?>

  <?php if (!empty($proposal_data['include_technologies'])): ?>
    <section class="page technologies">
      <div class="technologies-title">
        TECHNOLOGIES OFFERED BY SLOG SOLUTIONS PVT. LTD.
      </div>
      <table class="technologies-table">
        <thead>
          <tr>
            <th>BASIC TECHNOLOGIES</th>
            <th>ADVANCE TECHNOLOGIES</th>
            <th>LATEST TRENDING TECHNOLOGIES</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <ul>
                <li>AutoCAD</li>
                <li>BIM</li>
                <li>Advance Excel</li>
                <li>Office Automation</li>
                <li>C Language</li>
                <li>C++ Language</li>
                <li>Networking</li>
                <li>CCNA</li>
                <li>Java</li>
                <li>Android</li>
                <li>PHP</li>
                <li>Web Designing</li>
                <li>Web Development</li>
                <li>Java Script</li>
                <li>PCB Circuit Designing</li>
                <li>2D Designing</li>
              </ul>
            </td>
            <td>
              <ul>
                <li>Home Automation using IoT</li>
                <li>CCNP</li>
                <li>Internet of Things (IoT)</li>
                <li>Cloud Computing</li>
                <li>Big Data Hadoop</li>
                <li>PL/SQL</li>
                <li>Cloud System</li>
                <li>Embedded System using ARM</li>
                <li>Embedded System using AVR</li>
                <li>MATLAB</li>
                <li>PLC SCADA</li>
                <li>SolidWorks</li>
                <li>CATIA</li>
                <li>ANSYS</li>
                <li>CREO</li>
                <li>Revit</li>
                <li>STAAD.Pro</li>
                <li>Lumion</li>
                <li>Digital Marketing</li>
                <li>Personality Development Program</li>
                <li>Tally 9.0</li>
              </ul>
            </td>
            <td>
              <ul>
                <li>Drone Technology</li>
                <li>Python</li>
                <li>Machine Learning</li>
                <li>Electric Vehicle</li>
                <li>R - Language</li>
                <li>Data Science</li>
                <li>Artificial Intelligence</li>
                <li>Ethical Hacking</li>
                <li>Cyber Security</li>
                <li>MEAN Stack</li>
                <li>Angular, Joomla</li>
                <li>Node.js & React.js</li>
                <li>Django</li>
                <li>Blockchain</li>
                <li>Data Analytics</li>
                <li>Industrial Automation</li>
                <li>HMI & VFD</li>
                <li>Robotics</li>
                <li>VLSI, VHDL</li>
                <li>IoT using Raspberry Pi</li>
                <li>IoT using Arduino</li>
                <li>3D Printing</li>
                <li>Inventor</li>
                <li>Fusion 360</li>
                <li>3DS Max</li>
                <li>ETABS</li>
                <li>MS Project</li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  <?php endif; ?>

</body>
</html>