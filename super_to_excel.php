<?php
// super_to_excel.php

echo "Starting export..."; // Add this to see if the script starts

// Ensure this file is only accessible via POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['faculty_id'])) {
    echo "POST request received..."; // Check if POST request is received

    // Fetch the faculty ID from the request
    $faculty_id = $_POST['faculty_id'];

    include 'db_connection.php';  // Include your DB connection file
    
    // Get faculty details
    $result = $conn->query("SELECT * FROM faculty_list WHERE id = '$faculty_id'");
    
    if ($result->num_rows > 0) {
        $faculty = $result->fetch_assoc();
        echo "Faculty found: " . $faculty['firstname']; // Output faculty name for debugging
    } else {
        echo "No faculty found."; // Debug if no faculty is returned
    }

    // Fetch evaluation data for the faculty
    $result = $conn->query("SELECT evaluator, average_rating, weight, equivalent_point FROM evaluations WHERE faculty_id = '$faculty_id'");
    
    if ($result->num_rows > 0) {
        echo "Evaluation data found..."; // Debug if evaluation data exists
    } else {
        echo "No evaluation data found."; // Debug if no evaluation data
    }

    // Set headers for downloading the file as CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=faculty_evaluation_' . $faculty['id'] . '.csv');

    // Open the PHP output stream to write to the browser
    $output = fopen('php://output', 'w');

    // Add column headers to the CSV file
    fputcsv($output, ['Evaluator', 'Average Rating', 'Weight', 'Equivalent Point']);

    // Fetch and output the evaluation rows to the CSV file
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['evaluator'], $row['average_rating'], $row['weight'], $row['equivalent_point']]);
    }

    // Close the output stream
    fclose($output);
    exit;
} else {
    echo "No POST data received."; // Check if POST data is missing
}
?>
