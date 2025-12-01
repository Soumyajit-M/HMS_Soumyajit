<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';
    require_once __DIR__ . '/auth_helper.php';

$auth = new Auth();

// Quiet session check
    $auth = api_require_login();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get settings
        $action = $_GET['action'] ?? 'get';
        
        if ($action === 'export') {
            // Export settings
            $settings = getSettings();
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="settings.json"');
            echo json_encode($settings);
        } else {
            // Get settings
            $settings = getSettings();
            echo json_encode(['success' => true, 'settings' => $settings]);
        }
        break;
        
    case 'POST':
        // Save settings
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $action = $input['action'] ?? 'save';

        if ($action === 'import') {
            // Import settings
            $result = importSettings($input['settings']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Settings imported successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error importing settings']);
            }
        } else {
            // Save settings
            $category = $input['category'] ?? '';
            $settings = $input['settings'] ?? [];

            $result = saveSettings($category, $settings);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error saving settings']);
            }
        }
        break;
        
    case 'DELETE':
        // Reset settings
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            break;
        }
        $category = $input['category'] ?? '';

        $result = resetSettings($category);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Settings reset successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error resetting settings']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

// Get settings from database
function getSettings() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM system_settings");
    $stmt->execute();
    $settings = $stmt->fetchAll();
    
    $result = [];
    foreach ($settings as $setting) {
        $key = $setting['setting_key'];
        $value = json_decode($setting['setting_value'], true) ?: $setting['setting_value'];
        
        // Group settings by category
        if (strpos($key, 'general_') === 0) {
            $result['general'][substr($key, 8)] = $value;
        } elseif (strpos($key, 'hospital_') === 0) {
            $result['hospital'][substr($key, 9)] = $value;
        } elseif (strpos($key, 'email_') === 0) {
            $result['email'][substr($key, 6)] = $value;
        } elseif (strpos($key, 'ai_') === 0) {
            $result['ai'][substr($key, 3)] = $value;
        } elseif (strpos($key, 'security_') === 0) {
            $result['security'][substr($key, 9)] = $value;
        } else {
            $result['general'][$key] = $value;
        }
    }
    
    return $result;
}

// Save settings to database
function saveSettings($category, $settings) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $conn->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $settingKey = $category . '_' . $key;
            $settingValue = is_array($value) ? json_encode($value) : $value;
            
            $stmt = $conn->prepare("
                INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                 ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$settingKey, $settingValue]);
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Import settings
function importSettings($settings) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $conn->beginTransaction();
        
        foreach ($settings as $category => $categorySettings) {
            foreach ($categorySettings as $key => $value) {
                $settingKey = $category . '_' . $key;
                $settingValue = is_array($value) ? json_encode($value) : $value;
                
                $stmt = $conn->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$settingKey, $settingValue]);
            }
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Reset settings to default
function resetSettings($category) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $stmt = $conn->prepare("DELETE FROM system_settings WHERE setting_key LIKE ?");
        $stmt->execute([$category . '_%']);
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
