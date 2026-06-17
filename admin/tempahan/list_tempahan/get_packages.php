<?php
// get_packages.php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_login'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit;
}

include '../../../db.php';

$query = "
    SELECT 
        package_id,
        package_name,
        requires_activity_selection
    FROM packages
    WHERE status = 'active'
    ORDER BY package_id ASC
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $packages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $packages[] = [
            'package_id' => $row['package_id'],
            'package_name' => $row['package_name'],
            'requires_activity_selection' => (int) $row['requires_activity_selection']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'packages' => $packages
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No packages found.'
    ]);
}

exit;
?>