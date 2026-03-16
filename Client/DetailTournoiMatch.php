<?php
// Connexion à la base de données (configuration centralisée)
require_once __DIR__ . '/db.php';

$idT = isset($_GET['id']) ? intval($_GET['id']) : 0;

// GÉNÉRATION AUTOMATIQUE DE L'ARBRE 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_bracket') {
    // Compter les équipes inscrites
    $stmtTeams = $pdo->prepare("SELECT COUNT(*) FROM team WHERE idT = ?");
    $stmtTeams->execute([$idT]);
    $teamCount = (int)$stmtTeams->fetchColumn();

    if ($teamCount > 16) {
        die("Erreur : Un tournoi ne peut pas dépasser 16 équipes.");
    }
    if ($teamCount < 2) {
        die("Erreur : Il faut au moins 2 équipes inscrites pour générer un arbre.");
    }

    // Déterminer la taille de l'arbre
    $bracketSize = 2;
    if ($teamCount > 8) $bracketSize = 16;
    elseif ($teamCount > 4) $bracketSize = 8;
    elseif ($teamCount > 2) $bracketSize = 4;

    // Définir les rounds nécessaires
    $roundsToCreate = [];
    if ($bracketSize == 16) $roundsToCreate = ['Huitièmes de finale' => 8, 'Quarts de finale' => 4, 'Demi-finales' => 2, 'Finale' => 1];
    elseif ($bracketSize == 8) $roundsToCreate = ['Quarts de finale' => 4, 'Demi-finales' => 2, 'Finale' => 1];
    elseif ($bracketSize == 4) $roundsToCreate = ['Demi-finales' => 2, 'Finale' => 1];
    elseif ($bracketSize == 2) $roundsToCreate = ['Finale' => 1];

    // Insérer les rounds et les matchs vides dans la base
    foreach ($roundsToCreate as $roundName => $matchCount) {
        $stmtR = $pdo->prepare("INSERT INTO `round` (`name`, `idT`) VALUES (?, ?)");
        $stmtR->execute([$roundName, $idT]);
        $roundId = $pdo->lastInsertId();

        for ($i = 0; $i < $matchCount; $i++) {
            $stmtM = $pdo->prepare("INSERT INTO `match` (`team1_id`, `team2_id`, `team1_point`, `team2_point`, `idR`) VALUES (NULL, NULL, 0, 0, ?)");
            $stmtM->execute([$roundId]);
        }
    }
    header("Location: DetailTournoiMatch.php?id=" . $idT);
    exit;
}

// LOGIQUE DE MISE À JOUR DES SCORES ET PROGRESSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_match') {
    $matchId = (int)$_POST['match_id'];
    $t1 = !empty($_POST['team1_id']) ? (int)$_POST['team1_id'] : null;
    $t2 = !empty($_POST['team2_id']) ? (int)$_POST['team2_id'] : null;
    $p1 = (int)$_POST['team1_point'];
    $p2 = (int)$_POST['team2_point'];

    // Récupérer l'ancien état du match pour comparer le vainqueur
    $stmtOld = $pdo->prepare("SELECT team1_id, team2_id, team1_point, team2_point FROM `match` WHERE id=?");
    $stmtOld->execute([$matchId]);
    $oldMatch = $stmtOld->fetch(PDO::FETCH_ASSOC);

    $oldWinnerId = null;
    if ($oldMatch && $oldMatch['team1_id'] && $oldMatch['team2_id']
        && $oldMatch['team1_point'] != $oldMatch['team2_point']
        && ($oldMatch['team1_point'] > 0 || $oldMatch['team2_point'] > 0)) {
        $oldWinnerId = ($oldMatch['team1_point'] > $oldMatch['team2_point'])
            ? (int)$oldMatch['team1_id']
            : (int)$oldMatch['team2_id'];
    }

    // Mise à jour du match
    $stmtUpdate = $pdo->prepare("UPDATE `match` SET team1_id=?, team2_id=?, team1_point=?, team2_point=? WHERE id=?");
    $stmtUpdate->execute([$t1, $t2, $p1, $p2, $matchId]);

    // Déterminer le nouveau vainqueur
    $newWinnerId = null;
    if ($t1 && $t2 && $p1 !== $p2 && ($p1 > 0 || $p2 > 0)) {
        $newWinnerId = ($p1 > $p2) ? $t1 : $t2;
    }

    // Mise à jour des points en temps réel
    if ($oldWinnerId !== $newWinnerId) {
        if ($oldWinnerId) {
            // Retirer le point à l'ancien vainqueur (sans descendre sous 0)
            $pdo->prepare("UPDATE team SET points = GREATEST(0, points - 1) WHERE id=?")
                ->execute([$oldWinnerId]);
        }
        if ($newWinnerId) {
            // Ajouter 1 point au nouveau vainqueur
            $pdo->prepare("UPDATE team SET points = points + 1 WHERE id=?")
                ->execute([$newWinnerId]);
        }
    }

    // Logique de progression vers le round suivant
    if ($t1 && $t2 && $p1 !== $p2 && ($p1 > 0 || $p2 > 0)) {
        $winnerId = ($p1 > $p2) ? $t1 : $t2;

        $stmtM = $pdo->prepare("SELECT idR FROM `match` WHERE id=?");
        $stmtM->execute([$matchId]);
        $currentRoundId = $stmtM->fetchColumn();

        $stmtAllM = $pdo->prepare("SELECT id FROM `match` WHERE idR=? ORDER BY id ASC");
        $stmtAllM->execute([$currentRoundId]);
        $matchesInRound = $stmtAllM->fetchAll(PDO::FETCH_COLUMN);
        $matchIndex = array_search($matchId, $matchesInRound);

        $stmtNextR = $pdo->prepare("SELECT id FROM `round` WHERE idT=? AND id > ? ORDER BY id ASC LIMIT 1");
        $stmtNextR->execute([$idT, $currentRoundId]);
        $nextRoundId = $stmtNextR->fetchColumn();

        if ($nextRoundId) {
            $stmtNextM = $pdo->prepare("SELECT id FROM `match` WHERE idR=? ORDER BY id ASC");
            $stmtNextM->execute([$nextRoundId]);
            $matchesInNextRound = $stmtNextM->fetchAll(PDO::FETCH_COLUMN);

            $nextMatchIndex = (int)floor($matchIndex / 2);

            if (isset($matchesInNextRound[$nextMatchIndex])) {
                $nextMatchId = $matchesInNextRound[$nextMatchIndex];
                $isTeam1Slot = ($matchIndex % 2 === 0);

                if ($isTeam1Slot) {
                    $pdo->prepare("UPDATE `match` SET team1_id=? WHERE id=?")->execute([$winnerId, $nextMatchId]);
                } else {
                    $pdo->prepare("UPDATE `match` SET team2_id=? WHERE id=?")->execute([$winnerId, $nextMatchId]);
                }
            }
        }
    }
    header("Location: DetailTournoiMatch.php?id=" . $idT);
    exit;
}

$tournoi = null;
$allTeams = [];
$teamCount = 0;

try {
    $stmtT = $pdo->prepare("SELECT * FROM tournament WHERE id = ?");
    $stmtT->execute([$idT]);
    $tournoi = $stmtT->fetch(PDO::FETCH_ASSOC);

    if (!$tournoi) die("Tournoi introuvable.");

    // Récupérer toutes les équipes
    $stmtTeams = $pdo->prepare("SELECT id, name FROM team WHERE idT = ?");
    $stmtTeams->execute([$idT]);
    $allTeams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);
    $teamCount = count($allTeams);

    // Récupérer l'arbre des matchs
    $sql = "
        SELECT 
            r.id as round_id, r.name as round_name,
            m.id as match_id, m.team1_id, m.team2_id, m.team1_point, m.team2_point,
            t1.name as team1_name, t1.image_path as team1_image, 
            t2.name as team2_name, t2.image_path as team2_image
        FROM round r
        JOIN `match` m ON m.idR = r.id 
        LEFT JOIN team t1 ON m.team1_id = t1.id
        LEFT JOIN team t2 ON m.team2_id = t2.id
        WHERE r.idT = :idT
        ORDER BY r.id ASC, m.id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['idT' => $idT]);

    $bracket = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bracket[$row['round_name']][] = $row;
    }
} catch (Exception $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Matchs - <?= htmlspecialchars($tournoi['name']) ?></title>

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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            min-height: 600px;
        }

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

        .tournament-bracket-container {
            display: flex;
            align-items: stretch;
            justify-content: center;
            gap: 40px;
            padding-bottom: 20px;
            overflow-x: auto;
        }

        .round-column {
            display: flex;
            flex-direction: column;
            width: 280px;
            flex-shrink: 0;
        }

        .round-header {
            text-align: center;
            color: #8b949e;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border-bottom: 1px solid #30363d;
            padding-bottom: 10px;
            height: 40px;
            flex-shrink: 0;
        }

        .match-list {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .match-item {
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex: 1;
            position: relative;
            padding: 10px 0;
        }

        .match-card {
            background-color: #0d1117;
            border: 1px solid #30363d;
            border-radius: 8px;
            padding: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s, border-color 0.2s;
            position: relative;
            z-index: 2;
            width: 100%;
            margin: 0 !important;
        }

        .match-card:hover {
            border-color: #58a6ff;
        }

        .round-column:not(:last-child) .match-item:nth-child(odd)::after {
            content: '';
            position: absolute;
            right: -20px;
            top: 50%;
            bottom: 0;
            width: 20px;
            border-top: 2px solid #58a6ff;
            border-right: 2px solid #58a6ff;
            border-top-right-radius: 6px;
            z-index: 1;
        }

        .round-column:not(:last-child) .match-item:nth-child(even)::after {
            content: '';
            position: absolute;
            right: -20px;
            top: 0;
            bottom: 50%;
            width: 20px;
            border-bottom: 2px solid #58a6ff;
            border-right: 2px solid #58a6ff;
            border-bottom-right-radius: 6px;
            z-index: 1;
        }

        .round-column:not(:last-child) .match-item:only-child::after {
            border: none !important;
            border-top: 2px solid #58a6ff !important;
            bottom: auto !important;
            top: 50% !important;
            border-radius: 0 !important;
        }

        .round-column:not(:first-child) .match-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 50%;
            width: 20px;
            border-top: 2px solid #58a6ff;
            z-index: 1;
        }

        .team-row {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-bottom: 1px solid #21262d;
            height: 60px;
        }

        .team-row:last-child {
            border-bottom: none;
        }

        .team-logo {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            background-color: #21262d;
            border: 1px solid #30363d;
        }

        .team-select {
            flex-grow: 1;
            background: #161b22;
            color: #fff;
            border: 1px dashed #30363d;
            padding: 5px;
            border-radius: 5px;
            font-size: 0.85rem;
            outline: none;
            cursor: pointer;
            transition: 0.2s;
            appearance: none;
            -webkit-appearance: none;
        }

        .team-select:hover {
            border-color: #58a6ff;
            background: #21262d;
        }

        .team-select:focus {
            border-color: #58a6ff;
            border-style: solid;
        }

        .team-select option {
            background: #0d1117;
            color: #fff;
        }

        .score-input {
            width: 45px;
            background: #161b22;
            color: #fff;
            border: 1px solid #30363d;
            border-radius: 4px;
            padding: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 0.9rem;
            outline: none;
            margin-left: 10px;
        }

        .score-input:focus {
            border-color: #58a6ff;
        }

        .save-match-btn {
            width: 100%;
            background: #21262d;
            color: #58a6ff;
            border: none;
            padding: 6px;
            border-top: 1px solid #30363d;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 0 0 8px 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .save-match-btn:hover {
            background: #58a6ff;
            color: #0d1117;
        }

        .winner .team-select {
            color: #fff;
            font-weight: bold;
            border: 1px solid transparent;
            background: transparent;
            pointer-events: none;
        }

        .winner .score-input {
            color: #2ecc71;
            border-color: #2ecc71;
            pointer-events: none;
        }

        .loser .team-select {
            color: #484f58;
            border: 1px solid transparent;
            background: transparent;
            pointer-events: none;
        }

        .loser .score-input {
            color: #484f58;
            border-color: #30363d;
            pointer-events: none;
        }

        .champion-card {
            border: 2px solid #f1c40f;
            box-shadow: 0 0 15px rgba(241, 196, 15, 0.4);
            text-align: center;
            padding: 20px;
            background: linear-gradient(45deg, #0d1117, #1a1e23);
        }

        .champion-card .team-name {
            font-size: 1.2rem;
            color: #f1c40f;
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        .btn-generate {
            background-color: #2ea043;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(46, 160, 67, 0.3);
        }

        .btn-generate:hover {
            background-color: #238636;
            transform: translateY(-2px);
        }

        .btn-generate:disabled {
            background-color: #21262d;
            color: #8b949e;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
            border: 1px solid #30363d;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid mb-5" style="max-width: 95%;">
        <div class="tournament-header-banner">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <h1 class="font-weight-800 mb-0 text-white"><?= htmlspecialchars($tournoi['name']) ?></h1>
                    <p class="text-muted mb-2"><i class="fas fa-gamepad mr-2"></i> <?= htmlspecialchars($tournoi['game']) ?></p>
                </div>
            </div>
        </div>

        <div class="tournament-main-card">
            <ul class="nav nav-tabs nav-tabs-custom justify-content-center">
                <li class="nav-item"><a class="nav-link" href="DetailTournoi.php?id=<?= $idT ?>">Aperçu</a></li>
                <li class="nav-item"><a class="nav-link active" href="DetailTournoiMatch.php?id=<?= $idT ?>">Matchs</a></li>
                <li class="nav-item"><a class="nav-link" href="DetailTournoiParticipants.php?id=<?= $idT ?>">Participants</a></li>
            </ul>

            <h3 class="text-center mb-5 text-uppercase font-weight-700" style="letter-spacing: 1px;">Arbre du Tournoi</h3>

            <?php if (empty($bracket)): ?>
                <div class="text-center py-5">
                    <div class="p-5 d-inline-block rounded" style="background: rgba(13, 17, 23, 0.8); border: 1px solid #30363d; max-width: 500px;">
                        <i class="fas fa-sitemap fa-4x mb-4" style="color: #58a6ff;"></i>
                        <h3 class="text-white mb-3">L'arbre n'est pas encore généré</h3>
                        <p class="text-muted mb-4">Il y a actuellement <strong><?= $teamCount ?> équipe(s)</strong> inscrite(s) à ce tournoi (Maximum 16).</p>

                        <form method="POST">
                            <input type="hidden" name="action" value="generate_bracket">
                            <?php if ($teamCount >= 2 && $teamCount <= 16): ?>
                                <button type="submit" class="btn-generate">
                                    <i class="fas fa-magic mr-2"></i> Générer l'arbre automatiquement
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-generate" disabled>
                                    <i class="fas fa-magic mr-2"></i> Générer l'arbre
                                </button>
                                <?php if ($teamCount < 2): ?>
                                    <p class="text-danger mt-3 small"><i class="fas fa-exclamation-triangle"></i> Il faut au moins 2 équipes inscrites.</p>
                                <?php elseif ($teamCount > 16): ?>
                                    <p class="text-danger mt-3 small"><i class="fas fa-exclamation-triangle"></i> Il y a trop d'équipes (Maximum 16).</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="tournament-bracket-container">
                    <?php foreach ($bracket as $roundName => $matches): ?>
                        <div class="round-column">
                            <div class="round-header"><?= htmlspecialchars($roundName) ?></div>
                            <div class="match-list">
                                <?php foreach ($matches as $match): ?>
                                    <?php
                                    $s1 = (int)$match['team1_point'];
                                    $s2 = (int)$match['team2_point'];
                                    $isFinished = ($s1 !== $s2 && ($s1 > 0 || $s2 > 0));
                                    $class1 = $isFinished ? (($s1 > $s2) ? 'winner' : 'loser') : '';
                                    $class2 = $isFinished ? (($s2 > $s1) ? 'winner' : 'loser') : '';
                                    ?>
                                    <div class="match-item">
                                        <form method="POST" class="match-card">
                                            <input type="hidden" name="action" value="update_match">
                                            <input type="hidden" name="match_id" value="<?= $match['match_id'] ?>">

                                            <div class="team-row <?= $class1 ?>">
                                                <?php if (!empty($match['team1_image'])): ?><img src="<?= htmlspecialchars($match['team1_image']) ?>" class="team-logo"><?php endif; ?>
                                                <select name="team1_id" class="team-select" data-round="<?= $match['round_id'] ?>" <?= $isFinished ? 'tabindex="-1"' : '' ?>>
                                                    <option value="">+ Sélectionner une équipe</option>
                                                    <?php foreach ($allTeams as $t): ?>
                                                        <option value="<?= $t['id'] ?>" <?= ($match['team1_id'] == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="number" name="team1_point" class="score-input" value="<?= $s1 ?>" min="0" <?= $isFinished ? 'readonly' : '' ?>>
                                            </div>

                                            <div class="team-row <?= $class2 ?>">
                                                <?php if (!empty($match['team2_image'])): ?><img src="<?= htmlspecialchars($match['team2_image']) ?>" class="team-logo"><?php endif; ?>
                                                <select name="team2_id" class="team-select" data-round="<?= $match['round_id'] ?>" <?= $isFinished ? 'tabindex="-1"' : '' ?>>
                                                    <option value="">+ Sélectionner une équipe</option>
                                                    <?php foreach ($allTeams as $t): ?>
                                                        <option value="<?= $t['id'] ?>" <?= ($match['team2_id'] == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="number" name="team2_point" class="score-input" value="<?= $s2 ?>" min="0" <?= $isFinished ? 'readonly' : '' ?>>
                                            </div>

                                            <?php if (!$isFinished): ?>
                                                <button type="submit" class="save-match-btn"><i class="fas fa-save"></i> Enregistrer</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php
                    $lastRoundName = array_key_last($bracket);
                    $lastMatch = end($bracket[$lastRoundName]);
                    if ($lastMatch && ($lastMatch['team1_point'] > 0 || $lastMatch['team2_point'] > 0) && $lastMatch['team1_point'] != $lastMatch['team2_point']) {
                        $winnerName = ($lastMatch['team1_point'] > $lastMatch['team2_point']) ? $lastMatch['team1_name'] : $lastMatch['team2_name'];
                        $winnerImage = ($lastMatch['team1_point'] > $lastMatch['team2_point']) ? $lastMatch['team1_image'] : $lastMatch['team2_image'];
                    ?>
                        <div class="round-column">
                            <div class="round-header" style="color:#f1c40f; border-bottom-color: #f1c40f;">VAINQUEUR</div>
                            <div class="match-list">
                                <div class="match-item">
                                    <div class="match-card champion-card">
                                        <i class="fas fa-trophy fa-3x" style="color: #f1c40f; margin-bottom: 15px;"></i>
                                        <?php if (!empty($winnerImage)): ?>
                                            <br><img src="<?= htmlspecialchars($winnerImage) ?>" class="team-logo" style="width:60px; height:60px; margin:0 auto; border-radius:8px;">
                                        <?php endif; ?>
                                        <span class="team-name"><?= htmlspecialchars($winnerName) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selects = document.querySelectorAll('.team-select');

            function updateSelectConstraints() {
                const rounds = {};
                selects.forEach(select => {
                    const roundId = select.dataset.round;
                    if (!rounds[roundId]) rounds[roundId] = [];
                    rounds[roundId].push(select);
                });
                for (const roundId in rounds) {
                    const selectsInRound = rounds[roundId];
                    const selectedValues = selectsInRound.map(s => s.value).filter(v => v !== "");
                    selectsInRound.forEach(select => {
                        Array.from(select.options).forEach(option => {
                            if (option.value === "") return;
                            if (selectedValues.includes(option.value) && select.value !== option.value) {
                                option.disabled = true;
                                option.style.color = "#484f58";
                            } else {
                                option.disabled = false;
                                option.style.color = "#fff";
                            }
                        });
                    });
                }
            }
            updateSelectConstraints();
            selects.forEach(select => select.addEventListener('change', updateSelectConstraints));
        });
    </script>
</body>

</html>