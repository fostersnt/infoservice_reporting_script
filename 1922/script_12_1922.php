<?php
// Database credentials
$host = 'localhost';
$dbname = 'infoservice_callback_logs';
$username = 'txtgh_foster';
$password = 'password1234!';

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Date for the query
$queryDate = '2025-01-12';
$code = 1922;

// Queries
$totalQuery = "SELECT COUNT(msisdn) AS total FROM CDR WHERE DATE(transDate) = :queryDate AND code = :code";
$successQuery = "SELECT COUNT(msisdn) AS success FROM CDR WHERE DATE(transDate) = :queryDate AND code = :code AND status = 2";
$failedQuery = "SELECT COUNT(msisdn) AS failed FROM CDR WHERE DATE(transDate) = :queryDate AND code = :code AND status = 1";

try {
    // Prepare and execute queries
    $stmt = $pdo->prepare($totalQuery);
    $stmt->execute(['queryDate' => $queryDate, 'code' => $code]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->prepare($successQuery);
    $stmt->execute(['queryDate' => $queryDate, 'code' => $code]);
    $success = $stmt->fetch(PDO::FETCH_ASSOC)['success'];

    $stmt = $pdo->prepare($failedQuery);
    $stmt->execute(['queryDate' => $queryDate, 'code' => $code]);
    $failed = $stmt->fetch(PDO::FETCH_ASSOC)['failed'];

    // Prepare CSV data
    $csvData = [
        ['total', 'success', 'failed'],
        [$total, $success, $failed]
    ];

    // File path
    $filePath = __DIR__ . '/output_12_1922.csv';

    // Create and write to the CSV file
    $file = fopen($filePath, 'w');
    foreach ($csvData as $row) {
        fputcsv($file, $row);
    }
    fclose($file);

    echo "CSV file created successfully: $filePath\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
