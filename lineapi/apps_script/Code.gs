/**
 * ==========================================================
 * โรงพยาบาลภักดีชุมพล
 * LINE Gateway สำหรับระบบแจ้งซ่อม
 * PHP -> Google Apps Script -> LINE Messaging API
 * ==========================================================
 *
 * Script Properties ที่ใช้:
 * LINE_CHANNEL_ACCESS_TOKEN
 * LINE_TARGET_ID
 * WEBHOOK_SECRET
 */

/**
 * ใช้เฉพาะกรณีต้องตั้งค่าใหม่
 * สำคัญ: ต้องใส่ Token ใหม่ก่อน Run
 * ถ้ายังเป็น PASTE_NEW_CHANNEL_ACCESS_TOKEN_HERE ฟังก์ชันจะหยุดและไม่ทับค่าของเดิม
 */
function setupConfig() {
  var NEW_TOKEN = 'PASTE_NEW_CHANNEL_ACCESS_TOKEN_HERE';
  var TARGET_ID = 'Ce213eaf090cda6b4de630f90c70e7657';
  var SECRET = 'PDCRepair_2026_x7K9m2Q8v4';

  if (!NEW_TOKEN || NEW_TOKEN === 'PASTE_NEW_CHANNEL_ACCESS_TOKEN_HERE') {
    throw new Error('ยังไม่ได้ใส่ Channel Access Token ใหม่ จึงไม่แก้ Script Properties เดิม');
  }

  var props = PropertiesService.getScriptProperties();
  props.setProperty('LINE_CHANNEL_ACCESS_TOKEN', String(NEW_TOKEN).trim());
  props.setProperty('LINE_TARGET_ID', String(TARGET_ID).trim());
  props.setProperty('WEBHOOK_SECRET', String(SECRET).trim());

  Logger.log('บันทึก Script Properties เรียบร้อย');
  checkConfig();
}

function doPost(e) {
  try {
    var body = parseJsonBody_(e);
    var props = PropertiesService.getScriptProperties();

    var expectedSecret = props.getProperty('WEBHOOK_SECRET') || '';
    var token = props.getProperty('LINE_CHANNEL_ACCESS_TOKEN') || '';
    var targetId = props.getProperty('LINE_TARGET_ID') || '';

    if (!expectedSecret) {
      return jsonResponse_({ok:false,error:'WEBHOOK_SECRET is not configured'});
    }

    if (!body.secret || body.secret !== expectedSecret) {
      return jsonResponse_({ok:false,error:'Unauthorized'});
    }

    if (!token) {
      return jsonResponse_({ok:false,error:'LINE_CHANNEL_ACCESS_TOKEN is missing'});
    }

    if (!targetId) {
      return jsonResponse_({ok:false,error:'LINE_TARGET_ID is missing'});
    }

    var message = String(body.message || '').trim();
    if (!message) {
      return jsonResponse_({ok:false,error:'Message is empty'});
    }

    if (message.length > 4900) {
      message = message.substring(0, 4900) + '\n…';
    }

    var payload = {
      to: targetId,
      messages: [{type:'text', text:message}]
    };

    var response = UrlFetchApp.fetch(
      'https://api.line.me/v2/bot/message/push',
      {
        method: 'post',
        contentType: 'application/json',
        headers: {Authorization: 'Bearer ' + token},
        payload: JSON.stringify(payload),
        muteHttpExceptions: true
      }
    );

    var status = response.getResponseCode();
    var responseText = response.getContentText();
    var ok = status >= 200 && status < 300;

    console.log('LINE HTTP: ' + status);
    console.log('LINE RESPONSE: ' + responseText);

    return jsonResponse_({
      ok: ok,
      line_status: status,
      line_response: responseText,
      event: body.event || '',
      timestamp: new Date().toISOString()
    });

  } catch (err) {
    return jsonResponse_({
      ok:false,
      error:String(err && err.message ? err.message : err),
      timestamp:new Date().toISOString()
    });
  }
}

function doGet() {
  return jsonResponse_({
    ok:true,
    service:'Phakdee Chumphon Hospital LINE Gateway',
    message:'LINE Gateway is running',
    timestamp:new Date().toISOString()
  });
}

function parseJsonBody_(e) {
  if (!e || !e.postData || !e.postData.contents) {
    throw new Error('POST body is required');
  }
  try {
    return JSON.parse(e.postData.contents);
  } catch (err) {
    throw new Error('Invalid JSON body');
  }
}

function jsonResponse_(data) {
  return ContentService
    .createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}

function checkConfig() {
  var props = PropertiesService.getScriptProperties();
  var token = props.getProperty('LINE_CHANNEL_ACCESS_TOKEN') || '';
  var targetId = props.getProperty('LINE_TARGET_ID') || '';
  var secret = props.getProperty('WEBHOOK_SECRET') || '';

  var result = {
    LINE_CHANNEL_ACCESS_TOKEN: !!token,
    TOKEN_LENGTH: token.length,
    LINE_TARGET_ID: !!targetId,
    TARGET_ID: targetId,
    TARGET_TYPE: getTargetType_(targetId),
    WEBHOOK_SECRET: !!secret,
    SCRIPT_ID: ScriptApp.getScriptId()
  };

  Logger.log(JSON.stringify(result, null, 2));
  return result;
}

function getTargetType_(targetId) {
  if (!targetId) return 'NOT SET';
  if (targetId.charAt(0) === 'U') return 'USER';
  if (targetId.charAt(0) === 'C') return 'GROUP';
  if (targetId.charAt(0) === 'R') return 'ROOM';
  return 'UNKNOWN';
}

function testSendLine() {
  var props = PropertiesService.getScriptProperties();
  var token = props.getProperty('LINE_CHANNEL_ACCESS_TOKEN') || '';
  var targetId = props.getProperty('LINE_TARGET_ID') || '';

  if (!token) throw new Error('ยังไม่ได้ตั้ง LINE_CHANNEL_ACCESS_TOKEN');
  if (!targetId) throw new Error('ยังไม่ได้ตั้ง LINE_TARGET_ID');

  var now = Utilities.formatDate(new Date(), 'Asia/Bangkok', 'dd/MM/yyyy HH:mm:ss');
  var message =
    '🔧 ทดสอบระบบแจ้งซ่อม\n' +
    'โรงพยาบาลภักดีชุมพล\n\n' +
    '✅ LINE Messaging API ทำงานเรียบร้อย\n' +
    'เวลา: ' + now;

  var response = UrlFetchApp.fetch(
    'https://api.line.me/v2/bot/message/push',
    {
      method:'post',
      contentType:'application/json',
      headers:{Authorization:'Bearer ' + token},
      payload:JSON.stringify({
        to:targetId,
        messages:[{type:'text',text:message}]
      }),
      muteHttpExceptions:true
    }
  );

  var status = response.getResponseCode();
  var text = response.getContentText();

  Logger.log('HTTP Status: ' + status);
  Logger.log('LINE Response: ' + text);
  Logger.log('Target ID: ' + targetId);

  if (status < 200 || status >= 300) {
    throw new Error('LINE ส่งไม่สำเร็จ HTTP ' + status + ' : ' + text);
  }

  Logger.log('✅ ส่งข้อความเข้า LINE สำเร็จ');
}
