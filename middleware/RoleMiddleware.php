<?php
// TransitOps Role-Based Access Control Middleware

require_once dirname(__DIR__) . '/helpers/AuthHelper.php';

class RoleMiddleware {
    
    // Map controller actions to required permission keys
    private static $routePermissions = [
        // Vehicles Module
        'VehicleController@index'          => ['vehicles_manage', 'vehicles_view'],
        'VehicleController@getVehicle'     => ['vehicles_manage', 'vehicles_view'],
        'VehicleController@createVehicle'  => ['vehicles_manage'],
        'VehicleController@updateVehicle'  => ['vehicles_manage'],
        'VehicleController@deleteVehicle'  => ['vehicles_manage'],

        // Drivers Module
        'DriverController@index'          => ['drivers_manage', 'drivers_view'],
        'DriverController@getDriver'     => ['drivers_manage', 'drivers_view'],
        'DriverController@createDriver'  => ['drivers_manage'],
        'DriverController@updateDriver'  => ['drivers_manage'],
        'DriverController@deleteDriver'  => ['drivers_manage'],

        // Trips Module
        'TripController@index'        => ['trips_manage'],
        'TripController@getTrip'      => ['trips_manage'],
        'TripController@getTripLogs'  => ['trips_manage'],
        'TripController@createTrip'   => ['trips_manage'],
        'TripController@updateTrip'   => ['trips_manage'],
        'TripController@deleteTrip'   => ['trips_manage'],
        'TripController@dispatchTrip' => ['trips_manage'],
        'TripController@completeTrip' => ['trips_manage'],
        'TripController@cancelTrip'   => ['trips_manage'],

        // Maintenance Module
        'MaintenanceController@index'             => ['maintenance_manage'],
        'MaintenanceController@getMaintenance'     => ['maintenance_manage'],
        'MaintenanceController@createMaintenance'  => ['maintenance_manage'],
        'MaintenanceController@updateMaintenance'  => ['maintenance_manage'],
        'MaintenanceController@deleteMaintenance'  => ['maintenance_manage'],
        'MaintenanceController@completeMaintenance'=> ['maintenance_manage'],

        // Fuel Module
        'FuelController@index'          => ['fuel_manage'],
        'FuelController@getFuelLog'     => ['fuel_manage'],
        'FuelController@createFuelLog'  => ['fuel_manage'],
        'FuelController@updateFuelLog'  => ['fuel_manage'],
        'FuelController@deleteFuelLog'  => ['fuel_manage'],

        // Expenses Module
        'ExpenseController@index'          => ['expenses_manage'],
        'ExpenseController@getExpense'     => ['expenses_manage'],
        'ExpenseController@createExpense'  => ['expenses_manage'],
        'ExpenseController@updateExpense'  => ['expenses_manage'],
        'ExpenseController@deleteExpense'  => ['expenses_manage'],

        // Reports Module
        'ReportController@index'     => ['reports_view', 'reports_manage'],
        'ReportController@generate'  => ['reports_view', 'reports_manage'],

        // Settings Module
        'SettingController@index'          => ['all'],
        'SettingController@updateSettings' => ['all'],
    ];

    /**
     * Enforce access control permissions
     * @param string $controller
     * @param string $action
     */
    public static function handle($controller, $action) {
        $key = $controller . '@' . $action;
        
        // If the action has no specific permission requirement, allow it
        if (!isset(self::$routePermissions[$key])) {
            return;
        }

        $requiredPermissions = self::$routePermissions[$key];
        $authorized = false;

        // User must have at least one of the required permissions
        foreach ($requiredPermissions as $permission) {
            if (AuthHelper::hasPermission($permission)) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                      || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
            
            http_response_code(403);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'error' => 'Access Denied. You do not have permission to perform this action.'
                ]);
                exit;
            } else {
                // Render the 403 page
                $errorController = new class extends Controller {
                    public function show403() {
                        $this->view('errors/403');
                    }
                };
                $errorController->show403();
                exit;
            }
        }
    }
}
