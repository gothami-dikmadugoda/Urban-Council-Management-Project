<?php
// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check for TCPDF library
if (!file_exists('tcpdf/tcpdf.php')) {
    // TCPDF library is not available, show error message
    echo '<div style="color: red; font-weight: bold; padding: 20px;">
        Error: TCPDF library not found. Please install TCPDF to enable PDF exports.<br>
        For XAMPP users: Download TCPDF from <a href="https://github.com/tecnickcom/TCPDF/releases" target="_blank">https://github.com/tecnickcom/TCPDF/releases</a> and extract to a "tcpdf" folder in this directory.
    </div>';
    exit;
}

// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Today
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query appointments with filters
$sql = "SELECT a.*, v.name as visitor_name 
        FROM appointments a
        JOIN visitors v ON a.visitor_id = v.visitor_id
        WHERE 1=1";

if ($start_date) {
    $sql .= " AND DATE(a.appointment_date) >= '$start_date'";
}
if ($end_date) {
    $sql .= " AND DATE(a.appointment_date) <= '$end_date'";
}
if ($status_filter && $status_filter != 'all') {
    $sql .= " AND a.status = '$status_filter'";
}

$sql .= " ORDER BY a.appointment_date DESC";
$appointments = $conn->query($sql);

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Reception Dashboard');
$pdf->SetAuthor('Reception Dashboard System');
$pdf->SetTitle('Appointment Report');
$pdf->SetSubject('Appointment Report');
$pdf->SetKeywords('Appointment, Report, Reception');

// Set default header data
$pdf->SetHeaderData('', 0, 'Appointment Report', 'Generated on: ' . date('Y-m-d H:i:s'));

// Set header and footer fonts
$pdf->setHeaderFont(Array('helvetica', '', 10));
$pdf->setFooterFont(Array('helvetica', '', 8));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont('courier');

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Set image scale factor
$pdf->setImageScale(1.25);

// Set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
$pdf->SetFont('helvetica', '', 10);

// Add a page
$pdf->AddPage();

// Report title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Appointment Report', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Period: ' . date('F j, Y', strtotime($start_date)) . ' - ' . date('F j, Y', strtotime($end_date)), 0, 1, 'C');
if ($status_filter != 'all') {
    $pdf->Cell(0, 5, 'Status Filter: ' . ucfirst($status_filter), 0, 1, 'C');
}
$pdf->Ln(5);

// Calculate statistics
$status_counts = array(
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'completed' => 0
);

$total_duration = 0;
$appointment_data = array();

if ($appointments->num_rows > 0) {
    while($row = $appointments->fetch_assoc()) {
        $status_counts[$row['status']]++;
        $total_duration += $row['duration'];
        $appointment_data[] = $row;
    }
}

$total_appointments = array_sum($status_counts);
$avg_duration = $total_appointments > 0 ? $total_duration / $total_appointments : 0;

// Report summary section
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Report Summary', 0, 1);
$pdf->SetFont('helvetica', '', 10);

$pdf->Cell(60, 7, 'Total Appointments:', 1);
$pdf->Cell(30, 7, $total_appointments, 1, 1);

$pdf->Cell(60, 7, 'Pending Appointments:', 1);
$pdf->Cell(30, 7, $status_counts['pending'], 1, 1);

$pdf->Cell(60, 7, 'Confirmed Appointments:', 1);
$pdf->Cell(30, 7, $status_counts['confirmed'], 1, 1);

$pdf->Cell(60, 7, 'Completed Appointments:', 1);
$pdf->Cell(30, 7, $status_counts['completed'], 1, 1);

$pdf->Cell(60, 7, 'Cancelled Appointments:', 1);
$pdf->Cell(30, 7, $status_counts['cancelled'], 1, 1);

$pdf->Cell(60, 7, 'Average Duration:', 1);
$pdf->Cell(30, 7, number_format($avg_duration, 1) . ' hours', 1, 1);

$pdf->Ln(5);

// Appointments table
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Appointment Details', 0, 1);

if (count($appointment_data) > 0) {
    // Table header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(45, 7, 'Visitor', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Date & Time', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Purpose', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Status', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Duration', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Notes', 1, 1, 'C');
    
    // Table data
    $pdf->SetFont('helvetica', '', 9);
    foreach ($appointment_data as $row) {
        // Calculate cell heights based on content
        $height = max(
            $pdf->getStringHeight(45, $row['visitor_name']),
            $pdf->getStringHeight(40, $row['purpose'] ?: '-'),
            $pdf->getStringHeight(30, $row['notes'] ?: '-'),
            7
        );
        
        $pdf->MultiCell(45, $height, $row['visitor_name'], 1, 'L', 0, 0);
        $pdf->MultiCell(35, $height, format_date($row['appointment_date']), 1, 'L', 0, 0);
        $pdf->MultiCell(40, $height, $row['purpose'] ?: '-', 1, 'L', 0, 0);
        $pdf->MultiCell(25, $height, $row['status'], 1, 'L', 0, 0);
        $pdf->MultiCell(20, $height, $row['duration'] . ' hrs', 1, 'L', 0, 0);
        $pdf->MultiCell(30, $height, $row['notes'] ?: '-', 1, 'L', 0, 1);
    }
} else {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'No appointments found for the selected period', 1, 1, 'C');
}

// Output the PDF
$pdf->Output('appointment_report.pdf', 'I');
?>