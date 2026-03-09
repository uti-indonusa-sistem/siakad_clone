<?php
session_start();
if (isset($_SESSION['monitoring_user']) && $_SESSION['monitoring_user'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Monitoring Eksekutif</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
        }

        .login-card {
            background-color: var(--card-bg);
            padding: 2.5rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #334155;
            background-color: #0f172a;
            color: white;
            font-size: 1rem;
            box-sizing: border-box; /* Important for width: 100% to work correctly with padding */
            transition: all 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        button {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 1rem;
        }

        button:hover {
            background-color: var(--primary-dark);
        }

        .error-msg {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: none;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        /* Loading animation */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        button.loading .btn-text {
            display: none;
        }
        
        button.loading .spinner {
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">Executive Monitoring</div>
        <div class="subtitle">Silakan login untuk mengakses dashboard</div>

        <div id="errorMsg" class="error-msg"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Masukan ID Pengguna">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukan Kata Sandi">
            </div>

            <button type="submit" id="loginBtn">
                <span class="btn-text">Masuk</span>
                <div class="spinner"></div>
            </button>
        </form>
        
        <div style="margin-top: 2rem; font-size: 0.75rem; color: #475569;">
            Powered by Indonusa SIAKAD
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('loginBtn');
            const errorDiv = document.getElementById('errorMsg');
            
            btn.classList.add('loading');
            errorDiv.style.display = 'none';

            const formData = new FormData(this);

            fetch('auth_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                    btn.classList.remove('loading');
                }
            })
            .catch(error => {
                errorDiv.textContent = 'Terjadi kesalahan sistem.';
                errorDiv.style.display = 'block';
                btn.classList.remove('loading');
            });
        });
    </script>
</body>
</html>
