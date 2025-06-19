    <?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step1'])) {
        $_SESSION['test_name'] = $_POST['name'];
        $_SESSION['test_email'] = $_POST['email'];
        $_SESSION['test_phone'] = $_POST['phone'];
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>PHP Test | Infilogix</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #2c3e50, #4ca1af);
                color: #fff;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(15px);
                border-radius: 20px;
                padding: 40px 30px;
                box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
                max-width: 700px;
                width: 100%;
                animation: fadeIn 0.8s ease-in-out;
            }

            .form-control {
                background-color: rgba(255, 255, 255, 0.9);
            }

            .form-label {
                font-weight: 600;
            }

            .btn-custom {
                border-radius: 30px;
                padding: 10px 30px;
                font-weight: bold;
            }

            .progress {
                background-color: rgba(255, 255, 255, 0.2);
            }

            .progress-bar {
                transition: width 1s linear;
            }

            #timer {
                font-size: 20px;
                font-weight: bold;
                color: #00ffcc;
                text-shadow: 0 0 8px #00ffcc;
                animation: pulse 1s infinite;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes pulse {
                0% {
                    text-shadow: 0 0 5px #00ffcc;
                }

                50% {
                    text-shadow: 0 0 15px #00ffcc;
                }

                100% {
                    text-shadow: 0 0 5px #00ffcc;
                }
            }
        </style>

        <script>
            let timer;

            function startTimer() {
                let timeLeft = 30;
                const display = document.getElementById("timer");
                const progress = document.getElementById("progress-bar");
                display.innerText = "Time left: " + timeLeft + "s";

                timer = setInterval(function() {
                    timeLeft--;
                    display.innerText = "Time left: " + timeLeft + "s";
                    progress.style.width = (timeLeft / 30) * 100 + "%";

                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        document.getElementById("test-form").submit();
                    }
                }, 1000);
            }

            window.onload = function() {
                <?php if (isset($_SESSION['test_name'])): ?>
                    startTimer();
                <?php endif; ?>
            };
        </script>
    </head>

    <body>
        <div class="glass-card">
            <?php if (!isset($_SESSION['test_name'])): ?>
                <h3 class="text-center text-light mb-4">🚀 Welcome to the Infilogix PHP Test</h3>
                <form method="post" action="test.php">
                    <input type="hidden" name="step1" value="1">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="number" name="phone" class="form-control" required pattern="\d{10}" maxlength="10" title="Please enter a 10-digit phone number">
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-outline-light btn-custom">Start Test</button>
                    </div>
                </form>
            <?php else: ?>
                <h3 class="text-center text-warning mb-3">📝 PHP Developer Test</h3>
                <div class="mb-3 text-center">
                    <strong>Name:</strong> <?= htmlspecialchars($_SESSION['test_name']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($_SESSION['test_email']) ?><br>
                    <strong>Phone:</strong> <?= htmlspecialchars($_SESSION['test_phone']) ?>
                </div>
                <div class="mb-3">
                    <div class="progress" style="height: 20px;">
                        <div id="progress-bar" class="progress-bar bg-danger" style="width: 100%"></div>
                    </div>
                    <div class="text-center mt-2" id="timer"></div>
                </div>
                <form method="post" action="submit_test.php" id="test-form">
                    <?php
                    require_once __DIR__ . '/../includes/db.php';
                    $result = $conn->query("SELECT * FROM interview_questions ORDER BY id ASC");

                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="mb-3">';
                        echo "<label class='form-label'>$i. " . htmlspecialchars($row['question']) . "</label>";
                        echo "<textarea name='q$i' class='form-control' rows='3' required></textarea>";
                        echo '</div>';
                        $i++;
                    }
                    ?>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-custom">Submit Test</button>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </body>

    </html>