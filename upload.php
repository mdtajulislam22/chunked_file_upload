<?php
// Set the file directory
$targetDir = 'uploads/chunks/';

// Get the file name, chunk index, and total chunks
$fileName = $_POST['dzuuid'];
$chunkIndex = $_POST['dzchunkindex'];
$totalChunks = $_POST['dztotalchunkcount'];
$fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

// Ensure the directory exists
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Save the chunk
$chunkPath = $targetDir . $fileName . '/' . $chunkIndex;
if (!is_dir($targetDir . $fileName)) {
    mkdir($targetDir . $fileName, 0777, true);
}
move_uploaded_file($_FILES['file']['tmp_name'], $chunkPath);

// Check if all chunks have been uploaded
$uploadedChunks = scandir($targetDir . $fileName);
$uploadedChunks = array_diff($uploadedChunks, array('.', '..')); // Remove . and ..

if (count($uploadedChunks) == $totalChunks) {
    $finalFilePath = 'uploads/files/' . $fileName . '.' . $fileExtension;

    // Open the final file for writing in binary mode
    $finalFile = fopen($finalFilePath, 'ab');

    // Merge the chunks
    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkFilePath = $targetDir . $fileName . '/' . $i;
        $chunkFile = fopen($chunkFilePath, 'rb');
        stream_copy_to_stream($chunkFile, $finalFile);
        fclose($chunkFile);
    }

    fclose($finalFile);

    // Cleanup: delete the chunk directory
    array_map('unlink', glob($targetDir . $fileName . "/*"));
    rmdir($targetDir . $fileName);
}

echo json_encode(['status' => 'success']);
