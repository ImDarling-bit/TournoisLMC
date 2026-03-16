<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription - Aux Claviers Citoyens</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.55)), url('assets/img/Image_fond.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .auth-card {
            background: rgba(21, 26, 33, 0.95);
            border: 1px solid #30363d;
            padding: 40px;
            border-radius: 12px;
            margin-top: 50px;
            margin-bottom: 60px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .form-control {
            background-color: #0d1117 !important;
            border: 1px solid #30363d !important;
            color: white !important;
        }

        .form-control:focus {
            border-color: #58a6ff !important;
        }

        .auth-title {
            color: #58a6ff;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card">
                    <h2 class="text-center auth-title">Créer un compte</h2>
                    <div id="alert-box"></div>

                    <form id="regForm">
                        <div class="form-group mb-3">
                            <input class="form-control" placeholder="Nom d'utilisateur" type="text" id="name" required>
                        </div>
                        <div class="form-group mb-3">
                            <input class="form-control" placeholder="Email" type="email" id="email" required>
                        </div>
                        <div class="form-group mb-4">
                            <input class="form-control" placeholder="Mot de passe" type="password" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-block font-weight-bold">S'inscrire</button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="Connexion.php" class="text-primary"><small>Déjà un compte ? Se connecter</small></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="assets/js/argon.js"></script>
    <script>
        document.getElementById('regForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // CORRECTION : On envoie "password" au lieu de "pass" à l'API
            const data = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };

            try {
                const res = await fetch('../API/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const json = await res.json();

                if (res.ok) {
                    document.getElementById('alert-box').innerHTML = '<div class="alert alert-success">Succès ! Redirection...</div>';
                    setTimeout(() => window.location.href = 'Connexion.php', 1500);
                } else {
                    document.getElementById('alert-box').innerHTML = `<div class="alert alert-danger">${json.error_description || 'Erreur lors de l\'inscription'}</div>`;
                }
            } catch (err) {
                console.error(err);
                document.getElementById('alert-box').innerHTML = `<div class="alert alert-danger">Impossible de joindre le serveur.</div>`;
            }
        });
    </script>
</body>

</html>