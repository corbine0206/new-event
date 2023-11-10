<?php
    session_start();
    include 'connection.php';
    $user_id = $_SESSION['user_id'];
    $event_id = $_GET['eventID'];
    if (!isset($event_id)) {
        header("location: event.php");
    }
    $connection = openConnection();
    
    function getProductAndTechnologyLines($connection, $technologyId) {
    // Query to fetch product and technology lines by technology ID
    $query = "SELECT * FROM product_technology_lines WHERE technology_id = '$technologyId'";

    // Execute the query and fetch the results
    $result = mysqli_query($connection, $query);
    $productTechLines = array();
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productTechLines[] = $row;
        }
    }

    return $productTechLines;
}

    function getEventSessions($event_id) {
    $connection = openConnection();

    // Query to fetch event sessions by event ID
    $sessionSql = "SELECT * FROM event_sessions WHERE event_id = '$event_id'";
    
    // Execute the query and fetch the results
    $result = mysqli_query($connection, $sessionSql);
    $sessions = array();
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sessions[] = $row;
        }
    }

    // Close the database connection

    
    return $sessions;
}
// Define a function to get technologies by session ID
function getSessionTechnologies($connection, $sessionId) {

    // Query to fetch technologies by session ID
    $technologySql = "SELECT * FROM technologies WHERE session_id = '$sessionId'";
    
    // Execute the query and fetch the results
    $result = mysqli_query($connection, $technologySql);
    $technologies = array();
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $technologies[] = $row;
        }
    }

    // Close the database connection

    
    return $technologies;
}

// Function to get event details
function getEvent($connection, $event_id) {
    $eventSql = "SELECT * FROM events WHERE event_id = '$event_id'";
    $eventResult = $connection->query($eventSql);

    if ($eventResult->num_rows === 1) {
        $event = $eventResult->fetch_assoc();

        // Get event sessions
        $sessionSql = "SELECT * FROM event_sessions WHERE event_id = '$event_id'";
        $sessionResult = $connection->query($sessionSql);
        $event['sessions'] = [];

        if ($sessionResult->num_rows > 0) {
            while ($session = $sessionResult->fetch_assoc()) {
                // Get session technologies
                $techSql = "SELECT * FROM technologies WHERE session_id = '{$session['session_id']}'";
                $techResult = $connection->query($techSql);
                $session['technologies'] = [];

                if ($techResult->num_rows > 0) {
                    while ($technology = $techResult->fetch_assoc()) {
                        // Get product and technology lines
                        $prodTechSql = "SELECT * FROM product_technology_lines WHERE technology_id = '{$technology['technology_id']}'";
                        $prodTechResult = $connection->query($prodTechSql);
                        $technology['product_technology_lines'] = [];

                        if ($prodTechResult->num_rows > 0) {
                            while ($prodTechLine = $prodTechResult->fetch_assoc()) {
                                $technology['product_technology_lines'][] = $prodTechLine;
                            }
                        }

                        $session['technologies'][] = $technology;
                    }
                }

                $event['sessions'][] = $session;
            }
        }

        return $event;
    } else {
        return null; // Event not found
    }
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

    <title>Add Event</title>


    <?php include'link.php'; ?>

</head>

<style>
    .buttonRemove{
        padding: auto;
    }
</style>
<body id="page-top">

    <!-- Page Wrapper -->
     <div id="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include 'topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-calendar"></i> Edit Event</h6>
        </div>
        <div class="card mt-5">
            <div class="card-header">
                <h2>Update Event</h2>
            </div>
            <div class="card-body">
                <form id="eventForm" method="POST" enctype="multipart/form-data" action="update_event.php">
                    <!-- Your previous form elements go here -->

                    <?php
                    // Include your PHP functions and database connection here

                    // Call the getEvent function to fetch event data
                    $event = getEvent($connection, $event_id);

                    // Check if the event exists
                    if ($event) {
                    ?>
                    <!-- Your previous form elements go here -->
                    <?php
                    // Fetch event sessions
                    $sessions = getEventSessions($event_id);

                    if ($sessions) {
                        $sessionIndexCounter = 0;
                        foreach ($sessions as $session) {
                            $sessionIndexCounter++;
                            ?>
                            <div class="session">
                                <div class="row">
                                   <div class="col-md-4">
                                        <label for="session-<?php echo $session['session_id']; ?>">Session:</label>
                                        <input type="text" id="session-<?php echo $session['session_id']; ?>"
                                               class="form-control" name="session[]"
                                               value="<?php echo htmlspecialchars($session['session_title']); ?> session id <?php echo $sessionIndexCounter; ?>"
                                               required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="schedule-<?php echo $session['session_id']; ?>">Schedule:</label>
                                        <div class="input-group">
                                            <input type="date" id="schedule-<?php echo $session['session_id']; ?>"
                                                   class="form-control date-input" name="date[]"
                                                   value="<?php echo $session['date1']; ?>" required>
                                            <select class="form-control time-select" name="time[]" required>
                                                <option value="<?php echo $session['time1']; ?>"><?php echo $session['time1']; ?></option>
                                                <option value="06:00">06:00</option>
                                                <option value="07:00">07:00</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" id="techContainer-<?php echo $session['session_id']; ?>">
                                        <?php
                                        // Fetch technologies for this session
                                        $technologies = getSessionTechnologies($connection, $session['session_id']);
                                        if ($technologies) {
                                            $techIndexCounter = 0; // Initialize techIndexCounter for each session
                                            foreach ($technologies as $technology) {
                                                $techIndexCounter++;
                                                // Fetch product and technology line data for this technology
                                                $techLines = getProductAndTechnologyLines($connection, $technology['technology_id']);
                                                ?>
                                                <div class="row">
                                                        <div class="col-md-12" id="techContainer-<?php echo $session['session_id']; ?>">
                                                            <?php
                                                            // Fetch technologies for this session
                                                            $technologies = getSessionTechnologies($connection, $session['session_id']);
                                                            if ($technologies) {
                                                                // Initialize the counter
                                                                $techIndexCounter = 0;
                                                                foreach ($technologies as $key => $technology) {

                                                                    $techIndexCounter++;
                                                                    // Increment the counter for each technology
                                                                    // Fetch product and technology line data for this technology
                                                                    $techLines = getProductAndTechnologyLines($connection, $technology['technology_id']);
                                                                    ?>
                                                                    <div class="row" id="techContainer-<?php echo $sessionIndexCounter; ?>">
                                                                        <div class="col-md-4">
                                                                            <label for="technology-<?php echo $sessionIndexCounter; ?>-<?php echo $techIndexCounter; ?>">Technology:</label>
                                                                            <input type="text" class="form-control" id="technology-<?php echo $sessionIndexCounter; ?>-<?php echo $techIndexCounter; ?>"
                                                                                   name="technology[<?php echo $session['session_id']; ?>][]"
                                                                                   value="<?php echo htmlspecialchars($technology['technology_name']); ?>"
                                                                                   required>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <?php
                                                                            $lineIndexCounter = 0;
                                                                            foreach ($techLines as $ptl) {
                                                                                $lineIndexCounter++;
                                                                                ?>
                                                                                <div class="row" id="techContainer-<?php echo $sessionIndexCounter; ?>">
                                                                                    <div class="col-md-3">
                                                                                        <label for="product-<?php echo $sessionIndexCounter; ?>-<?php echo $techIndexCounter; ?>-<?php echo $lineIndexCounter; ?>">Product:</label>
                                                                                        <input type="text" class="form-control"
                                                                                            id="product-<?php echo $sessionIndexCounter; ?>-<?php echo $techIndexCounter; ?>-<?php echo $lineIndexCounter; ?>"
                                                                                            name="product[<?php echo $sessionIndexCounter; ?>]
                                                                                            [<?php echo $techIndexCounter; ?>]
                                                                                            []"
                                                                                            value="<?php echo htmlspecialchars($ptl['product_name']); ?>"
                                                                                            required
                                                                                            data-session-index="<?php echo $sessionIndexCounter; ?>"
                                                                                            data-tech-index="<?php echo $techIndexCounter; ?>"
                                                                                            data-line-index="<?php echo $lineIndexCounter; ?>">
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <label for="technologyLine-<?php echo $sessionIndexCounter; ?>-<?php echo $techIndexCounter; ?>-<?php echo $lineIndexCounter; ?>">Technology Line:</label>
                                                                                        <input type="text" class="form-control"
                                                                                            id="technologyLine-<?php echo $sessionIndexCounter; ?>-<?php echo $techIndexCounter; ?>-<?php echo $lineIndexCounter; ?>"
                                                                                            name="technologyLine[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]"
                                                                                            value="<?php echo htmlspecialchars($ptl['technology_line']); ?>"
                                                                                            required
                                                                                            data-session-index="<?php echo $sessionIndexCounter; ?>"
                                                                                            data-tech-index="<?php echo $techIndexCounter; ?>"
                                                                                            data-line-index="<?php echo $lineIndexCounter; ?>">
                                                                                    </div>
                                                                                    <div class="col-md-4 text-center">
                                                                                        <!-- Add a Remove button here -->
                                                                                        <button type="button" class="btn btn-danger text-center" style="margin-top: 11%;"
                                                                                            data-remove-index="<?php echo $lineIndexCounter; ?>"
                                                                                            onclick="removeProductAndLine(<?php echo $sessionIndexCounter; ?>, <?php echo $techIndexCounter; ?>, <?php echo $lineIndexCounter; ?>)">Remove Product and Technology Line</button>
                                                                                    </div>
                                                                                </div>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                            <!-- Add an Add Technology button here -->
                                                                            <button type="button" class="btn btn-info"
                                                                                    onclick="addTechnology('<?php echo $session['session_id']; ?>')">Add Technology
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <div id="sessionContainer">
                        <!-- Sessions will be added here -->
                    </div>
                    <!-- Add Session Button -->
                    <button type="button" class="btn btn-primary" id="addSessionBtn" onclick="addSession()">Add Session</button>

                    <!-- Submit Button -->
                    <div class="text-center mt-3">
                        <button type="submit" form="eventForm" class="btn btn-success">Submit</button>
                    </div>

                    <!-- Your previous form elements go here -->
                    <?php
                    } else {
                        echo "<p>Event not found.</p>";
                    }
                    ?>
                </form>
            </div>
            <!-- End of Main Content -->

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
    </div>
</div>

            </div>
        </div>
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
        // Define an object to keep track of technology line indices for each session and technology
        const techLineIndices = {};

        // Function to add a new session
        let sessionIndex = <?php echo count($sessions); ?>;

        // Function to add a new session
        function addSession() {
             sessionIndex++;
            const sessionContainer = document.getElementById("sessionContainer");

            // Create a new session div
            const sessionDiv = document.createElement("div");
            sessionDiv.className = "session";
            sessionDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label for="session-${sessionIndex}">Session:</label>
                        <input type="text" id="session-${sessionIndex}" class="form-control" name="session[]" required value="session id ${sessionIndex}"> 
                    </div>
                    <div class="col-md-4">
                        <label for="schedule-${sessionIndex}">Schedule:</label>
                        <div class="input-group">
                            <input type="date" id="schedule-${sessionIndex}" class="form-control date-input" name="date[]" required>
                            <select class="form-control time-select" name="time[]" required>
                                <option value="06:00">06:00</option>
                                <option value="07:00">07:00</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12" id="techContainer-${sessionIndex}">
                        <button type="button"  class="btn btn-info" onclick="addTechnology(${sessionIndex})">Add Technology</button>
                    </div>
                </div>
            `;

            // Append the new session to the container
            sessionContainer.appendChild(sessionDiv);
        }

        // Function to remove a technology element within a session
        function removeTechnology(sessionIndex, techIndex) {
            const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
            const techDivs = techContainer.querySelectorAll('.form-group');

            // Check if the techIndex is valid
            if (techIndex >= 0 && techIndex < techDivs.length) {
                const techToRemove = techDivs[techIndex];
                techToRemove.remove();
            }
        }

        // Function to remove a product and technology line within a technology element
        function removeProductAndLine(sessionIndex, techIndex, lineIndex) {
            // Use the data attributes to select the elements
            console.log(`${sessionIndex},${techIndex}, ${lineIndex}` );
            const productElements = document.querySelectorAll(`input[name="product[${sessionIndex}][${techIndex}][]"]
                                                                            [data-session-index="${sessionIndex}"]
                                                                            [data-tech-index="${techIndex}"]
                                                                            [data-line-index="${lineIndex}"]`);
            const techLineElements = document.querySelectorAll(`input[name="technologyLine[${sessionIndex}][${techIndex}][]"][data-session-index="${sessionIndex}"][data-tech-index="${techIndex}"][data-line-index="${lineIndex}"]`);

            productElements.forEach((productElement) => {
                if (productElement) {
                    productElement.parentElement.parentElement.remove();
                }
            });

            techLineElements.forEach((techLineElement) => {
                if (techLineElement) {
                    techLineElement.parentElement.parentElement.remove();
                }
            });

            // You can add any additional logic here as needed
        }



        // Function to initialize the technology line indices
        function initTechLineIndices(sessionId, techId) {
            if (!techLineIndices[sessionId]) {
                techLineIndices[sessionId] = {};
            }
            if (!techLineIndices[sessionId][techId]) {
                techLineIndices[sessionId][techId] = 0;
            }
        }
        // Function to add a new technology element within a session
        function addTechnology(sessionIndex) {
            const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
            const techDiv = document.createElement("div");
            techDiv.className = "form-group";

            // Initialize the technology index for this session
            let techIndex = techContainer.querySelectorAll('.form-group').length;

            techDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label>Technology:</label>
                        <input type="text" class="form-control" name="technology[${sessionIndex}][]" required>
                    </div>
                    <div class="col-md-2">
                        <label>Product:</label>
                        <input type="text" class="form-control" name="product[${sessionIndex}][${techIndex}][]" required
                            data-session-index="${sessionIndex}" data-tech-index="${techIndex}" data-line-index="0">
                    </div>
                    <div class="col-md-2">
                        <label>Technology Line:</label>
                        <input type="text" class="form-control" name="technologyLine[${sessionIndex}][${techIndex}][]" required
                            data-session-index="${sessionIndex}" data-tech-index="${techIndex}" data-line-index="0">
                    </div>
                    <div class="col-md-8">
                        <button type="button" class="btn btn-primary" onclick="addProductAndLine(${sessionIndex}, ${techIndex})">Add Product and Technology Line</button>
                        <button type="button" class="btn btn-danger" data-remove-index="${techIndex}" onclick="removeTechnology(${sessionIndex}, ${techIndex})">Remove Technology</button>
                    </div>
                </div>
            `;

            // Append the new technology to the container
            techContainer.appendChild(techDiv);

            // Initialize the technology line indices
            initTechLineIndices(sessionIndex, techIndex);
        }

        // Function to add a product and technology line within a technology element
        function addProductAndLine(sessionIndex, techIndex) {
            const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
            const techDiv = techContainer.querySelectorAll('.form-group')[techIndex];

            if (techDiv) {
                const lineIndex = techLineIndices[sessionIndex][techIndex] + 1;

                // Create a new row
                const rowDiv = document.createElement("div");
                rowDiv.className = "row"; // Create a new row to contain the elements

                // Create the col-md-4 for "Product"
                const productDiv = document.createElement("div");
                productDiv.className = "col-md-2";
                productDiv.innerHTML = `
                    <label>Product:</label>
                    <input type="text" class="form-control" name="product[${sessionIndex}][${techIndex}][]" required
                        data-session-index="${sessionIndex}" data-tech-index="${techIndex}" data-line-index="${lineIndex}">
                `;

                // Create the col-md-4 for "Technology Line"
                const techLineDiv = document.createElement("div");
                techLineDiv.className = "col-md-2";
                techLineDiv.innerHTML = `
                    <label>Technology Line:</label>
                    <input type="text" class="form-control" name="technologyLine[${sessionIndex}][${techIndex}][]" required
                        data-session-index="${sessionIndex}" data-tech-index="${techIndex}" data-line-index="${lineIndex}">
                `;

                // Create an empty col-md-4 to align with the layout
                const emptyDiv = document.createElement("div");
                emptyDiv.className = "col-md-4";

                // Create the col-md-4 for the "Remove" button
                const removeButtonDiv = document.createElement("div");
                removeButtonDiv.className = "col-md-4";
                removeButtonDiv.innerHTML = `
                    <button type="button" style="margin-top: 6%;" class="btn btn-danger" data-remove-index="${lineIndex}" onclick="removeProductAndLine(${sessionIndex}, ${techIndex}, ${lineIndex})">Remove Product and Technology Line</button>
                `;

                // Append the col-md-4 elements to the row
                rowDiv.appendChild(emptyDiv);
                rowDiv.appendChild(productDiv);
                rowDiv.appendChild(techLineDiv);
                rowDiv.appendChild(removeButtonDiv);

                // Append the new row containing product, technology line, and remove button to the techDiv
                techDiv.appendChild(rowDiv);

                // Increment the technology line index
                techLineIndices[sessionIndex][techIndex]++;
            }
        }
    </script>
