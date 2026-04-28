<?php
// services/PdfExporter.php — Wraps FPDF/TCPDF for PDF output

class PdfExporter {
    private $pdf;
    
    public function __construct() {
        $fpdfPath = __DIR__ . '/../assets/vendors/composer/setasign/fpdf/fpdf.php';
        if (!file_exists($fpdfPath)) {
            throw new Exception("FPDF not found. Please install FPDF via composer.");
        }
        require_once $fpdfPath;
        $this->pdf = new FPDF();
    }
    
    /**
     * Generate load report PDF
     */
    public function generateLoadReport(array $data): void {
        $this->pdf->AddPage();
        $this->pdf->SetFont('Arial', 'B', 16);
        $this->pdf->Cell(0, 10, 'Teacher Load Assignment Report', 0, 1, 'C');
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell(0, 6, 'Generated: ' . $data['generated_at'], 0, 1, 'C');
        $this->pdf->Ln(5);
        
        // Summary
        $summary = $data['summary'];
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 8, 'Summary', 0, 1);
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell(60, 6, 'Total Teachers: ' . $summary['total_teachers'], 0, 1);
        $this->pdf->Cell(60, 6, 'Overloaded: ' . $summary['overload_count'], 0, 1);
        $this->pdf->Cell(60, 6, 'Underloaded: ' . $summary['underload_count'], 0, 1);
        $this->pdf->Cell(60, 6, 'Total Units Assigned: ' . $summary['total_assigned_units'], 0, 1);
        $this->pdf->Ln(5);
        
        // Teacher details
        foreach ($data['teachers'] as $teacher) {
            $this->pdf->AddPage();
            $this->pdf->SetFont('Arial', 'B', 14);
            $this->pdf->Cell(0, 8, $teacher['last_name'] . ', ' . $teacher['first_name'], 0, 1);
            $this->pdf->SetFont('Arial', '', 10);
            $this->pdf->Cell(0, 6, 'ID: ' . $teacher['employee_id'] . ' | Dept: ' . $teacher['department'], 0, 1);
            $this->pdf->Cell(0, 6, 'Load: ' . $teacher['current_load'] . '/' . $teacher['max_units'] . ' units (' . strtoupper($teacher['status']) . ')', 0, 1);
            $this->pdf->Ln(3);
            
            if (!empty($teacher['assignments'])) {
                $this->pdf->SetFont('Arial', 'B', 11);
                $this->pdf->Cell(40, 7, 'Subject', 1);
                $this->pdf->Cell(20, 7, 'Units', 1);
                $this->pdf->Cell(25, 7, 'Day', 1);
                $this->pdf->Cell(30, 7, 'Time', 1);
                $this->pdf->Cell(30, 7, 'Room', 1);
                $this->pdf->Cell(35, 7, 'Section', 1);
                $this->pdf->Ln();
                
                $this->pdf->SetFont('Arial', '', 10);
                foreach ($teacher['assignments'] as $a) {
                    $this->pdf->Cell(40, 6, $a['code'], 1);
                    $this->pdf->Cell(20, 6, $a['units'], 1);
                    $this->pdf->Cell(25, 6, $a['day_of_week'], 1);
                    $this->pdf->Cell(30, 6, $a['start_time'] . '-' . $a['end_time'], 1);
                    $this->pdf->Cell(30, 6, $a['room'] ?? '-', 1);
                    $this->pdf->Cell(35, 6, $a['section'] ?? '-', 1);
                    $this->pdf->Ln();
                }
            }
        }
    }
    
    /**
     * Output PDF to browser
     */
    public function output(string $filename = 'report.pdf'): void {
        $this->pdf->Output('D', $filename);
    }
}
