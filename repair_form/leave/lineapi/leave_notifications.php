<?php

require_once __DIR__ . '/line_sender.php';

function leave_line_safe($value, $fallback = '-')
{
    $text = trim((string)$value);
    return $text !== '' ? $text : $fallback;
}

function leave_line_date_display($date)
{
    if (!$date) return '-';
    $ts = strtotime((string)$date);
    if (!$ts) return (string)$date;
    return date('d/m/Y', $ts);
}

function leave_line_summary_text(array $row)
{
    return
        "🏥 ระบบการลางาน โรงพยาบาลภักดีชุมพล\n"
        . "เลขที่: " . leave_line_safe($row['leave_no']) . "\n"
        . "ผู้ลา: " . leave_line_safe($row['employee_name']) . "\n"
        . "หน่วยงาน: " . leave_line_safe($row['department_name']) . "\n"
        . "ประเภท: " . leave_line_safe($row['leave_type_name']) . "\n"
        . "วันที่: " . leave_line_date_display($row['start_date'])
        . " - " . leave_line_date_display($row['end_date']) . "\n"
        . "จำนวน: " . leave_line_safe($row['leave_days']) . " วัน\n"
        . "เหตุผล: " . leave_line_safe($row['reason']);
}

function leave_line_kv($label, $value)
{
    return array(
        'type' => 'box',
        'layout' => 'baseline',
        'spacing' => 'sm',
        'contents' => array(
            array(
                'type' => 'text',
                'text' => (string)$label,
                'color' => '#7B8B92',
                'size' => 'sm',
                'flex' => 3
            ),
            array(
                'type' => 'text',
                'text' => leave_line_safe($value),
                'wrap' => true,
                'color' => '#16313B',
                'size' => 'sm',
                'flex' => 7,
                'weight' => 'bold'
            )
        )
    );
}

function leave_line_leave_bubble(array $row, $title, $statusText, array $buttons = array())
{
    $body = array(
        array(
            'type' => 'text',
            'text' => 'โรงพยาบาลภักดีชุมพล',
            'size' => 'xs',
            'color' => '#168486',
            'weight' => 'bold'
        ),
        array(
            'type' => 'text',
            'text' => (string)$title,
            'size' => 'xl',
            'weight' => 'bold',
            'color' => '#123447',
            'wrap' => true,
            'margin' => 'md'
        ),
        array(
            'type' => 'text',
            'text' => (string)$statusText,
            'size' => 'sm',
            'color' => '#9A6500',
            'wrap' => true,
            'margin' => 'sm'
        ),
        array('type' => 'separator', 'margin' => 'lg'),
        leave_line_kv('เลขที่', $row['leave_no']),
        leave_line_kv('ผู้ลา', $row['employee_name']),
        leave_line_kv('หน่วยงาน', $row['department_name']),
        leave_line_kv('ประเภท', $row['leave_type_name']),
        leave_line_kv(
            'วันที่',
            leave_line_date_display($row['start_date'])
            . ' - '
            . leave_line_date_display($row['end_date'])
        ),
        leave_line_kv('จำนวน', leave_line_safe($row['leave_days']) . ' วัน'),
        leave_line_kv('เหตุผล', $row['reason'])
    );

    $footerContents = array();
    foreach ($buttons as $button) {
        $footerContents[] = array(
            'type' => 'button',
            'style' => isset($button['style']) ? $button['style'] : 'primary',
            'color' => isset($button['color']) ? $button['color'] : '#0E7D78',
            'height' => 'sm',
            'action' => array(
                'type' => 'uri',
                'label' => (string)$button['label'],
                'uri' => (string)$button['uri']
            ),
            'margin' => 'sm'
        );
    }

    if (!$footerContents) {
        $footerContents[] = array(
            'type' => 'button',
            'style' => 'primary',
            'color' => '#0E7D78',
            'height' => 'sm',
            'action' => array(
                'type' => 'uri',
                'label' => 'ดูรายละเอียด',
                'uri' => leave_line_detail_url($row['id'])
            )
        );
    }

    return array(
        'type' => 'bubble',
        'header' => array(
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0A5966',
            'paddingAll' => '14px',
            'contents' => array(
                array(
                    'type' => 'text',
                    'text' => 'LEAVE MANAGEMENT',
                    'color' => '#FFFFFF',
                    'weight' => 'bold',
                    'size' => 'sm'
                )
            )
        ),
        'body' => array(
            'type' => 'box',
            'layout' => 'vertical',
            'spacing' => 'md',
            'contents' => $body
        ),
        'footer' => array(
            'type' => 'box',
            'layout' => 'vertical',
            'spacing' => 'sm',
            'contents' => $footerContents
        )
    );
}

function leave_line_send_role_message(mysqli $conn, array $row, $userId, $event, array $messages, $includeDefault, $summary)
{
    $userId = (int)$userId;
    $targets = leave_line_targets_for_users(
        $conn,
        $userId > 0 ? array($userId) : array(),
        (bool)$includeDefault
    );

    return leave_line_send_to_targets(
        $conn,
        $targets,
        $messages,
        $event,
        (int)$row['id'],
        $userId > 0 ? $userId : null,
        $summary
    );
}

/**
 * แจ้งตอนสร้างใบลา:
 * - ผู้ยื่นลา
 * - ผู้รับมอบงาน
 * - หัวหน้างาน
 * - DEFAULT CC
 */
function leave_line_notify_created(mysqli $conn, array $row)
{
    $summary = leave_line_summary_text($row);

    // ผู้ยื่นลา: ยืนยันว่าระบบรับใบลาแล้ว
    $requesterBubble = leave_line_leave_bubble(
        $row,
        'บันทึกคำขอลาแล้ว',
        'ระบบส่งต่อผู้รับมอบงานและหัวหน้างานเรียบร้อย',
        array(
            array('label' => 'ติดตามใบลา', 'uri' => leave_line_detail_url($row['id']), 'style' => 'primary', 'color' => '#0E7D78')
        )
    );
    leave_line_send_role_message(
        $conn,
        $row,
        (int)$row['user_id'],
        'leave_created_requester',
        array(leave_line_flex_message('บันทึกคำขอลา ' . $row['leave_no'], $requesterBubble)),
        false,
        $summary
    );

    // ผู้รับมอบงาน
    $handoverBubble = leave_line_leave_bubble(
        $row,
        'มีงานให้รับมอบ',
        'กรุณาตรวจสอบและยืนยันการรับมอบงาน',
        array(
            array('label' => 'รับมอบงาน / ตรวจสอบ', 'uri' => leave_line_detail_url($row['id']), 'style' => 'primary', 'color' => '#0E7D78')
        )
    );
    leave_line_send_role_message(
        $conn,
        $row,
        (int)$row['handover_user_id'],
        'leave_created_handover',
        array(leave_line_flex_message('มีงานให้รับมอบ ' . $row['leave_no'], $handoverBubble)),
        false,
        $summary
    );

    // หัวหน้า + CC: แจ้งล่วงหน้าว่ามีคำขอใหม่
    $supervisorBubble = leave_line_leave_bubble(
        $row,
        'มีคำขอลาใหม่',
        'รอผู้รับมอบงานยืนยัน ก่อนหัวหน้างานพิจารณา',
        array(
            array('label' => 'ดูรายละเอียด', 'uri' => leave_line_detail_url($row['id']), 'style' => 'primary', 'color' => '#0E7D78')
        )
    );
    leave_line_send_role_message(
        $conn,
        $row,
        (int)$row['supervisor_user_id'],
        'leave_created_supervisor',
        array(leave_line_flex_message('คำขอลาใหม่ ' . $row['leave_no'], $supervisorBubble)),
        true,
        $summary
    );
}

function leave_line_notify_handover(mysqli $conn, array $row, $accepted)
{
    $summary = leave_line_summary_text($row);

    if ($accepted) {
        $approveUrl = leave_line_action_url(
            $row['id'],
            'approve',
            $row['supervisor_user_id']
        );
        $rejectUrl = leave_line_action_url(
            $row['id'],
            'reject',
            $row['supervisor_user_id']
        );

        $bubble = leave_line_leave_bubble(
            $row,
            'พร้อมพิจารณาใบลา',
            'ผู้รับมอบงานยืนยันแล้ว กรุณาพิจารณาใบลา',
            array(
                array('label' => 'อนุมัติ', 'uri' => $approveUrl, 'style' => 'primary', 'color' => '#138A68'),
                array('label' => 'ไม่อนุมัติ', 'uri' => $rejectUrl, 'style' => 'secondary', 'color' => '#D9534F'),
                array('label' => 'ดูรายละเอียด', 'uri' => leave_line_detail_url($row['id']), 'style' => 'link', 'color' => '#0E7D78')
            )
        );

        leave_line_send_role_message(
            $conn,
            $row,
            (int)$row['supervisor_user_id'],
            'leave_ready_for_approval',
            array(leave_line_flex_message('รออนุมัติใบลา ' . $row['leave_no'], $bubble)),
            true,
            $summary
        );

        $text = "✅ ผู้รับมอบงานยืนยันแล้ว\n\n" . $summary . "\n\nสถานะ: รอหัวหน้างานอนุมัติ";
        leave_line_send_role_message(
            $conn,
            $row,
            (int)$row['user_id'],
            'leave_handover_accepted',
            array(leave_line_text_message($text)),
            false,
            $summary
        );
    } else {
        $text = "↩️ ผู้รับมอบงานส่งคืนใบลาให้แก้ไข\n\n"
            . $summary
            . "\n\nหมายเหตุ: "
            . leave_line_safe($row['handover_comment']);

        leave_line_send_role_message(
            $conn,
            $row,
            (int)$row['user_id'],
            'leave_handover_rejected',
            array(leave_line_text_message($text)),
            true,
            $summary
        );
    }
}

function leave_line_notify_decision(mysqli $conn, array $row, $approved)
{
    $summary = leave_line_summary_text($row);
    $text = ($approved ? "✅ ใบลาได้รับการอนุมัติ" : "❌ ใบลาไม่ผ่านการอนุมัติ")
        . "\n\n"
        . $summary
        . "\n\nความเห็นหัวหน้า: "
        . leave_line_safe($row['supervisor_comment'])
        . "\n\nดูรายละเอียด: "
        . leave_line_detail_url($row['id']);

    $targets = leave_line_targets_for_users(
        $conn,
        array(
            (int)$row['user_id'],
            (int)$row['handover_user_id']
        ),
        true
    );

    leave_line_send_to_targets(
        $conn,
        $targets,
        array(leave_line_text_message($text)),
        $approved ? 'leave_approved' : 'leave_rejected',
        (int)$row['id'],
        (int)$row['user_id'],
        $summary
    );
}

function leave_line_notify_cancel_request(mysqli $conn, array $row)
{
    $summary = leave_line_summary_text($row);
    $approveUrl = leave_line_action_url(
        $row['id'],
        'approve_cancel',
        $row['supervisor_user_id']
    );
    $rejectUrl = leave_line_action_url(
        $row['id'],
        'reject_cancel',
        $row['supervisor_user_id']
    );

    $bubble = leave_line_leave_bubble(
        $row,
        'คำขอยกเลิกใบลา',
        'ผู้ยื่นใบลาขอให้หัวหน้างานพิจารณาการยกเลิก',
        array(
            array('label' => 'อนุมัติยกเลิก', 'uri' => $approveUrl, 'style' => 'primary', 'color' => '#D9534F'),
            array('label' => 'ไม่อนุมัติยกเลิก', 'uri' => $rejectUrl, 'style' => 'secondary', 'color' => '#0E7D78'),
            array('label' => 'ดูรายละเอียด', 'uri' => leave_line_detail_url($row['id']), 'style' => 'link', 'color' => '#0E7D78')
        )
    );

    leave_line_send_role_message(
        $conn,
        $row,
        (int)$row['supervisor_user_id'],
        'leave_cancel_requested',
        array(leave_line_flex_message('คำขอยกเลิก ' . $row['leave_no'], $bubble)),
        true,
        $summary
    );
}

function leave_line_notify_cancel_decision(mysqli $conn, array $row, $approved)
{
    $summary = leave_line_summary_text($row);
    $text = ($approved ? "✅ หัวหน้างานยืนยันการยกเลิกใบลา" : "ℹ️ หัวหน้างานไม่อนุมัติการยกเลิกใบลา")
        . "\n\n"
        . $summary
        . "\n\nดูรายละเอียด: "
        . leave_line_detail_url($row['id']);

    $targets = leave_line_targets_for_users(
        $conn,
        array(
            (int)$row['user_id'],
            (int)$row['handover_user_id']
        ),
        true
    );

    leave_line_send_to_targets(
        $conn,
        $targets,
        array(leave_line_text_message($text)),
        $approved ? 'leave_cancel_approved' : 'leave_cancel_rejected',
        (int)$row['id'],
        (int)$row['user_id'],
        $summary
    );
}
