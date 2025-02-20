<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #007bff;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
        }
        .body {
            padding: 20px;
            color: #333333;
        }
        .footer {
            background-color: #f1f1f1;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            color: #555555;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="cid:logo.png" alt="Platform Logo">
            <h1>ANNONCE</h1>
        </div>
        <div class="body">

            <p>{{$messageContent}}</p>
            <p>L'équipe {{ config('app.name') }} </p>
        </div>
        <div class="footer">
            <p>Besoin d'aide ? Contactez nous:</p>
            <p>Email: <a href="mailto:newsletter@bunker-shop.store">newsletter@bunker-shop.store</a> | WhatsApp: <a href="https://wa.me/695164183">(+237) 695-164-183</a></p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
