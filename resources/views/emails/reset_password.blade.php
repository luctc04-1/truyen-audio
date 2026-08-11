<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu - Truyện Audio</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #09090b;
            color: #fafafa;
            margin: 0;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 480px;
            margin: 0 auto;
            background-color: #111113;
            border: 1px solid #27272a;
            border-radius: 16px;
            padding: 36px 28px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
        }
        .brand {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%);
            color: #ffffff;
            font-size: 24px;
            font-weight: 800;
            border-radius: 12px;
            margin-bottom: 12px;
            box-shadow: 0 4px 14px rgba(168, 85, 247, 0.3);
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 6px 0;
        }
        .text {
            font-size: 14px;
            color: #a1a1aa;
            line-height: 1.6;
            margin: 0 0 20px 0;
            text-align: center;
        }
        .otp-box {
            background-color: rgba(168, 85, 247, 0.08);
            border: 1px dashed rgba(168, 85, 247, 0.4);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #c084fc;
            margin: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .expiry {
            font-size: 12px;
            color: #a1a1aa;
            margin-top: 10px;
            margin-bottom: 0;
        }
        .footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #27272a;
            font-size: 12px;
            color: #71717a;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <h1 class="title">Khôi Phục Mật Khẩu</h1>
        </div>
        
        <p class="text">
            Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản Truyện Audio. Sử dụng mã OTP bên dưới để hoàn tất quá trình xác minh:
        </p>

        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
            <p class="expiry">Mã OTP này có hiệu lực trong <strong>15 phút</strong>.</p>
        </div>

        <p class="text">
            Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này để bảo vệ tài khoản của bạn.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} Truyện Audio. All rights reserved.<br>
            Email này được gửi tự động, vui lòng không phản hồi trực tiếp.
        </div>
    </div>
</body>
</html>
