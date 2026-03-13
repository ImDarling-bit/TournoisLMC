<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Détails Tournoi</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6)), url('assets/img/Image_fond.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
        }

        /* HEADER */
        .tournament-header-banner {
            background: linear-gradient(rgba(21, 26, 33, 0.2), rgba(21, 26, 33, 0.95)), url('assets/img/RL.jpg');
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

        /* NAVIGATION*/
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

        .nav-tabs-custom .nav-link.active {
            background-color: #21262d;
            color: #fff;
            border-radius: 6px;
        }

        /* TEAM CARDS */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .team-card {
            background: rgba(48, 54, 61, 0.2);
            border: 1px solid #30363d;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: 0.3s;
        }

        .team-card:hover {
            transform: translateY(-5px);
            background: rgba(48, 54, 61, 0.4);
            border-color: #58a6ff;
        }

        .team-logo {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            margin-bottom: 15px;
            object-fit: cover;
            border: 1px solid #30363d;
        }

        .team-name {
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
            text-transform: uppercase;
        }

        .btn-inscription {
            background-color: #238636;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            padding: 10px 20px;
        }
    </style>
</head>

<?php
$paths = [__DIR__ . '/db.php', __DIR__ . '/../db.php', __DIR__ . '/../API/db.php', $_SERVER['DOCUMENT_ROOT'] . '/API/db.php'];
$pdo = null;
foreach ($paths as $path) { if (file_exists($path)) { require_once $path; if (isset($pdo)) break; } }

$idT = isset($_GET['id']) ? intval($_GET['id']) : 0;
$teams = [];

if ($idT > 0 && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM team WHERE idT = ?");
    $stmt->execute([$idT]);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<body>
    <?php include 'navbar.php';?>

    <div class="container">
        <div class="tournament-header-banner">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <h1 class="font-weight-800 mb-0" style="color: #ffffffff">Tournoi</h1>
                    <div>
                        <span class="status-dot"></span> 
                        <span style="color: #58a6ff; font-weight: bold;">En cours</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tournament-main-card">
            <ul class="nav nav-tabs nav-tabs-custom justify-content-center">
                <li class="nav-item"><a class="nav-link" href="DetailTournoi.php?id=<?= $idT ?>">Aperçu</a></li>
                <li class="nav-item"><a class="nav-link" href="DetailTournoiMatch.php?id=<?= $idT ?>">Matchs</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Participants</a></li>
            </ul>

            <h3 class="text-center mb-4 text-uppercase font-weight-700" style="letter-spacing: 1px;">Équipes inscrites</h3>

            <div class="team-grid">
                <?php if (empty($teams)): ?>
                    <p class="text-center text-muted col-12">Aucune équipe inscrite.</p>
                <?php else: ?>
                    <?php foreach ($teams as $team): ?>
                        <div class="team-card">
                            <?php if (!empty($team['image_path'])): ?>
                                <img src="<?= htmlspecialchars($team['image_path']) ?>" class="team-logo">
                            <?php else: ?>
                                <div style="width:60px;height:60px;margin:0 auto 15px;background:#21262d;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                    <span>T</span>
                                </div>
                            <?php endif; ?>
                            <div class="team-name"><?= htmlspecialchars($team['name']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="assets/js/argon.js"></script>
</body>


</html>