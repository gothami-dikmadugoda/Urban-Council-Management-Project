<?php
header('Content-Type: application/json');
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $target = $uploadDir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $target)) {
        echo json_encode(['success' => true, 'file_url' => '/urban2/uploads/' . basename($file['name'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?> 