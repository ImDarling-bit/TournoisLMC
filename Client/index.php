<?php
require_once __DIR__ . '/db.php';

$count_tournaments = (int)$pdo->query("SELECT COUNT(*) FROM tournament")->fetchColumn();
$count_users       = (int)$pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$count_teams       = (int)$pdo->query("SELECT COUNT(*) FROM team")->fetchColumn();

$last_tournaments = $pdo->query(
    "SELECT id, name, game, status FROM tournament ORDER BY id DESC LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);
?>
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
                <tbody>
                    <?php if (empty($last_tournaments)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Aucun tournoi pour le moment.</td></tr>
                    <?php else: foreach ($last_tournaments as $t):
                        $statusColor = $t['status'] === 'Terminé' ? '#2ecc71' : ($t['status'] === 'Ouvert' ? '#f1c40f' : '#58a6ff');
                    ?>
                        <tr>
                            <th scope="row">
                                <div class="media align-items-center">
                                    <div class="avatar rounded-circle mr-3" style="background-color:#21262d;color:#58a6ff;">
                                        <i class="ni ni-trophy"></i>
                                    </div>
                                    <div class="media-body">
                                        <span class="mb-0 text-sm font-weight-bold text-white"><?= htmlspecialchars($t['name']) ?></span>
                                    </div>
                                </div>
                            </th>
                            <td><?= htmlspecialchars($t['game']) ?></td>
                            <td>
                                <span class="badge badge-dot mr-4">
                                    <i style="background-color:<?= $statusColor ?> !important;" class="bg-success"></i>
                                    <span style="color:<?= $statusColor ?>"><?= htmlspecialchars($t['status']) ?></span>
                                </span>
                            </td>
                            <td>
                                <a href="DetailTournoi.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script>
        // Compteurs animés alimentés par PHP (visibles sans connexion)
        function animateValue(id, end, duration) {
            const obj = document.getElementById(id);
            if (!obj || !end) return;
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerHTML = Math.floor(progress * end);
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        }

        document.addEventListener('DOMContentLoaded', () => {
            animateValue('count-tournaments', <?= $count_tournaments ?>, 1000);
            animateValue('count-users',       <?= $count_users ?>,       1000);
            animateValue('count-teams',       <?= $count_teams ?>,       1000);
        });
    </script>
</body>

</html>