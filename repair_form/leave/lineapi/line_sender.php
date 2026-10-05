 <?php

    require_once __DIR__ . '/line_apps_script_config.php';

    /**
     * ตรวจว่า URL Apps Script ถูกตั้งค่าแล้วหรือยัง
     */
    function leave_line_is_configured()
    {
        return defined('LEAVE_LINE_APPS_SCRIPT_URL')
            && LEAVE_LINE_APPS_SCRIPT_URL !== ''
            && strpos(LEAVE_LINE_APPS_SCRIPT_URL, 'PASTE_LEAVE_APPS_SCRIPT_EXEC_URL_HERE') === false
            && preg_match('~^https://script\.google\.com/macros/s/.+/exec$~', LEAVE_LINE_APPS_SCRIPT_URL);
    }

    function leave_line_h($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * สร้าง text message
     */
    function leave_line_text_message($text)
    {
        return array(
            'type' => 'text',
            'text' => (string)$text
        );
    }

    /**
     * สร้าง Flex Message
     * DS
     */


    function leave_line_flex_message($altText, array $contents)
    {
        return array(
            'type' => 'flex',
            'altText' => (string)$altText,
            'contents' => $contents
        );
    }

    /**
     * ส่ง Messages ไปยัง target เดียวผ่าน Apps Script
     */
    function leave_line_send_messages($targetId, array $messages, $event = '', array $context = array())
    {
        $targetId = trim((string)$targetId);
        $result = array(
            'ok' => false,
            'skipped' => false,
            'http_code' => 0,
            'response' => '',
            'error' => '',
            'target_id' => $targetId,
            'event' => (string)$event
        );

        if ($targetId === '') {
            $result['skipped'] = true;
            $result['error'] = 'ไม่มี LINE Target ID';
            return $result;
        }

        $prefix = strtoupper(substr($targetId, 0, 1));
        if (!in_array($prefix, array('U', 'C', 'R'), true)) {
            $result['error'] = 'LINE Target ID ไม่ถูกต้อง';
            return $result;
        }

        if (!$messages) {
            $result['error'] = 'ไม่มีข้อความสำหรับส่ง';
            return $result;
        }

        if (!leave_line_is_configured()) {
            $result['skipped'] = true;
            $result['error'] = 'ยังไม่ได้ตั้ง LEAVE_LINE_APPS_SCRIPT_URL';
            return $result;
        }

        $payload = array(
            'secret' => LEAVE_LINE_APPS_SCRIPT_SHARED_SECRET,
            'target_id' => $targetId,
            'event' => (string)$event,
            'messages' => array_values($messages),
            'context' => $context
        );

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $result['error'] = 'JSON encode failed';
            return $result;
        }

        if (!function_exists('curl_init')) {
            $result['error'] = 'PHP cURL extension is not enabled';
            return $result;
        }

        $ch = curl_init(LEAVE_LINE_APPS_SCRIPT_URL);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json'
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => LEAVE_LINE_CURL_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => LEAVE_LINE_CURL_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => (bool)LEAVE_LINE_CURL_SSL_VERIFY,
            CURLOPT_SSL_VERIFYHOST => LEAVE_LINE_CURL_SSL_VERIFY ? 2 : 0,
            CURLOPT_USERAGENT => 'PhakdeeChumphonLeave/1.0'
        ));

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result['http_code'] = $httpCode;
        $result['response'] = $raw !== false ? (string)$raw : '';

        if ($raw === false) {
            $result['error'] = $curlError !== '' ? $curlError : 'cURL request failed';
            return $result;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $result['error'] = 'Apps Script HTTP ' . $httpCode;
            return $result;
        }

        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) {
            $result['error'] = 'Apps Script response ไม่ใช่ JSON';
            return $result;
        }

        if (!empty($decoded['ok'])) {
            $result['ok'] = true;
        } else {
            $result['error'] = isset($decoded['error'])
                ? (string)$decoded['error']
                : 'LINE notification failed';
        }

        return $result;
    }

    function leave_line_send_text($targetId, $text, $event = '', array $context = array())
    {
        return leave_line_send_messages(
            $targetId,
            array(leave_line_text_message($text)),
            $event,
            $context
        );
    }

    /**
     * LINE ID ของ user จากฐานข้อมูล
     */
    function leave_line_user_target(mysqli $conn, $userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) return '';

        $res = $conn->query("SHOW TABLES LIKE 'leave_line_accounts'");
        if (!$res || $res->num_rows === 0) return '';

        $stmt = $conn->prepare(
            "SELECT line_user_id
         FROM leave_line_accounts
         WHERE user_id=?
           AND is_active=1
         LIMIT 1"
        );
        if (!$stmt) return '';

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? trim((string)$row['line_user_id']) : '';
    }

    /**
     * Default / CC targets จาก config
     */
    function leave_line_default_targets()
    {
        $raw = defined('LEAVE_LINE_DEFAULT_TARGETS')
            ? (string)LEAVE_LINE_DEFAULT_TARGETS
            : '';

        $targets = array();
        foreach (preg_split('/[\s,;]+/', $raw) as $id) {
            $id = trim($id);
            if ($id !== '' && in_array(strtoupper(substr($id, 0, 1)), array('U', 'C', 'R'), true)) {
                $targets[$id] = $id;
            }
        }
        return array_values($targets);
    }

    /**
     * รวม LINE ID จาก user หลายคน + default CC และตัดค่าซ้ำ
     */
    function leave_line_targets_for_users(mysqli $conn, array $userIds, $includeDefault = true)
    {
        $targets = array();

        foreach ($userIds as $userId) {
            $id = leave_line_user_target($conn, (int)$userId);
            if ($id !== '') $targets[$id] = $id;
        }

        if ($includeDefault) {
            foreach (leave_line_default_targets() as $id) {
                $targets[$id] = $id;
            }
        }

        return array_values($targets);
    }

    /**
     * บันทึก log แบบไม่ทำให้ workflow หลักล้ม
     */
    function leave_line_log(mysqli $conn, $applicationId, $userId, $targetId, $event, $messageSummary, array $result)
    {
        $exists = $conn->query("SHOW TABLES LIKE 'leave_line_logs'");
        if (!$exists || $exists->num_rows === 0) return;

        $status = !empty($result['ok'])
            ? 'sent'
            : (!empty($result['skipped']) ? 'skipped' : 'failed');

        $httpCode = isset($result['http_code']) ? (int)$result['http_code'] : 0;
        $response = isset($result['response']) ? (string)$result['response'] : '';
        $error = isset($result['error']) ? (string)$result['error'] : '';

        $stmt = $conn->prepare(
            "INSERT INTO leave_line_logs
         (application_id,user_id,target_id,event_name,message_summary,send_status,http_code,response_text,error_text,created_at)
         VALUES(?,?,?,?,?,?,?,?,?,NOW())"
        );
        if (!$stmt) return;

        $applicationId = $applicationId ? (int)$applicationId : null;
        $userId = $userId ? (int)$userId : null;
        $targetId = (string)$targetId;
        $event = (string)$event;
        $messageSummary = mb_substr((string)$messageSummary, 0, 500, 'UTF-8');

        $stmt->bind_param(
            'iissssiss',
            $applicationId,
            $userId,
            $targetId,
            $event,
            $messageSummary,
            $status,
            $httpCode,
            $response,
            $error
        );
        $stmt->execute();
        $stmt->close();
    }

    /**
     * ส่งหลาย target
     */
    function leave_line_send_to_targets(mysqli $conn, array $targets, array $messages, $event, $applicationId = null, $userId = null, $summary = '')
    {
        $results = array();
        $dedupe = array();

        foreach ($targets as $targetId) {
            $targetId = trim((string)$targetId);
            if ($targetId === '' || isset($dedupe[$targetId])) continue;
            $dedupe[$targetId] = true;

            $result = leave_line_send_messages(
                $targetId,
                $messages,
                $event,
                array('application_id' => $applicationId)
            );

            leave_line_log(
                $conn,
                $applicationId,
                $userId,
                $targetId,
                $event,
                $summary,
                $result
            );

            $results[$targetId] = $result;
        }

        return $results;
    }

    /**
     * URL detail
     */
    function leave_line_detail_url($applicationId)
    {
        return rtrim(LEAVE_PUBLIC_URL, '/')
            . '/detail.php?id='
            . urlencode((string)(int)$applicationId);
    }

    /**
     * เซ็น Action URL สำหรับปุ่มอนุมัติ
     * URL ยังต้อง Login และตรวจ user_id ที่ฝั่ง PHP อีกชั้น
     */
    function leave_line_action_url($applicationId, $action, $authorizedUserId, $ttlSeconds = 172800)
    {
        $applicationId = (int)$applicationId;
        $authorizedUserId = (int)$authorizedUserId;
        $expires = time() + max(300, (int)$ttlSeconds);
        $action = (string)$action;

        $base = $applicationId . '|' . $action . '|' . $authorizedUserId . '|' . $expires;
        $sig = hash_hmac('sha256', $base, LEAVE_LINE_ACTION_SECRET);

        return rtrim(LEAVE_PUBLIC_URL, '/')
            . '/line_approval.php?id=' . $applicationId
            . '&action=' . rawurlencode($action)
            . '&user=' . $authorizedUserId
            . '&expires=' . $expires
            . '&sig=' . rawurlencode($sig);
    }

    function leave_line_verify_action_signature($applicationId, $action, $authorizedUserId, $expires, $sig)
    {
        $applicationId = (int)$applicationId;
        $authorizedUserId = (int)$authorizedUserId;
        $expires = (int)$expires;

        if ($expires < time() || $sig === '') return false;

        $base = $applicationId . '|' . (string)$action . '|' . $authorizedUserId . '|' . $expires;
        $expected = hash_hmac('sha256', $base, LEAVE_LINE_ACTION_SECRET);

        return hash_equals($expected, (string)$sig);
    }
