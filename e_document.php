<?php
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";
$message_type = "success";

/*
    รองรับ 3 ภาษา
    th = ไทย
    en = English
    cn = 中文
*/
$lang = isset($_GET["lang"]) ? $_GET["lang"] : "th";
$allow_lang = ["th", "en", "cn"];
if (!in_array($lang, $allow_lang)) {
    $lang = "th";
}

$text = [
    "th" => [
        "title" => "ระบบ E-Document",
        "back" => "← กลับเมนูหลัก",
        "form_title" => "รับ-ส่งเอกสารภายในหน่วยงาน",
        "doc_no" => "เลขหนังสือ",
        "short_title" => "หัวข้อย่อ",
        "description" => "รายละเอียด",
        "sender_name" => "ชื่อผู้ส่ง",
        "sender_department" => "แผนกผู้ส่ง",
        "send_type" => "รูปแบบการส่ง",
        "send_department" => "ส่งตามแผนก",
        "send_person" => "ส่งรายบุคคล",
        "receiver_department" => "เลือกแผนกผู้รับ",
        "receiver_person" => "เลือกผู้รับ",
        "file_upload" => "อัปโหลดเอกสาร",
        "status" => "สถานะ",
        "save" => "บันทึกเอกสาร",
        "list_title" => "รายการเอกสารของฉัน",
        "history_title" => "ประวัติเอกสารที่ได้รับ",
        "download" => "ดาวน์โหลด",
        "ack" => "ลงรับเอกสาร",
        "pending" => "รอดำเนินการ",
        "edit" => "แก้ไข",
        "cancel" => "ยกเลิก",
        "new_doc_saved" => "บันทึกเอกสารเรียบร้อยแล้ว",
        "required" => "กรุณากรอกข้อมูลให้ครบ",
        "choose_one" => "กรุณาเลือกผู้รับแบบใดแบบหนึ่ง",
        "file_error" => "อัปโหลดไฟล์ไม่สำเร็จ",
        "email_sent" => "และส่งอีเมลแจ้งเตือนเรียบร้อย",
        "email_fail" => "บันทึกแล้ว แต่ส่งอีเมลไม่สำเร็จ"
    ],
    "en" => [
        "title" => "E-Document System",
        "back" => "← Back to Dashboard",
        "form_title" => "Internal Document Transfer",
        "doc_no" => "Document No.",
        "short_title" => "Short Title",
        "description" => "Description",
        "sender_name" => "Sender Name",
        "sender_department" => "Sender Department",
        "send_type" => "Send Type",
        "send_department" => "Send by Department",
        "send_person" => "Send to Individual",
        "receiver_department" => "Select Department",
        "receiver_person" => "Select Receiver",
        "file_upload" => "Upload Document",
        "status" => "Status",
        "save" => "Save Document",
        "list_title" => "My Documents",
        "history_title" => "Received Document History",
        "download" => "Download",
        "ack" => "Acknowledge",
        "pending" => "Pending",
        "edit" => "Edit",
        "cancel" => "Cancel",
        "new_doc_saved" => "Document saved successfully",
        "required" => "Please fill in all required fields",
        "choose_one" => "Please choose only one receiver type",
        "file_error" => "File upload failed",
        "email_sent" => "and email notification sent successfully",
        "email_fail" => "saved, but email sending failed"
    ],
    "cn" => [
        "title" => "电子公文系统",
        "back" => "← 返回主菜单",
        "form_title" => "单位内部公文收发",
        "doc_no" => "公文编号",
        "short_title" => "简要标题",
        "description" => "详细内容",
        "sender_name" => "发送人",
        "sender_department" => "发送部门",
        "send_type" => "发送方式",
        "send_department" => "按部门发送",
        "send_person" => "发送给个人",
        "receiver_department" => "选择接收部门",
        "receiver_person" => "选择接收人",
        "file_upload" => "上传文件",
        "status" => "状态",
        "save" => "保存公文",
        "list_title" => "我的公文",
        "history_title" => "已接收公文记录",
        "download" => "下载",
        "ack" => "签收",
        "pending" => "待处理",
        "edit" => "修改",
        "cancel" => "取消",
        "new_doc_saved" => "公文保存成功",
        "required" => "请完整填写资料",
        "choose_one" => "请选择一种接收方式",
        "file_error" => "文件上传失败",
        "email_sent" => "并已发送邮件通知",
        "email_fail" => "已保存，但邮件发送失败"
    ]
];

$t = $text[$lang];

// ดึงข้อมูลผู้ใช้ปัจจุบัน
$stmtUser = $conn->prepare("
    SELECT u.fullname, u.username, u.email, u.department_id, d.department_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    WHERE u.id = ?
");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$currentUser = $stmtUser->get_result()->fetch_assoc();

$current_fullname = $currentUser["fullname"] ?? $_SESSION["fullname"];
$current_email = $currentUser["email"] ?? "";
$current_department_id = $currentUser["department_id"] ?? "";
$current_department = $currentUser["department_name"] ?? "";
// รายชื่อแผนก
$departments = [];

$sqlDept = "SELECT id, department_name FROM departments WHERE status = 'ใช้งาน' ORDER BY department_name ASC";
$resultDept = $conn->query($sqlDept);

if ($resultDept && $resultDept->num_rows > 0) {
    while ($row = $resultDept->fetch_assoc()) {
        $departments[] = $row;
    }
}
// รายชื่อผู้ใช้
$users = [];
$resUsers = $conn->query("
    SELECT u.id, u.fullname, u.email, d.department_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    ORDER BY u.fullname ASC
");
if ($resUsers) {
    while ($row = $resUsers->fetch_assoc()) {
        $users[] = $row;
    }
}
// ลงรับเอกสาร
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ack_document"])) {
    $receiver_id = intval($_POST["receiver_id"]);
    $document_id = intval($_POST["document_id"]);

    $stmt = $conn->prepare("UPDATE document_receivers SET is_acknowledged = 1, received_at = NOW() WHERE id = ? AND document_id = ?");
    $stmt->bind_param("ii", $receiver_id, $document_id);
    if ($stmt->execute()) {
        $logType = "acknowledge";
        $logDetail = "ผู้ใช้ลงรับเอกสารแล้ว";
        $stmtLog = $conn->prepare("INSERT INTO document_logs (document_id, user_id, action_type, action_detail) VALUES (?, ?, ?, ?)");
        $stmtLog->bind_param("iiss", $document_id, $user_id, $logType, $logDetail);
        $stmtLog->execute();
        $stmtLog->close();

        $message = "ลงรับเอกสารเรียบร้อยแล้ว";
        $message_type = "success";
    } else {
        $message = "ไม่สามารถลงรับเอกสารได้";
        $message_type = "error";
    }
    $stmt->close();
}

// เปลี่ยนสถานะเอกสาร
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_doc_status"])) {
    $document_id = intval($_POST["document_id"]);
    $status = trim($_POST["status"]);
    $allowed_status = ["รอดำเนินการ", "ส่งสำเร็จ", "แก้ไข", "ยกเลิก"];

    if (in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE documents SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $document_id, $user_id);

        if ($stmt->execute()) {
            $logType = "status_update";
            $logDetail = "เปลี่ยนสถานะเอกสารเป็น " . $status;
            $stmtLog = $conn->prepare("INSERT INTO document_logs (document_id, user_id, action_type, action_detail) VALUES (?, ?, ?, ?)");
            $stmtLog->bind_param("iiss", $document_id, $user_id, $logType, $logDetail);
            $stmtLog->execute();
            $stmtLog->close();

            $message = "อัปเดตสถานะเอกสารเรียบร้อยแล้ว";
            $message_type = "success";
        } else {
            $message = "ไม่สามารถอัปเดตสถานะเอกสารได้";
            $message_type = "error";
        }
        $stmt->close();
    }
}

// บันทึกเอกสาร
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_document"])) {
    $doc_no = trim($_POST["doc_no"]);
    $short_title = trim($_POST["short_title"]);
    $description = trim($_POST["description"]);
    $sender_name = trim($_POST["sender_name"]);
    $sender_department = trim($_POST["sender_department"]);
    $send_type = trim($_POST["send_type"]);
    $receiver_department = trim($_POST["receiver_department"] ?? "");
    $receiver_user_id = intval($_POST["receiver_user_id"] ?? 0);
    $status = trim($_POST["status"]);

    $allowed_status = ["รอดำเนินการ", "ส่งสำเร็จ", "แก้ไข", "ยกเลิก"];

    if (
        empty($doc_no) || empty($short_title) || empty($description) ||
        empty($sender_name) || empty($sender_department) || empty($send_type) ||
        empty($status)
    ) {
        $message = $t["required"];
        $message_type = "error";
    } elseif (!in_array($status, $allowed_status)) {
        $message = "สถานะไม่ถูกต้อง";
        $message_type = "error";
    } else {
        if ($send_type == "department" && empty($receiver_department)) {
            $message = $t["choose_one"];
            $message_type = "error";
        } elseif ($send_type == "person" && $receiver_user_id <= 0) {
            $message = $t["choose_one"];
            $message_type = "error";
        } elseif (!isset($_FILES["document_file"]) || $_FILES["document_file"]["error"] != 0) {
            $message = $t["file_error"];
            $message_type = "error";
        } else {
            $upload_dir = "uploads/documents/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $original_name = $_FILES["document_file"]["name"];
            $tmp_name = $_FILES["document_file"]["tmp_name"];
            $ext = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_name = time() . "_" . rand(1000,9999) . "." . $ext;
            $target_file = $upload_dir . $new_name;
            $mime_type = $_FILES["document_file"]["type"];

            if (move_uploaded_file($tmp_name, $target_file)) {
                $stmt = $conn->prepare("INSERT INTO documents (user_id, doc_no, short_title, description, file_name, file_path, file_type, sender_name, sender_department, send_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "issssssssss",
                    $user_id, $doc_no, $short_title, $description,
                    $original_name, $target_file, $mime_type,
                    $sender_name, $sender_department, $send_type, $status
                );

                if ($stmt->execute()) {
                    $document_id = $stmt->insert_id;
                    $stmt->close();

                    $email_success = true;

                    
                    if ($send_type == "department") {
                        $stmtDeptUsers = $conn->prepare("SELECT id, fullname, email, department FROM users WHERE department = ?");
                        $stmtDeptUsers->bind_param("s", $receiver_department);
                        $stmtDeptUsers->execute();
                        $deptUsers = $stmtDeptUsers->get_result();

                        while ($u = $deptUsers->fetch_assoc()) {
                            $receiver_name = $u["fullname"];
                            $receiver_email = $u["email"];
                            $receiver_dept = $u["department"];
                            $receive_type = "department";

                            $stmtRec = $conn->prepare("INSERT INTO document_receivers (document_id, receiver_user_id, receiver_name, receiver_email, receiver_department, receive_type) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmtRec->bind_param("iissss", $document_id, $u["id"], $receiver_name, $receiver_email, $receiver_dept, $receive_type);
                            $stmtRec->execute();
                            $stmtRec->close();

                            if (!empty($receiver_email)) {
                                $subject = "แจ้งเตือนเอกสารใหม่: " . $short_title;
                                $mail_message = "คุณมีเอกสารใหม่เลขที่ " . $doc_no . " เรื่อง " . $short_title;
                                $headers = "Content-Type: text/plain; charset=UTF-8";
                                if (!@mail($receiver_email, $subject, $mail_message, $headers)) {
                                    $email_success = false;
                                }
                            }
                        }
                        $stmtDeptUsers->close();

                    } elseif ($send_type == "person") {
                        $stmtOne = $conn->prepare("SELECT id, fullname, email, department FROM users WHERE id = ?");
                        $stmtOne->bind_param("i", $receiver_user_id);
                        $stmtOne->execute();
                        $oneUser = $stmtOne->get_result()->fetch_assoc();
                        $stmtOne->close();

                        if ($oneUser) {
                            $receive_type = "person";
                            $stmtRec = $conn->prepare("INSERT INTO document_receivers (document_id, receiver_user_id, receiver_name, receiver_email, receiver_department, receive_type) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmtRec->bind_param(
                                "iissss",
                                $document_id,
                                $oneUser["id"],
                                $oneUser["fullname"],
                                $oneUser["email"],
                                $oneUser["department"],
                                $receive_type
                            );
                            $stmtRec->execute();
                            $stmtRec->close();

                            if (!empty($oneUser["email"])) {
                                $subject = "แจ้งเตือนเอกสารใหม่: " . $short_title;
                                $mail_message = "คุณมีเอกสารใหม่เลขที่ " . $doc_no . " เรื่อง " . $short_title;
                                $headers = "Content-Type: text/plain; charset=UTF-8";
                                if (!@mail($oneUser["email"], $subject, $mail_message, $headers)) {
                                    $email_success = false;
                                }
                            }
                        }
                    }

                    $logType = "create";
                    $logDetail = "สร้างเอกสารใหม่";
                    $stmtLog = $conn->prepare("INSERT INTO document_logs (document_id, user_id, action_type, action_detail) VALUES (?, ?, ?, ?)");
                    $stmtLog->bind_param("iiss", $document_id, $user_id, $logType, $logDetail);
                    $stmtLog->execute();
                    $stmtLog->close();

                    if ($email_success) {
                        $message = $t["new_doc_saved"] . " " . $t["email_sent"];
                    } else {
                        $message = $t["new_doc_saved"] . " " . $t["email_fail"];
                    }
                    $message_type = "success";
                } else {
                    $message = "บันทึกเอกสารไม่สำเร็จ";
                    $message_type = "error";
                }
            } else {
                $message = $t["file_error"];
                $message_type = "error";
            }
        }
    }
}

// บันทึกประวัติดาวน์โหลด
if (isset($_GET["download_id"])) {
    $download_id = intval($_GET["download_id"]);

    $stmtDoc = $conn->prepare("SELECT file_path, file_name FROM documents WHERE id = ?");
    $stmtDoc->bind_param("i", $download_id);
    $stmtDoc->execute();
    $doc = $stmtDoc->get_result()->fetch_assoc();
    $stmtDoc->close();

    if ($doc && file_exists($doc["file_path"])) {
        $logType = "download";
        $logDetail = "ดาวน์โหลดเอกสาร";
        $stmtLog = $conn->prepare("INSERT INTO document_logs (document_id, user_id, action_type, action_detail) VALUES (?, ?, ?, ?)");
        $stmtLog->bind_param("iiss", $download_id, $user_id, $logType, $logDetail);
        $stmtLog->execute();
        $stmtLog->close();

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . basename($doc["file_name"]) . "\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . filesize($doc["file_path"]));
        readfile($doc["file_path"]);
        exit;
    }
}

// รายการเอกสารที่ฉันส่ง
$stmtDocs = $conn->prepare("SELECT * FROM documents WHERE user_id = ? ORDER BY id DESC");
$stmtDocs->bind_param("i", $user_id);
$stmtDocs->execute();
$myDocuments = $stmtDocs->get_result();

// รายการเอกสารที่ฉันได้รับ
$stmtReceived = $conn->prepare("
    SELECT dr.*, d.doc_no, d.short_title, d.file_name, d.created_at, d.id AS document_id
    FROM document_receivers dr
    INNER JOIN documents d ON dr.document_id = d.id
    WHERE dr.receiver_user_id = ?
    ORDER BY dr.id DESC
");
$stmtReceived->bind_param("i", $user_id);
$stmtReceived->execute();
$receivedDocuments = $stmtReceived->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo $t["title"]; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .top-bar {
            background: #6f42c1;
            padding: 15px;
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        .page-wrapper {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.10);
            margin-bottom: 25px;
        }
        .btn-back, .lang-btn, .download-btn, .ack-btn {
            display: inline-block;
            padding: 10px 16px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }
        .btn-back { background: #4a67ff; }
        .lang-btn { background: #6f42c1; margin-left: 5px; }
        .download-btn { background: #198754; }
        .ack-btn { background: #fd7e14; }
        input, select, textarea, button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: Tahoma, sans-serif;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        button {
            background: #6f42c1;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover { opacity: .92; }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .success {
    background: #198754;
}
        .message-success {
            background: #e8fff0;
            color: #0f7a36;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .message-error {
            background: #ffeaea;
            color: #b30000;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .table-box { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }
        table th {
            background: #6f42c1;
            color: white;
        }
        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 220px;
        }
        .status-form select {
            margin: 0;
            min-width: 140px;
        }
        .status-form button {
            margin: 0;
            width: auto;
            padding: 10px 14px;
            white-space: nowrap;
        }
        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 13px;
            color: white;
        }
        .pending { background: #f0ad4e; }
        .editing { background: #0d6efd; }
        .cancel { background: #dc3545; }

        @media (max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
            .status-form {
                flex-direction: column;
                align-items: stretch;
            }
            .status-form button { width: 100%; }
        }
    </style>
</head>
<body>

<div class="top-bar"><?php echo $t["title"]; ?></div>

<div class="page-wrapper">

    <a href="dashboard.php" class="btn-back"><?php echo $t["back"]; ?></a>
    <a href="e_document.php?lang=th" class="lang-btn">ไทย</a>
    <a href="e_document.php?lang=en" class="lang-btn">English</a>
    <a href="e_document.php?lang=cn" class="lang-btn">中文</a>

    <div class="card">
        <h2><?php echo $t["form_title"]; ?></h2>

        <?php if (!empty($message)) { ?>
            <div class="<?php echo $message_type == 'success' ? 'message-success' : 'message-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="save_document" value="1">

            <div class="grid-2">
                <div>
                    <label><?php echo $t["doc_no"]; ?></label>
                    <input type="text" name="doc_no" required>
                </div>
                <div>
                    <label><?php echo $t["short_title"]; ?></label>
                    <input type="text" name="short_title" required>
                </div>
            </div>

            <label><?php echo $t["description"]; ?></label>
            <textarea name="description" required></textarea>

            <div class="grid-2">
                <div>
                    <label><?php echo $t["sender_name"]; ?></label>
                    <input type="text" name="sender_name" value="<?php echo htmlspecialchars($current_fullname); ?>" required>
                </div>
                <div>
                    <label><?php echo $t["sender_department"]; ?></label>
                    <input type="text" name="sender_department" value="<?php echo htmlspecialchars($current_department); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label><?php echo $t["send_type"]; ?></label>
                    <select name="send_type" id="send_type" onchange="toggleReceiver()" required>
                        <option value="department"><?php echo $t["send_department"]; ?></option>
                        <option value="person"><?php echo $t["send_person"]; ?></of[ption>
                    </select>
                </div>
                <div>
                    <label><?php echo $t["status"]; ?></label>
                    <select name="status" required>
                        <option value="รอดำเนินการ">รอดำเนินการ</option>
                        <option value="แก้ไข">แก้ไข</option>
                        <option value="ยกเลิก">ยกเลิก</option>
                    </select>
                </div>
            </div>

           <div id="department_box">
    <label><?php echo $t["receiver_department"]; ?></label>
    <select name="receiver_department" required>
        <option value="">-- เลือกแผนก --</option>
        <?php foreach ($departments as $dept) { ?>
            <option value="<?php echo htmlspecialchars($dept["department_name"]); ?>">
                <?php echo htmlspecialchars($dept["department_name"]); ?>
            </option>
        <?php } ?>
    </select>
</div>


           <div id="person_box" style="display:none;">
    <label><?php echo $t["receiver_person"]; ?></label>
    <select name="receiver_user_id">
        <option value="">-- เลือกผู้รับ --</option>
        <?php foreach ($users as $u) { ?>
            <?php if ($u["id"] != $user_id) { ?>
                <option value="<?php echo $u["id"]; ?>">
                    <?php echo htmlspecialchars($u["fullname"] . " (" . ($u["department_name"] ?? "-") . ")"); ?>
                </option>
            <?php } ?>
        <?php } ?>
    </select>
</div>
            <label><?php echo $t["file_upload"]; ?></label>
            <input type="file" name="document_file" required>

            <button type="submit"><?php echo $t["save"]; ?></button>
        </form>
    </div>

    <div class="card">
        <h2><?php echo $t["list_title"]; ?></h2>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>เลขหนังสือ</th>
                    <th>หัวข้อย่อ</th>
                    <th>ไฟล์</th>
                    <th>สถานะปัจจุบัน</th>
                    <th>แก้ไขสถานะ</th>
                    <th>วันที่สร้าง</th>
                </tr>

                <?php if ($myDocuments && $myDocuments->num_rows > 0) { ?>
                    <?php while($row = $myDocuments->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo htmlspecialchars($row["doc_no"]); ?></td>
                            <td><?php echo htmlspecialchars($row["short_title"]); ?></td>
                            <td>
                                <a class="download-btn" href="e_document.php?download_id=<?php echo $row["id"]; ?>&lang=<?php echo $lang; ?>">
                                    <?php echo $t["download"]; ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                    $status_class = "pending";
                                  if ($row["status"] == "ส่งสำเร็จ") $status_class = "success";
if ($row["status"] == "แก้ไข") $status_class = "editing";
if ($row["status"] == "ยกเลิก") $status_class = "cancel";
                                ?>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($row["status"]); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="update_doc_status" value="1">
                                    <input type="hidden" name="document_id" value="<?php echo $row["id"]; ?>">
                                    <select name="status" required>
                                       <option value="รอดำเนินการ" <?php if ($row["status"] == "รอดำเนินการ") echo "selected"; ?>>รอดำเนินการ</option>
<option value="ส่งสำเร็จ" <?php if ($row["status"] == "ส่งสำเร็จ") echo "selected"; ?>>ส่งสำเร็จ</option>
<option value="แก้ไข" <?php if ($row["status"] == "แก้ไข") echo "selected"; ?>>แก้ไข</option>
<option value="ยกเลิก" <?php if ($row["status"] == "ยกเลิก") echo "selected"; ?>>ยกเลิก</option>
                                    </select>
                                    <button type="submit">บันทึก</button>
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">ยังไม่มีข้อมูลเอกสาร</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <div class="card">
        <h2><?php echo $t["history_title"]; ?></h2>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>เลขหนังสือ</th>
                    <th>หัวข้อย่อ</th>
                    <th>ไฟล์</th>
                    <th>ลงรับ</th>
                    <th>วันเวลา</th>
                </tr>

                <?php if ($receivedDocuments && $receivedDocuments->num_rows > 0) { ?>
                    <?php while($row = $receivedDocuments->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row["document_id"]; ?></td>
                            <td><?php echo htmlspecialchars($row["doc_no"]); ?></td>
                            <td><?php echo htmlspecialchars($row["short_title"]); ?></td>
                            <td>
                                <a class="download-btn" href="e_document.php?download_id=<?php echo $row["document_id"]; ?>&lang=<?php echo $lang; ?>">
                                    <?php echo $t["download"]; ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($row["is_acknowledged"] == 1) { ?>
                                    ลงรับแล้ว
                                <?php } else { ?>
                                    <form method="POST">
                                        <input type="hidden" name="ack_document" value="1">
                                        <input type="hidden" name="receiver_id" value="<?php echo $row["id"]; ?>">
                                        <input type="hidden" name="document_id" value="<?php echo $row["document_id"]; ?>">
                                        <button type="submit" class="ack-btn"><?php echo $t["ack"]; ?></button>
                                    </form>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo !empty($row["received_at"]) ? htmlspecialchars($row["received_at"]) : "-"; ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">ยังไม่มีเอกสารที่ได้รับ</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</div>

<script>
function toggleReceiver() {
    var sendType = document.getElementById("send_type").value;
    var departmentBox = document.getElementById("department_box");
    var personBox = document.getElementById("person_box");

    if (sendType === "department") {
        departmentBox.style.display = "block";
        personBox.style.display = "none";
    } else {
        departmentBox.style.display = "none";
        personBox.style.display = "block";
    }
}
</script>

</body>
</html>