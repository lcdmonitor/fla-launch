    <!-- navbar-->
    <nav class="collapse navbar-collapse dropdown-nav" id="navbar">
        <div class="container-xxl dropdown-nav__container text-white">
            <div class="navbar__links">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link fs-5" href="/"><i class="bi bi-house-door-fill nav-space"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-5" href="/launch.php"><i class="bi bi-stars nav-space"></i>Launches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-5" href="/news.php"><i class="bi bi-newspaper nav-space"></i>News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-5" href="/education.php"><i class="bi bi-book nav-space"></i>Education</a>
                    </li>
                    <?php if (!GetIsUserLoggedIn()) { ?>
                        <li>
                            <a class="nav-link fs-5" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-key nav-space"></i>Login</a>
                        </li>
                    <?php } else { ?>
                        <li>
                            <a class="nav-link fs-5" href="/logout.php"><i class="bi bi-box-arrow-left nav-space"></i>Logout</a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div>
                <button class="navbar-toggler navbar dropdown-nav__closeButton text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="#navbar" aria-expanded="false" aria-label="toggle navigation"><i class="bi bi-x"></i></button>
            </div>

        </div>
    </nav>

    <!-- navbar end-->