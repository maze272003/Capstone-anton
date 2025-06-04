<?php
require_once('tcpdf/tcpdf.php');

// Check if form data is sent
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    // Decode the JSON data
    $sales_data = json_decode(html_entity_decode($_POST['sales_data']), true);
    
    // Check if this is a custom date range report with the new structure
    if (isset($sales_data['data'])) {
        $report_data = $sales_data['data'];
        $grand_total = $sales_data['grand_total'];
        $profit = $sales_data['profit'];
        $low_stock_data = $sales_data['low_stock'];
    } else {
        // For backward compatibility
        $report_data = $sales_data;
        $grand_total = 0;
        $total_buying_price = 0;
        foreach ($report_data as $row) {
            $grand_total += $row['total_saleing_price'];
            $total_buying_price += ($row['buy_price'] * $row['total_sales']);
        }
        $profit = $grand_total - $total_buying_price;
        $low_stock_data = array();
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
    $pdf->SetFont('dejavusans', '', 10);

    // Add a landscape page
    $pdf->AddPage();

    // Add logo
    $logo_path = 'uploads/img/logo.png';
    $logo_added = false;
    $logo_height = 0;
    $logo_y_pos = 10;

    if (file_exists($logo_path)) {
        try {
            $img_size = getimagesize($logo_path);
            if ($img_size !== false) {
                $img_width = $img_size[0];
                $img_height = $img_size[1];
                $max_width = 40;
                $ratio = $max_width / $img_width;
                $new_width = $max_width;
                $new_height = $img_height * $ratio;
                $pdf->Image($logo_path, 15, $logo_y_pos, $new_width, $new_height, 'PNG');
                $logo_added = true;
                $logo_height = $new_height;
            }
        } catch (Exception $e) {
            error_log('Failed to add logo to PDF: ' . $e->getMessage());
        }
    }

    // Report title
    $title_y_pos = $logo_added ? $logo_y_pos + $logo_height + 5 : 15;
    $pdf->SetY($title_y_pos);
    $html = '<h2 style="text-align:center;">Spring Bullbars - Sales Report</h2>';
    $html .= "<p style='text-align:center;'><strong>Date Range: </strong> $start_date to $end_date</p>";

    // Sales Table
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
    
    foreach ($report_data as $row) {
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
                  <td align="right"><strong>₱' . number_format($grand_total, 2) . '</strong></td>
                </tr>
                <tr style="font-weight:bold; background-color:#f2f2f2;">
                  <td colspan="4"></td>
                  <td align="center"><strong>Profit</strong></td>
                  <td align="right"><strong>₱' . number_format($profit, 2) . '</strong></td>
                </tr>
              </tfoot>
            </table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Add Low Stock Products Section
    if (!empty($low_stock_data)) {
        $pdf->AddPage('L');
        
        $html = '<h2 style="text-align:center; color:#d9534f;">Low Stock Products (Quantity < 100)</h2>';
        
        foreach ($low_stock_data as $category) {
            $html .= '<h3 style="background-color:#f2f2f2; padding:5px;">' 
                   . htmlspecialchars($category['category_name']) 
                   . ' <span style="color:#d9534f;">(' . $category['count'] . ' items)</span></h3>';
            
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">
                        <thead>
                            <tr style="background-color:#f2f2f2; text-align:center;">
                                <th width="70%">Product Name</th>
                                <th width="30%">Current Quantity</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($category['products'] as $product) {
                $quantity_style = ($product['quantity'] == 0) ? 'color:red; font-weight:bold;' : 'color:#d9534f;';
                $out_of_stock = ($product['quantity'] == 0) ? ' (OUT OF STOCK)' : '';
                
                $html .= '<tr>
                            <td width="70%">' . htmlspecialchars($product['name']) . '</td>
                            <td width="30%" style="' . $quantity_style . '">' 
                          . htmlspecialchars($product['quantity']) . $out_of_stock . '</td>
                          </tr>';
            }
            
            $html .= '</tbody></table><br>';
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    // Output PDF (force download)
    $pdf->Output('Sales_Report_' . date('Y-m-d') . '.pdf', 'D');
}
?>