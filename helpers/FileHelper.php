<?php
// TransitOps Secure File Upload Helper

class FileHelper {

    /**
     * Upload an image file (e.g. driver photo or vehicle image)
     * @param array $file The $_FILES element
     * @param string $subfolder Subdirectory inside uploads (e.g., 'vehicles', 'drivers')
     * @return string|false Path to save in DB, or false on failure
     */
    public static function uploadImage($file, $subfolder = 'general') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Validate size (limit to 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return false;
        }

        // Get extension
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($ext)) {
            $ext = ($mimeType === 'image/png') ? 'png' : (($mimeType === 'image/webp') ? 'webp' : 'jpg');
        }

        // Generate unique filename
        $fileName = uniqid($subfolder . '_', true) . '.' . $ext;
        $targetPath = UPLOAD_PATH . '/' . $subfolder . '/' . $fileName;

        // Move file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/' . $subfolder . '/' . $fileName;
        }

        return false;
    }

    /**
     * Upload a document file (e.g., driver license, vehicle insurance PDF)
     * @param array $file The $_FILES element
     * @param string $subfolder Subdirectory (e.g. 'documents')
     * @return string|false Path to save in DB, or false on failure
     */
    public static function uploadDocument($file, $subfolder = 'documents') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Validate size (limit to 10MB)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }

        // Validate file type (PDF, DOC, DOCX, JPEG, PNG)
        $allowedTypes = [
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        ];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return false;
        }

        // Get extension
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        // Generate unique filename
        $fileName = uniqid('doc_', true) . '.' . $ext;
        $targetPath = UPLOAD_PATH . '/' . $subfolder . '/' . $fileName;

        // Move file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/' . $subfolder . '/' . $fileName;
        }

        return false;
    }
}
