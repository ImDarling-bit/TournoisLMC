<?php

// Connexion à la base de données (configuration centralisée)
require_once __DIR__ . '/db.php';

// Récupération des infos du tournoi
$idT = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tournoi = null;

if ($idT > 0) {
    // On recupere les infos du tournoi et le nombre de participants actuels
    $sql = "SELECT t.*, 
            (SELECT COUNT(*) FROM team WHERE idT = t.id) as current_teams 
            FROM tournament t 
            WHERE t.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idT]);
    $tournoi = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$tournoi) {
    die("Tournoi introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($tournoi['name']) ?> - Aperçu</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6)), url('assets/img/Image_fond.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
        }

        .tournament-header-banner {
            background: linear-gradient(rgba(21, 26, 33, 0.2), rgba(21, 26, 33, 0.95)), url('assets/img/rocket-league-cover-680x89.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 12px 12px 0 0;
            padding: 40px;
            margin-top: 20px;
            border: 1px solid #30363d;
            border-bottom: none;
        }

        .tournament-main-card {
            background-color: rgba(21, 26, 33, 0.95);
            border: 1px solid #30363d;
            border-radius: 0 0 12px 12px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            min-height: 500px;
        }

        .status-dot {
            height: 10px;
            width: 10px;
            background-color: #58a6ff;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            box-shadow: 0 0 8px #58a6ff;
        }

        /* NAVIGATION */
        .nav-tabs-custom {
            background: #0d1117;
            border-radius: 8px;
            padding: 5px;
            border: 1px solid #30363d;
            margin-bottom: 30px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #8b949e;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #fff;
        }

        .nav-tabs-custom .nav-link.active {
            background-color: #21262d;
            color: #fff;
            border-radius: 6px;
        }

        .info-box {
            background: rgba(48, 54, 61, 0.3);
            border: 1px solid #30363d;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-label {
            color: #8b949e;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        <div class="tournament-header-banner">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <h1 class="font-weight-800 mb-0 text-white"><?= htmlspecialchars($tournoi['name']) ?></h1>
                    <p class="text-muted mb-2"><i class="fas fa-gamepad mr-2"></i> <?= htmlspecialchars($tournoi['game']) ?></p>
                    <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
                        <span class="status-dot"></span>
                        <span id="status-label" style="color: #58a6ff; font-weight: bold;"><?= htmlspecialchars($tournoi['status']) ?></span>
                        <span class="text-muted">
                            <i class="fas fa-users mr-1"></i> <?= $tournoi['current_teams'] ?> / <?= $tournoi['TeamCount'] ?> Équipes
                        </span>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <select id="status-select" style="background:#161b22;color:#fff;border:1px solid #30363d;border-radius:6px;padding:4px 10px;font-size:0.85rem;outline:none;cursor:pointer;">
                                <option value="Ouvert"   <?= $tournoi['status']==='Ouvert'   ? 'selected' : '' ?>>Ouvert</option>
                                <option value="En cours" <?= $tournoi['status']==='En cours' ? 'selected' : '' ?>>En cours</option>
                                <option value="Terminé"  <?= $tournoi['status']==='Terminé'  ? 'selected' : '' ?>>Terminé</option>
                            </select>
                            <button id="status-btn" onclick="updateStatus()" style="background:#238636;color:#fff;border:none;border-radius:6px;padding:4px 14px;font-size:0.85rem;font-weight:bold;cursor:pointer;">
                                Appliquer
                            </button>
                            <span id="status-msg" style="font-size:0.8rem;"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="tournament-main-card">

            <ul class="nav nav-tabs nav-tabs-custom justify-content-center">
                <li class="nav-item"><a class="nav-link active" href="#">Aperçu</a></li>
                <li class="nav-item"><a class="nav-link" href="DetailTournoiMatch.php?id=<?= $idT ?>">Matchs</a></li>
                <li class="nav-item"><a class="nav-link" href="DetailTournoiParticipants.php?id=<?= $idT ?>">Participants</a></li>
            </ul>

            <div class="row">
                <div class="col-md-8">
                    <h3 class="text-uppercase ls-1 mb-3">À propos du tournoi</h3>
                    <div class="info-box">
                        <p class="mb-0" style="line-height: 1.7;">
                            Bienvenue sur la page officielle du tournoi <strong><?= htmlspecialchars($tournoi['name']) ?></strong>.
                            Préparez-vous à affronter les meilleures équipes sur <?= htmlspecialchars($tournoi['game']) ?>.
                            <br><br>
                            Le format du tournoi se déroulera en phases éliminatoires. Assurez-vous que votre équipe est complète avant la date de début.
                            Bonne chance à tous les participants !
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <h3 class="text-uppercase ls-1 mb-3">Informations</h3>

                    <div class="info-box">
                        <div class="mb-3">
                            <div class="info-label">Jeu</div>
                            <div class="info-value"><?= htmlspecialchars($tournoi['game']) ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="info-label">Date de début</div>
                            <div class="info-value">
                                <i class="far fa-calendar-alt text-primary mr-2"></i>
                                <?= date('d/m/Y', strtotime($tournoi['DateDeDebut'])) ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="info-label">Date de fin</div>
                            <div class="info-value">
                                <i class="far fa-flag text-danger mr-2"></i>
                                <?= date('d/m/Y', strtotime($tournoi['DateDeFin'])) ?>
                            </div>
                        </div>

                        <div>
                            <div class="info-label">Organisateur</div>
                            <div class="info-value">Admin</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function updateStatus() {
            const token = localStorage.getItem('user_token');
            if (!token) {
                document.getElementById('status-msg').innerHTML = '<span style="color:#f85149;">Connectez-vous d\'abord.</span>';
                return;
            }

            const select = document.getElementById('status-select');
            const newStatus = select.value;
            const btn = document.getElementById('status-btn');
            btn.disabled = true;

            try {
                const res = await fetch(`${API_BASE_URL}/tournaments/<?= $idT ?>`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        name: <?= json_encode($tournoi['name']) ?>,
                        game: <?= json_encode($tournoi['game']) ?>,
                        status: newStatus
                    })
                });

                if (res.ok) {
                    document.getElementById('status-label').textContent = newStatus;
                    document.getElementById('status-msg').innerHTML = '<span style="color:#2ecc71;">✓ Mis à jour</span>';
                    setTimeout(() => document.getElementById('status-msg').innerHTML = '', 2500);
                } else {
                    const json = await res.json();
                    document.getElementById('status-msg').innerHTML = `<span style="color:#f85149;">${json.error || 'Erreur'}</span>`;
                }
            } catch (e) {
                document.getElementById('status-msg').innerHTML = '<span style="color:#f85149;">Erreur serveur.</span>';
            }

            btn.disabled = false;
        }
    </script>
</body>

</html>