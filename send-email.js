const http = require('http');
const https = require('https');
const url = require('url');

const PORT = 3001;
const RECIPIENT_EMAIL = 'kenji.noto@relight-rl.com';

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
        
        req.on('end', () => {
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
                console.log(`\n✉️  送信先: ${RECIPIENT_EMAIL}\n`);
                
                // In production, use a service like SendGrid, AWS SES, or Nodemailer with SMTP
                // For now, just log to console
                
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ 
                    success: true, 
                    message: 'お問い合わせを受け付けました',
                    recipient: RECIPIENT_EMAIL
                }));
                
            } catch (error) {
                console.error('Error processing request:', error);
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
    console.log(`\n🚀 Email server running on port ${PORT}`);
    console.log(`📧 Recipient: ${RECIPIENT_EMAIL}\n`);
});
