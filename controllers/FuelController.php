<?php
// TransitOps Fuel Management Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/FuelLog.php';
require_once dirname(__DIR__) . '/models/Vehicle.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class FuelController extends Controller {
    private $fuelLogModel;
    private $vehicleModel;

    public function __construct() {
        $this->fuelLogModel = new FuelLog();
        $this->vehicleModel = new Vehicle();
    }

    /**
     * List fuel logs
     */
    public function index() {
        $logs = $this->fuelLogModel->getLogsWithDetails();
        $vehicles = $this->vehicleModel->where("status != 'Retired'", [], "name ASC");

        $this->view('fuel/index', [
            'title' => 'Fuel Management | TransitOps',
            'headerTitle' => 'Fuel Consumption Log',
            'logs' => $logs,
            'vehicles' => $vehicles
        ]);
    }

    /**
     * Get single fuel log details (AJAX)
     */
    public function getFuelLog() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'Log ID is required.']);
        }

        $log = $this->fuelLogModel->find($id);
        if ($log) {
            $this->json(['success' => true, 'data' => $log]);
        } else {
            $this->json(['success' => false, 'error' => 'Fuel log not found.']);
        }
    }

    /**
     * Create fuel log (AJAX POST)
     */
    public function createFuelLog() {
        $data = $this->getPostData();

        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $date = $data['date'] ?? '';
        $quantity = floatval($data['quantity'] ?? 0);
        $cost = floatval($data['cost'] ?? 0);
        $station = trim($data['station'] ?? '');
        $odometer = intval($data['odometer'] ?? 0);

        // 1. Validation
        if (!$vehicleId || empty($date) || $quantity <= 0 || $cost <= 0 || empty($station) || $odometer <= 0) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Selected vehicle does not exist.']);
        }

        // 2. Mileage Calculation
        $prevOdo = $this->fuelLogModel->getPreviousOdometer($vehicleId, $date);
        $mileage = 0.00;

        if ($prevOdo !== null) {
            $distance = $odometer - $prevOdo;
            if ($distance > 0) {
                $mileage = round($distance / $quantity, 2);
            } elseif ($distance < 0) {
                $this->json(['success' => false, 'error' => "Odometer reading ({$odometer} km) is less than the previous fuel log odometer ({$prevOdo} km). Please verify."]);
            }
        }

        // 3. Save Record
        $insertData = [
            'vehicle_id' => $vehicleId,
            'date' => $date,
            'quantity' => $quantity,
            'cost' => $cost,
            'station' => $station,
            'odometer' => $odometer,
            'mileage' => $mileage
        ];

        $db = Database::connect();
        try {
            $db->beginTransaction();

            $insertedId = $this->fuelLogModel->create($insertData);
            if (!$insertedId) {
                throw new Exception('Database save operation failed.');
            }

            // 4. Update Vehicle Odometer if newer
            if ($odometer > intval($vehicle['odometer'])) {
                $this->vehicleModel->update($vehicleId, ['odometer' => $odometer]);
            }

            $db->commit();

            AuditHelper::log('Create Fuel Log', 'FuelLog', $insertedId, "Logged fuel of {$quantity} L (\${$cost}) for vehicle {$vehicle['registration_number']}. Calculated Mileage: {$mileage} km/L.");
            $this->json(['success' => true, 'message' => 'Fuel entry logged successfully.']);

        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Failed to log fuel entry: ' . $e->getMessage()]);
        }
    }

    /**
     * Update fuel log (AJAX POST)
     */
    public function updateFuelLog() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Log ID is required.']);
        }

        $log = $this->fuelLogModel->find($id);
        if (!$log) {
            $this->json(['success' => false, 'error' => 'Fuel log record not found.']);
        }

        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $date = $data['date'] ?? '';
        $quantity = floatval($data['quantity'] ?? 0);
        $cost = floatval($data['cost'] ?? 0);
        $station = trim($data['station'] ?? '');
        $odometer = intval($data['odometer'] ?? 0);

        if (!$vehicleId || empty($date) || $quantity <= 0 || $cost <= 0 || empty($station) || $odometer <= 0) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Vehicle does not exist.']);
        }

        // Recalculate mileage excluding current log ID
        $prevOdo = $this->fuelLogModel->getPreviousOdometer($vehicleId, $date, $id);
        $mileage = 0.00;

        if ($prevOdo !== null) {
            $distance = $odometer - $prevOdo;
            if ($distance > 0) {
                $mileage = round($distance / $quantity, 2);
            } elseif ($distance < 0) {
                $this->json(['success' => false, 'error' => "Odometer reading ({$odometer} km) is less than the previous fuel log odometer ({$prevOdo} km). Please verify."]);
            }
        }

        $updateData = [
            'vehicle_id' => $vehicleId,
            'date' => $date,
            'quantity' => $quantity,
            'cost' => $cost,
            'station' => $station,
            'odometer' => $odometer,
            'mileage' => $mileage
        ];

        $db = Database::connect();
        try {
            $db->beginTransaction();

            $this->fuelLogModel->update($id, $updateData);

            if ($odometer > intval($vehicle['odometer'])) {
                $this->vehicleModel->update($vehicleId, ['odometer' => $odometer]);
            }

            $db->commit();

            AuditHelper::log('Update Fuel Log', 'FuelLog', $id, "Updated fuel details for vehicle {$vehicle['registration_number']}. Mileage updated to {$mileage} km/L.");
            $this->json(['success' => true, 'message' => 'Fuel entry updated successfully.']);

        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Failed to update record: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete fuel log (AJAX POST)
     */
    public function deleteFuelLog() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Record ID is required.']);
        }

        $log = $this->fuelLogModel->find($id);
        if (!$log) {
            $this->json(['success' => false, 'error' => 'Log not found.']);
        }

        if ($this->fuelLogModel->delete($id)) {
            $vehicle = $this->vehicleModel->find($log['vehicle_id']);
            AuditHelper::log('Delete Fuel Log', 'FuelLog', $id, "Deleted fuel log record of Date {$log['date']} for vehicle " . ($vehicle ? $vehicle['registration_number'] : 'ID ' . $log['vehicle_id']));
            $this->json(['success' => true, 'message' => 'Fuel log record deleted successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to delete fuel log.']);
        }
    }
}
