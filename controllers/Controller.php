<?php
// TransitOps Base Controller

abstract class Controller {

    protected function isValidDateInput($value, $includeTime = false) {
        if (!is_string($value) || $value === '') {
            return false;
        }

        $formats = $includeTime
            ? ['Y-m-d\TH:i', 'Y-m-d H:i', 'Y-m-d H:i:s']
            : ['Y-m-d'];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if (
                $date !== false
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
                && $date->format($format) === $value
            ) {
                return true;
            }
        }

        return false;
    }

    protected function isValidNumberInput($value, $minimum, $maximum = null, $integer = false) {
        if (!is_scalar($value) || !is_numeric($value)) {
            return false;
        }

        $number = (float) $value;
        if (!is_finite($number) || $number < $minimum || ($maximum !== null && $number > $maximum)) {
            return false;
        }

        return !$integer || filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function isAllowedValue($value, array $allowedValues) {
        return is_string($value) && in_array($value, $allowedValues, true);
    }
    
    /**
     * Render a view layout
     * @param string $viewPath (e.g. 'vehicles/index')
     * @param array $data Data to extract in the view
     */
    protected function view($viewPath, $data = []) {
        // Extract variables to local scope
        extract($data);
        
        // Define full view file path
        $viewFile = dirname(__DIR__) . "/views/{$viewPath}.php";
        
        if (!file_exists($viewFile)) {
            die("View file not found: views/{$viewPath}.php");
        }

        // Determine if it is a layouts page or login page
        // The login page and error pages don't need the header/sidebar layout wrapped
        $noLayoutViews = ['auth/login', 'errors/403', 'errors/404'];
        
        if (in_array($viewPath, $noLayoutViews)) {
            include $viewFile;
        } else {
            // Load within the main shell layout
            $contentView = $viewFile;
            include dirname(__DIR__) . "/views/layouts/main.php";
        }
    }

    /**
     * Send JSON response
     * @param array|object $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to local route
     * @param string $path
     */
    protected function redirect($path) {
        header("Location: " . BASE_URL . $path);
        exit;
    }

    /**
     * Sanitize POST request inputs
     * @return array
     */
    protected function getPostData() {
        // Check if content-type is json
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }

        $data = [];
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $data[$key] = filter_var_array($value, FILTER_DEFAULT);
            } else {
                $data[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }

    /**
     * Sanitize GET request inputs
     * @return array
     */
    protected function getGetData() {
        $data = [];
        foreach ($_GET as $key => $value) {
            if (is_array($value)) {
                $data[$key] = filter_var_array($value, FILTER_DEFAULT);
            } else {
                $data[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }
}
