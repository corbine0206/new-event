<?php
    session_start();
    include 'connection.php';
    $event_id = isset($_GET['eventID']) ? $_GET['eventID'] : null;
    $email = isset($_GET['email']) ? $_GET['email'] : null;

    try {
        if ($event_id !== null && $email !== null) {
            $connection = openConnection();
            $strSql = "SELECT * FROM participants where event_id = '$event_id' and email = '$email'";
            $result = mysqli_query($connection, $strSql);
            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    $reqPersons = mysqli_fetch_array($result);
                    mysqli_free_result($result);
                }
            }
           
        } else {
            throw new Exception('No event ID or email');
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    ?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Event</title>


    <?php include'link.php'; ?>

</head>
    <style>
        /* Center the container */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Style for the dropdown menus */
        .tech-dropdown {
            display: none;
        }

        /* Style for labels */
        label {
            display: block;
            margin-bottom: 10px;
        }

        /* Style for the content container */
        #content {
            text-align: center;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #f7f7f7;
            border-radius: 5px;
        }
    </style>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php //include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <form method="post">
                <?php
                    if ($reqPersons['status'] == 0) {
                        $con = openConnection();
                        $strSql = "SELECT * FROM technologies where event_id = '$event_id'";
                        $result = getRecord($con, $strSql);

                        foreach ($result as $key => $value) {
                            echo '<label>';
                            echo '<input type="checkbox" name="technology[]" value="' . $value['technology_id'] . '"> ' . $value['technology_name'];
                            echo '</label>';

                            // Query the product_technology_lines table for data related to the current technology_id
                            $techId = $value['technology_id'];
                            $dropdownSql = "SELECT * FROM product_technology_lines WHERE technology_id = '$techId'";
                            $dropdownResult = getRecord($con, $dropdownSql);

                            // Create the dropdown menu
                            echo '<select class="tech-dropdown" name="dropdown_' . $techId . '">';
                            echo '<option value="">Select a line</option>'; // Optional initial option
                            foreach ($dropdownResult as $dropdownKey => $dropdownValue) {
                                echo '<option value="' . $dropdownValue['technology_line'] .'">' . $dropdownValue['technology_line'] . '</option>';
                            }
                            echo '</select><br>';
                        }
                        closeConnection($con);
                        echo '<input type="submit" name="btnSubmit" value="Submit">';
                        }
                        elseif ($reqPersons['status'] == 1) {
                            echo 'already answered';
                        }
                        else{
                            echo "PAGE 404 not found";
                        }
                ?>
                    
                </form>
            </div>
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright Demand &copy; Generation</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    

    <?php include'script.php'; ?>

</body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get all the checkboxes
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                var dropdown = document.querySelector('select[name="dropdown_' + this.value + '"]');
                
                if (this.checked) {
                    dropdown.style.display = "block";
                } else {
                    dropdown.style.display = "none";
                }
            });
        });
    });
</script>

