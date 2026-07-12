<?php
// TransitOps Maintenance Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/Maintenance.php';
require_once dirname(__DIR__) . '/models/Vehicle.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class MaintenanceController extends Controller {
    private $maintenanceModel;
    private $vehicleModel;

    public function __construct() {
        $this->maintenanceModel = new Maintenance();
        $this->vehicleModel = new Vehicle();
    }

    /**
     * Display maintenance list
     */
    public function index() {
        $history = $this->maintenanceModel->getHistoryWithDetails();
        
        // Fetch all vehicles (excluding Retired) for the options dropdown
        $vehicles = $this->vehicleModel->where("status != 'Retired'", [], "name ASC");

        $this->view('maintenance/index', [
            'title' => 'Maintenance Schedule | TransitOps',
            'headerTitle' => 'Maintenance Log',
            'history' => $history,
            'vehicles' => $vehicles
        ]);
    }

    /**
     * Fetch single maintenance details (AJAX)
     */
    public function getMaintenance() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'Maintenance ID is required.']);
        }

        $record = $this->maintenanceModel->find($id);
        if ($record) {
            $this->json(['success' => true, 'data' => $record]);
        } else {
            $this->json(['success' => false, 'error' => 'Record not found.']);
        }
    }

    /**
     * Create maintenance log (AJAX POST)
     */
    public function createMaintenance() {
        $data = $this->getPostData();
        
        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $type = trim($data['type'] ?? '');
        $description = trim($data['description'] ?? '');
        $date = $data['date'] ?? '';
        $cost = floatval($data['cost'] ?? 0);
        $vendor = trim($data['vendor'] ?? '');
        $nextServiceKm = intval($data['next_service_km'] ?? 0);
        $nextServiceDate = $data['next_service_date'] ?? '';
        $status = $data['status'] ?? 'Pending'; // Pending, In Progress, Completed

        // 1. Validation
        if (!$vehicleId || empty($type) || empty($description) || empty($date) || empty($vendor) || !$nextServiceKm || empty($nextServiceDate)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Selected vehicle does not exist.']);
        }

        // Business Rule validation: cannot start maintenance if vehicle is on trip
        if ($status === 'In Progress' && $vehicle['status'] === 'On Trip') {
            $this->json(['success' => false, 'error' => 'Vehicle is currently On Trip. Complete or cancel the trip before placing it in the shop.']);
        }

        // 2. Database Insert
        $insertData = [
            'vehicle_id' => $vehicleId,
            'type' => $type,
            'description' => $description,
            'date' => $date,
            'cost' => $cost,
            'vendor' => $vendor,
            'next_service_km' => $nextServiceKm,
            'next_service_date' => $nextServiceDate,
            'status' => $status
        ];

        $db = Database::connect();
        try {
            $db->beginTransaction();

            $insertedId = $this->maintenanceModel->create($insertData);
            if (!$insertedId) {
                throw new Exception('Database insertion failed.');
            }

            // 3. Automation transitions
            // "When maintenance starts (In Progress), Vehicle status automatically becomes In Shop"
            if ($status === 'In Progress') {
                $this->vehicleModel->update($vehicleId, ['status' => 'In Shop']);
            }
            // "When maintenance completes, Vehicle status automatically changes to Available"
            elseif ($status === 'Completed') {
                $this->vehicleModel->update($vehicleId, ['status' => 'Available']);
            }

            $db->commit();

            AuditHelper::log('Create Maintenance', 'Maintenance', $insertedId, "Maintenance logged for vehicle {$vehicle['registration_number']} (Status: {$status}). Cost: \${$cost}");
            $this->json(['success' => true, 'message' => 'Maintenance record created successfully.']);

        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Failed to create record: ' . $e->getMessage()]);
        }
    }

    /**
     * Update maintenance record
     */
    public function updateMaintenance() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Record ID is required.']);
        }

        $record = $this->maintenanceModel->find($id);
        if (!$record) {
            $this->json(['success' => false, 'error' => 'Maintenance log not found.']);
        }

        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $type = trim($data['type'] ?? '');
        $description = trim($data['description'] ?? '');
        $date = $data['date'] ?? '';
        $cost = floatval($data['cost'] ?? 0);
        $vendor = trim($data['vendor'] ?? '');
        $nextServiceKm = intval($data['next_service_km'] ?? 0);
        $nextServiceDate = $data['next_service_date'] ?? '';
        $status = $data['status'] ?? 'Pending';

        if (!$vehicleId || empty($type) || empty($description) || empty($date) || empty($vendor) || !$nextServiceKm || empty($nextServiceDate)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Vehicle does not exist.']);
        }

        // Validate vehicle availability if changing to In Progress
        if ($status === 'In Progress' && $record['status'] !== 'In Progress') {
            if ($vehicle['status'] === 'On Trip') {
                $this->json(['success' => false, 'error' => 'Cannot start maintenance. Vehicle is currently On Trip.']);
            }
        }

        $updateData = [
            'vehicle_id' => $vehicleId,
            'type' => $type,
            'description' => $description,
            'date' => $date,
            'cost' => $cost,
            'vendor' => $vendor,
            'next_service_km' => $nextServiceKm,
            'next_service_date' => $nextServiceDate,
            'status' => $status
        ];

        $db = Database::connect();
        try {
            $db->beginTransaction();

            $this->maintenanceModel->update($id, $updateData);

            // Transitions based on status change
            if ($status === 'In Progress') {
                $this->vehicleModel->update($vehicleId, ['status' => 'In Shop']);
            } elseif ($status === 'Completed') {
                $this->vehicleModel->update($vehicleId, ['status' => 'Available']);
            } elseif ($status === 'Pending' && $record['status'] === 'In Progress') {
                // Restores vehicle if it was in shop and now scheduled back to pending
                $this->vehicleModel->update($vehicleId, ['status' => 'Available']);
            }

            $db->commit();

            AuditHelper::log('Update Maintenance', 'Maintenance', $id, "Maintenance details updated for vehicle {$vehicle['registration_number']} (Status: {$status}).");
            $this->json(['success' => true, 'message' => 'Maintenance record updated successfully.']);

        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete maintenance ticket directly
     */
    public function completeMaintenance() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Maintenance ID is required.']);
        }

        $record = $this->maintenanceModel->find($id);
        if (!$record) {
            $this->json(['success' => false, 'error' => 'Maintenance log not found.']);
        }

        $db = Database::connect();
        try {
            $db->beginTransaction();

            // 1. Update status
            $this->maintenanceModel->update($id, ['status' => 'Completed']);

            // 2. Change Vehicle status to Available
            $this->vehicleModel->update($record['vehicle_id'], ['status' => 'Available']);

            $db->commit();

            $vehicle = $this->vehicleModel->find($record['vehicle_id']);
            AuditHelper::log('Complete Maintenance', 'Maintenance', $id, "Maintenance ticket for vehicle {$vehicle['registration_number']} marked Completed.");
            $this->json(['success' => true, 'message' => 'Maintenance ticket marked Completed. Vehicle returned to Available.']);

        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Action failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete maintenance record
     */
    public function deleteMaintenance() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Record ID is required.']);
        }

        $record = $this->maintenanceModel->find($id);
        if (!$record) {
            $this->json(['success' => false, 'error' => 'Record not found.']);
        }

        $db = Database::connect();
        try {
            $db->beginTransaction();

            $this->maintenanceModel->delete($id);

            // Restore vehicle to Available if it was in the shop for this deleted record
            if ($record['status'] === 'In Progress') {
                $this->vehicleModel->update($record['vehicle_id'], ['status' => 'Available']);
            }

            $db->commit();

            $vehicle = $this->vehicleModel->find($record['vehicle_id']);
            AuditHelper::log('Delete Maintenance', 'Maintenance', $id, "Deleted maintenance ticket record for vehicle " . ($vehicle ? $vehicle['registration_number'] : 'ID ' . $record['vehicle_id']));
            $this->json(['success' => true, 'message' => 'Maintenance record deleted successfully.']);

        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Delete failed: ' . $e->getMessage()]);
        }
    }
}
