<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Render the proposal PDF and save it to STORAGE_PATH.
 *
 * - Enables remote assets (http/https) if you ever need them
 * - Sets HTML5 parser and DPI to match browser print metrics
 * - Sets default font to 'cambria' (ensure your @font-face points to Cambria TTFs)
 * - Sets chroot + base path to your /public folder so relative URLs like "assets/..." work
 * - Injects a <base> tag pointing to /public so that all relative paths resolve reliably
 */
function render_proposal_pdf(array $proposal, array $items): string {
    // Resolve your public web root where assets (css, images, fonts) live
    $publicRoot = realpath(__DIR__ . '/../public');
    if ($publicRoot === false) {
        throw new RuntimeException('Public root not found at ../public');
    }

    // Build a file:// URL to public root (used in <base> tag fallback)
    $publicRootFileUrl = 'file://' . $publicRoot . '/';

    // Dompdf options
    $options = new Options();
    $options->set('isRemoteEnabled', true);        // allow http(s) assets if you ever use them
    $options->set('isHtml5ParserEnabled', true);   // better CSS/HTML handling
    $options->set('dpi', 96);                      // align with Chrome print DPI
    $options->setDefaultFont('cambria');           // ensure Cambria is the fallback
    // Restrict filesystem access to /public so local assets can be read safely
    // (All local files must be inside this chroot to be accessible)
    $options->setChroot($publicRoot);

    $dompdf = new Dompdf($options);

    // Set base path so relative URLs in HTML & CSS (e.g., assets/...) resolve under /public
    // This complements the chroot and helps Dompdf resolve CSS url(...) and <img src="...">
    $dompdf->setBasePath($publicRoot);

    // Make template variables available
    ob_start();
    $proposal_data  = $proposal;
    $proposal_items = $items;

    // OPTIONAL helper for templates (if you choose to use it there):
    // $asset('assets/images/banner.png') -> "file:///.../public/assets/images/banner.png"
    $asset = function (string $relativePath) use ($publicRoot) : string {
        $relativePath = ltrim($relativePath, '/');
        return 'file://' . $publicRoot . '/' . $relativePath;
    };

    include __DIR__ . '/../templates/proposal_pdf.php';
    $html = ob_get_clean();

    // Inject a <base> tag pointing at /public so that any relative paths in the
    // template (images, css, fonts) resolve correctly. If a <base> already exists,
    // this will leave it alone.
    if (stripos($html, '<base ') === false) {
        if (preg_match('~<head[^>]*>~i', $html)) {
            $html = preg_replace(
                '~(<head[^>]*>)~i',
                '$1' . "\n" . '    <base href="' . htmlspecialchars($publicRootFileUrl, ENT_QUOTES, 'UTF-8') . '">' . "\n",
                $html,
                1
            );
        } else {
            // No <head>? Prepend one to be safe.
            $html = "<head>\n" .
                    '    <base href="' . htmlspecialchars($publicRootFileUrl, ENT_QUOTES, 'UTF-8') . '">' . "\n" .
                    "</head>\n" . $html;
        }
    }

    // Load, render, save
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait'); // exact margins/sizing should be controlled via CSS @page
    $dompdf->render();

    $output   = $dompdf->output();
    $filename = 'proposal_' . $proposal['id'] . '.pdf';
    $filepath = STORAGE_PATH . '/' . $filename;

    // Ensure storage directory exists
    $storageDir = dirname($filepath);
    if (!is_dir($storageDir)) {
        if (!mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            throw new RuntimeException('Failed to create storage directory: ' . $storageDir);
        }
    }

    file_put_contents($filepath, $output);
    return $filename;
}
