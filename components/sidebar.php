<?php
$activePage = $activePage ?? "";
$basePath = $basePath ?? "";

if (!function_exists('has_permission')) {
    $permissionHelper = __DIR__ . '/../repair_form/admin/permissions_helper.php';
    if (is_file($permissionHelper)) {
        require_once $permissionHelper;
    }
}

/*
 * Sidebar style based on the supplied reference image.
 * Keep routes pointing to the existing Wedphakdee modules.
 */
$menuItems = [
    "dashboard" => ["icon" => "🏠", "label" => "ภาพรวม", "href" => "dashboard.php", "permission" => "dashboard.view", "match" => ["dashboard"]],
    "personnel" => ["icon" => "🧑‍⚕️", "label" => "บุคลากร", "href" => "repair_form/personnel/index.php", "permission" => "personnel.view", "match" => ["personnel"]],
    "attendance" => ["icon" => "⏱️", "label" => "ลงเวลา", "href" => "repair_form/attendance/index.php", "permission" => "attendance.view", "match" => ["attendance"]],
    "leave" => ["icon" => "📝", "label" => "ระบบลา", "href" => "repair_form/leave/index.php", "permission" => "leave.view", "match" => ["leave"]],
    "meeting" => ["icon" => "🏢", "label" => "ห้องประชุม", "href" => "repair_form/meeting_final/index.php", "permission" => "meeting.view", "match" => ["meeting"]],
    "meeting_booking" => ["icon" => "🗓️", "label" => "จองห้องประชุม", "href" => "repair_form/meeting_final/booking.php", "permission" => "meeting.create", "match" => ["meeting_booking"]],
    "vehicle" => ["icon" => "🚑", "label" => "ยานพาหนะ", "href" => "vehicle/index.php", "permission" => "vehicle.view", "match" => ["vehicle"]],
    "vehicle_request" => ["icon" => "🛣️", "label" => "ขอใช้ยานพาหนะ", "href" => "vehicle/index.php", "permission" => "vehicle.view", "match" => ["vehicle_request"]],
    "repair" => ["icon" => "🛠️", "label" => "แจ้งซ่อม", "href" => "repair_form/home_repair.php", "permission" => "repair.view", "match" => ["repair"]],
    "computer" => ["icon" => "💻", "label" => "คอมพิวเตอร์", "href" => "repair_form/computer/indexrepairlist.php", "permission" => "computer.view", "match" => ["computer"]],
    "medical" => ["icon" => "🩺", "label" => "เครื่องมือแพทย์", "href" => "repair_form/medical/index.php", "permission" => "medical.view", "match" => ["medical"]],
    "propertywork" => ["icon" => "📦", "label" => "งานทรัพย์สิน", "href" => "repair_form/propertywork/index.php", "permission" => "propertywork.view", "match" => ["propertywork", "asset"]],
];

function sidebar_can_show($permission)
{
    if (!$permission) {
        return true;
    }

    if (!function_exists('has_permission')) {
        return true;
    }

    return has_permission($permission);
}

function sidebar_item_active($itemKey, $item, $activePage)
{
    if ($activePage === $itemKey) {
        return true;
    }

    if (!empty($item['match']) && in_array($activePage, $item['match'], true)) {
        /* Do not let the two paired shortcut rows become active together. */
        if ($itemKey === 'meeting_booking' && $activePage === 'meeting') {
            return false;
        }
        if ($itemKey === 'vehicle_request' && $activePage === 'vehicle') {
            return false;
        }
        return true;
    }

    return false;
}

$isAdmin = function_exists('is_admin_user') && is_admin_user();
?>
<style>
    /* ===== Wedphakdee reference sidebar ===== */
    .sidebar {
        width: 254px !important;
        min-width: 254px !important;
        background: #0d3733 !important;
        padding: 0 0 18px !important;
        box-shadow: none !important;
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        height: 100vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        z-index: 1000 !important;
        color: #fff !important;
        font-family: 'Noto Sans Thai', Tahoma, sans-serif !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, .22) transparent;
    }

    .sidebar::-webkit-scrollbar {
        width: 5px !important;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent !important;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, .20) !important;
        border-radius: 20px !important;
    }

    .sidebar-brand {
        height: 68px;
        padding: 9px 8px 7px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .sidebar-brand-logo {
        width: 45px;
        height: 45px;
        min-width: 45px;
        background: #fff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d6b64;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: .2px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
    }

    .sidebar-brand-text {
        min-width: 0;
        padding-top: 3px;
        line-height: 1.2;
    }

    .sidebar-brand-name {
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-brand-subtitle {
        margin-top: 5px;
        color: #cfe7e3;
        font-size: 11px;
        font-weight: 400;
        white-space: nowrap;
    }

    .sidebar-menu {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .sidebar-menu li {
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    .sidebar-menu a.sidebar-link {
        height: 44px !important;
        margin: 2px 8px !important;
        padding: 0 13px !important;
        border: 0 !important;
        border-left: 0 !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        color: #fff !important;
        text-decoration: none !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        transition: background .16s ease, transform .16s ease !important;
        box-shadow: none !important;
    }

    .sidebar-menu a.sidebar-link:hover {
        background: rgba(23, 83, 77, .72) !important;
        color: #fff !important;
        padding-left: 13px !important;
        border-left: 0 !important;
        text-decoration: none !important;
        transform: none !important;
    }

    .sidebar-menu a.sidebar-link.active {
        background: #17534d !important;
        color: #fff !important;
        border-left: 0 !important;
        box-shadow: none !important;
    }

    .sidebar-icon {
        width: 21px;
        min-width: 21px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        font-size: 15px;
        line-height: 1;
    }

    .sidebar-label {
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-section-title {
        margin: 14px 0 4px !important;
        padding: 0 19px !important;
        color: #87b4ae !important;
        font-size: 10px !important;
        font-weight: 400 !important;
        line-height: 22px !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
    }

    .sidebar-admin-link .sidebar-icon {
        filter: saturate(.95);
    }

    .sidebar-logout-wrap {
        margin-top: 26px !important;
    }

    .sidebar-menu a.sidebar-link.logout-link {
        color: #ffd7d7 !important;
    }

    /* Keep the existing page layout aligned with the new 254px sidebar. */
    body .main-content,
    body .content,
    body .content-wrapper {
        margin-left: 254px;
    }

    body .top-bar {
        left: 254px !important;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: relative !important;
            top: auto !important;
            width: 100% !important;
            min-width: 100% !important;
            height: auto !important;
            max-height: none !important;
        }

        .sidebar-brand {
            padding-left: 12px;
            padding-right: 12px;
        }

        body .top-bar {
            left: 0 !important;
        }

        body .main-content,
        body .content,
        body .content-wrapper {
            margin-left: 0 !important;
        }

        body .container,
        body .container-wrapper {
            flex-direction: column !important;
        }
    }
</style>

<aside class="sidebar" aria-label="เมนูหลัก">
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">PDC</div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">โรงพยาบาลภักดีชุมพล</div>
            <div class="sidebar-brand-subtitle">ระบบสารสนเทศภายใน</div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <?php foreach ($menuItems as $menuKey => $item): ?>
            <?php if (sidebar_can_show($item["permission"])): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($basePath . $item["href"], ENT_QUOTES, 'UTF-8'); ?>"
                        class="sidebar-link <?php echo sidebar_item_active($menuKey, $item, $activePage) ? 'active' : ''; ?>">
                        <span class="sidebar-icon" aria-hidden="true"><?php echo $item["icon"]; ?></span>
                        <span class="sidebar-label"><?php echo htmlspecialchars($item["label"], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($isAdmin): ?>
            <li class="sidebar-section-title">ผู้ดูแลระบบ</li>
            <li>
                <a class="sidebar-link sidebar-admin-link <?php echo $activePage === 'admin_users' ? 'active' : ''; ?>"
                    href="<?php echo htmlspecialchars($basePath . 'repair_form/admin/users.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="sidebar-icon" aria-hidden="true">👥</span>
                    <span class="sidebar-label">ผู้ใช้งานและสิทธิ์</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link sidebar-admin-link <?php echo $activePage === 'admin_modules' ? 'active' : ''; ?>"
                    href="<?php echo htmlspecialchars($basePath . 'repair_form/admin/modules.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="sidebar-icon" aria-hidden="true">🧭</span>
                    <span class="sidebar-label">จัดการเมนู</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link sidebar-admin-link <?php echo $activePage === 'admin' ? 'active' : ''; ?>"
                    href="<?php echo htmlspecialchars($basePath . 'repair_form/admin/index.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="sidebar-icon" aria-hidden="true">⚙️</span>
                    <span class="sidebar-label">ข้อมูลพื้นฐาน</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link sidebar-admin-link <?php echo $activePage === 'admin_history' ? 'active' : ''; ?>"
                    href="<?php echo htmlspecialchars($basePath . 'repair_form/admin/diagnostic.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="sidebar-icon" aria-hidden="true">🧾</span>
                    <span class="sidebar-label">ประวัติการใช้งาน</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="sidebar-logout-wrap">
            <a href="<?php echo htmlspecialchars($basePath . 'logout.php', ENT_QUOTES, 'UTF-8'); ?>"
                class="sidebar-link logout-link">
                <span class="sidebar-icon" aria-hidden="true">🚪</span>
                <span class="sidebar-label">ออกจากระบบ</span>
            </a>
        </li>
    </ul>
</aside>