<?php
require_once __DIR__ . '/auth.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$csrfToken = csrf_token();
$scriptNonce = function_exists('csp_nonce') ? csp_nonce() : '';
$nonceAttribute = $scriptNonce !== '' ? ' nonce="' . htmlspecialchars($scriptNonce, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';
?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary app-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-layer-group me-2"></i>Inventarverwaltung
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-microchip me-1"></i>Inventar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'koffer.php' ? 'active' : ''; ?>" href="koffer.php">
                            <i class="fas fa-toolbox me-1"></i>Sets &amp; Koffer
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['ausleihe.php', 'batch_operations.php'], true) ? 'active' : ''; ?>" href="#" id="loanDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-exchange-alt me-1"></i>Ausleihe
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="loanDropdown">
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'ausleihe.php' ? 'active' : ''; ?>" href="ausleihe.php">
                                    <i class="fas fa-plus-circle me-1"></i>Neue Ausleihe
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'batch_operations.php' ? 'active' : ''; ?>" href="batch_operations.php">
                                    <i class="fas fa-layer-group me-1"></i>Massenvorgänge
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'uebersicht.php' ? 'active' : ''; ?>" href="uebersicht.php">
                            <i class="fas fa-chart-bar me-1"></i>Übersicht
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <button id="darkModeToggle" class="btn btn-outline-light btn-icon" type="button" title="Darstellung umschalten" aria-label="Darstellung umschalten">
                        <i class="fas fa-moon"></i>
                    </button>
                    <?php if (is_logged_in()): ?>
                        <span class="navbar-text d-none d-lg-inline">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars(current_username(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                        </span>
                        <form method="POST" action="logout.php" class="d-inline">
                            <?php echo csrf_input(); ?>
                            <button class="btn btn-outline-light" type="submit">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <script<?php echo $nonceAttribute; ?>>
        window.RaspiVerwaltung = window.RaspiVerwaltung || {};
        window.RaspiVerwaltung.csrfToken = <?php echo json_encode($csrfToken, JSON_UNESCAPED_SLASHES); ?>;

        if (window.jQuery) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': window.RaspiVerwaltung.csrfToken
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;

            if (!darkModeToggle) {
                return;
            }

            const applyIcon = function() {
                darkModeToggle.innerHTML = body.getAttribute('data-bs-theme') === 'dark'
                    ? '<i class="fas fa-sun"></i>'
                    : '<i class="fas fa-moon"></i>';
            };

            if (localStorage.getItem('darkMode') === 'enabled') {
                body.setAttribute('data-bs-theme', 'dark');
            }

            applyIcon();

            darkModeToggle.addEventListener('click', function() {
                if (body.getAttribute('data-bs-theme') === 'dark') {
                    body.removeAttribute('data-bs-theme');
                    localStorage.setItem('darkMode', 'disabled');
                } else {
                    body.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('darkMode', 'enabled');
                }
                applyIcon();
            });
        });
    </script>
