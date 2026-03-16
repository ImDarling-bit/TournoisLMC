<?php
$config = require __DIR__ . '/config.php';

$api_base_url = isset($config['api_url']) ? rtrim($config['api_url'], '/') : '../API';
?>

<style>
    .navbar-custom {
        background-color: rgba(21, 26, 33, 0.95);
        border-bottom: 1px solid #30363d;
        padding: 0.8rem 1rem;
        backdrop-filter: blur(10px);
    }

    .navbar-brand {
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #f0f6fc !important;
    }

    .navbar-brand span {
        color: #58a6ff;
    }

    .nav-link {
        color: #8b949e !important;
        font-weight: 600;
        margin-right: 15px;
        transition: 0.2s;
    }

    .nav-link:hover {
        color: #58a6ff !important;
    }

    .btn-nav-login {
        color: #f0f6fc;
        border: 1px solid #30363d;
        background: transparent;
        font-weight: 600;
        padding: 6px 15px;
        border-radius: 6px;
        margin-right: 10px;
    }

    .btn-nav-login:hover {
        background-color: #21262d;
        border-color: #8b949e;
        color: #fff;
    }

    .btn-nav-register {
        background-color: #238636;
        color: white;
        border: none;
        font-weight: bold;
        padding: 6px 15px;
        border-radius: 6px;
    }

    .btn-nav-register:hover {
        background-color: #2ea043;
        color: white;
    }

    .user-profile-link {
        display: flex;
        align-items: center;
        color: #f0f6fc;
        font-weight: bold;
        margin-right: 20px;
        text-decoration: none;
        transition: 0.2s;
    }

    .user-profile-link:hover {
        color: #58a6ff;
        text-decoration: none;
    }

    .user-avatar-small {
        width: 32px;
        height: 32px;
        background-color: #58a6ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d1117;
        font-weight: bold;
        margin-right: 10px;
    }
</style>

<script>
    const API_BASE_URL = "<?php echo $api_base_url; ?>";
</script>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">Tournois <span>LMC</span></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a href="index.php" class="nav-link">Accueil</a></li>
                <li class="nav-item"><a href="ListeTournoi.php" class="nav-link">Tournois</a></li>
                <li class="nav-item"><a href="Leaderboard.php" class="nav-link">Classement</a></li>
            </ul>
            <ul class="navbar-nav align-items-center">
                <div id="auth-guest" style="display: flex;" class="align-items-center">
                    <li class="nav-item"><a href="Connexion.php" class="btn btn-nav-login btn-sm">Connexion</a></li>
                    <li class="nav-item"><a href="Inscription.php" class="btn btn-nav-register btn-sm">Inscription</a></li>
                </div>
                <div id="auth-logged" style="display: none;" class="align-items-center">
                    <li class="nav-item">
                        <a href="Profil.php" class="user-profile-link">
                            <span class="user-avatar-small" id="nav-avatar-letter">U</span>
                            <span id="nav-username-display">Mon Profil</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" onclick="navLogout(event)" class="nav-link text-danger" style="margin-right: 0;">
                            <i class="ni ni-button-power"></i> Déconnexion
                        </a>
                    </li>
                </div>
            </ul>
        </div>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const token = localStorage.getItem('user_token');
        const userName = localStorage.getItem('user_name');
        const guestBlock = document.getElementById('auth-guest');
        const loggedBlock = document.getElementById('auth-logged');
        const nameDisplay = document.getElementById('nav-username-display');
        const avatarLetter = document.getElementById('nav-avatar-letter');

        if (token) {
            guestBlock.style.setProperty('display', 'none', 'important');
            loggedBlock.style.setProperty('display', 'flex', 'important');
            if (userName) {
                nameDisplay.textContent = userName;
                avatarLetter.textContent = userName.charAt(0).toUpperCase();
            }
        } else {
            guestBlock.style.setProperty('display', 'flex', 'important');
            loggedBlock.style.setProperty('display', 'none', 'important');
        }
    });

    function navLogout(e) {
        e.preventDefault();
        localStorage.removeItem('user_token');
        localStorage.removeItem('user_name');
        window.location.href = 'index.php';
    }
</script>