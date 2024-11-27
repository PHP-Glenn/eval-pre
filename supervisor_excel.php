<?php
// Include necessary files for database connection
include 'db_connect.php';
require 'vendor/autoload.php'; // Load PhpSpreadsheet via Composer

function ordinal_suffix($num) {
    $num = $num % 100;
    if ($num < 11 || $num > 13) {
        switch ($num % 10) {
            case 1: return $num . 'st';
            case 2: return $num . 'nd';
            case 3: return $num . 'rd';
        }
    }
    return $num . 'th';
}

session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Get the faculty ID from the query string
$faculty_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;

if ($faculty_id == 0) {
    die('Invalid faculty ID.');
}

// Fetch faculty data
$faculty_query = $conn->query("SELECT CONCAT(firstname, ' ', lastname) AS name FROM faculty_list WHERE id = $faculty_id");
$faculty_data = $faculty_query->fetch_assoc();
$faculty_name = $faculty_data['name'] ?? 'Unknown Faculty';

// Fetch academic year and semester
$academic_year = $_SESSION['academic']['year'] ?? 'N/A';
$academic_semester = $_SESSION['academic']['semester'] ?? 'N/A';

// Fetch evaluation data
$supervisor_query = $conn->query("
    SELECT 
        sl.school_id,
        c.criteria AS criteria_name,
        SUM(sea.rate) AS total_score,
        (SUM(sea.rate) / COUNT(sea.rate)) AS average_score
    FROM supervisor_evaluation_answers sea
    INNER JOIN supervisor_evaluation_list sel ON sel.id = sea.evaluation_id
    INNER JOIN supervisor_list sl ON sl.id = sel.supervisor_id
    INNER JOIN question_list q ON q.id = sea.question_id
    INNER JOIN criteria_list c ON c.id = q.criteria_id
    WHERE sel.faculty_id = $faculty_id
    GROUP BY sl.school_id, c.criteria
");

$evaluation_data = [];
$criteria_list = ['Commitment', 'Knowledge of Subject Matter', 'Teaching for Independent Learning', 'Management of Learning'];

while ($row = $supervisor_query->fetch_assoc()) {
    $school_id = $row['school_id'];
    $criteria_name = $row['criteria_name'];
    $total_score = $row['total_score'];

    if (!isset($evaluation_data[$school_id])) {
        $evaluation_data[$school_id] = ['criteria' => [], 'total' => 0, 'average' => 0];
    }

    $evaluation_data[$school_id]['criteria'][$criteria_name] = $total_score;
    $evaluation_data[$school_id]['total'] += $total_score;
}

// Calculate averages for each school
foreach ($evaluation_data as $school_id => &$data) {
    $data['average'] = count($criteria_list) > 0 ? round($data['total'] / count($criteria_list), 2) : 0;
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add headers
$sheet->setCellValue('A1', 'Supervisor Evaluation Report');
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A2', 'Zamboanga State Polytechnic State University');
$sheet->mergeCells('A2:G2');
$sheet->setCellValue('A3', 'R.T. Lim Boulevard, Zamboanga City');
$sheet->mergeCells('A3:G3');
$sheet->setCellValue('A4', 'Evaluation Period: July 1, 2019 - July 30, 2023');
$sheet->mergeCells('A4:G4');

$sheet->setCellValue('A6', 'Name of Faculty: ' . $faculty_name);
$sheet->setCellValue('A7', 'Academic Year: ' . $academic_year . ' ' . ordinal_suffix($academic_semester) . ' Semester');

// Add column headers
$headers = ['School ID', 'Commitment', 'Knowledge of Subject Matter', 'Teaching for Independent Learning', 'Management of Learning', 'Total', 'Average'];
$headerStyle = [
    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F81BD']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$dataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];

$sheet->fromArray($headers, NULL, 'A9');
$sheet->getStyle('A9:G9')->applyFromArray($headerStyle);

// Populate the data
$rowNumber = 10;
foreach ($evaluation_data as $school_id => $data) {
    $sheet->setCellValue("A{$rowNumber}", $school_id);

    $total = 0;
    foreach ($criteria_list as $index => $criteria) {
        $columnLetter = chr(66 + $index); // Convert index to column letter (B, C, D, ...)
        $score = $data['criteria'][$criteria] ?? 0;
        $sheet->setCellValue("{$columnLetter}{$rowNumber}", $score);
        $total += $score;
    }

    $sheet->setCellValue("F{$rowNumber}", $total);
    $sheet->setCellValue("G{$rowNumber}", $data['average']);
    $rowNumber++;
}

// Add overall totals
$overall_total = array_sum(array_column($evaluation_data, 'total'));
$overall_average = count($evaluation_data) > 0 ? round(array_sum(array_column($evaluation_data, 'average')) / count($evaluation_data), 2) : 0;

$sheet->setCellValue("A{$rowNumber}", 'Overall');
$sheet->setCellValue("F{$rowNumber}", $overall_total);
$sheet->setCellValue("G{$rowNumber}", $overall_average);

// Apply styling to the table data
$sheet->getStyle("A10:G{$rowNumber}")->applyFromArray($dataStyle);

// Download the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Supervisor_Evaluation_Report_' . str_replace(' ', '_', $faculty_name) . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
