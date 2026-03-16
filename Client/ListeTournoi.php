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
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-live {
            background: rgba(88, 166, 255, 0.1);
            color: #58a6ff;
            border: 1px solid #58a6ff;
        }

        .status-end {
            background: rgba(46, 160, 67, 0.1);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }

        .status-open {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
            border: 1px solid #f1c40f;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="list-container">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="m-0" style="color:#58a6ff; font-weight: 800; letter-spacing: 1px;">TOURNOIS</h1>
                        <a href="Formulaire_tournoi.php" class="btn btn-success font-weight-bold">
                            + CRÉER
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center">
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
                            <tbody id="tournament-body">
                            </tbody>
                        </table>

                        <div id="loading" class="text-center py-4 text-muted">
                            Chargement des tournois...
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/argon.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = localStorage.getItem('user_token');
            const headers = {
                'Content-Type': 'application/json'
            };

            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            fetch(`${API_BASE_URL}/tournaments`, {
                    headers: headers
                })
                .then(response => {
                    if (!response.ok) throw new Error("Non autorisé ou erreur serveur");
                    return response.json();
                })
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    const tbody = document.getElementById('tournament-body');

                    if (data.error) {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${data.error}</td></tr>`;
                        return;
                    }

                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Aucun tournoi trouvé.</td></tr>';
                        return;
                    }

                    let html = '';
                    data.forEach(t => {
                        let sClass = 'status-live';
                        if (t.status === 'Terminé') sClass = 'status-end';
                        if (t.status === 'Ouvert') sClass = 'status-open';

                        // Formatage des dates de début et de fin
                        const dateStart = t.DateDeDebut ? new Date(t.DateDeDebut).toLocaleDateString('fr-FR') : 'Non définie';
                        const dateEnd = t.DateDeFin ? new Date(t.DateDeFin).toLocaleDateString('fr-FR') : 'Non définie';

                        html += `
                            <tr>
                                <td class="font-weight-bold text-white">${t.name}</td>
                                <td>${t.game}</td>
                                <td>${dateStart}</td>
                                <td>${dateEnd}</td>
                                <td><span class="status-badge ${sClass}">${t.status}</span></td>
                                <td>
                                    <a href="DetailTournoi.php?id=${t.id}" class="btn btn-sm btn-outline-info">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('loading').innerHTML = "<span class='text-danger'>Vous devez être connecté pour voir les tournois.</span>";
                });
        });
    </script>
</body>

</html>