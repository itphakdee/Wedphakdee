-- ============================================================
-- โรงพยาบาลภักดีชุมพล
-- จัดระเบียบ "กลุ่มงาน" ให้เหลือรายการมาตรฐานเพียงชุดเดียว
-- ============================================================

START TRANSACTION;

-- 1) สร้าง/เปิดใช้งาน 13 กลุ่มงานมาตรฐาน และจัดลำดับใหม่
INSERT INTO personnel_career_options (option_type, option_name, sort_order, status) VALUES
('work_group','กลุ่มงานบริหารทั่วไป',10,'active'),
('work_group','กลุ่มงานเทคนิคการแพทย์',20,'active'),
('work_group','กลุ่มงานทันตกรรม',30,'active'),
('work_group','กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',40,'active'),
('work_group','กลุ่มงานการแพทย์',50,'active'),
('work_group','กลุ่มงานโภชนศาสตร์',60,'active'),
('work_group','กลุ่มงานทางรังสีวิทยา',70,'active'),
('work_group','กลุ่มงานเวชกรรมฟื้นฟู',80,'active'),
('work_group','งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์',90,'active'),
('work_group','กลุ่มงานบริการด้านปฐมภูมิและองค์รวม',100,'active'),
('work_group','กลุ่มงานการพยาบาล',110,'active'),
('work_group','กลุ่มอำนวยการ',120,'active'),
('work_group','กลุ่มงานการแพทย์แผนไทย',130,'active')
ON DUPLICATE KEY UPDATE sort_order=VALUES(sort_order), status='active';

-- 2) รวมชื่อที่ซ้ำ/ความหมายเดียวกันก่อนปิดชื่อเก่า
UPDATE personnel p
JOIN personnel_career_options oldwg ON oldwg.id=p.work_group_id AND oldwg.option_type='work_group' AND oldwg.option_name='กลุ่มงานรังสีวิทยา'
JOIN personnel_career_options newwg ON newwg.option_type='work_group' AND newwg.option_name='กลุ่มงานทางรังสีวิทยา'
SET p.work_group_id=newwg.id;

UPDATE personnel p
JOIN personnel_career_options oldwg ON oldwg.id=p.work_group_id AND oldwg.option_type='work_group' AND oldwg.option_name='กลุ่มงานเวชศาสตร์ฟื้นฟู'
JOIN personnel_career_options newwg ON newwg.option_type='work_group' AND newwg.option_name='กลุ่มงานเวชกรรมฟื้นฟู'
SET p.work_group_id=newwg.id;

UPDATE personnel p
JOIN personnel_career_options oldwg ON oldwg.id=p.work_group_id AND oldwg.option_type='work_group' AND oldwg.option_name IN (
    'กลุ่มงานประกันสุขภาพ ยุทธศาสตร์และสารสนเทศทางการแพทย์',
    'กลุ่มงานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์'
)
JOIN personnel_career_options newwg ON newwg.option_type='work_group' AND newwg.option_name='งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์'
SET p.work_group_id=newwg.id;

-- 3) ซ่อนชื่อเก่า/ชื่อที่ไม่ได้อยู่ในรายการมาตรฐานจาก Dropdown
-- ไม่ DELETE เพื่อรักษาประวัติฐานข้อมูลเดิม
UPDATE personnel_career_options
SET status='inactive'
WHERE option_type='work_group'
  AND option_name NOT IN (
    'กลุ่มงานบริหารทั่วไป',
    'กลุ่มงานเทคนิคการแพทย์',
    'กลุ่มงานทันตกรรม',
    'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',
    'กลุ่มงานการแพทย์',
    'กลุ่มงานโภชนศาสตร์',
    'กลุ่มงานทางรังสีวิทยา',
    'กลุ่มงานเวชกรรมฟื้นฟู',
    'งานประกันสุขภาพยุทธศาสตร์และสารสนเทศทางการแพทย์',
    'กลุ่มงานบริการด้านปฐมภูมิและองค์รวม',
    'กลุ่มงานการพยาบาล',
    'กลุ่มอำนวยการ',
    'กลุ่มงานการแพทย์แผนไทย'
  );

COMMIT;

-- ตรวจสอบผล: ควรได้ 13 รายการ
SELECT id, option_name, sort_order, status
FROM personnel_career_options
WHERE option_type='work_group' AND status='active'
ORDER BY sort_order, option_name;
