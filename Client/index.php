<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Accueil - Aux Claviers Citoyens</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            background-color: #0d1117;
            color: #fff;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .hero-section {
            position: relative;
            background: linear-gradient(rgba(13, 17, 23, 0.8), rgba(13, 17, 23, 0.95)), url('assets/img/Image_fond.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding-top: 80px;
            padding-bottom: 150px;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .hero-title span {
            color: #58a6ff;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #8b949e;
            max-width: 700px;
            margin: 0 auto 40px auto;
            line-height: 1.6;
        }

        /* STATS CARDS */
        .stats-container {
            margin-top: -100px;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            background: rgba(22, 27, 34, 0.95);
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, border-color 0.3s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            border-color: #58a6ff;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #58a6ff, #1f6feb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
            line-height: 1;
        }

        .stat-label {
            color: #8b949e;
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: 5px;
        }

        /* TABLE */
        .table-dark-custom {
            background-color: #161b22;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #30363d;
        }

        .table-dark-custom th {
            background-color: #21262d;
            border-color: #30363d;
            color: #8b949e;
        }

        .table-dark-custom td {
            border-color: #30363d;
            color: #c9d1d9;
            vertical-align: middle;
        }

        /* BUTTONS */
        .btn-cta {
            background-color: #238636;
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            transition: 0.3s;
        }

        .btn-cta:hover {
            transform: translateY(-2px);
            background-color: #2ea043;
            color: white;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">Tournois <span>LMC</span></h1>
            <p class="hero-subtitle">
                La plateforme e-sport de référence. Rejoignez des centaines de joueurs,
                créez vos équipes et participez à des tournois exclusifs sur vos jeux préférés.
            </p>
            <div>
                <a href="Inscription.php" class="btn btn-cta mr-3">Rejoindre la compétition</a>
                <a href="ListeTournoi.php" class="btn btn-outline-secondary px-4 py-3 font-weight-bold" style="border-radius:8px;">Voir les tournois</a>
            </div>
        </div>
    </div>

    <div class="container stats-container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="stat-card">
                    <i class="fas fa-trophy stat-icon"></i>
                    <div class="stat-number" id="count-tournaments">0</div>
                    <div class="stat-label">Tournois Organisés</div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-number" id="count-users">0</div>
                    <div class="stat-label">Joueurs Inscrits</div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card">
                    <i class="fas fa-gamepad stat-icon"></i>
                    <div class="stat-number" id="count-teams">0</div>
                    <div class="stat-label">Équipes Actives</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-2 pt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title m-0" style="font-size: 1.8rem;">Derniers Tournois</h2>
                <p class="text-muted m-0">Rejoignez-les avant la clôture des inscriptions.</p>
            </div>
            <a href="ListeTournoi.php" class="btn btn-sm btn-primary">Tout voir</a>
        </div>

        <div class="table-responsive table-dark-custom shadow">
            <table class="table align-items-center table-flush">
                <thead class="thead-dark">
                    <tr>
                        <th>Nom du tournoi</th>
                        <th>Jeu</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="home-tournament-list">
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = localStorage.getItem('user_token');
            const tbody = document.getElementById('home-tournament-list');

            // Si pas de token, on ne peut pas charger les données de l'API protégée
            if (!token) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Veuillez vous <a href="Connexion.php" class="text-primary">connecter</a> pour voir les données.</td></tr>';
                return;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            };

            // 1. Charger les TOURNOIS
            fetch(`${API_BASE_URL}/tournaments`, {
                    headers: headers
                })
                .then(res => {
                    if (res.status === 401) throw new Error("Non autorisé");
                    if (!res.ok) throw new Error("Erreur réseau");
                    return res.json();
                })
                .then(data => {
                    animateValue("count-tournaments", 0, data.length || 0, 1000);

                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3">Aucun tournoi récent.</td></tr>';
                        return;
                    }

                    // On prend les 3 derniers tournois
                    const lastThree = data.slice(0, 3);
                    let html = '';

                    lastThree.forEach(t => {
                        let statusColor = t.status === 'Terminé' ? '#2ecc71' : '#58a6ff';

                        html += `
                            <tr>
                                <th scope="row">
                                    <div class="media align-items-center">
                                        <div class="avatar rounded-circle mr-3" style="background-color: #21262d; color: #58a6ff;">
                                            <i class="ni ni-trophy"></i>
                                        </div>
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold text-white">${t.name}</span>
                                        </div>
                                    </div>
                                </th>
                                <td>${t.game}</td>
                                <td>
                                    <span class="badge badge-dot mr-4">
                                      <i class="bg-success" style="background-color: ${statusColor} !important;"></i> 
                                      <span class="status" style="color:${statusColor}">${t.status}</span>
                                    </span>
                                </td>
                                <td>
                                    <a href="DetailTournoi.php?id=${t.id}" class="btn btn-sm btn-outline-primary">Voir</a>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                })
                .catch(err => {
                    console.error("Erreur tournois:", err);
                    if (err.message === "Non autorisé") {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-warning">Session expirée. Veuillez vous reconnecter.</td></tr>';
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Impossible de charger les tournois.</td></tr>';
                    }
                });

            // 2. Charger les UTILISATEURS (Joueurs inscrits)
            fetch(`${API_BASE_URL}/users`, {
                    headers: headers
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.error) {
                        animateValue("count-users", 0, data.length || 0, 1000);
                    }
                })
                .catch(err => console.error("Erreur utilisateurs:", err));

            // 3. Charger les ÉQUIPES (Équipes actives)
            fetch(`${API_BASE_URL}/teams`, {
                    headers: headers
                })
                .then(res => res.json())
                .then(data => {
                    const teams = Array.isArray(data) ? data : (data.data ?? []);
                    if (!data.error) {
                        animateValue("count-teams", 0, teams.length || 0, 1000);
                    }
                })
                .catch(err => console.error("Erreur équipes:", err));
        });

        // Fonction d'animation fluide des nombres
        function animateValue(id, start, end, duration) {
            if (!end) end = 0;
            const obj = document.getElementById(id);
            if (!obj) return;
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerHTML = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
    </script>
</body>

</html>