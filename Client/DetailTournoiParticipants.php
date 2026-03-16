<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Participants - Tournoi</title>

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
        }

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
            margin-bottom: 40px;
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
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .team-card {
            background: rgba(48, 54, 61, 0.2);
            border: 1px solid #30363d;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: 0.3s;
            position: relative;
        }

        .team-card:hover {
            transform: translateY(-5px);
            background: rgba(48, 54, 61, 0.4);
            border-color: #58a6ff;
        }

        .team-avatar {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            margin: 0 auto 15px;
            background: #21262d;
            border: 1px solid #30363d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #58a6ff;
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

        .btn-delete-team {
            position: absolute;
            top: 8px;
            right: 8px;
            background: transparent;
            border: none;
            color: #484f58;
            font-size: 0.85rem;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 4px;
            transition: 0.2s;
            line-height: 1;
        }

        .btn-delete-team:hover {
            color: #f85149;
            background: rgba(248, 81, 73, 0.1);
        }

        /* FORMULAIRE AJOUT */
        .add-team-bar {
            display: none;
            align-items: center;
            gap: 10px;
            background: rgba(13, 17, 23, 0.8);
            border: 1px solid #30363d;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .add-team-bar.visible {
            display: flex;
        }

        .add-team-bar input {
            flex: 1;
            background: #161b22;
            border: 1px solid #30363d;
            color: #fff;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.9rem;
            outline: none;
            transition: 0.2s;
        }

        .add-team-bar input:focus {
            border-color: #58a6ff;
        }

        .add-team-bar input::placeholder {
            color: #484f58;
        }

        .btn-add-confirm {
            background: #238636;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .btn-add-confirm:hover { background: #2ea043; }

        .btn-add-cancel {
            background: transparent;
            color: #8b949e;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 8px 14px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-add-cancel:hover {
            color: #fff;
            border-color: #8b949e;
        }

        .btn-add-team {
            background: #238636;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: bold;
            font-size: 0.85rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-add-team:hover { background: #2ea043; }

        #alert-participants {
            margin-bottom: 15px;
        }
    </style>
</head>

<?php
// Connexion à la base de données (configuration centralisée)
require_once __DIR__ . '/db.php';

$idT = isset($_GET['id']) ? intval($_GET['id']) : 0;
$teams = [];

if ($idT > 0 && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM team WHERE idT = ?");
    $stmt->execute([$idT]);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="tournament-header-banner">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <h1 class="font-weight-800 mb-0" style="color: #fff">Tournoi #<?= $idT ?></h1>
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

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="m-0 text-uppercase font-weight-700" style="letter-spacing: 1px;">
                    Équipes inscrites
                    <span class="text-muted" style="font-size:0.9rem; font-weight:400;">(<?= count($teams) ?>)</span>
                </h3>
                <button class="btn-add-team" onclick="toggleAddForm()">
                    <i class="fas fa-plus mr-1"></i> Ajouter une équipe
                </button>
            </div>

            <div id="alert-participants"></div>

            <div class="add-team-bar" id="add-team-bar">
                <input type="text" id="new-team-name" placeholder="Nom de l'équipe..." maxlength="100" autocomplete="off">
                <button class="btn-add-confirm" onclick="submitAddTeam()">
                    <i class="fas fa-check mr-1"></i> Ajouter
                </button>
                <button class="btn-add-cancel" onclick="toggleAddForm()">Annuler</button>
            </div>

            <div class="team-grid" id="team-grid">
                <?php if (empty($teams)): ?>
                    <p class="text-center text-muted col-12" id="empty-msg">Aucune équipe inscrite.</p>
                <?php else: ?>
                    <?php foreach ($teams as $team): ?>
                        <div class="team-card" id="team-card-<?= $team['id'] ?>">
                            <button class="btn-delete-team" onclick="deleteTeam(<?= $team['id'] ?>, '<?= htmlspecialchars(addslashes($team['name'])) ?>')" title="Supprimer">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php if (!empty($team['image_path'])): ?>
                                <img src="<?= htmlspecialchars($team['image_path']) ?>" class="team-logo">
                            <?php else: ?>
                                <div class="team-avatar">
                                    <?= strtoupper(mb_substr($team['name'], 0, 1)) ?>
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
    <script>
        const TOURNAMENT_ID = <?= $idT ?>;
        const token = localStorage.getItem('user_token');

        function toggleAddForm() {
            const bar = document.getElementById('add-team-bar');
            bar.classList.toggle('visible');
            if (bar.classList.contains('visible')) {
                document.getElementById('new-team-name').focus();
            }
        }

        document.getElementById('new-team-name').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') submitAddTeam();
            if (e.key === 'Escape') toggleAddForm();
        });

        async function submitAddTeam() {
            const input = document.getElementById('new-team-name');
            const name = input.value.trim();

            if (!name) {
                showAlert('Veuillez saisir un nom d\'équipe.', 'warning');
                input.focus();
                return;
            }

            if (!token) {
                showAlert('Vous devez être connecté.', 'danger');
                return;
            }

            try {
                const res = await fetch(`${API_BASE_URL}/tournaments/${TOURNAMENT_ID}/teams`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ name: name })
                });

                const json = await res.json();

                if (!res.ok) {
                    showAlert(json.error || 'Erreur lors de l\'ajout.', 'danger');
                    return;
                }

                const team = json.data;
                addTeamCard(team.id, name);
                input.value = '';
                toggleAddForm();
                showAlert('Équipe "' + name + '" ajoutée avec succès !', 'success');
                updateCount(1);

            } catch (err) {
                console.error(err);
                showAlert('Impossible de joindre le serveur.', 'danger');
            }
        }

        async function deleteTeam(teamId, teamName) {
            if (!confirm(`Supprimer l'équipe "${teamName}" ?`)) return;

            if (!token) {
                showAlert('Vous devez être connecté.', 'danger');
                return;
            }

            try {
                const res = await fetch(`${API_BASE_URL}/tournaments/${TOURNAMENT_ID}/teams/${teamId}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token }
                });

                if (!res.ok) {
                    const json = await res.json();
                    showAlert(json.error || 'Erreur lors de la suppression.', 'danger');
                    return;
                }

                document.getElementById('team-card-' + teamId)?.remove();
                showAlert('Équipe supprimée.', 'success');
                updateCount(-1);

            } catch (err) {
                console.error(err);
                showAlert('Impossible de joindre le serveur.', 'danger');
            }
        }

        function addTeamCard(id, name) {
            document.getElementById('empty-msg')?.remove();

            const grid = document.getElementById('team-grid');
            const letter = name.charAt(0).toUpperCase();
            const card = document.createElement('div');
            card.className = 'team-card';
            card.id = 'team-card-' + id;
            card.innerHTML = `
                <button class="btn-delete-team" onclick="deleteTeam(${id}, '${name.replace(/'/g, "\\'")}')" title="Supprimer">
                    <i class="fas fa-times"></i>
                </button>
                <div class="team-avatar">${letter}</div>
                <div class="team-name">${name.toUpperCase()}</div>
            `;
            grid.appendChild(card);
        }

        function updateCount(delta) {
            const heading = document.querySelector('h3 .text-muted');
            if (!heading) return;
            const current = parseInt(heading.textContent.replace(/[()]/g, '')) || 0;
            heading.textContent = '(' + Math.max(0, current + delta) + ')';
        }

        function showAlert(msg, type) {
            const box = document.getElementById('alert-participants');
            box.innerHTML = `<div class="alert alert-${type} py-2">${msg}</div>`;
            setTimeout(() => box.innerHTML = '', 3000);
        }
    </script>
</body>

</html>
