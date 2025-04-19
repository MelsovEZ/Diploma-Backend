<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f7f7f7;
            padding: 40px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        h1 {
            font-size: 36px;
            font-weight: 700;
            color: #2C3E50;
            margin-bottom: 12px;
            text-align: center;
        }

        p {
            font-size: 16px;
            color: #7F8C8D;
            margin-bottom: 30px;
            text-align: center;
        }

        .code-container {
            background-color: #EAF1FB;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 32px;
            text-align: center;
        }

        .verification-code {
            font-size: 48px;
            font-weight: bold;
            color: #3498DB;
        }

        .copy-button {
            background-color: #3498DB;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin: 0 auto;
            text-decoration: none;
            text-align: center;
        }

        .copy-button:hover {
            background-color: #2980B9;
        }
    </style>
</head>
<body>
<div class="container">
    @if(isset($logo))
        <img src="{{ $logo }}" alt="Company Logo" style="height: 150px; display: block; margin-left: auto; margin-right: auto;">
    @else
        <p>Изображение не доступно.</p>
    @endif



    <h1>Подтвердите Ваш Email</h1>
    <p>Пожалуйста, введите следующий код для завершения регистрации. Код истечет через 10 минут.</p>

    <div class="code-container">
        <h2 id="textToCopy" class="verification-code">{{ $code }}</h2>
    </div>

    <p>Если вы не запрашивали этот код, просто проигнорируйте это письмо.</p>

    <button class="copy-button" onclick="copyText()">Copy Text</button>
</div>
</body>
<script>
    function copyText() {
        const textElement = document.getElementById("textToCopy");
        const text = textElement.textContent;

        const tempTextArea = document.createElement("textarea");
        tempTextArea.value = text;
        document.body.appendChild(tempTextArea);
        tempTextArea.select();
        document.execCommand("copy");
        document.body.removeChild(tempTextArea);
    }
</script>
</html>
