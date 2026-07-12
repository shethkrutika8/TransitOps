<?php
// TransitOps Trip Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/Trip.php';
require_once dirname(__DIR__) . '/models/Vehicle.php';
require_once dirname(__DIR__) . '/models/Driver.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class TripController extends Controller {
    private $tripModel;
    private $vehicleModel;
    private $driverModel;

    public function __construct() {
        $this->tripModel = new Trip();
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new Driver();
    }

    /**
     * Display Trips Management Page
     */
    public function index() {
        $trips = $this->tripModel->getTripsWithDetails();
        
        // Fetch list of available/active vehicles and drivers for the assignment dropdowns
        $vehicles = $this->vehicleModel->where("status = 'Available'", [], "name ASC");
        $drivers = $this->driverModel->where("status = 'Available'", [], "name ASC");

        $this->view('trips/index', [
            'title' => 'Manage Trips | TransitOps',
            'headerTitle' => 'Trip Management',
            'trips' => $trips,
            'vehicles' => $vehicles,
            'drivers' => $drivers
        ]);
    }

    /**
     * Fetch single trip details (AJAX)
     */
    public function getTrip() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'Trip ID is required.']);
        }

        $trip = $this->tripModel->getTripDetails($id);
        if ($trip) {
            $this->json(['success' => true, 'data' => $trip]);
        } else {
            $this->json(['success' => false, 'error' => 'Trip not found.']);
        }
    }

    /**
     * Fetch trip transition timeline logs (AJAX)
     */
    public function getTripLogs() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'Trip ID is required.']);
        }

        $logs = $this->tripModel->getTripLogs($id);
        $this->json(['success' => true, 'data' => $logs]);
    }

    /**
     * Create a new trip (AJAX POST)
     */
    public function createTrip() {
        $data = $this->getPostData();
        
        $source = trim($data['source'] ?? '');
        $destination = trim($data['destination'] ?? '');
        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $driverId = intval($data['driver_id'] ?? 0);
        $cargoType = trim($data['cargo_type'] ?? '');
        $cargoWeight = floatval($data['cargo_weight'] ?? 0);
        $plannedDistance = floatval($data['planned_distance'] ?? 0);
        $startDate = $data['start_date'] ?? '';
        $expectedEndDate = $data['expected_end_date'] ?? '';
        $revenue = floatval($data['revenue'] ?? 0);
        $status = $data['status'] ?? 'Draft'; // Draft or Dispatched

        // 1. Core Field Validation
        if (empty($source) || empty($destination) || !$vehicleId || !$driverId || empty($cargoType) || $cargoWeight <= 0 || $plannedDistance <= 0 || empty($startDate) || empty($expectedEndDate)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        // 2. Fetch Vehicle and Driver
        $vehicle = $this->vehicleModel->find($vehicleId);
        $driver = $this->driverModel->find($driverId);

        if (!$vehicle || !$driver) {
            $this->json(['success' => false, 'error' => 'Selected vehicle or driver does not exist.']);
        }

        // 3. Business Rule Validations
        if ($vehicle['status'] === 'In Shop') {
            $this->json(['success' => false, 'error' => 'Business Rule Violation: Vehicle currently under maintenance in shop cannot be assigned.']);
        }
        if ($vehicle['status'] === 'Retired') {
            $this->json(['success' => false, 'error' => 'Business Rule Violation: Retired vehicle cannot be assigned.']);
        }
        if ($vehicle['status'] === 'On Trip') {
            $this->json(['success' => false, 'error' => 'Business Rule Violation: Vehicle is already assigned to another active trip.']);
        }

        if (strtotime($driver['license_expiry']) < time()) {
            $this->json(['success' => false, 'error' => 'Business Rule Violation: Selected driver license is expired. Cannot assign.']);
        }
        if ($driver['status'] === 'Suspended') {
            $this->json(['success' => false, 'error' => 'Business Rule Violation: Driver is suspended and cannot be assigned to trips.']);
        }
        if ($driver['status'] === 'On Trip') {
            $this->json(['success' => false, 'error' => 'Business Rule Violation: Driver is already assigned to another active trip.']);
        }

        if ($cargoWeight > floatval($vehicle['capacity'])) {
            $this->json(['success' => false, 'error' => "Business Rule Violation: Cargo weight ({$cargoWeight} kg) exceeds vehicle's maximum load capacity ({$vehicle['capacity']} kg)."]);
        }

        // 4. Save Trip
        $tripNumber = $this->tripModel->generateTripNumber();
        $insertData = [
            'trip_number' => $tripNumber,
            'source' => $source,
            'destination' => $destination,
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'cargo_type' => $cargoType,
            'cargo_weight' => $cargoWeight,
            'planned_distance' => $plannedDistance,
            'start_date' => date('Y-m-d H:i:s', strtotime($startDate)),
            'expected_end_date' => date('Y-m-d H:i:s', strtotime($expectedEndDate)),
            'revenue' => $revenue,
            'status' => $status
        ];

        $insertedId = $this->tripModel->create($insertData);
        if ($insertedId) {
            $userId = AuthHelper::userId();
            // Create Trip Log
            $this->tripModel->logStatusTransition($insertedId, 'Draft', 'Trip created in system.', $userId);
            AuditHelper::log('Create Trip', 'Trip', $insertedId, "Trip {$tripNumber} created (Source: {$source}, Destination: {$destination}).");

            // If status is immediately Dispatched, run transitions
            if ($status === 'Dispatched') {
                $this->vehicleModel->update($vehicleId, ['status' => 'On Trip']);
                $this->driverModel->update($driverId, ['status' => 'On Trip']);
                $this->tripModel->logStatusTransition($insertedId, 'Dispatched', 'Trip dispatched immediately upon creation.', $userId);
                AuditHelper::log('Dispatch Trip', 'Trip', $insertedId, "Trip {$tripNumber} dispatched immediately.");
            }

            $this->json(['success' => true, 'message' => "Trip {$tripNumber} registered successfully."]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to create trip record in database.']);
        }
    }

    /**
     * Update trip (Draft Mode Only)
     */
    public function updateTrip() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Trip ID is required.']);
        }

        $trip = $this->tripModel->find($id);
        if (!$trip) {
            $this->json(['success' => false, 'error' => 'Trip not found.']);
        }

        if ($trip['status'] !== 'Draft') {
            $this->json(['success' => false, 'error' => 'Dispatched, Completed or Cancelled trips cannot be edited.']);
        }

        $source = trim($data['source'] ?? '');
        $destination = trim($data['destination'] ?? '');
        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $driverId = intval($data['driver_id'] ?? 0);
        $cargoType = trim($data['cargo_type'] ?? '');
        $cargoWeight = floatval($data['cargo_weight'] ?? 0);
        $plannedDistance = floatval($data['planned_distance'] ?? 0);
        $startDate = $data['start_date'] ?? '';
        $expectedEndDate = $data['expected_end_date'] ?? '';
        $revenue = floatval($data['revenue'] ?? 0);

        if (empty($source) || empty($destination) || !$vehicleId || !$driverId || empty($cargoType) || $cargoWeight <= 0 || $plannedDistance <= 0 || empty($startDate) || empty($expectedEndDate)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        $driver = $this->driverModel->find($driverId);

        if (!$vehicle || !$driver) {
            $this->json(['success' => false, 'error' => 'Selected vehicle or driver does not exist.']);
        }

        // Validate vehicle availability (allow if it's already assigned to this exact draft trip)
        if ($vehicleId !== intval($trip['vehicle_id'])) {
            if ($vehicle['status'] !== 'Available') {
                $this->json(['success' => false, 'error' => 'Selected vehicle is not available (Retired, In Shop, or On Trip).']);
            }
        }

        // Validate driver availability
        if ($driverId !== intval($trip['driver_id'])) {
            if ($driver['status'] !== 'Available') {
                $this->json(['success' => false, 'error' => 'Selected driver is not available (Suspended, Off Duty, or On Trip).']);
            }
            if (strtotime($driver['license_expiry']) < time()) {
                $this->json(['success' => false, 'error' => 'Selected driver license has expired.']);
            }
        }

        if ($cargoWeight > floatval($vehicle['capacity'])) {
            $this->json(['success' => false, 'error' => "Cargo weight exceeds selected vehicle load capacity ({$vehicle['capacity']} kg)."]);
        }

        $updateData = [
            'source' => $source,
            'destination' => $destination,
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'cargo_type' => $cargoType,
            'cargo_weight' => $cargoWeight,
            'planned_distance' => $plannedDistance,
            'start_date' => date('Y-m-d H:i:s', strtotime($startDate)),
            'expected_end_date' => date('Y-m-d H:i:s', strtotime($expectedEndDate)),
            'revenue' => $revenue
        ];

        if ($this->tripModel->update($id, $updateData)) {
            AuditHelper::log('Update Trip', 'Trip', $id, "Trip draft {$trip['trip_number']} details updated.");
            $this->json(['success' => true, 'message' => "Trip {$trip['trip_number']} updated successfully."]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to update trip details.']);
        }
    }

    /**
     * Dispatch Trip
     */
    public function dispatchTrip() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;
        $notes = trim($data['notes'] ?? 'Vehicle dispatched.');

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Trip ID is required.']);
        }

        $trip = $this->tripModel->find($id);
        if (!$trip) {
            $this->json(['success' => false, 'error' => 'Trip not found.']);
        }

        if ($trip['status'] !== 'Draft') {
            $this->json(['success' => false, 'error' => 'Only Draft trips can be Dispatched.']);
        }

        $vehicle = $this->vehicleModel->find($trip['vehicle_id']);
        $driver = $this->driverModel->find($trip['driver_id']);

        // Check if vehicle/driver status changed between creation of draft and dispatch
        if ($vehicle['status'] !== 'Available') {
            $this->json(['success' => false, 'error' => 'Cannot dispatch. Assigned vehicle status is not Available.']);
        }
        if ($driver['status'] !== 'Available') {
            $this->json(['success' => false, 'error' => 'Cannot dispatch. Assigned driver status is not Available.']);
        }
        if (strtotime($driver['license_expiry']) < time()) {
            $this->json(['success' => false, 'error' => 'Cannot dispatch. Driver license has expired.']);
        }

        // Apply state transitions
        $db = Database::connect();
        try {
            $db->beginTransaction();

            // 1. Update Trip status
            $this->tripModel->update($id, ['status' => 'Dispatched']);
            
            // 2. Update Vehicle status to On Trip
            $this->vehicleModel->update($trip['vehicle_id'], ['status' => 'On Trip']);

            // 3. Update Driver status to On Trip
            $this->driverModel->update($trip['driver_id'], ['status' => 'On Trip']);

            // 4. Log Transition
            $userId = AuthHelper::userId();
            $this->tripModel->logStatusTransition($id, 'Dispatched', $notes, $userId);

            $db->commit();
            
            AuditHelper::log('Dispatch Trip', 'Trip', $id, "Trip {$trip['trip_number']} dispatched. Vehicle: {$vehicle['registration_number']}, Driver: {$driver['name']}");
            $this->json(['success' => true, 'message' => "Trip {$trip['trip_number']} has been dispatched successfully."]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Dispatch failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete Trip
     */
    public function completeTrip() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;
        $notes = trim($data['notes'] ?? 'Trip completed successfully.');
        $currentOdometer = intval($data['odometer'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Trip ID is required.']);
        }

        $trip = $this->tripModel->find($id);
        if (!$trip) {
            $this->json(['success' => false, 'error' => 'Trip not found.']);
        }

        if ($trip['status'] !== 'Dispatched') {
            $this->json(['success' => false, 'error' => 'Only active Dispatched trips can be Completed.']);
        }

        // State transitions
        $db = Database::connect();
        try {
            $db->beginTransaction();

            // 1. Update Trip status
            $this->tripModel->update($id, ['status' => 'Completed']);

            // 2. Update Vehicle status to Available
            $vehicleUpdate = ['status' => 'Available'];
            if ($currentOdometer > 0) {
                // Check if new odometer value is greater than existing
                $vehicle = $this->vehicleModel->find($trip['vehicle_id']);
                if ($currentOdometer > intval($vehicle['odometer'])) {
                    $vehicleUpdate['odometer'] = $currentOdometer;
                }
            }
            $this->vehicleModel->update($trip['vehicle_id'], $vehicleUpdate);

            // 3. Update Driver status to Available
            $this->driverModel->update($trip['driver_id'], ['status' => 'Available']);

            // 4. Log Transition
            $userId = AuthHelper::userId();
            $this->tripModel->logStatusTransition($id, 'Completed', $notes, $userId);

            $db->commit();

            AuditHelper::log('Complete Trip', 'Trip', $id, "Trip {$trip['trip_number']} marked as completed.");
            $this->json(['success' => true, 'message' => "Trip {$trip['trip_number']} completed successfully."]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Failed to complete trip: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel Trip
     */
    public function cancelTrip() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;
        $notes = trim($data['notes'] ?? 'Trip cancelled by dispatcher.');

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Trip ID is required.']);
        }

        $trip = $this->tripModel->find($id);
        if (!$trip) {
            $this->json(['success' => false, 'error' => 'Trip not found.']);
        }

        if ($trip['status'] === 'Completed' || $trip['status'] === 'Cancelled') {
            $this->json(['success' => false, 'error' => 'Completed or already Cancelled trips cannot be cancelled.']);
        }

        // Cancel restores vehicle/driver availability if they were already On Trip
        $restoreAvailability = ($trip['status'] === 'Dispatched');

        $db = Database::connect();
        try {
            $db->beginTransaction();

            // 1. Update Trip status
            $this->tripModel->update($id, ['status' => 'Cancelled']);

            if ($restoreAvailability) {
                // 2. Restore Vehicle status to Available
                $this->vehicleModel->update($trip['vehicle_id'], ['status' => 'Available']);

                // 3. Restore Driver status to Available
                $this->driverModel->update($trip['driver_id'], ['status' => 'Available']);
            }

            // 4. Log Transition
            $userId = AuthHelper::userId();
            $this->tripModel->logStatusTransition($id, 'Cancelled', $notes, $userId);

            $db->commit();

            AuditHelper::log('Cancel Trip', 'Trip', $id, "Trip {$trip['trip_number']} cancelled.");
            $this->json(['success' => true, 'message' => "Trip {$trip['trip_number']} has been cancelled."]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'error' => 'Failed to cancel trip: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete trip (Draft mode only)
     */
    public function deleteTrip() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Trip ID is required.']);
        }

        $trip = $this->tripModel->find($id);
        if (!$trip) {
            $this->json(['success' => false, 'error' => 'Trip not found.']);
        }

        if ($trip['status'] !== 'Draft') {
            $this->json(['success' => false, 'error' => 'Only Draft trips can be deleted from the database registry.']);
        }

        if ($this->tripModel->delete($id)) {
            AuditHelper::log('Delete Trip', 'Trip', $id, "Deleted trip draft record {$trip['trip_number']}.");
            $this->json(['success' => true, 'message' => 'Trip draft record deleted successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to delete trip record.']);
        }
    }
}
