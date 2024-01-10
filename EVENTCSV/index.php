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
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="http://localhost/js/tailwind.config.js"></script>

        <title>SB Admin 2 - Login</title>

        <?php include'link.php'; ?>
    </head>

    <body>
        <section class="bg-cover" style="background-image: url('https://media-s3-us-east-1.ceros.com/g3-communications/images/2021/04/21/bf088fa43296be6d4cee5685a37e6a30/untitled.gif');">
            <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
                <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                            Sign in to your account
                        </h1>
                        <form class="space-y-4 md:space-y-6" method="post">
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
                                <input type="email" id="inputEmail" name="txtEmail" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="name@company.com" required="">
                            </div>
                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                                <input type="password" id="inputPassword" name="txtPassword" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">
                            </div>
                            <button type="submit" name="btnLogin" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Sign in</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
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