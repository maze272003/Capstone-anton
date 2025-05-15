<?php
require_once('tcpdf/tcpdf.php');

// Check if form data is sent
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $sales_data = json_decode($_POST['sales_data'], true);
    
    // Create new PDF document in Landscape mode ('L')
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Spring Bullbars');
    $pdf->SetTitle('Sales Report');
    
    // Set UTF-8 supporting font
    $pdf->SetFont('dejavusans', '', 10);  // ✅ Supports the ₱ symbol

    // Add a landscape page
    $pdf->AddPage();

    // Report title
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

    foreach ($sales_data as $row) {
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
