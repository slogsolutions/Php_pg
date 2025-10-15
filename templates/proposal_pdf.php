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

  <base href="<?= 'file://' . $publicRoot . '/' ?>">

  <link rel="stylesheet" href="<?= $asset('assets/pdf.css') ?>">

<style>
    @page { size: A4; margin: 0; }
    html, body { margin: 0; padding: 0; }
    /* Keep cover content on the same page; push date to the bottom */
    .cover .cover-inner {
      min-height: 100vh; /* Use min-height 100vh (Dompdf uses this for 100%) */
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    /* Enforce a page break before the Introduction and all subsequent dynamic sections */
    .page-break-before { page-break-before: always; }
  </style>
</head>
<body>

  <section class="page cover">
    <div class="cover-inner">
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
        <div class="footer-left">SLOG- A MSME CERTIFIED ENTERPRISES</div>
        <div class="footer-right" style="font-size: 11pt; font-weight: 600; margin-right: 10px;">
          DATE: <?= htmlspecialchars($proposal_data["date"]) ?>
        </div>
      </footer>
    </div>
  </section>

  <section class="page content page-break-before">
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

    <div class="signature" style="font-family: Cambria, serif; font-size: 12pt;">
      <p style="margin: 0; margin-top: 5px; line-height: 1.4; font-weight: normal;">
        <strong><?= htmlspecialchars($proposal_data["signatory_name"]) ?></strong><br/>
        <strong><?= htmlspecialchars($proposal_data["signatory_title"]) ?></strong><br/>
        <strong></strong><br/>
        Mob: <?= htmlspecialchars($proposal_data["signatory_phone"]) ?><br/>
        Email: <a href="mailto:<?= htmlspecialchars($proposal_data["signatory_email"]) ?>" style="font-weight: 700; color: #0000FF; text-decoration: underline;"><?= htmlspecialchars($proposal_data["signatory_email"]) ?></a><br/>
        slog.doon@gmail.com
      </p>
    </div>
  </section>

  <?php
/** We output ONE .page section per item to ensure:
 * - a new page before each content section (using page-break-before)
 * - no extra blank page after the last one (handled by .page:last-of-type in pdf.css)
 */
foreach ($proposal_items as $it):
    $label    = trim((string)($it['label'] ?? ''));
    $rawBody  = $it['body'] ?? '';
    $body     = is_array($rawBody) ? $rawBody
              : (is_string($rawBody) ? json_decode($rawBody, true) : null);
    if (is_null($body) && is_string($rawBody)) $body = $rawBody;

    // [[PAGEBREAK]] marker is no longer needed (each item is its own page), so ignore it
    if (is_string($body) && strpos($body, '[[PAGEBREAK]]') !== false) {
      $body = str_replace('[[PAGEBREAK]]', '', $body);
    }
    $kind = is_array($body) ? ($body['__kind'] ?? 'content') : null;
?>
  <section class="page program-structure page-break-before" style="padding:20px 30px; font-family: Cambria, serif; font-size: 12pt;">
    <div class="section">
      <div class="page-title">
        <?= htmlspecialchars($label ?: 'Section') ?>
      </div>

      <?php if ($kind === 'page'): ?>
        <h2><?= htmlspecialchars($body['title'] ?? $label ?: 'Page') ?></h2>

      <?php elseif ($kind === 'table' || $kind === 'key_value_table'): // Use the unified structured table format ?>
        <h3><?= htmlspecialchars($body['title'] ?? $label ?: 'Table') ?></h3>
        <?php
            $cols = $body['columns'] ?? [];
            $rows = $body['rows'] ?? [];
        ?>

        <?php if (!empty($rows)): ?>
            <table class="proposal-table" style="width:100%; border-collapse:collapse; font-family: Cambria, serif; font-size:12pt; margin-top:10px;">
                <?php if ($kind === 'table'): // Show headers for generic table ?>
                <thead>
                    <tr>
                        <?php foreach ($cols as $c): ?>
                            <th style="border:1px solid #777; padding:8px; text-align:left; font-weight:700; background:#e7f3d9;"><?= htmlspecialchars((string)$c) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <?php foreach ($r as $c): ?>
                                <td style="border:1px solid #777; padding:8px; vertical-align:top; word-wrap:break-word;"><?= nl2br(htmlspecialchars((string)$c)) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <?php elseif ($kind === 'key_value_table' && count($cols) === 2 && ($cols[0] === 'label' || $cols[0] === 'Label')): // 2-column key-value table ?>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <?php if (!empty($r[0]) || !empty($r[1])): ?>
                        <tr>
                            <td style="width: 30%; font-weight: 700; white-space: nowrap; text-align: left; border: 1px solid #777; padding: 8px; vertical-align: top; font-size: 12pt;"><?= htmlspecialchars((string)$r[0]) ?></td>
                            <td style="width: 70%; white-space: normal; border: 1px solid #777; padding: 8px; vertical-align: top; font-size: 12pt;"><?= nl2br(htmlspecialchars((string)$r[1])) ?></td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                <?php endif; ?>
            </table>
        <?php endif; ?>


      <?php elseif ($kind === 'content'): ?>
        <?php
          $subTitle = trim((string)($body['subTitle'] ?? $label ?: 'Course Content'));
          $richText = (string)($body['richText'] ?? '');
        ?>
        <?php if ($subTitle !== ''): ?>
          <h4><?= htmlspecialchars($subTitle) ?></h4>
        <?php endif; ?>
        <div class="body"><?= nl2br(htmlspecialchars($richText)) ?></div>

      <?php else: /* Legacy plain-text mode */ ?>
        <?php
          $text    = trim((string)$body);
          $isCourse= stripos($label, 'COURSE:') === 0;
          $isTable = stripos($label, 'TABLE:') === 0;
        ?>

        <?php if ($isCourse): ?>
          <?php $lines = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $text))); ?>
          <?php if (!empty($lines)): ?>
            <ul style="margin:6px 0 14px; font-size:12pt; line-height:1.5; padding-left:20px; list-style-type: disc; font-family: Cambria, serif;">
              <?php foreach ($lines as $ln): ?>
                <li><?= htmlspecialchars($ln) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div style="white-space:pre-line; font-size:12pt; line-height:1.5; font-family: Cambria, serif;">
              <?= nl2br(htmlspecialchars($text)) ?>
            </div>
          <?php endif; ?>

        <?php elseif ($isTable): ?>
          <?php $rows = array_map('trim', preg_split("/\r\n|\r|\n/", $text)); ?>
          <table style="width:100%; border-collapse:collapse; font-family: Cambria, serif; font-size:12pt;">
            <?php foreach ($rows as $r): ?>
              <?php if ($r === '') continue; $cols = array_map('trim', explode('|', $r)); ?>
              <tr>
                <?php foreach ($cols as $c): ?>
                  <td style="border:1px solid #777; padding:6px;"><?= htmlspecialchars($c) ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </table>

        <?php else: ?>
          <div class="body"><?= nl2br(htmlspecialchars($text)) ?></div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </section>
<?php endforeach; ?>

  <?php if (!empty($proposal_data['include_about'])): ?>
<section class="page about page-break-before" style="font-family: Cambria, serif; padding: 20px 0 0 0;">
  <div style="background:#c8dca4; width:100%; padding:6px 12px; box-sizing:border-box;">
    <span style="font-size:14pt; font-weight:700; color:#000; text-decoration:underline;">
      ABOUT SLOG SOLUTIONS PVT. LTD.
    </span>
  </div>

  <ul style="
      list-style-position: outside;
      margin: 20px 16px 0 0;     /* top gap + small right margin */
      padding-left: 18px;        /* keeps bullets visible while flush-left */
      font-size: 12pt;
      line-height: 1.55;         /* little spacing between lines */
      color:#000;">
    <li style="padding-left:6px;">SLOG Solutions Pvt. Ltd. is incorporated on September 2018 having 7+ year of experience in services.</li>
    <li style="padding-left:6px;">SLOG is an ISO 9001 : 2015 certified Company</li>
    <li style="padding-left:6px;">SLOG is a MSME Certified Organization</li>
    <li style="padding-left:6px;">SLOG is approved by Ministry of Corporate Affairs, Government of India</li>
    <li style="padding-left:6px;">SLOG provides Technical Training program, corporate training programs, Faculty development program, Capacity building program, Outbound Training Program.</li>
    <li style="padding-left:6px;">SLOG is recognized by Start-up India.</li>
    <li style="padding-left:6px;">SLOG is Collaborated with E&amp;ICT Academy IIT Roorkee For Corporate Trainings</li>
    <li style="padding-left:6px;">SLOG is collaborated with IIT Roorkee Alumni Association DC</li>
    <li style="padding-left:6px;">SLOG is also collaborated with 100 year old Institution of Engineering, India.</li>
    <li style="padding-left:6px;">SLOG is Certiport Authorized Testing Centre (CATC) for BRAND CERTIFICATION of
      <span style="color:red; font-weight:600;">Autodesk, QuickBooks, Microsoft, Apple and Adobe.</span>
    </li>
    <li style="padding-left:6px;">SLOG Deliver more than
      <span style="color:red; font-weight:600;">70 + Technical programs to Ministry of Defense (Indian Army, Indian Navy)</span>
      in <span style="color:red; font-weight:600;">PAN India</span> Includes: Southern Command (Pune), Western Command (Chandigarh), MCEME Schendrabad, Jammu &amp; Kashmir, Assam, Telangana, Amritsar, Dehradun, Haridwar, Bangalore, Schendrabad and many more.
    </li>
    <li style="padding-left:6px;">SLOG Deliver more than 200+ Corporate / Professional Trainings / Faculty development programs include organizations like DRDO, ORDNANCE (OFIL), KV faculties, UBTER, UTU, World Bank projects and many more.</li>
    <li style="padding-left:6px;">SLOG Deliver more than 800+ Student Development program, benefited more than 30,000 students.</li>
    <li style="padding-left:6px;">SLOG deliver Virtual training program in online mode to 15,000+ polytechnic students at a time.</li>
    <li style="padding-left:6px;">SLOG has a group of 100+ Industry Experts and collaborated with IIT Roorkee Alumni Association DC for Expert Guest Lecture by <span style="color:red; font-weight:600;">IITians</span></li>
    <li style="padding-left:6px;">SLOG provide technical training program, Personality development program, Corporate trainings, Student development programs in more than 100+ latest trending technologies.</li>
  </ul>
</section>
  <?php endif; ?>

  <?php if (!empty($proposal_data['include_technologies'])): ?>
    <section class="page technologies page-break-before" style="padding: 18px 28px; font-family: Cambria, serif;">
      <div style="background:#ffd800; padding:10px 8px; text-align:center; font-weight:800; font-size:14pt; margin-bottom:12px; font-family: Cambria, serif;">
        TECHNOLOGIES OFFERED BY SLOG SOLUTIONS PVT. LTD.
      </div>

      <table style="width:90%; margin:0 auto; border-collapse:collapse; table-layout:fixed; font-family: Cambria, serif;">

        <thead>
          <tr>
            <th style="border:1px solid #777; padding:8px; font-weight:800; background:#f2f2f2; text-align:left; width:33%; font-family: Cambria, serif; font-size: 14pt;">BASIC TECHNOLOGIES</th>
            <th style="border:1px solid #777; padding:8px; font-weight:800; background:#f2f2f2; text-align:left; width:33%; font-family: Cambria, serif; font-size: 14pt;">ADVANCE TECHNOLOGIES</th>
            <th style="border:1px solid #777; padding:8px; font-weight:800; background:#f2f2f2; text-align:left; width:33%; font-family: Cambria, serif; font-size:14pt;">
  LATEST TRENDING<br>TECHNOLOGIES</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="vertical-align:top; border:1px solid #777; padding:10px; font-family: Cambria, serif;">
              <ul style="margin:0; padding-left:18px; font-size:12pt; line-height:1.45; font-family: Cambria, serif;">
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

            <td style="vertical-align:top; border:1px solid #777; padding:10px; font-family: Cambria, serif;">
              <ul style="margin:0; padding-left:18px; font-size:12pt; line-height:1.45; font-family: Cambria, serif;">
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

            <td style="vertical-align:top; border:1px solid #777; padding:10px; font-family: Cambria, serif; width:33%; word-wrap:break-word;">
              <ul style="margin:0; padding-left:18px; font-size:12pt; line-height:1.45; font-family: Cambria, serif;">
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