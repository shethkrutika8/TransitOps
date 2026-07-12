<?php
// TransitOps Driver Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/Driver.php';
require_once dirname(__DIR__) . '/helpers/FileHelper.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class DriverController extends Controller {
    private $driverModel;

    public function __construct() {
        $this->driverModel = new Driver();
    }

    /**
     * Display drivers listing
     */
    public function index() {
        $drivers = $this->driverModel->all("created_at DESC");
        
        $this->view('drivers/index', [
            'title' => 'Manage Drivers | TransitOps',
            'headerTitle' => 'Driver Management',
            'drivers' => $drivers
        ]);
    }

    /**
     * Fetch single driver details (AJAX)
     */
    public function getDriver() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'Driver ID is required.']);
        }

        $driver = $this->driverModel->find($id);
        if ($driver) {
            $this->json(['success' => true, 'data' => $driver]);
        } else {
            $this->json(['success' => false, 'error' => 'Driver not found.']);
        }
    }

    /**
     * Create a new driver (AJAX POST)
     */
    public function createDriver() {
        $data = $this->getPostData();
        
        $name = trim($data['name'] ?? '');
        $employeeId = strtoupper(trim($data['employee_id'] ?? ''));
        $licenseNumber = trim($data['license_number'] ?? '');
        $licenseCategory = trim($data['license_category'] ?? '');
        $licenseExpiry = $data['license_expiry'] ?? '';
        $mobile = trim($data['mobile'] ?? '');
        $email = trim($data['email'] ?? '');
        $address = trim($data['address'] ?? '');
        $safetyScore = floatval($data['safety_score'] ?? 100.00);
        $experience = intval($data['experience'] ?? 0);
        $emergencyContact = trim($data['emergency_contact'] ?? '');
        $status = $data['status'] ?? 'Available';

        // 1. Validation
        if (empty($name) || empty($employeeId) || empty($licenseNumber) || empty($licenseCategory) || empty($licenseExpiry) || empty($mobile) || empty($email)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        if (!$this->driverModel->isEmployeeIdUnique($employeeId)) {
            $this->json(['success' => false, 'error' => "Employee ID '{$employeeId}' already exists."]);
        }

        if ($safetyScore < 0 || $safetyScore > 100) {
            $this->json(['success' => false, 'error' => 'Safety score must be between 0 and 100.']);
        }

        if ($experience < 0) {
            $this->json(['success' => false, 'error' => 'Driving experience cannot be negative.']);
        }

        // 2. File Upload Handling
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploaded = FileHelper::uploadImage($_FILES['photo'], 'drivers');
            if ($uploaded) {
                $photoPath = $uploaded;
            } else {
                $this->json(['success' => false, 'error' => 'Failed to upload photo. Invalid image format or size too large.']);
            }
        }

        $licensePath = null;
        if (isset($_FILES['license_doc']) && $_FILES['license_doc']['error'] === UPLOAD_ERR_OK) {
            $uploaded = FileHelper::uploadDocument($_FILES['license_doc'], 'documents');
            if ($uploaded) {
                $licensePath = $uploaded;
            } else {
                $this->json(['success' => false, 'error' => 'Failed to upload license document. Invalid file format or size too large.']);
            }
        }

        // 3. Insert Record
        $insertData = [
            'name' => $name,
            'employee_id' => $employeeId,
            'license_number' => $licenseNumber,
            'license_category' => $licenseCategory,
            'license_expiry' => $licenseExpiry,
            'mobile' => $mobile,
            'email' => $email,
            'address' => $address,
            'safety_score' => $safetyScore,
            'experience' => $experience,
            'emergency_contact' => $emergencyContact,
            'status' => $status,
            'photo_path' => $photoPath,
            'license_path' => $licensePath
        ];

        $insertedId = $this->driverModel->create($insertData);
        if ($insertedId) {
            AuditHelper::log('Create Driver', 'Driver', $insertedId, "Driver {$name} ({$employeeId}) added.");
            $this->json(['success' => true, 'message' => "Driver {$name} added successfully."]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to register driver in database.']);
        }
    }

    /**
     * Update an existing driver (AJAX POST)
     */
    public function updateDriver() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Driver ID is required for editing.']);
        }

        $driver = $this->driverModel->find($id);
        if (!$driver) {
            $this->json(['success' => false, 'error' => 'Driver record not found.']);
        }

        $name = trim($data['name'] ?? '');
        $employeeId = strtoupper(trim($data['employee_id'] ?? ''));
        $licenseNumber = trim($data['license_number'] ?? '');
        $licenseCategory = trim($data['license_category'] ?? '');
        $licenseExpiry = $data['license_expiry'] ?? '';
        $mobile = trim($data['mobile'] ?? '');
        $email = trim($data['email'] ?? '');
        $address = trim($data['address'] ?? '');
        $safetyScore = floatval($data['safety_score'] ?? 100.00);
        $experience = intval($data['experience'] ?? 0);
        $emergencyContact = trim($data['emergency_contact'] ?? '');
        $status = $data['status'] ?? 'Available';

        // 1. Validation
        if (empty($name) || empty($employeeId) || empty($licenseNumber) || empty($licenseCategory) || empty($licenseExpiry) || empty($mobile) || empty($email)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        if (!$this->driverModel->isEmployeeIdUnique($employeeId, $id)) {
            $this->json(['success' => false, 'error' => "Employee ID '{$employeeId}' already exists."]);
        }

        if ($safetyScore < 0 || $safetyScore > 100) {
            $this->json(['success' => false, 'error' => 'Safety score must be between 0 and 100.']);
        }

        // 2. File Upload Handling
        $photoPath = $driver['photo_path'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploaded = FileHelper::uploadImage($_FILES['photo'], 'drivers');
            if ($uploaded) {
                // Delete old file
                if ($photoPath && file_exists(APP_PATH . '/' . $photoPath)) {
                    @unlink(APP_PATH . '/' . $photoPath);
                }
                $photoPath = $uploaded;
            } else {
                $this->json(['success' => false, 'error' => 'Failed to upload photo. Invalid image format or size too large.']);
            }
        }

        $licensePath = $driver['license_path'];
        if (isset($_FILES['license_doc']) && $_FILES['license_doc']['error'] === UPLOAD_ERR_OK) {
            $uploaded = FileHelper::uploadDocument($_FILES['license_doc'], 'documents');
            if ($uploaded) {
                // Delete old file
                if ($licensePath && file_exists(APP_PATH . '/' . $licensePath)) {
                    @unlink(APP_PATH . '/' . $licensePath);
                }
                $licensePath = $uploaded;
            } else {
                $this->json(['success' => false, 'error' => 'Failed to upload license document. Invalid file format or size too large.']);
            }
        }

        // 3. Update Record
        $updateData = [
            'name' => $name,
            'employee_id' => $employeeId,
            'license_number' => $licenseNumber,
            'license_category' => $licenseCategory,
            'license_expiry' => $licenseExpiry,
            'mobile' => $mobile,
            'email' => $email,
            'address' => $address,
            'safety_score' => $safetyScore,
            'experience' => $experience,
            'emergency_contact' => $emergencyContact,
            'status' => $status,
            'photo_path' => $photoPath,
            'license_path' => $licensePath
        ];

        if ($this->driverModel->update($id, $updateData)) {
            AuditHelper::log('Update Driver', 'Driver', $id, "Driver {$name} ({$employeeId}) details updated.");
            $this->json(['success' => true, 'message' => "Driver {$name} details updated successfully."]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to update driver details.']);
        }
    }

    /**
     * Delete driver (AJAX POST)
     */
    public function deleteDriver() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Driver ID is required.']);
        }

        $driver = $this->driverModel->find($id);
        if (!$driver) {
            $this->json(['success' => false, 'error' => 'Driver not found.']);
        }

        // Check if driver is on a trip
        if ($driver['status'] === 'On Trip') {
            $this->json(['success' => false, 'error' => 'Cannot delete driver. The driver is currently assigned to an active trip.']);
        }

        // Remove files
        if ($driver['photo_path'] && file_exists(APP_PATH . '/' . $driver['photo_path'])) {
            @unlink(APP_PATH . '/' . $driver['photo_path']);
        }
        if ($driver['license_path'] && file_exists(APP_PATH . '/' . $driver['license_path'])) {
            @unlink(APP_PATH . '/' . $driver['license_path']);
        }

        if ($this->driverModel->delete($id)) {
            AuditHelper::log('Delete Driver', 'Driver', $id, "Driver {$driver['name']} ({$driver['employee_id']}) deleted.");
            $this->json(['success' => true, 'message' => 'Driver deleted successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to delete driver from system.']);
        }
    }
}
