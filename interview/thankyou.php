
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thank You | Infilogix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .thankyou-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }

        .thankyou-card h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .thankyou-card p {
            font-size: 1.2rem;
            margin-bottom: 25px;
        }

        .btn-home {
            padding: 10px 30px;
            font-size: 16px;
        }

        .checkmark {
            font-size: 60px;
            color: #00ffad;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="thankyou-card">
        <div class="checkmark">✔️</div>
        <h1>Thank You!</h1>
        <p>Your test has been successfully submitted.<br>
            We appreciate your time and effort.</p>
        <a href="test.php" class="btn btn-outline-light btn-home">Back to Home</a>
    </div>
</body>

</html>