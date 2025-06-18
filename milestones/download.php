<?php
header('Content-Type: text/html; charset=UTF-8');

require_once '../vendor/autoload.php'; // Correct path depending on your project
require_once '../includes/db.php'; // Your database connection

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch milestone
    $sql = "SELECT pm.*, p.name AS project_name, c.name AS client_name, c.address AS client_address
    FROM project_milestones pm
    JOIN projects p ON pm.project_id = p.id
    JOIN clients c ON p.client_id = c.id
    WHERE pm.id = '$id' AND pm.status = 'completed'";


    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $milestone = mysqli_fetch_assoc($result);

        // === Fetch and Update Invoice Number ===
        // Check if milestone already has invoice number
        if (!empty($milestone['invoice_no'])) {
            $invoice_no_formatted = $milestone['invoice_no'];
        } else {
            // Fetch counter and increment (as above)
            $counter_result = mysqli_query($conn, "SELECT id, last_invoice_no FROM milestone_invoice_counter ORDER BY id DESC LIMIT 1");

            if ($counter_result && mysqli_num_rows($counter_result) > 0) {
                $row = mysqli_fetch_assoc($counter_result);
                $current_invoice_no = $row['last_invoice_no'] + 1;

                $row_id = intval($row['id']);
                mysqli_query($conn, "UPDATE milestone_invoice_counter SET last_invoice_no = $current_invoice_no WHERE id = $row_id");

                $invoice_no_formatted = str_pad($current_invoice_no, 4, '0', STR_PAD_LEFT);
            } else {
                mysqli_query($conn, "INSERT INTO milestone_invoice_counter (last_invoice_no) VALUES (1)");
                $invoice_no_formatted = '0001';
            }

            // Save invoice number in milestone (so it stays fixed)
            mysqli_query($conn, "UPDATE project_milestones SET invoice_no = '$invoice_no_formatted' WHERE id = $id");
        }


        // ========================================

        // Create HTML for PDF

        $currency_symbols = [
            'USD' => '$',
            'INR' => '₹'
        ];
        $currency_code = $milestone['currency_code'] ?? '';
        $symbol = $currency_symbols[$currency_code] ?? $currency_code;
        $amount_display = isset($milestone['amount']) ? $symbol . ' ' . number_format($milestone['amount'], 2) . '/-' : '-';

        $clean_description = preg_replace('/\xC2\xA0|\s+$/u', '', $milestone['description']);
        // Step 1: Decode HTML entities like &nbsp; to real characters
        $desc = html_entity_decode($milestone['description'], ENT_QUOTES, 'UTF-8');

        // Step 2: Remove any non-breaking spaces and regular trailing whitespace
        $desc = preg_replace('/[\x{00A0}\s]+$/u', '', $desc); // removes NBSP and spaces at end

        // Step 3: Encode again to HTML-safe for PDF
        // Convert HTML entities to plain text (strip tags), preserve line breaks
        $plain_text = strip_tags(html_entity_decode($milestone['description'], ENT_QUOTES, 'UTF-8'));
        $plain_text = trim($plain_text);
        $description_html = '<div style="white-space: pre-wrap; direction: ltr; text-align: left;">' . htmlspecialchars($plain_text, ENT_QUOTES, 'UTF-8') . '</div>';
        $invoice_id_display = 'INV-' . $invoice_no_formatted;


        $html = '<meta charset="UTF-8">

   
        <style>
            * { font-family: Sans-serif,  DejaVu Sans; }
            td {
                width: 330px;
                background-color: rgba(18, 35, 45, 0.53);
                border-radius: 7px;
                padding: 10px;
                color: white;
            }
            .row {
                height: 50px;
                width: 700px;
            }
            h1 {
                margin-bottom: 5px;
                margin-top: 0px;
                font-size: 25px;
            }
            h5 {
                margin-bottom: 15px;
                margin-top: 15px;
                color: grey;
            }
            th {
                color: white;
                background-color: #F3C100;
                border-radius: 7px 0px 0px;
            }
            pre {
                text-align: right;
                font-size: 20px;
            }
            p {
                margin-bottom: 0px;
                margin-top: 0px;
            }
        </style>
        
        <h1 style="color: #F3C100;">Invoice</h1>
        <h5>Invoice No <b style="color:black;">#' . $invoice_id_display . '</b></h5>
        <h5>Invoice Date: <b style="color:black;">' . gmdate("F j, Y") . '</b></h5>
                
        <table>
            <tr>
                <td>
                    <h1 style="color: #F3C100;margin-bottom:19px;">Billed By</h1>
                    <strong>Hemandeep Singh</strong> 
                    <p>E-257 3rd Floor , VIII-B, Industrial Area , Mohali,</p>
                    <p>Mohali,</p>
                    <p>Punjab, India - 140308</p>
                    <p><strong>PAN:</strong> BHHPS5003E</p>
                </td>
                <td style="padding-bottom:70px;">
                    <h1 style="color: #F3C100;margin-bottom:19px;">Billed To</h1>
                    <strong>' . htmlspecialchars($milestone['client_name']) . '</strong>
                    <p>' . nl2br(htmlspecialchars($milestone['client_address'])) . '</p>
                </td>
            </tr>
        </table>
        <br>
        <table>
            <tr>
                <th style="width: 500px;">Description</th>
                <th style="width: 165px; border-radius: 0px 7px 0px 0px;">Amount</th>
            </tr>
            <tr>
               <td style="width: 500px; border-radius: 0px 0px 0px 7px; background-color: #12232D;">' . $description_html . '</td>
                <td style="width: 165px; border-radius: 0px 0px 7px 0px; background-color: #12232D;text-align:center;">' . htmlspecialchars($amount_display) . '</td>
            </tr>
        </table>
        <br>
        <hr>
    <pre style="padding-right:40px;">Total :  ' . $amount_display . '</pre>
        <hr>
        ';
        // Setup DOMPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output PDF
        $dompdf->stream('milestone_' . $milestone['id'] . '.pdf', ['Attachment' => true]);
        exit();
    } else {
        echo "Milestone not found or not completed.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}
