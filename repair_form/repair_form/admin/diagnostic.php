<?php
require_once __DIR__ . '/config_admin.php';
admin_only();

echo '<h2>Admin Diagnostic</h2>';
echo '<p>PHP: '.htmlspecialchars(PHP_VERSION,ENT_QUOTES,'UTF-8').'</p>';
echo '<p>MySQL: '.htmlspecialchars($conn->server_info,ENT_QUOTES,'UTF-8').'</p>';

echo '<h3>users</h3>';
$r=$conn->query("DESCRIBE users");
if(!$r){echo '<pre>'.htmlspecialchars($conn->error).'</pre>';}
else{
 echo '<table border="1" cellpadding="6"><tr><th>Field</th><th>Type</th></tr>';
 while($x=$r->fetch_assoc()){
  echo '<tr><td>'.htmlspecialchars($x['Field']).'</td><td>'.htmlspecialchars($x['Type']).'</td></tr>';
 }
 echo '</table>';
}

echo '<h3>admin_permissions</h3>';
$r=$conn->query("SHOW TABLES LIKE 'admin_permissions'");
echo $r && $r->num_rows ? 'มีตารางแล้ว' : 'ยังไม่มีตาราง - ให้รัน admin_permissions.sql';
?>
