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

// Date and code for the query
$queryDate = '2025-01-11';
$code = 1917;

// Queries
$totalQuery = "SELECT planId, COUNT(msisdn) AS total 
               FROM CDR 
               WHERE DATE(transDate) = :queryDate AND code = :code 
               GROUP BY planId 
               ORDER BY total ASC";

$successQuery = "SELECT planId, COUNT(msisdn) AS success 
                 FROM CDR 
                 WHERE DATE(transDate) = :queryDate AND code = :code AND status = 2 
                 GROUP BY planId 
                 ORDER BY success ASC";

$failedQuery = "SELECT planId, COUNT(msisdn) AS failed 
                FROM CDR 
                WHERE DATE(transDate) = :queryDate AND code = :code AND status = 1 
                GROUP BY planId 
                ORDER BY failed ASC";

try {
    // Execute the total query
    $stmt = $pdo->prepare($totalQuery);
    $stmt->execute(['queryDate' => $queryDate, 'code' => $code]);
    $totalResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Execute the success query
    $stmt = $pdo->prepare($successQuery);
    $stmt->execute(['queryDate' => $queryDate, 'code' => $code]);
    $successResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Execute the failed query
    $stmt = $pdo->prepare($failedQuery);
    $stmt->execute(['queryDate' => $queryDate, 'code' => $code]);
    $failedResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare CSV data
    $csvData = [['planId', 'total', 'success', 'failed']];

    foreach ($totalResults as $row) {
        $planId = $row['planId'];
        $total = $row['total'];

        // Find success count for the current planId
        $success = 0;
        foreach ($successResults as $successRow) {
            if ($successRow['planId'] == $planId) {
                $success = $successRow['success'];
                break;
            }
        }

        // Find failed count for the current planId
        $failed = 0;
        foreach ($failedResults as $failedRow) {
            if ($failedRow['planId'] == $planId) {
                $failed = $failedRow['failed'];
                break;
            }
        }

        // Add row to CSV data
        $csvData[] = [$planId, $total, $success, $failed];
    }

    // File path
    $filePath = __DIR__ . '/keywords_11_1917.csv';

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
