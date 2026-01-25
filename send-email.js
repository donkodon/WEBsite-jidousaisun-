const http = require('http');
const nodemailer = require('nodemailer');

const PORT = 3001;
const RECIPIENT_EMAIL = 'kenji.noto@relight-rl.com';

// Xserver SMTP configuration
const transporter = nodemailer.createTransport({
    host: 'sv16714.xserver.jp',
    port: 465,
    secure: true, // SSL
    auth: {
        user: 'kenji.noto@relight-rl.com',
        pass: 'kenji0614'
    },
    // Additional settings for better compatibility
    tls: {
        rejectUnauthorized: false
    }
});

// Verify SMTP connection
transporter.verify(function(error, success) {
    if (error) {
        console.log('❌ SMTP接続エラー:', error);
    } else {
        console.log('✅ SMTPサーバー接続成功');
    }
});

// Simple email sending server
const server = http.createServer((req, res) => {
    // Enable CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    if (req.method === 'POST' && req.url === '/send-email') {
        let body = '';
        
        req.on('data', chunk => {
            body += chunk.toString();
        });
        
        req.on('end', async () => {
            try {
                const formData = JSON.parse(body);
                
                // Format email content
                const emailContent = `
新しいお問い合わせがありました

【会社名】
${formData.company}

【お名前】
${formData.name}

【メールアドレス】
${formData.email}

【電話番号】
${formData.phone || '未記入'}

【お問い合わせ種別】
${formData.inquiryType}

【お問い合わせ内容】
${formData.message}

【送信日時】
${new Date().toLocaleString('ja-JP', { timeZone: 'Asia/Tokyo' })}
                `.trim();
                
                console.log('='.repeat(50));
                console.log('📧 新しいお問い合わせ');
                console.log('='.repeat(50));
                console.log(emailContent);
                console.log('='.repeat(50));
                
                // Send actual email via Xserver SMTP
                try {
                    const info = await transporter.sendMail({
                        from: `"Relight お問い合わせフォーム" <kenji.noto@relight-rl.com>`,
                        to: RECIPIENT_EMAIL,
                        subject: `【Relight】お問い合わせ: ${formData.inquiryType}`,
                        text: emailContent,
                        html: `<pre style="font-family: 'Noto Sans JP', sans-serif; white-space: pre-wrap;">${emailContent}</pre>`
                    });
                    
                    console.log('✅ メール送信成功!');
                    console.log('📧 送信先:', RECIPIENT_EMAIL);
                    console.log('🆔 Message ID:', info.messageId);
                    console.log('='.repeat(50));
                    console.log('');
                    
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ 
                        success: true, 
                        message: 'お問い合わせを受け付けました',
                        recipient: RECIPIENT_EMAIL,
                        messageId: info.messageId
                    }));
                    
                } catch (emailError) {
                    console.error('❌ メール送信エラー:', emailError);
                    
                    res.writeHead(500, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ 
                        success: false, 
                        error: 'メール送信に失敗しました',
                        details: emailError.message
                    }));
                }
                
            } catch (error) {
                console.error('❌ リクエスト処理エラー:', error);
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: false, error: 'Invalid request' }));
            }
        });
    } else {
        res.writeHead(404);
        res.end('Not found');
    }
});

server.listen(PORT, () => {
    console.log('');
    console.log('🚀 Email server running on port', PORT);
    console.log('📧 Recipient:', RECIPIENT_EMAIL);
    console.log('🌐 SMTP Server: sv16714.xserver.jp');
    console.log('');
});
