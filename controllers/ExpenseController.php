<?php
// TransitOps Expense Controller

require_once dirname(__DIR__) . '/controllers/Controller.php';
require_once dirname(__DIR__) . '/models/Expense.php';
require_once dirname(__DIR__) . '/models/Vehicle.php';
require_once dirname(__DIR__) . '/helpers/AuditHelper.php';
require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class ExpenseController extends Controller {
    private $expenseModel;
    private $vehicleModel;

    public function __construct() {
        $this->expenseModel = new Expense();
        $this->vehicleModel = new Vehicle();
    }

    /**
     * Display expense tracking list
     */
    public function index() {
        $expenses = $this->expenseModel->getExpensesWithDetails();
        $vehicles = $this->vehicleModel->where("status != 'Retired'", [], "name ASC");
        
        // Calculate Total Operational Cost dynamically
        $totalCost = $this->expenseModel->getTotalOperationalCost();

        $this->view('expenses/index', [
            'title' => 'Operational Expenses | TransitOps',
            'headerTitle' => 'Expense Register',
            'expenses' => $expenses,
            'vehicles' => $vehicles,
            'totalCost' => $totalCost
        ]);
    }

    /**
     * Get single expense details (AJAX)
     */
    public function getExpense() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'error' => 'Expense ID is required.']);
        }

        $expense = $this->expenseModel->find($id);
        if ($expense) {
            $this->json(['success' => true, 'data' => $expense]);
        } else {
            $this->json(['success' => false, 'error' => 'Expense not found.']);
        }
    }

    /**
     * Create operational expense (AJAX POST)
     */
    public function createExpense() {
        $data = $this->getPostData();

        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $type = trim($data['type'] ?? '');
        $amount = floatval($data['amount'] ?? 0);
        $date = $data['date'] ?? '';
        $remarks = trim($data['remarks'] ?? '');

        // 1. Validation
        if (!$vehicleId || empty($type) || $amount <= 0 || empty($date)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Selected vehicle does not exist.']);
        }

        // 2. Insert Record
        $insertData = [
            'vehicle_id' => $vehicleId,
            'type' => $type,
            'amount' => $amount,
            'date' => $date,
            'remarks' => $remarks
        ];

        $insertedId = $this->expenseModel->create($insertData);
        if ($insertedId) {
            AuditHelper::log('Create Expense', 'Expense', $insertedId, "Logged expense {$type} of \${$amount} for vehicle {$vehicle['registration_number']}.");
            $this->json(['success' => true, 'message' => 'Expense logged successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to save expense in the database.']);
        }
    }

    /**
     * Update operational expense (AJAX POST)
     */
    public function updateExpense() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Expense ID is required.']);
        }

        $expense = $this->expenseModel->find($id);
        if (!$expense) {
            $this->json(['success' => false, 'error' => 'Expense record not found.']);
        }

        $vehicleId = intval($data['vehicle_id'] ?? 0);
        $type = trim($data['type'] ?? '');
        $amount = floatval($data['amount'] ?? 0);
        $date = $data['date'] ?? '';
        $remarks = trim($data['remarks'] ?? '');

        if (!$vehicleId || empty($type) || $amount <= 0 || empty($date)) {
            $this->json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        if (!$vehicle) {
            $this->json(['success' => false, 'error' => 'Vehicle does not exist.']);
        }

        $updateData = [
            'vehicle_id' => $vehicleId,
            'type' => $type,
            'amount' => $amount,
            'date' => $date,
            'remarks' => $remarks
        ];

        if ($this->expenseModel->update($id, $updateData)) {
            AuditHelper::log('Update Expense', 'Expense', $id, "Updated expense {$type} details for vehicle {$vehicle['registration_number']}.");
            $this->json(['success' => true, 'message' => 'Expense details updated successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to update expense record.']);
        }
    }

    /**
     * Delete operational expense (AJAX POST)
     */
    public function deleteExpense() {
        $data = $this->getPostData();
        $id = $data['id'] ?? null;

        if (!$id) {
            $this->json(['success' => false, 'error' => 'Record ID is required.']);
        }

        $expense = $this->expenseModel->find($id);
        if (!$expense) {
            $this->json(['success' => false, 'error' => 'Expense record not found.']);
        }

        if ($this->expenseModel->delete($id)) {
            $vehicle = $this->vehicleModel->find($expense['vehicle_id']);
            AuditHelper::log('Delete Expense', 'Expense', $id, "Deleted expense {$expense['type']} of \${$expense['amount']} for vehicle " . ($vehicle ? $vehicle['registration_number'] : 'ID ' . $expense['vehicle_id']));
            $this->json(['success' => true, 'message' => 'Expense record deleted successfully.']);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to delete expense record.']);
        }
    }
}
