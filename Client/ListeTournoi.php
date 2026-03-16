<?php
require_once __DIR__ . '/db.php';

$tournaments = [];
try {
    $stmt = $pdo->query("SELECT id, name, game, status, DateDeDebut, DateDeFin FROM tournament ORDER BY id DESC");
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = "Impossible de charger les tournois.";
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Liste des Tournois</title>

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

        .list-container {
            background-color: rgba(21, 26, 33, 0.95);
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 40px;
            margin-top: 30px;
            margin-bottom: 60px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .table td {
            border-color: #21262d;
            color: #f0f6fc;
            vertical-align: middle;
        }

        .table th {
            border-color: #30363d;
            color: #8b949e;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-live    { background: rgba(88,166,255,0.1);  color: #58a6ff; border: 1px solid #58a6ff; }
        .status-end     { background: rgba(46,160,67,0.1);   color: #2ecc71; border: 1px solid #2ecc71; }
        .status-open    { background: rgba(241,196,15,0.1);  color: #f1c40f; border: 1px solid #f1c40f; }

        /* Filtres */
        .filter-bar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-btn {
            background: transparent;
            border: 1px solid #30363d;
            color: #8b949e;
            border-radius: 20px;
            padding: 5px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .filter-btn:hover { border-color: #58a6ff; color: #58a6ff; }

        .filter-btn.active {
            background: #58a6ff;
            border-color: #58a6ff;
            color: #0d1117;
        }

        .filter-btn.active.f-open    { background: #f1c40f; border-color: #f1c40f; }
        .filter-btn.active.f-live    { background: #58a6ff; border-color: #58a6ff; }
        .filter-btn.active.f-end     { background: #2ecc71; border-color: #2ecc71; }

        tr.hidden-row { display: none; }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="list-container">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="m-0" style="color:#58a6ff; font-weight:800; letter-spacing:1px;">TOURNOIS</h1>
                        <a href="Formulaire_tournoi.php" class="btn btn-success font-weight-bold">+ CRÉER</a>
                    </div>

                    <?php if (isset($db_error)): ?>
                        <p class="text-danger text-center"><?= htmlspecialchars($db_error) ?></p>
                    <?php else: ?>

                    <div class="table-responsive">
                        <table class="table align-items-center" id="tournaments-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Jeu</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tournaments)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Aucun tournoi pour le moment.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tournaments as $t): ?>
                                        <?php
                                            $status = htmlspecialchars($t['status']);
                                            $sClass = 'status-live';
                                            if ($t['status'] === 'Terminé') $sClass = 'status-end';
                                            if ($t['status'] === 'Ouvert')  $sClass = 'status-open';

                                            $dateStart = !empty($t['DateDeDebut'])
                                                ? date('d/m/Y', strtotime($t['DateDeDebut']))
                                                : '—';
                                            $dateEnd = !empty($t['DateDeFin'])
                                                ? date('d/m/Y', strtotime($t['DateDeFin']))
                                                : '—';
                                        ?>
                                        <tr data-status="<?= $status ?>">
                                            <td class="font-weight-bold text-white"><?= htmlspecialchars($t['name']) ?></td>
                                            <td><?= htmlspecialchars($t['game']) ?></td>
                                            <td><?= $dateStart ?></td>
                                            <td><?= $dateEnd ?></td>
                                            <td><span class="status-badge <?= $sClass ?>"><?= $status ?></span></td>
                                            <td>
                                                <a href="DetailTournoi.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-info">Voir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script>
        function filterStatus(btn, status) {
            // Mettre à jour les boutons actifs
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Afficher/masquer les lignes
            document.querySelectorAll('#tournaments-table tbody tr[data-status]').forEach(row => {
                if (status === 'tous' || row.dataset.status === status) {
                    row.classList.remove('hidden-row');
                } else {
                    row.classList.add('hidden-row');
                }
            });

            // Message si aucun résultat visible
            const visible = document.querySelectorAll('#tournaments-table tbody tr[data-status]:not(.hidden-row)');
            const empty   = document.getElementById('no-result-msg');
            if (visible.length === 0) {
                if (!empty) {
                    const tr = document.createElement('tr');
                    tr.id = 'no-result-msg';
                    tr.innerHTML = '<td colspan="6" class="text-center text-muted py-3">Aucun tournoi avec ce statut.</td>';
                    document.querySelector('#tournaments-table tbody').appendChild(tr);
                }
            } else {
                empty?.remove();
            }
        }
    </script>
</body>

</html>
