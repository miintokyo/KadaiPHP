        <header class="top-nav">

            <p class="top-nav-title" id="header-title">
            <?= htmlspecialchars($pageTitle ?? "Tana") ?>
            </p>

            <a href="mySettings.php#<?= urlencode($settingsSection ?? 'account') ?>" class="top-nav-item">
                <span class="nav-icon">⚙️</span>
            </a>
        </header>
