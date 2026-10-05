/**
 * ==========================================================
 * โรงพยาบาลภักดีชุมพล
 * LINE OA Gateway - ระบบลางาน
 *
 * PHP -> Google Apps Script -> LINE Messaging API
 * ==========================================================
 *
 * Script Properties:
 * LEAVE_LINE_CHANNEL_ACCESS_TOKEN
 * LEAVE_WEBHOOK_SECRET
 */

function setupLeaveConfig() {
  var NEW_TOKEN = 'PASTE_NEW_CHANNEL_ACCESS_TOKEN_HERE';
  var SECRET = 'PDCLeave_2026_Ln4Q8v2R7s9M';

  var props = PropertiesService.getScriptProperties();
  var oldToken = String(
    props.getProperty('LEAVE_LINE_CHANNEL_ACCESS_TOKEN') || ''
  ).trim();

  var finalToken = (
    NEW_TOKEN &&
    NEW_TOKEN !== 'PASTE_NEW_CHANNEL_ACCESS_TOKEN_HERE'
  ) ? String(NEW_TOKEN).trim() : oldToken;

  if (!finalToken) {
    throw new Error(
      'ยังไม่มี LEAVE_LINE_CHANNEL_ACCESS_TOKEN กรุณาใส่ Channel Access Token ใน NEW_TOKEN ก่อน'
    );
  }

  props.setProperty(
    'LEAVE_LINE_CHANNEL_ACCESS_TOKEN',
    finalToken
  );

  props.setProperty(
    'LEAVE_WEBHOOK_SECRET',
    SECRET
  );

  Logger.log('ตั้งค่า LINE OA ระบบลาเรียบร้อย');
  checkLeaveConfig();
}

function doPost(e) {
  try {
    var body = parseJsonBody_(e);
    var props = PropertiesService.getScriptProperties();

    var expectedSecret = String(
      props.getProperty('LEAVE_WEBHOOK_SECRET') || ''
    ).trim();

    var token = String(
      props.getProperty('LEAVE_LINE_CHANNEL_ACCESS_TOKEN') || ''
    ).trim();

    if (!expectedSecret) {
      return jsonResponse_({ok:false,error:'LEAVE_WEBHOOK_SECRET is missing'});
    }

    if (!token) {
      return jsonResponse_({ok:false,error:'LEAVE_LINE_CHANNEL_ACCESS_TOKEN is missing'});
    }

    if (
      !body.secret ||
      String(body.secret).trim() !== expectedSecret
    ) {
      return jsonResponse_({ok:false,error:'Unauthorized'});
    }

    var targetId = String(body.target_id || '').trim();

    if (!targetId) {
      return jsonResponse_({ok:false,error:'target_id is missing'});
    }

    var targetType = getTargetType_(targetId);
    if (targetType === 'UNKNOWN' || targetType === 'NOT SET') {
      return jsonResponse_({
        ok:false,
        error:'Invalid LINE target ID',
        target_id:targetId
      });
    }

    var messages = [];

    if (Array.isArray(body.messages) && body.messages.length > 0) {
      messages = sanitizeMessages_(body.messages);
    } else if (body.message) {
      messages = [
        {
          type:'text',
          text:String(body.message).substring(0,4900)
        }
      ];
    }

    if (!messages.length) {
      return jsonResponse_({ok:false,error:'messages are empty'});
    }

    var payload = {
      to: targetId,
      messages: messages
    };

    var response = UrlFetchApp.fetch(
      'https://api.line.me/v2/bot/message/push',
      {
        method:'post',
        contentType:'application/json',
        headers:{
          Authorization:'Bearer ' + token
        },
        payload:JSON.stringify(payload),
        muteHttpExceptions:true
      }
    );

    var status = response.getResponseCode();
    var responseText = response.getContentText();
    var ok = status >= 200 && status < 300;

    Logger.log('EVENT = ' + String(body.event || ''));
    Logger.log('TARGET = ' + targetId);
    Logger.log('LINE HTTP = ' + status);
    Logger.log('LINE RESPONSE = ' + responseText);

    return jsonResponse_({
      ok:ok,
      line_status:status,
      line_response:responseText,
      target_id:targetId,
      target_type:targetType,
      event:String(body.event || ''),
      timestamp:new Date().toISOString()
    });

  } catch (err) {
    return jsonResponse_({
      ok:false,
      error:String(err && err.message ? err.message : err),
      timestamp:new Date().toISOString()
    });
  }
}

function sanitizeMessages_(messages) {
  var output = [];

  for (var i = 0; i < messages.length && output.length < 5; i++) {
    var m = messages[i];

    if (!m || typeof m !== 'object') continue;

    if (m.type === 'text') {
      var text = String(m.text || '').trim();
      if (text) {
        output.push({
          type:'text',
          text:text.substring(0,4900)
        });
      }
      continue;
    }

    if (
      m.type === 'flex' &&
      m.contents &&
      typeof m.contents === 'object'
    ) {
      output.push({
        type:'flex',
        altText:String(m.altText || 'แจ้งเตือนระบบลางาน').substring(0,400),
        contents:m.contents
      });
    }
  }

  return output;
}

function doGet() {
  var props = PropertiesService.getScriptProperties();

  var token = String(
    props.getProperty('LEAVE_LINE_CHANNEL_ACCESS_TOKEN') || ''
  );

  var secret = String(
    props.getProperty('LEAVE_WEBHOOK_SECRET') || ''
  );

  return jsonResponse_({
    ok:true,
    service:'Phakdee Chumphon Hospital Leave LINE Gateway',
    message:'Leave LINE Gateway is running',
    token_configured:token.length > 0,
    token_length:token.length,
    secret_configured:secret.length > 0,
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

function getTargetType_(targetId) {
  if (!targetId) return 'NOT SET';
  if (targetId.charAt(0) === 'U') return 'USER';
  if (targetId.charAt(0) === 'C') return 'GROUP';
  if (targetId.charAt(0) === 'R') return 'ROOM';
  return 'UNKNOWN';
}

function checkLeaveConfig() {
  var props = PropertiesService.getScriptProperties();

  var token = String(
    props.getProperty('LEAVE_LINE_CHANNEL_ACCESS_TOKEN') || ''
  );

  var secret = String(
    props.getProperty('LEAVE_WEBHOOK_SECRET') || ''
  );

  var result = {
    TOKEN_EXISTS:!!token,
    TOKEN_LENGTH:token.length,
    SECRET_EXISTS:!!secret,
    SCRIPT_ID:ScriptApp.getScriptId()
  };

  Logger.log(JSON.stringify(result, null, 2));
  return result;
}

/**
 * ทดสอบ User ID เริ่มต้นที่ผู้ใช้ให้มา
 */
function testLeaveLine() {
  var TEST_TARGET_ID = 'Udcb9130b37083ee5b2aa3ecfe119e8e2';

  var props = PropertiesService.getScriptProperties();

  var token = String(
    props.getProperty('LEAVE_LINE_CHANNEL_ACCESS_TOKEN') || ''
  ).trim();

  if (!token) {
    throw new Error('ยังไม่ได้ตั้ง LEAVE_LINE_CHANNEL_ACCESS_TOKEN');
  }

  var now = Utilities.formatDate(
    new Date(),
    'Asia/Bangkok',
    'dd/MM/yyyy HH:mm:ss'
  );

  var payload = {
    to:TEST_TARGET_ID,
    messages:[
      {
        type:'text',
        text:
          '🏥 ทดสอบ LINE OA ระบบลางาน\n' +
          'โรงพยาบาลภักดีชุมพล\n\n' +
          '✅ LINE Messaging API ทำงานเรียบร้อย\n' +
          'เวลา: ' + now
      }
    ]
  };

  var response = UrlFetchApp.fetch(
    'https://api.line.me/v2/bot/message/push',
    {
      method:'post',
      contentType:'application/json',
      headers:{Authorization:'Bearer ' + token},
      payload:JSON.stringify(payload),
      muteHttpExceptions:true
    }
  );

  var status = response.getResponseCode();
  var text = response.getContentText();

  Logger.log('HTTP Status: ' + status);
  Logger.log('Target ID: ' + TEST_TARGET_ID);
  Logger.log('LINE Response: ' + text);

  if (status < 200 || status >= 300) {
    throw new Error('LINE ส่งไม่สำเร็จ HTTP ' + status + ' : ' + text);
  }

  Logger.log('✅ ส่ง LINE สำเร็จ');
}
