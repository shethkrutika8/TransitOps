<?php
// TransitOps Vehicle Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/Vehicle.php';
require_once dirname(__DIR__) . '/helpers/FileHelper.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class VehicleController extends Controller {
    private $vehicleModel;

    public function __construct() {
        $this->vehicleModel = new Vehicle();
    }

    /**
     * Display vehicles listing
     */
    public function index() {
        $vehicles = $this->vehicleModel->all("created_at DESC");
        
        $this->view('vehicles/index', [
            'title' => 'Manage Vehicles | TransitOps',
            'headerTitle' => 'Vehicle Management',
            'vehicles' => $vehicles
        ]);
    }

    /**
     * Fetch single vehicle details (AJAX)
     */
    public function getVehicle() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'Vehicle ID is required.']);
        }

        $vehicle = $this->vehicleModel->find($id);
        if ($vehicle) {
            $this->json(['success' => true, 'data' => $vehicle]);
        } else {
            $this->json(['success' => false, 'error' => 'Vehicle not found.']);
        }
    }

    /**
     * Create a new vehicle (AJAX POST)
     */
    public function createVehicle() {
        $data = $this->getPostData();
        
        $regNo = strtoupper(trim($data['registration_number'] ?? ''));
        $name = trim($data['name'] ?? '');
        $model = trim($data['model'] ?? '');
        $type = trim($data['type'] ?? '');
        $manufacturer = trim($data['manufacturer'] ?? '');
        $year = intval($data['year'] ?? 0);
        $capacity = floatval($data['capacity'] ?? 0);
        $odometer = intval($data['odometer'] ?? 0);
        $purchaseCost = floatval($data['purchase_cost'] ?? 0);
        $purchaseDate = $data['purchase_date'] ?? '';
        $insuranceExpiry = $data['insurance_expiry'] ?? '';
        $rcExpiry = $data['rc_expiry'] ?? '';
        $status = $data['status'] ?? 'Available';

        // 1. Validation
        if (empty($regNo) || empty($name) || empty($model) || empty($type) || empty($capacity) || empty($purchaseDate)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        if (!$this->vehicleModel->isRegNumberUnique($regNo)) {
            $this->json(['success' => false, 'error' => "Vehicle registration number '{$regNo}' already exists."]);
        }

        if ($capacity <= 0) {
            $this->json(['success' => false, 'error' => 'Load capacity must be a positive number.']);
        }

        if ($year < 1900 || $year > date('Y') + 1) {
            $this->json(['success' => false, 'error' => 'Please input a valid vehicle manufacturing year.']);
        }

        // 2. Document/Image Handling
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = FileHelper::uploadImage($_FILES['image'], 'vehicles');
            if ($uploaded) {
                $imagePath = $uploaded;
            } else {
                $this->json(['success' => false, 'error' => 'Failed to upload vehicle image. Invalid format or size too large.']);
            }
        }

        // 3. Insert Record
        $insertData = [
            'registration_number' => $regNo,
            'name' => $name,
            'model' => $model,
            'type' => $type,
            'manufacturer' => $manufacturer,
            'year' => $year,
            'capacity' => $capacity,
            'odometer' => $odometer,
            'purchase_cost' => $purchaseCost,
            'purchase_date' => $purchaseDate,
            'insurance_expiry' => $insuranceExpiry,
            'rc_expiry' => $rcExpiry,
            'status' => $status,
            'image_path' => $imagePath
        ];

        $insertedId = $this->vehicleModel->create($insertData);
        if ($insertedId) {
            AuditHelper::log('Create Vehicle', 'Vehicle', $insertedId, "Vehicle {$name} ({$regNo}) registered in system.");
            $this->json(['success' => true, 'message' => "Vehicle {$regNo} registered successfully."]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to register vehicle in the database.']);
        }
    }

    /**
     * Update an existing vehicle (AJAX POST)
     */
    public function updateVehicle() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Vehicle ID is required for editing.']);
        }

        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Vehicle record not found.']);
        }

        $regNo = strtoupper(trim($data['registration_number'] ?? ''));
        $name = trim($data['name'] ?? '');
        $model = trim($data['model'] ?? '');
        $type = trim($data['type'] ?? '');
        $manufacturer = trim($data['manufacturer'] ?? '');
        $year = intval($data['year'] ?? 0);
        $capacity = floatval($data['capacity'] ?? 0);
        $odometer = intval($data['odometer'] ?? 0);
        $purchaseCost = floatval($data['purchase_cost'] ?? 0);
        $purchaseDate = $data['purchase_date'] ?? '';
        $insuranceExpiry = $data['insurance_expiry'] ?? '';
        $rcExpiry = $data['rc_expiry'] ?? '';
        $status = $data['status'] ?? 'Available';

        // 1. Validation
        if (empty($regNo) || empty($name) || empty($model) || empty($type) || empty($capacity) || empty($purchaseDate)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        if (!$this->vehicleModel->isRegNumberUnique($regNo, $id)) {
            $this->json(['success' => false, 'error' => "Vehicle registration number '{$regNo}' already exists."]);
        }

        if ($capacity <= 0) {
            $this->json(['success' => false, 'error' => 'Load capacity must be a positive number.']);
        }

        // 2. Document/Image Handling
        $imagePath = $vehicle['image_path'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploaded = FileHelper::uploadImage($_FILES['image'], 'vehicles');
            if ($uploaded) {
                // Delete old image if exists
                if ($imagePath && file_exists(APP_PATH . '/' . $imagePath)) {
                    @unlink(APP_PATH . '/' . $imagePath);
                }
                $imagePath = $uploaded;
            } else {
                $this->json(['success' => false, 'error' => 'Failed to upload vehicle image. Invalid format or size too large.']);
            }
        }

        // 3. Update Record
        $updateData = [
            'registration_number' => $regNo,
            'name' => $name,
            'model' => $model,
            'type' => $type,
            'manufacturer' => $manufacturer,
            'year' => $year,
            'capacity' => $capacity,
            'odometer' => $odometer,
            'purchase_cost' => $purchaseCost,
            'purchase_date' => $purchaseDate,
            'insurance_expiry' => $insuranceExpiry,
            'rc_expiry' => $rcExpiry,
            'status' => $status,
            'image_path' => $imagePath
        ];

        if ($this->vehicleModel->update($id, $updateData)) {
            AuditHelper::log('Update Vehicle', 'Vehicle', $id, "Vehicle details of {$regNo} updated.");
            $this->json(['success' => true, 'message' => "Vehicle {$regNo} details updated successfully."]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to update vehicle record.']);
        }
    }

    /**
     * Delete vehicle (AJAX POST)
     */
    public function deleteVehicle() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Vehicle ID is required.']);
        }

        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Vehicle not found.']);
        }

        // Check if vehicle is currently on a trip
        if ($vehicle['status'] === 'On Trip') {
            $this->json(['success' => false, 'error' => 'Cannot delete vehicle. The vehicle is currently assigned to an active trip.']);
        }

        // Remove files
        if ($vehicle['image_path'] && file_exists(APP_PATH . '/' . $vehicle['image_path'])) {
            @unlink(APP_PATH . '/' . $vehicle['image_path']);
        }

        if ($this->vehicleModel->delete($id)) {
            AuditHelper::log('Delete Vehicle', 'Vehicle', $id, "Vehicle {$vehicle['name']} ({$vehicle['registration_number']}) deleted.");
            $this->json(['success' => true, 'message' => 'Vehicle deleted successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to delete vehicle from the system.']);
        }
    }
}
