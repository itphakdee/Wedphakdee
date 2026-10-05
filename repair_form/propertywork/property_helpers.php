<?php
if (!function_exists('property_e')) {
    function property_e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('property_require_login')) {
    function property_require_login()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../../login.php');
            exit;
        }
    }
}

if (!function_exists('property_csrf_token')) {
    function property_csrf_token()
    {
        if (empty($_SESSION['property_csrf_token'])) {
            if (function_exists('random_bytes')) {
                $_SESSION['property_csrf_token'] = bin2hex(random_bytes(24));
            } else {
                $_SESSION['property_csrf_token'] = sha1(uniqid(mt_rand(), true));
            }
        }
        return $_SESSION['property_csrf_token'];
    }
}

if (!function_exists('property_verify_csrf')) {
    function property_verify_csrf($token)
    {
        $saved = isset($_SESSION['property_csrf_token']) ? $_SESSION['property_csrf_token'] : '';
        if ($saved === '' || !is_string($token)) {
            return false;
        }
        return function_exists('hash_equals') ? hash_equals($saved, $token) : $saved === $token;
    }
}

if (!function_exists('property_flash')) {
    function property_flash($type, $message)
    {
        $_SESSION['property_flash'] = array('type' => $type, 'message' => $message);
    }
}

if (!function_exists('property_take_flash')) {
    function property_take_flash()
    {
        if (empty($_SESSION['property_flash'])) {
            return null;
        }
        $flash = $_SESSION['property_flash'];
        unset($_SESSION['property_flash']);
        return $flash;
    }
}

if (!function_exists('property_status_meta')) {
    function property_status_meta($status)
    {
        $map = array(
            'ใช้งาน' => array('class' => 'status-active', 'label' => 'ใช้งาน', 'icon' => '●'),
            'ชำรุด' => array('class' => 'status-damaged', 'label' => 'ชำรุด', 'icon' => '●'),
            'ส่งซ่อม' => array('class' => 'status-repair', 'label' => 'ส่งซ่อม', 'icon' => '●'),
            'จำหน่าย' => array('class' => 'status-retired', 'label' => 'จำหน่าย', 'icon' => '●')
        );
        return isset($map[$status]) ? $map[$status] : array('class' => 'status-unknown', 'label' => $status !== '' ? $status : 'ไม่ระบุ', 'icon' => '●');
    }
}

if (!function_exists('property_currency')) {
    function property_currency($amount)
    {
        return number_format((float)$amount, 2);
    }
}

if (!function_exists('property_nullable_date')) {
    function property_nullable_date($value)
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }
}

if (!function_exists('property_upload_image')) {
    function property_upload_image($file, $oldImage)
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldImage;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('อัปโหลดรูปภาพไม่สำเร็จ');
        }

        if (!empty($file['size']) && $file['size'] > 5 * 1024 * 1024) {
            throw new Exception('รูปภาพต้องมีขนาดไม่เกิน 5 MB');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'webp');
        if (!in_array($extension, $allowed, true)) {
            throw new Exception('รองรับเฉพาะไฟล์ JPG, PNG และ WEBP');
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
            if ($finfo) {
                finfo_close($finfo);
            }
            $allowedMime = array('image/jpeg', 'image/png', 'image/webp');
            if ($mime && !in_array($mime, $allowedMime, true)) {
                throw new Exception('ชนิดไฟล์รูปภาพไม่ถูกต้อง');
            }
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/property/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception('ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้');
        }

        $newName = 'asset_' . date('Ymd_His') . '_' . mt_rand(10000, 99999) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
            throw new Exception('ไม่สามารถบันทึกรูปภาพได้');
        }

        if ($oldImage) {
            $oldPath = $uploadDir . basename($oldImage);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return $newName;
    }
}

if (!function_exists('property_delete_image')) {
    function property_delete_image($image)
    {
        if (!$image) {
            return;
        }
        $path = dirname(__DIR__, 2) . '/uploads/property/' . basename($image);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

if (!function_exists('property_query_value')) {
    function property_query_value($conn, $sql, $field, $default)
    {
        $result = $conn->query($sql);
        if (!$result) {
            return $default;
        }
        $row = $result->fetch_assoc();
        return isset($row[$field]) ? $row[$field] : $default;
    }
}

if (!function_exists('property_table_exists')) {
    function property_table_exists($conn)
    {
        $result = $conn->query("SHOW TABLES LIKE 'properties'");
        return $result && $result->num_rows > 0;
    }
}
