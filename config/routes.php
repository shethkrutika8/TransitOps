<?php
// TransitOps Route Map

return [
    // Authentication Routes
    'GET|/login' => 'AuthController@loginView',
    'POST|/login' => 'AuthController@loginProcess',
    'GET|/logout' => 'AuthController@logout',

    // Dashboard
    'GET|/' => 'DashboardController@index',
    'GET|/dashboard' => 'DashboardController@index',

    // Vehicles Module
    'GET|/vehicles' => 'VehicleController@index',
    'GET|/vehicles/get' => 'VehicleController@getVehicle',
    'POST|/vehicles/create' => 'VehicleController@createVehicle',
    'POST|/vehicles/update' => 'VehicleController@updateVehicle',
    'POST|/vehicles/delete' => 'VehicleController@deleteVehicle',

    // Drivers Module
    'GET|/drivers' => 'DriverController@index',
    'GET|/drivers/get' => 'DriverController@getDriver',
    'POST|/drivers/create' => 'DriverController@createDriver',
    'POST|/drivers/update' => 'DriverController@updateDriver',
    'POST|/drivers/delete' => 'DriverController@deleteDriver',

    // Trips Module
    'GET|/trips' => 'TripController@index',
    'GET|/trips/get' => 'TripController@getTrip',
    'GET|/trips/logs' => 'TripController@getTripLogs',
    'POST|/trips/create' => 'TripController@createTrip',
    'POST|/trips/update' => 'TripController@updateTrip',
    'POST|/trips/delete' => 'TripController@deleteTrip',
    'POST|/trips/dispatch' => 'TripController@dispatchTrip',
    'POST|/trips/complete' => 'TripController@completeTrip',
    'POST|/trips/cancel' => 'TripController@cancelTrip',

    // Maintenance Module
    'GET|/maintenance' => 'MaintenanceController@index',
    'GET|/maintenance/get' => 'MaintenanceController@getMaintenance',
    'POST|/maintenance/create' => 'MaintenanceController@createMaintenance',
    'POST|/maintenance/update' => 'MaintenanceController@updateMaintenance',
    'POST|/maintenance/delete' => 'MaintenanceController@deleteMaintenance',
    'POST|/maintenance/complete' => 'MaintenanceController@completeMaintenance',

    // Fuel Module
    'GET|/fuel' => 'FuelController@index',
    'GET|/fuel/get' => 'FuelController@getFuelLog',
    'POST|/fuel/create' => 'FuelController@createFuelLog',
    'POST|/fuel/update' => 'FuelController@updateFuelLog',
    'POST|/fuel/delete' => 'FuelController@deleteFuelLog',

    // Expense Module
    'GET|/expenses' => 'ExpenseController@index',
    'GET|/expenses/get' => 'ExpenseController@getExpense',
    'POST|/expenses/create' => 'ExpenseController@createExpense',
    'POST|/expenses/update' => 'ExpenseController@updateExpense',
    'POST|/expenses/delete' => 'ExpenseController@deleteExpense',

    // Reports Module
    'GET|/reports' => 'ReportController@index',
    'GET|/reports/generate' => 'ReportController@generate',

    // Notifications Module
    'GET|/notifications' => 'NotificationController@index',
    'GET|/notifications/latest' => 'NotificationController@latest',
    'POST|/notifications/read' => 'NotificationController@markAsRead',
    'POST|/notifications/update' => 'NotificationController@update',
    'POST|/notifications/delete' => 'NotificationController@delete',

    // Settings Module
    'GET|/settings' => 'SettingController@index',
    'POST|/settings/update' => 'SettingController@updateSettings',
];
