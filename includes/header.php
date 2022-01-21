<?php
session_start();
require('./includes/functions.inc.php')
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Florida Launch Alliance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">

    <!-- bootstrap icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <!-- Google Fonts-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- Header Start-->
    <header class="header p-3 position-absolute start-0 top-0 end-0">
        <div class="d-flex justify-content-between align-items-center">
            <a href="/" class="text-decoration-none text-white fs-5 fw-bold">Florida Launch Alliance</a>

            <div>
                <?php if (!GetIsUserLoggedIn()) { ?>
                    <a class="text-decoration-none text-white fs-5 login-link" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                <?php } else { ?>
                    <a class="text-decoration-none text-white fs-5 login-link" href="/logout.php">Logout</a>
                <?php } ?>
                <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="#navbar" aria-expanded="false" aria-label="toggle navigation">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>
    <!-- Header End-->

    <!-- navbar-->
    <nav class="collapse navbar-collapse dropdown-nav" id="navbar">
        <div class="container-xxl dropdown-nav__container text-white">

            <ul>
                <?php if (!GetIsUserLoggedIn()) { ?>
                    <li><a class="text-decoration-none text-white fs-5" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
                <?php } else { ?>
                    <li><a class="text-decoration-none text-white fs-5" href="logout.php">Logout</a></li>
                <?php } ?>
            </ul>

            <div>
                <button class="navbar-toggler navbar dropdown-nav__closeButton text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="#navbar" aria-expanded="false" aria-label="toggle navigation"><i class="bi bi-x"></i></button>
            </div>
        </div>
    </nav>

    <!-- navbar end-->

    <!-- login modal -->
    <!-- Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="needs-validation" action="login.php" id="loginForm" novalidate>
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Login</label>
                            <input type="username" class="form-control" id="username" name="username" aria-describedby="usernameHelp" required>
                            <div id="usernameHelp" class="form-text">username or email address</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit" onclick="return processLogin();">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- end login modal -->