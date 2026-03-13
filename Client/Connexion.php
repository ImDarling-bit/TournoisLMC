<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Connexion - Aux Claviers Citoyens</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Même style que Inscription */
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.55)), url('assets/img/Image_fond.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
            min-height: 100vh;
        }

        .auth-card {
            background-color: rgba(21, 26, 33, 0.95);
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 40px;
            margin-top: 80px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .auth-title {
            color: #58a6ff;
            text-align: center;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .form-control {
            background-color: #0d1117 !important;
            border: 1px solid #30363d !important;
            color: white !important;
        }

        .form-control:focus {
            border-color: #58a6ff !important;
        }

        .btn-auth {
            background-color: #58a6ff;
            color: #0d1117;
            width: 100%;
            padding: 12px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            margin-top: 20px;
        }

        .btn-auth:hover {
            background-color: #79c0ff;
        }

        .text-link {
            color: #58a6ff;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card">
                    <h2 class="auth-title">Connexion</h2>

                    <div id="alert-container"></div>

                    <form id="loginForm">
                        <div class="form-group mb-3">
                            <div class="input-group input-group-merge input-group-alternative">
                                <input class="form-control" placeholder="Email" type="email" id="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group input-group-merge input-group-alternative">
                                <input class="form-control" placeholder="Mot de passe" type="password" id="pass" required>
                            </div>
                        </div>

                        <div class="custom-control custom-control-alternative custom-checkbox">
                            <input class="custom-control-input" id=" customCheckLogin" type="checkbox">
                            <label class="custom-control-label" for=" customCheckLogin">
                                <span class="text-muted">Se souvenir de moi</span>
                            </label>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-auth">Se connecter</button>
                        </div>
                    </form>

                    <div class="row mt-3">
                        <div class="col-6">
                            <a href="#" class="text-light"><small>Mot de passe oublié ?</small></a>
                        </div>
                        <div class="col-6 text-right">
                            <a href="Inscription.php" class="text-link"><small>Créer un compte</small></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/argon.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('pass').value;

            try {
                const response = await fetch('../API/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('user_token', data.access_token);
                    localStorage.setItem('user_name', data.user_name || 'Joueur');

                    window.location.href = 'Profil.php';
                } else {
                    alert(data.error_description || "Erreur de connexion : Mauvais identifiants");
                }
            } catch (error) {
                console.error("Erreur serveur :", error);
                alert("Impossible de joindre le serveur.");
            }
        });
    </script>
</body>

</html>