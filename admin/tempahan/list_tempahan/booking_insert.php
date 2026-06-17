<?php
include '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $slot_id = $_POST['slot_id'];
    $package_id = $_POST['package_id'];
    $organization = $_POST['organization_name'];
    $contact = $_POST['contact_person'];
    $phone = $_POST['phone_number'];
    $email = $_POST['email'];
    $participants = $_POST['total_participants'];

    $stmt = $conn->prepare("
        INSERT INTO bookings 
        (slot_id, package_id, organization_name, contact_person, phone_number, email, total_participants, booking_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->bind_param("iissssi",
        $slot_id,
        $package_id,
        $organization,
        $contact,
        $phone,
        $email,
        $participants
    );

    if ($stmt->execute()) {
        header("Location: tempahan.php?success=added");
    } else {
        echo "Error: " . $stmt->error;
    }
}