<?php
// services/ImportParser.php — CSV/Excel reader using PhpSpreadsheet

require_once __DIR__ . '/../config/constants.php';

class ImportParser {
    
    /**
     * Parse uploaded file (CSV or Excel)
     */
    public function parse(string $filePath, string $type): array {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        
        if ($ext === 'csv') {
            return $this->parseCsv($filePath);
        }
        
        // For Excel, we'd use PhpSpreadsheet if available
        if (in_array($ext, ['xlsx', 'xls'])) {
            return $this->parseExcel($filePath);
        }
        
        throw new Exception("Unsupported file type: $ext");
    }
    
    private function parseCsv(string $path): array {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) throw new Exception("Cannot open file");
        
        $headers = fgetcsv($handle);
        if (!$headers) throw new Exception("Empty CSV file");
        
        // Normalize headers
        $headers = array_map('strtolower', array_map('trim', $headers));
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) continue;
            $row = array_combine($headers, $data);
            if ($row) $rows[] = $row;
        }
        fclose($handle);
        
        return ['headers' => $headers, 'rows' => $rows];
    }
    
    private function parseExcel(string $path): array {
        // Check if PhpSpreadsheet is available via composer vendor path
        $vendorPath = __DIR__ . '/../assets/vendors/composer/autoload.php';
        if (!file_exists($vendorPath)) {
            throw new Exception("Composer autoloader not found. Please run: composer install");
        }
        
        require_once $vendorPath;
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        
        if (empty($data)) throw new Exception("Empty Excel file");
        
        $headers = array_map('strtolower', array_map('trim', array_shift($data)));
        $rows = [];
        
        foreach ($data as $row) {
            if (count($row) !== count($headers)) continue;
            $combined = array_combine($headers, $row);
            if ($combined) $rows[] = $combined;
        }
        
        return ['headers' => $headers, 'rows' => $rows];
    }
    
    /**
     * Validate teacher import rows
     */
    public function validateTeachers(array $rows): array {
        $errors = [];
        $required = ['employee_id', 'first_name', 'last_name'];
        
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            foreach ($required as $field) {
                if (empty($row[$field])) {
                    $errors[] = "Row $line: Missing $field";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate subject import rows
     */
    public function validateSubjects(array $rows): array {
        $errors = [];
        $required = ['code', 'name', 'units'];
        
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            foreach ($required as $field) {
                if (empty($row[$field])) {
                    $errors[] = "Row $line: Missing $field";
                }
            }
            if (!empty($row['units']) && !is_numeric($row['units'])) {
                $errors[] = "Row $line: Units must be numeric";
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate schedule import rows
     */
    public function validateSchedules(array $rows): array {
        $errors = [];
        $required = ['subject_code', 'day_of_week', 'start_time', 'end_time'];
        $validDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            foreach ($required as $field) {
                if (empty($row[$field])) {
                    $errors[] = "Row $line: Missing $field";
                }
            }
            if (!empty($row['day_of_week']) && !in_array($row['day_of_week'], $validDays)) {
                $errors[] = "Row $line: Invalid day_of_week. Use: Mon, Tue, Wed, Thu, Fri, Sat, Sun";
            }
        }
        
        return $errors;
    }
}
