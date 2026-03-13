<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Classement Mondial - ACC</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link type="text/css" href="assets/css/argon.css" rel="stylesheet">
    <link type="text/css" href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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

        .ranking-header {
            text-align: center;
            padding: 60px 0 40px 0;
        }

        .ranking-title {
            font-size: 3rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #fff;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .ranking-subtitle {
            color: #8b949e;
            font-size: 1.1rem;
        }

        .podium-container {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .podium-card {
            background: rgba(22, 27, 34, 0.95);
            border: 1px solid #30363d;
            border-radius: 12px;
            text-align: center;
            padding: 20px;
            margin: 0 15px;
            width: 260px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s;
        }

        .podium-card:hover {
            transform: translateY(-10px);
        }

        .rank-1 {
            order: 2;
            height: 320px;
            border-color: #ffd700;
            z-index: 10;
        }

        .rank-2 {
            order: 1;
            height: 280px;
            border-color: #c0c0c0;
        }

        .rank-3 {
            order: 3;
            height: 260px;
            border-color: #cd7f32;
        }

        .medal-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .medal-gold {
            color: #ffd700;
            text-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
        }

        .medal-silver {
            color: #c0c0c0;
            text-shadow: 0 0 15px rgba(192, 192, 192, 0.4);
        }

        .medal-bronze {
            color: #cd7f32;
            text-shadow: 0 0 15px rgba(205, 127, 50, 0.4);
        }

        .team-avatar-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #21262d;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            border: 3px solid #30363d;
        }

        .team-name-lg {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            margin-bottom: 5px;
        }

        .team-points-lg {
            font-size: 1.2rem;
            color: #58a6ff;
            font-weight: bold;
        }

        .ranking-list {
            background-color: rgba(21, 26, 33, 0.95);
            border: 1px solid #30363d;
            border-radius: 12px;
            overflow: hidden;
        }

        .table-dark-custom th {
            background-color: #161b22;
            color: #8b949e;
            border-bottom: 1px solid #30363d;
            border-top: none;
        }

        .table-dark-custom td {
            border-bottom: 1px solid #21262d;
            color: #c9d1d9;
            vertical-align: middle;
        }

        .rank-cell {
            font-weight: bold;
            font-size: 1.1rem;
            color: #8b949e;
        }

        @media (max-width: 768px) {
            .podium-container {
                flex-direction: column;
                align-items: center;
            }

            .rank-1,
            .rank-2,
            .rank-3 {
                order: unset;
                height: auto;
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container flex-grow-1">
        <div class="ranking-header">
            <h1 class="ranking-title">Classement Mondial</h1>
            <p class="ranking-subtitle">Les meilleures équipes de la saison</p>
        </div>

        <div class="podium-container" id="podium-area">
            <div class="text-center w-100">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="ranking-list shadow">
                    <div class="table-responsive">
                        <table class="table align-items-center table-dark-custom mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 80px;">#</th>
                                    <th scope="col">Équipe</th>
                                    <th scope="col" class="text-center">Points</th>
                                    <th scope="col" class="text-right">Tendance</th>
                                </tr>
                            </thead>
                            <tbody id="ranking-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Utilisation de la nouvelle route globale /teams
            fetch('../API/teams')
                .then(response => {
                    if (!response.ok) throw new Error("Erreur réseau");
                    return response.json();
                })
                .then(data => {
                    // Si l'API renvoie une erreur ou un tableau vide
                    if (data.error || !Array.isArray(data)) {
                        throw new Error(data.error || "Format invalide");
                    }

                    // Tri manuel des équipes par points décroissants (Le JS fait le travail du ORDER BY DESC)
                    data.sort((a, b) => b.points - a.points);

                    renderPodium(data);
                    renderTable(data);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('podium-area').innerHTML = '<p class="text-danger text-center">Impossible de charger le classement.</p>';
                    document.getElementById('ranking-body').innerHTML = '';
                });
        });

        function renderPodium(teams) {
            const container = document.getElementById('podium-area');
            container.innerHTML = '';

            if (teams.length === 0) {
                container.innerHTML = '<p class="text-muted text-center w-100">Aucune équipe classée pour le moment.</p>';
                return;
            }

            const top3 = teams.slice(0, 3);

            if (top3[0]) container.innerHTML += createPodiumCard(top3[0], 1);
            if (top3[1]) container.innerHTML += createPodiumCard(top3[1], 2);
            if (top3[2]) container.innerHTML += createPodiumCard(top3[2], 3);
        }

        function createPodiumCard(team, rank) {
            let medalClass = '',
                medalIcon = '',
                rankLabel = '';

            if (rank === 1) {
                medalClass = 'rank-1';
                medalIcon = 'medal-gold fa-crown';
                rankLabel = 'Champion';
            }
            if (rank === 2) {
                medalClass = 'rank-2';
                medalIcon = 'medal-silver fa-medal';
                rankLabel = '2ème Place';
            }
            if (rank === 3) {
                medalClass = 'rank-3';
                medalIcon = 'medal-bronze fa-medal';
                rankLabel = '3ème Place';
            }

            const avatarLetter = (team.name || "?").charAt(0).toUpperCase();

            return `
                <div class="podium-card ${medalClass}">
                    <i class="fas ${medalIcon} medal-icon"></i>
                    <div class="team-avatar-lg">${avatarLetter}</div>
                    <div class="team-name-lg">${team.name}</div>
                    <div class="team-points-lg">${team.points || 0} pts</div>
                    <div class="text-muted small mt-2 text-uppercase font-weight-bold">${rankLabel}</div>
                </div>
            `;
        }

        function renderTable(teams) {
            const tbody = document.getElementById('ranking-body');
            const rest = teams.slice(3); // On affiche à partir du 4ème

            if (rest.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">La suite du classement apparaîtra ici.</td></tr>';
                return;
            }

            let html = '';
            rest.forEach((team, index) => {
                const realRank = index + 4;
                const avatarLetter = (team.name || "?").charAt(0).toUpperCase();

                html += `
                    <tr>
                        <td class="text-center rank-cell">#${realRank}</td>
                        <th scope="row">
                            <div class="media align-items-center">
                                <span class="avatar rounded-circle mr-3 bg-dark text-white font-weight-bold" style="border: 1px solid #30363d;">
                                    ${avatarLetter}
                                </span>
                                <div class="media-body">
                                    <span class="mb-0 text-sm font-weight-bold text-white">${team.name}</span>
                                </div>
                            </div>
                        </th>
                        <td class="text-center">
                            <span class="badge badge-success" style="font-size:0.9rem; background: rgba(46, 160, 67, 0.2); color: #2ecc71; border: 1px solid #2ecc71;">
                                ${team.points || 0} pts
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="text-primary mr-3"><i class="fa fa-minus"></i></span>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }
    </script>
</body>

</html>