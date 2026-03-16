<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Aux Claviers Citoyens</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.55)), url('assets/img/Image_fond.png');
            background-size: cover;
            background-attachment: fixed;
            color: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .profile-header {
            background-image: url('assets/img/Image_fond.png');
            background-size: cover;
            background-position: center top;
            min-height: 300px;
            position: relative;
        }

        .profile-header .mask {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(21, 26, 33, 0.6), rgba(21, 26, 33, 1));
        }

        .profile-card {
            background-color: #0d1117;
            border: 1px solid #30363d;
            border-radius: 12px;
            margin-top: -100px;
            margin-bottom: 60px;
            padding: 30px;
            position: relative;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #58a6ff;
            border: 5px solid #0d1117;
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="profile-header d-flex align-items-center">
        <div class="mask"></div>
        <div class="container d-flex align-items-center z-2">
            <div class="col-lg-7 col-md-10">
                <h1 class="display-2 text-white" id="welcome-msg">Chargement...</h1>
                <p class="text-white mt-0 mb-5">Vous retrouverez vos informations depuis cet espace.</p>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-xl-8 order-xl-2 mx-auto">
                <div class="profile-card text-center">
                    <div class="user-avatar" id="avatar-letter">?</div>

                    <div class="d-flex justify-content-between mb-4">
                        <button class="btn btn-sm btn-danger" onclick="logout()">Déconnexion</button>
                    </div>

                    <div class="mt-5 pt-3">
                        <h3 class="text-white mb-1" id="p-name" style="font-size: 2rem;">...</h3>
                        <div class="h6 font-weight-300 text-muted" id="p-email" style="font-size: 1.1rem; margin-bottom: 30px;">...</div>

                        <hr class="my-4" style="border-color: #30363d;" />

                        <div class="text-left bg-dark p-4 rounded border border-secondary" style="border-color: #30363d !important; background-color: #161b22 !important;">
                            <h4 class="text-white mb-3"><i class="ni ni-trophy mr-2 text-primary"></i> Rôle Organisateur</h4>
                            <p class="text-muted text-sm">
                                Votre compte vous permet de créer et d'administrer des tournois.
                                Rendez-vous dans la section <a href="ListeTournoi.php" class="text-primary font-weight-bold">Tournois</a> pour inscrire des équipes et mettre à jour les résultats.
                            </p>
                            <a href="Formulaire_tournoi.php" class="btn btn-success mt-2">+ Créer un nouveau tournoi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = localStorage.getItem('user_token');

            if (!token) {
                window.location.href = 'Connexion.php';
                return;
            }

            fetch(`${API_BASE_URL}/auth/me`, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    if (res.status === 401) {
                        logout();
                        throw new Error("Session expirée");
                    }
                    return res.json();
                })
                .then(response => {
                    const user = response.data;

                    if (user) {
                        document.getElementById('welcome-msg').innerText = "Bonjour, " + (user.name || "Joueur");
                        document.getElementById('p-name').innerText = user.name || "Anonyme";
                        document.getElementById('p-email').innerText = user.email;

                        const firstLetter = (user.name || "U").charAt(0).toUpperCase();
                        document.getElementById('avatar-letter').innerText = firstLetter;
                    }
                })
                .catch(err => {
                    console.error("Erreur de récupération profil:", err);
                });
        });

        function logout() {
            localStorage.removeItem('user_token');
            localStorage.removeItem('user_name');
            window.location.href = 'index.php';
        }
    </script>
</body>

</html>