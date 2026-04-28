<?php
// api/export.php — GET CSV/PDF report blob

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../services/ReportGenerator.php';
require_once __DIR__ . '/../services/PdfExporter.php';
require_once __DIR__ . '/../services/AuditLogger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'GET only']);
    exit;
}

$type = $_GET['format'] ?? 'csv'; // csv or pdf
$reportType = $_GET['report'] ?? 'load'; // load or schedule

try {
    $generator = new ReportGenerator();
    
    if ($reportType === 'load') {
        $data = $generator->generateLoadReport([
            'department' => $_GET['department'] ?? ''
        ]);
    } else {
        $data = $generator->generateScheduleReport();
    }
    
    AuditLogger::log('export', 'report', null, "Exported $reportType report as $type");
    
    if ($type === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="load_report_' . date('Y_m_d') . '.csv"');
        
        $out = fopen('php://output', 'w');
        
        if ($reportType === 'load') {
            fputcsv($out, ['Teacher Load Assignment Report - Generated: ' . $data['generated_at']]);
            fputcsv($out, []);
            fputcsv($out, ['Summary']);
            foreach ($data['summary'] as $k => $v) fputcsv($out, [$k, $v]);
            fputcsv($out, []);
            fputcsv($out, ['Employee ID', 'Name', 'Department', 'Employment', 'Max Units', 'Current Load', 'Status', 'Assignments']);
            
            foreach ($data['teachers'] as $t) {
                $assignStr = '';
                foreach ($t['assignments'] as $a) {
                    $assignStr .= $a['code'] . ' (' . $a['day_of_week'] . ' ' . $a['start_time'] . '-' . $a['end_time'] . '), ';
                }
                fputcsv($out, [
                    $t['employee_id'],
                    $t['last_name'] . ', ' . $t['first_name'],
                    $t['department'],
                    $t['employment_type'],
                    $t['max_units'],
                    $t['current_load'],
                    $t['status'],
                    rtrim($assignStr, ', ')
                ]);
            }
        } else {
            fputcsv($out, ['Subject Code', 'Subject Name', 'Day', 'Start', 'End', 'Room', 'Section', 'Assigned Teacher']);
            foreach ($data as $row) {
                fputcsv($out, [
                    $row['code'], $row['name'], $row['day_of_week'],
                    $row['start_time'], $row['end_time'],
                    $row['room'] ?? '', $row['section'] ?? '',
                    ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')
                ]);
            }
        }
        fclose($out);
        exit;
        
    } else { // PDF
        $pdf = new PdfExporter();
        if ($reportType === 'load') {
            $pdf->generateLoadReport($data);
        }
        $pdf->output('load_report_' . date('Y_m_d') . '.pdf');
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
