<?php
// TransitOps Base Controller

abstract class Controller {
    
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
