<style>
    /* Style du Footer "Dark Gaming" */
    .footer-custom {
        background-color: #0d1117;
        /* Fond très sombre */
        border-top: 1px solid #30363d;
        /* Bordure subtile */
        padding: 50px 0 20px 0;
        margin-top: auto;
        /* Pousse le footer vers le bas si la page est courte */
        color: #8b949e;
        position: relative;
        z-index: 10;
    }

    .footer-title {
        color: #f0f6fc;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }

    .footer-text {
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .footer-link {
        color: #8b949e;
        text-decoration: none;
        transition: color 0.2s ease;
        display: block;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }

    .footer-link:hover {
        color: #58a6ff;
        /* Bleu au survol */
        text-decoration: none;
    }

    .footer-input {
        background-color: #161b22;
        border: 1px solid #30363d;
        color: #fff;
    }

    .footer-input:focus {
        background-color: #161b22;
        border-color: #58a6ff;
        color: #fff;
    }

    .copyright-row {
        border-top: 1px solid #21262d;
        margin-top: 40px;
        padding-top: 20px;
        font-size: 0.85rem;
        text-align: center;
    }
</style>

<footer class="footer-custom">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="footer-title">Tournois LMC</h5>
                <p class="footer-text">
                    La plateforme e-sport de référence. Participez à des tournois,
                    rejoignez des équipes et vivez votre passion de la compétition.
                </p>
            </div>

            <div class="col-md-2 mb-4 col-6">
                <h6 class="footer-title">Navigation</h6>
                <a href="index.php" class="footer-link">Accueil</a>
                <a href="ListeTournoi.php" class="footer-link">Tournois</a>
                <a href="Classement.php" class="footer-link">Classement</a>
            </div>
        </div>

        <div class="copyright-row">
            &copy; <?php echo date("Y"); ?> Tournois LMC. Tous droits réservés.
        </div>
    </div>
</footer>