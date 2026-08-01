<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>


<!DOCTYPE html>
<html>

<head>
    <!-- basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- site metas -->
    <title>BoxKado</title>
    <!-- bootstrap css -->
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
    <!-- style css -->
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <!-- Responsive-->
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <!-- fevicon -->
    <link rel="icon" href="../assets/images/boxkado-icon.png" type="image/gif" />
    <!-- font css -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="../assets/css/jquery.mCustomScrollbar.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">

    <style>
        body {
            background-image: url('../assets/images/banner-bg.png');
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .register-container {
            width: 100%;
            max-width: 400px;
        }

        .card {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .form-control {
            border-radius: 5px;
        }

        .btn-register {
            background-color: #ff94c4;
            color: white;
            border-radius: 5px;
            width: 100%;
        }

        .login-link {
            text-align: center;
            margin-top: 10px;
        }

        .login-link a {
            color: #ff94c4;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #ff94c4;
            cursor: pointer;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container py-5 my-5">
        <div class="row justify-content-start">
            <div class="col-md-4">
                <div class="register-container">
                    <div class="card">
                        <h3 class="text-center">Registrasi</h3>
                        <form id="registerForm" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control pe-5" id="password" name="password"
                                        autocomplete="off">
                                    <button type="button" class="password-toggle" data-target="password"
                                        aria-label="Tampilkan password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Konfirmasi</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control pe-5" id="confirmPassword"
                                        name="confirmPassword" autocomplete="off">
                                    <button type="button" class="password-toggle" data-target="confirmPassword"
                                        aria-label="Tampilkan password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-register">Daftar</button>
                        </form>
                        <div class="login-link">
                            <p>Sudah memiliki akun? <a href="index.php">Login</a></p>
                            <a href="../index.php" class="text-muted small d-block mt-3">
                                <i class="fa fa-arrow-left me-1"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Javascript files -->
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.0.0.min.js"></script>
    <script src="../assets/js/plugin.js"></script>
    <script src="../assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="../assets/js/custom.js"></script>

    <!-- Add SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $('.password-toggle').on('click', function () {
                var targetId = $(this).data('target');
                var input = $('#' + targetId);
                var icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    $(this).attr('aria-label', 'Sembunyikan password');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    $(this).attr('aria-label', 'Tampilkan password');
                }
            });

            $("#registerForm").submit(function (e) {
                e.preventDefault();

                var username = $("#username").val();
                var password = $("#password").val();
                var confirmPassword = $("#confirmPassword").val();

                if (password !== confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: "Password dan Konfirmasi Password tidak sama!",
                        confirmButtonColor: '#ff94c4'
                    });
                    return;
                }

                $.ajax({
                    url: '../functions.php',
                    type: 'POST',
                    data: {
                        action: 'register',
                        username: username,
                        password: password
                    },
                    success: function (response) {
                        var data = JSON.parse(response);

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                confirmButtonColor: '#ff94c4'
                            }).then(function () {
                                window.location.href = "register.php";
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonColor: '#ff94c4'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: error,
                            confirmButtonColor: '#ff94c4'
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>