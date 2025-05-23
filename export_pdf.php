<?php
require_once('tcpdf/tcpdf.php');

// Check if form data is sent
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    // Decode the JSON data
    $sales_data = json_decode($_POST['sales_data'], true);
    
    // Check if this is a daily/weekly/monthly report with the new structure
    if (isset($sales_data['data'])) {
        $report_data = $sales_data['data'];
        $grand_total = $sales_data['grand_total'];
        $profit = $sales_data['profit'];
    } else {
        // For backward compatibility with custom date range
        $report_data = $sales_data;
        $grand_total = 0;
        $total_buying_price = 0;
        foreach ($report_data as $row) {
            $grand_total += $row['total_saleing_price'];
            $total_buying_price += ($row['buy_price'] * $row['total_sales']);
        }
        $profit = $grand_total - $total_buying_price;
    }

    // Create new PDF document in Landscape mode ('L')
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Spring Bullbars');
    $pdf->SetTitle('Sales Report');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set UTF-8 supporting font
    $pdf->SetFont('dejavusans', '', 10);  // ✅ Supports the ₱ symbol

    // Add a landscape page
    $pdf->AddPage();

    // Add logo
    $logo_path = 'uploads/img/logo.png';
    $logo_added = false; // Flag to check if logo was successfully added
    $logo_height = 0; // Variable to store logo height
    $logo_y_pos = 10; // Y position of the logo

    if (file_exists($logo_path)) {
        // Try to add the image directly without conversion
        try {
            // Get the dimensions of the logo
            $img_size = getimagesize($logo_path);
            if ($img_size !== false) {
                $img_width = $img_size[0];
                $img_height = $img_size[1];
                
                // Calculate new dimensions to maintain aspect ratio (max width 40mm)
                $max_width = 40;
                $ratio = $max_width / $img_width;
                $new_width = $max_width;
                $new_height = $img_height * $ratio;
                
                // Add logo to top left of the page
                $pdf->Image($logo_path, 15, $logo_y_pos, $new_width, $new_height, 'PNG');
                $logo_added = true;
                $logo_height = $new_height;
            }
        } catch (Exception $e) {
            // If image adding fails, continue without the logo
            error_log('Failed to add logo to PDF: ' . $e->getMessage());
        }
    }

    // Report title with adjusted position to accommodate logo
    // Set Y position for the title below the logo if added, otherwise at the top margin
    $title_y_pos = $logo_added ? $logo_y_pos + $logo_height + 5 : 15; // Add 5mm padding below logo
    $pdf->SetY($title_y_pos);
    $html = '<h2 style="text-align:center;">Spring Bullbars - Sales Report</h2>';
    $html .= "<p style='text-align:center;'><strong>Date Range: </strong> $start_date to $end_date</p>";

    // Table structure (with proper column width alignment)
    $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">
                <thead>
                    <tr style="background-color:#f2f2f2; text-align:center;">
                        <th width="15%">Date</th>
                        <th width="25%">Product Title</th>
                        <th width="15%">Buying Price</th>
                        <th width="15%">Selling Price</th>
                        <th width="15%">Total Qty</th>
                        <th width="15%">Total Sales</th>
                    </tr>
                </thead>
                <tbody>';
    
    $total_sales = 0;
    $total_profit = 0;

    foreach ($report_data as $row) {
        $total_sales += $row['total_saleing_price'];
        $profit = ($row['sale_price'] - $row['buy_price']) * $row['total_sales'];
        $total_profit += $profit;

        $html .= '<tr>
                    <td width="15%" align="center">' . htmlspecialchars($row['date']) . '</td>
                    <td width="25%" align="left">' . htmlspecialchars(ucfirst($row['name'])) . '</td>
                    <td width="15%" align="right">₱' . number_format($row['buy_price'], 2) . '</td>
                    <td width="15%" align="right">₱' . number_format($row['sale_price'], 2) . '</td>
                    <td width="15%" align="center">' . htmlspecialchars($row['total_sales']) . '</td>
                    <td width="15%" align="right">₱' . number_format($row['total_saleing_price'], 2) . '</td>
                  </tr>';
    }

    $html .= '</tbody>
              <tfoot>
                <tr style="font-weight:bold; background-color:#f2f2f2;">
                  <td colspan="4"></td>
                  <td align="center"><strong>Grand Total</strong></td>
                  <td align="right"><strong>₱' . number_format($total_sales, 2) . '</strong></td>
                </tr>
                <tr style="font-weight:bold; background-color:#f2f2f2;">
                  <td colspan="4"></td>
                  <td align="center"><strong>Profit</strong></td>
                  <td align="right"><strong>₱' . number_format($total_profit, 2) . '</strong></td>
                </tr>
              </tfoot>
            </table>';

    // Write the HTML to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF (force download)
    $pdf->Output('Sales_Report.pdf', 'D');
}
?>
