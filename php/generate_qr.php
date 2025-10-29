<?php
require_once 'lib/phpqrcode/qrlib.php';

function generateQRCode($ticketId, $email) {
    $qrPath = '../qr_codes/' . $ticketId . '.png';
    
    // Create QR code
    QRcode::png($ticketId . '|' . $email, $qrPath, QR_ECLEVEL_L, 4);
    
    return $qrPath;
}
?>