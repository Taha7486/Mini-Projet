<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once '../includes/session.php';
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in']);
        exit();
    }
    
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit();
    }
    
    // Check if files were uploaded
    if (!isset($_FILES['attachments']) || empty($_FILES['attachments']['name'][0])) {
        echo json_encode(['success' => false, 'message' => 'No files uploaded']);
        exit();
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../storage/email_attachments/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Allowed file types
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];
    
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    $uploadedFiles = [];
    $errors = [];
    
    // Process each uploaded file
    $fileCount = count($_FILES['attachments']['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $_FILES['attachments']['name'][$i];
        $fileTmpName = $_FILES['attachments']['tmp_name'][$i];
        $fileSize = $_FILES['attachments']['size'][$i];
        $fileType = $_FILES['attachments']['type'][$i];
        $fileError = $_FILES['attachments']['error'][$i];
        
        // Check for upload errors
        if ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading file '$fileName': " . getUploadErrorMessage($fileError);
            continue;
        }
        
        // Check file size
        if ($fileSize > $maxFileSize) {
            $errors[] = "File '$fileName' is too large. Maximum size is 10MB.";
            continue;
        }
        
        // Check file type
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "File type '$fileType' for '$fileName' is not allowed.";
            continue;
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $uniqueFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $filePath)) {
            $uploadedFiles[] = [
                'original_name' => $fileName,
                'stored_name' => $uniqueFileName,
                'path' => $filePath,
                'size' => $fileSize,
                'type' => $fileType
            ];
        } else {
            $errors[] = "Failed to save file '$fileName'";
        }
    }
    
    // Return response
    if (empty($uploadedFiles)) {
        echo json_encode([
            'success' => false, 
            'message' => 'No files were successfully uploaded',
            'errors' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'files' => $uploadedFiles,
            'errors' => $errors
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'File is too large';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
}
?>
