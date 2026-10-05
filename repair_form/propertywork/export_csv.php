<?php
require_once '../../config.php';require_once __DIR__.'/property_helpers.php';property_require_login();
header('Content-Type:text/csv; charset=UTF-8');header('Content-Disposition:attachment; filename="property_registry_'.date('Ymd_His').'.csv"');
$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,array('ID','เลขครุภัณฑ์','ปีงบ','ชื่อครุภัณฑ์','ประเภท','หมวดหมู่','ยี่ห้อ','รุ่น','Serial','หน่วยงาน','สถานที่','ผู้รับผิดชอบ','วันที่จัดซื้อ','วันหมดประกัน','ผู้จำหน่าย','ราคา','สถานะ','หมายเหตุ'));
$res=$conn->query('SELECT * FROM properties ORDER BY id DESC');if($res){while($r=$res->fetch_assoc()){fputcsv($out,array($r['id'],$r['asset_no'],$r['budget_year'],$r['property_name'],$r['property_type'],$r['category'],$r['brand'],$r['model'],$r['serial_no'],$r['department'],$r['location'],$r['responsible_person'],$r['purchase_date'],$r['warranty_date'],$r['vendor'],$r['price'],$r['status'],$r['note']));}}fclose($out);exit;
