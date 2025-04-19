<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение Email</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f7f7f7;
            padding: 40px;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            padding: 40px;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2C3E50;
            text-align: center;
            border-bottom: 2px solid #dcdcdc;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        p {
            font-size: 16px;
            color: #555555;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .highlight {
            background-color: #F0F4F8;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 18px;
            text-align: center;
            font-weight: bold;
            color: #2E86C1;
        }

        .code-container {
            background-color: #F0F4F8;
            padding: 1px;
            border-radius: 12px;
            margin-bottom: 32px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .verification-code {
            font-size: 42px;
            font-weight: bold;
            color: #2E86C1;
            letter-spacing: 6px;
        }

        .copy-instruction {
            text-align: center;
            color: #444;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            color: #666;
            margin-top: 40px;
        }

        .footer p {
            margin: 0;
            font-size: 14px;
        }

        hr {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 30px 0;
        }

        .footer span {
            color: #5fa8d3;
            font-weight: bold;
        }

        /* Add table for centering the image */
        .image-wrapper {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="image-wrapper">
        <img src="https://diploma-bucket.s3.fr-par.scw.cloud/Ainala_src/logo.png" alt="Company Logo" style="height: 150px; user-select: none; pointer-events: none; -webkit-user-drag: none; -moz-user-drag: none;">
    </div>

    <h1>Подтверждение Email</h1>

    <p>
        Привет! Благодарим за регистрацию на нашем сайте Ainala. Для завершения процесса регистрации, пожалуйста, введите код подтверждения, который мы отправили вам на почту.
    </p>

    <div class="highlight">
        Код подтверждения действителен в течение 10 минут.
    </div>

    <div class="code-container">
        <h2 id="textToCopy" class="verification-code">{{ $code }}</h2>
    </div>

    <div class="copy-instruction">
        <p><strong>Скопируйте код и вставьте его в соответствующее поле на сайте.</strong></p>
    </div>

    <hr>

    <div class="footer">
        <p>Если у вас возникли вопросы, свяжитесь с нами по адресу: <span>ainalacompany@gmail.com</span></p>
        <p>С уважением, Команда Ainala</p>
    </div>

</div>
</body>
</html>
