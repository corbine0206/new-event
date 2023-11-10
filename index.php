<?php
    session_start();
    ob_start();
    include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Login</title>

    <?php include'link.php'; ?>
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
  <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back Master!</h1>
                                    </div>
                                    <form class="user" method="post">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="exampleInputEmail" name="txtEmail" aria-describedby="emailHelp"
                                                placeholder="Enter Email Address...">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="exampleInputPassword" name="txtPassword" placeholder="Password">
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                            </div>
                                        </div>
                                        <button type="submit" name="btnLogin" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                    <!--<hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.php">Create an Account!</a>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include'script.php'; ?>

</body>

</html>
<?php
    if (isset($_POST['btnLogin'])) {
        $con = openConnection();
        $email = $_POST['txtEmail'];
        $password = sha1($_POST['txtPassword']);
        $strSql = "SELECT * FROM users where email = '$email' AND password = '$password' and status = 1";
        $result = mysqli_query($con, $strSql);
        if(mysqli_num_rows($result) > 0){
            $result = mysqli_fetch_array($result);
            $_SESSION['user_id'] = $result['user_id'];
            header('location: event.php');
        }
        else{
            echo '
                <script type="text/javascript">
                    $(document).ready(function() {
                        swal({
                            title: "Failed", 
                            text: "Credentials not found",
                            icon: "warning"
                        })
                    });
                </script>
            ';
        }
    }
    
?>