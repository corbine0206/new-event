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

<?php
if (isset($_POST['btnSubmit'])) {
    // Establish a database connection if needed
    $connection = openConnection();

    $email = $_GET['email'];
    $event_id = $_GET['eventID'];
    if (isset($_POST["technology"])) {
        $selectedTechnologies = $_POST["technology"];

        foreach ($selectedTechnologies as $selectedTechId) {
            $selectedDropdownValue = $_POST["dropdown_" . $selectedTechId];
            $dataExplode = explode("-", $selectedDropdownValue);
            $response = $dataExplode[0];
            $product_id = $dataExplode[1];
            $technology_id = $dataExplode[2];
            $session_id = $dataExplode[3];
            // Insert the selected technology_id and dropdown value into your database
             $insertSql = "INSERT INTO response (event_id, email, product_id, technology_id, session_id, response) VALUES ('$event_id','$email', '$product_id', '$technology_id', '$session_id', '$response')";

             if (mysqli_query($connection, $insertSql)) {
                 
             } else {
                 echo 'Error inserting data for technology ID ' . $selectedTechId . ': ' . mysqli_error($con) . '<br>';
             }
        }
    }
    // Retrieve the dataset
    $datasetSql = "SELECT technology_line, es.session_title ,pt.session_id, t.technology_name, es.date1, es.time1, es.time2
    FROM product_technology_lines as pt 
    join technologies as t on pt.technology_id = t.technology_id 
    join event_sessions as es on es.session_id = pt.session_id where pt.event_id = '$event_id'";
    
    // Execute the SQL query and fetch the data
    $result = mysqli_query($connection, $datasetSql);
    
    // Initialize an array to store dataset entries
    $dataset = array();

    // Fetch rows and add them to the dataset array
    while ($row = mysqli_fetch_assoc($result)) {
        $dataset[] = $row;
    }

    // Get selected technologies from the form
    $selected_technologies = $_POST["technology"];
    $selected_data = [];
    // Iterate through selected technologies
    foreach ($selected_technologies as $tech_id) {
        // Check if a dropdown menu associated with this technology is set
        $dropdown_key = "dropdown_" . $tech_id;
        if (isset($_POST[$dropdown_key])) {
            // Add selected product and technology data to the array
            $selectedDropdownValue = $_POST[$dropdown_key];
            $dataExplode = explode("-", $selectedDropdownValue);
            $response = $dataExplode[0];
            $selected_data[] = $response;
        }
    }
    // Combine the data into an associative array
    $dataToPass = array(
        "user_preferences" => $selected_data,
        "dataset" => $dataset
    );

    // Encode the data as JSON
    $jsonData = json_encode($dataToPass);

    // Close the database connection if it's open
    mysqli_close($connection);

    // Define the API endpoint URL for localhost
    $apiEndpoint = 'http://localhost/event/receive_json.php'; // Adjust the path to your project folder

    // Initialize cURL session
    $ch = curl_init($apiEndpoint);

    // Set cURL options to send a POST request with JSON data
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }

    // Close cURL session
    curl_close($ch);

    // You can handle the response from the API here if needed
    echo 'Response from API: ' . $response;
}

?>


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
                    <?php
                        if ($reqPersons['status'] == 1) {
                            echo "You already answered the form";
                        }
                        else{ ?>
                            <form method="post">
                                <input type="text" value="<?php echo $_GET['eventID'] ?>" name="event_id"> <!-- Replace "process-form.php" with your PHP script's filename -->
                                <input type="text" value="<?php echo $_GET['email'] ?>" name="email">
                                <?php
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
                                        echo '<option value="' . $dropdownValue['technology_line'] . '-'.$dropdownValue['product_id'].'-'.$dropdownValue['technology_id'].'-'.$dropdownValue['session_id'].'">' . $dropdownValue['technology_line'] . '</option>';
                                    }
                                    echo '</select><br>';
                                }
                                ?>
                                <input type="submit" name="btnSubmit">
                            </form>
                       <?php }
                    ?>
                
                </div>
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2023</span>
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
<script>
        document.addEventListener("DOMContentLoaded", function () {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            var maxChecked = 3; // Set the maximum number of checkboxes allowed
            
            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener("change", function () {
                    var checkedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');
                    
                    if (checkedCheckboxes.length >= maxChecked) {
                        checkboxes.forEach(function (cb) {
                            if (!cb.checked) {
                                cb.disabled = true;
                            }
                        });
                    } else {
                        checkboxes.forEach(function (cb) {
                            cb.disabled = false;
                        });
                    }
                    
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
