<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Créer un Tournoi - ACC</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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

        .tournament-card {
            background-color: rgba(21, 26, 33, 0.95);
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 40px;
            margin-top: 50px;
            margin-bottom: 60px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .tournament-title {
            color: #58a6ff;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control {
            background-color: #0d1117 !important;
            border: 1px solid #30363d !important;
            color: #fff !important;
        }

        .form-control:focus {
            border-color: #58a6ff !important;
        }

        .form-control:disabled {
            background-color: #161b22 !important;
            color: #8b949e !important;
            cursor: not-allowed;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        label {
            color: #8b949e;
            font-size: 0.85rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .btn-create {
            background-color: #238636;
            border: none;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            padding: 15px;
            width: 100%;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .btn-create:hover {
            background-color: #2ea043;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10">
                <div class="tournament-card">
                    <h1 class="tournament-title">Nouveau Tournoi</h1>

                    <div id="alert-container"></div>

                    <form id="createTForm">
                        <div class="form-group">
                            <label>Nom du tournoi</label>
                            <input type="text" class="form-control" id="t-name" placeholder="Ex: Rocket League Summer Cup" required>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Jeu vidéo imposé</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="background:#161b22; border-color:#30363d;"><i class="fas fa-car-side"></i></span>
                                    </div>
                                    <input type="text" class="form-control font-weight-bold" value="Rocket League" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de début</label>
                                    <input type="date" class="form-control" id="t-start" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de fin</label>
                                    <input type="date" class="form-control" id="t-end" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre d'équipes max</label>
                                    <input type="number" class="form-control" id="t-count" value="16" min="2" max="64" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select class="form-control" id="t-status">
                                        <option value="Ouvert">Ouvert (Inscriptions)</option>
                                        <option value="En cours">En cours (Tournoi démarré)</option>
                                        <option value="Terminé">Terminé</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-create">Créer le tournoi</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!localStorage.getItem('user_token')) {
                alert("Vous devez être connecté pour créer un tournoi.");
                window.location.href = 'Connexion.php';
            }
        });

        document.getElementById('createTForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const token = localStorage.getItem('user_token');
            const data = {
                name: document.getElementById('t-name').value,
                game: "Rocket League",
                teamcount: document.getElementById('t-count').value,
                status: document.getElementById('t-status').value,
                DateDeDebut: document.getElementById('t-start').value,
                DateDeFin: document.getElementById('t-end').value
            };

            try {
                const res = await fetch('../API/tournaments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(data)
                });

                const json = await res.json();

                if (res.ok) {
                    document.getElementById('alert-container').innerHTML = '<div class="alert alert-success">Tournoi créé avec succès !</div>';
                    setTimeout(() => window.location.href = "ListeTournoi.php", 1000);
                } else {
                    document.getElementById('alert-container').innerHTML = `<div class="alert alert-danger">${json.error || "Erreur lors de la création"}</div>`;
                }
            } catch (e) {
                console.error(e);
            }
        });
    </script>
</body>

</html>