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
        if (
            !isset($file['error'], $file['size'], $file['tmp_name'])
            || $file['error'] !== UPLOAD_ERR_OK
            || !in_array($subfolder, ['vehicles', 'drivers'], true)
        ) {
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
        if ($fileInfo === false) {
            return false;
        }
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            return false;
        }

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $ext = $extensions[$mimeType];

        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = UPLOAD_PATH . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR . $fileName;

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
        if (
            !isset($file['error'], $file['size'], $file['tmp_name'])
            || $file['error'] !== UPLOAD_ERR_OK
            || $subfolder !== 'documents'
        ) {
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
        if ($fileInfo === false) {
            return false;
        }
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            return false;
        }

        $extensions = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        $ext = $extensions[$mimeType];

        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = UPLOAD_PATH . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR . $fileName;

        // Move file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/' . $subfolder . '/' . $fileName;
        }

        return false;
    }
}
