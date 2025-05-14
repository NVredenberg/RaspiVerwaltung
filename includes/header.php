    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-microchip me-2"></i>Raspi-Verwaltung
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-boxes me-1"></i>Bauteile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'koffer.php' ? 'active' : ''; ?>" href="koffer.php">
                            <i class="fas fa-briefcase me-1"></i>Koffer
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['ausleihe.php', 'batch_operations.php']) ? 'active' : ''; ?>" href="#" id="ausleiheDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-exchange-alt me-1"></i>Ausleihe
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="ausleiheDropdown">
                            <li>
                                <a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'ausleihe.php' ? 'active' : ''; ?>" href="ausleihe.php">
                                    <i class="fas fa-plus-circle me-1"></i>Neue Ausleihe
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'batch_operations.php' ? 'active' : ''; ?>" href="batch_operations.php">
                                    <i class="fas fa-layer-group me-1"></i>Massenausleihe/Rückgabe
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['uebersicht.php']) ? 'active' : ''; ?>" href="#" id="ausleiheDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-bar me-1"></i>Übersicht
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="ausleiheDropdown">
                            <li>
                                <a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'uebersicht.php' ? 'active' : ''; ?>" href="uebersicht.php">
                                    <i class="fas fa-list me-1"></i>Ausleihübersicht
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <button id="darkModeToggle" class="btn btn-outline-light me-3">
                        <i class="fas fa-moon"></i>
                    </button>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <span class="navbar-text me-3">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                        <a class="btn btn-outline-light" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        #darkModeToggle {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        #darkModeToggle:hover {
            transform: rotate(15deg);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;
            
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'enabled') {
                body.setAttribute('data-bs-theme', 'dark');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
            
            darkModeToggle.addEventListener('click', function() {
                if (body.getAttribute('data-bs-theme') === 'dark') {
                    body.removeAttribute('data-bs-theme');
                    localStorage.setItem('darkMode', 'disabled');
                    darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                } else {
                    body.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('darkMode', 'enabled');
                    darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                }
            });
        });
    </script>
