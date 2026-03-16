<?php

require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM team ORDER BY points DESC");
    $classement = $stmt->fetchAll();
} catch (Exception $e) {
    $classement = [];
    $erreur_bdd = "Impossible de se connecter à la base de données.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Leaderboard des équipes</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Ton CSS original conservé */
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.55)), url('assets/img/Image_fond.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
        }

        .list-container {
            background-color: rgba(21, 26, 33, 0.95);
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 40px;
            margin-top: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .page-title {
            color: #58a6ff;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }

        .table {
            color: #8b949e;
        }

        .table thead th {
            border-bottom: 1px solid #30363d;
            color: #8b949e;
            text-transform: uppercase;
            font-size: 0.75rem;
            border-top: none;
        }

        .table td {
            border-bottom: 1px solid #21262d;
            vertical-align: middle;
            color: #f0f6fc;
        }

        .btn-view {
            color: #2ecc71;
            background: none;
            border: none;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="mb-4 text-center">🏆 Classement des Équipes</h2>

        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle">
                <thead class="bg-dark text-white">
                    <tr>
                        <th scope="col" class="text-center" style="width: 80px;">Rang</th>
                        <th scope="col">Équipe</th>
                        <th scope="col" class="text-center">Points</th>
                        <th scope="col" class="text-end">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rang = 1; ?>
                    <?php foreach ($classement as $team): ?>
                        <?php
                        $classRang = '';
                        $icone = $rang;
                        if ($rang == 1) {
                            $icone = '🥇';
                            $classRang = 'table-warning';
                        } elseif ($rang == 2) {
                            $icone = '🥈';
                        } elseif ($rang == 3) {
                            $icone = '🥉';
                        }
                        ?>
                        <tr class="<?= $classRang ?>">
                            <td class="text-center fs-4 font-weight-bold"><?= $icone ?></td>
                            <td>
                                <strong><?= htmlspecialchars($team['name']) ?></strong>
                            </td>
                            <td class="text-center fw-bold"><?= $team['points'] ?></td>
                            <td class="text-end">
                                <a href="#" class="btn-view">👁 Voir</a>
                            </td>
                        </tr>
                        <?php $rang++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>