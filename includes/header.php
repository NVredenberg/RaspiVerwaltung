            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Bauteile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="koffer.php">Koffer/Nutzer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ausleihe.php">Ausleihen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="uebersicht.php">Übersicht</a>
                    </li>
                </ul>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <span class="navbar-text me-2">
                        Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <a class="btn btn-outline-danger" href="logout.php">Logout</a>
                <?php endif; ?>
            </div>
        </div>

