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

    <?php include 'link.php'; ?>

</head>

<style>
    .buttonRemove {
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
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-calendar"></i> Edit Event
                            </h6>
                        </div>
                        <div class="card mt-5">
                            <div class="card-header">
                                <h2>Update Event</h2>
                            </div>
                            <div class="card-body">
                                <form id="eventForm" method="POST" enctype="multipart/form-data" action="insert_event1.php">
                                    <div class="form-group">
                                        <label for="eventTitle">Event Title:</label>
                                        <input type="text" id="eventTitle" name="eventTitle" class="form-control" required="">
                                    </div>
                                    <div class="form-group">
                                        <label for="csvFile">Select a CSV file:</label>
                                        <input type="file" class="form-control-file" name="csvFile" id="csvFile" accept=".csv">
                                    </div>
                                     <div id="sessionContainer">
                                        <!-- Sessions will be added here -->
                                        <?php
                                            $sessions = getEventSessions($event_id);
                                            if ($sessions) {
                                                $sessionIndexCounter = 0;
                                                foreach ($sessions as $session) { ?>
                                                    <div class="session">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label for="session-<?php echo $sessionIndexCounter; ?>">Session:</label>
                                                                <input type="text" id="session-<?php echo $sessionIndexCounter; ?>" class="form-control" name="session[]" required value="<?php echo $session['session_title']; ?>"> 
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="schedule-0">Schedule:</label>
                                                                <div class="input-group">
                                                                    <input type="date" id="schedule-<?php echo $sessionIndexCounter; ?>" class="form-control date-input" name="date[]" required value="<?php echo $session['date1']; ?>">
                                                                    <select class="form-control time-select" name="time[]" required="">
                                                                        <option value="<?php echo $session['time1']; ?>"><?php echo $session['time1']; ?></option>
                                                                        <option value="06:00">06:00</option>
                                                                        <option value="07:00">07:00</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12" id="techContainer-<?php echo $sessionIndexCounter; ?>">
                                                                <button type="button" class="btn btn-info" onclick="addTechnology(<?php echo $sessionIndexCounter; ?>)">Add Technology</button>
                                                                <?php
                                                                    $technologies = getSessionTechnologies($connection, $session['session_id']);
                                                                        if ($technologies) {
                                                                            $techIndexCounter = 0;
                                                                            foreach ($technologies as $technology) { 
                                                                                $lineIndexCounter = 0;
                                                                                $techLines = getProductAndTechnologyLines($connection, $technology['technology_id']);
                                                                                if ($techLines) {
                                                                                    foreach ($techLines as $key => $value) {
                                                                                        echo '<div class="col-md-12" id="techContainer-'.$sessionIndexCounter.'-'. $techIndexCounter. '">';
                                                                                        if ($lineIndexCounter == 0) { ?>
                                                                                            <div class="form-group">
                                                                                                <div class="row">
                                                                                                    <div class="col-md-4">
                                                                                                        <label>Technology:</label>
                                                                                                        <input type="text" class="form-control" name="technology[<?php echo $sessionIndexCounter; ?>][]" value="<?php echo $technology['technology_name'] ?>">
                                                                                                    </div>
                                                                                                    <div class="row">
                                                                                                        <div class="col-md-6">
                                                                                                            <label>Product:</label>
                                                                                                            <input type="text" class="form-control" name="product[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]"  data-session-index="<?php echo $sessionIndexCounter; ?>" data-tech-index="<?php echo $techIndexCounter; ?>" data-line-index="<?php echo $lineIndexCounter; ?>" value="<?php echo $value['product_name']; ?>">
                                                                                                        </div>
                                                                                                        <div class="col-md-6">
                                                                                                            <label>Technology Line:</label>
                                                                                                            <input type="text" class="form-control" name="technologyLine[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]"  data-session-index="<?php echo $sessionIndexCounter; ?>" data-tech-index="<?php echo $techIndexCounter; ?>" data-line-index="<?php echo $lineIndexCounter; ?>" value="<?php echo $value['technology_line']; ?>">
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-8">
                                                                                                            <button type="button" class="btn btn-primary" onclick="addProductAndLine(<?php echo $sessionIndexCounter; ?>, <?php echo $techIndexCounter; ?>)">Add Product and Technology Line</button>
                                                                                                            <button type="button" class="btn btn-danger" data-remove-index="<?php echo $lineIndexCounter; ?>" onclick="removeTechnology(<?php echo $sessionIndexCounter; ?>, <?php echo $techIndexCounter; ?>)">Remove Technology</button>
                                                                                                        </div>
                                                                                                </div>
                                                                                            </div>
                                                                                <?php 
                                                                                    }
                                                                                    else{ ?>
                                                                                        <div class="form-group">
                                                                                            <div class="row">
                                                                                                <div class="col-md-4"></div>
                                                                                                <div class="col-md-2">
                                                                                                    <label>Product:</label>
                                                                                                    <input type="text" class="form-control" name="product[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]" data-session-index="<?php echo $sessionIndexCounter; ?>" data-tech-index="<?php echo $techIndexCounter; ?>" data-line-index="<?php echo $lineIndexCounter; ?>" value="<?php echo $value['product_name']; ?>">
                                                                                                </div><div class="col-md-2">
                                                                                                    <label>Technology Line:</label>
                                                                                                    <input type="text" class="form-control" name="technologyLine[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]" data-session-index="<?php echo $sessionIndexCounter; ?>" data-tech-index="<?php echo $techIndexCounter; ?>" data-line-index="<?php echo $lineIndexCounter; ?>" value="<?php echo $value['technology_line']; ?>">
                                                                                                </div><div class="col-md-4">
                                                                                                    <button type="button" style="margin-top: 6%;" class="btn btn-danger" data-remove-index="<?php echo $lineIndexCounter; ?>" onclick="removeProductAndLine(<?php echo $sessionIndexCounter; ?>, <?php echo $techIndexCounter; ?>, <?php echo $lineIndexCounter; ?>)">Remove Product and Technology Line</button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php 
                                                                                    }
                                                                                    $lineIndexCounter++; 
                                                                                echo '</div>';
                                                                                }
                                                                                ?>
                                                                            <?php
                                                                            $techIndexCounter++;
                                                                            }
                                                                        }
                                                                    }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php 
                                                $sessionIndexCounter++;   
                                                }
                                            }
                                        ?>
                                <!-- Submit Button -->
                                <div class="text-center mt-3">
                                    <button type="submit" form="eventForm" class="btn btn-success">Submit</button>
                                </div>
                            </form>
                            </div>
                            <!-- Add Session Button -->
                            <button type="button" class="btn btn-primary" id="addSessionBtn" onclick="addSession()">Add Session</button>

                            <!-- Submit Button -->
                            <div class="text-center mt-3">
                                <button type="submit" form="eventForm" class="btn btn-success">Submit</button>
                            </div>
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
    <?php include 'script.php'; ?>

</body>

<script>
    // Define an object to keep track of technology line indices for each session and technology
    const techLineIndices = {};

    // Function to add a new session
    let sessionIndex = <?php echo $sessionIndexCounter; ?>;

    // Function to add a new session
    function addSession() {
        
        const sessionContainer = document.getElementById("sessionContainer");
        sessionIndex++;
        // Create a new session div
        const sessionDiv = document.createElement("div");
        sessionDiv.className = "session";
        sessionDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label for="session-${sessionIndex}">Session:</label>
                        <input type="text" id="session-${sessionIndex}" class="form-control" name="session[]" required>
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
                        <button type="button" class="btn btn-info" onclick="addTechnology(${sessionIndex})">Add Technology</button>
                    </div>
                </div>
            `;

        // Append the new session to the container
        sessionContainer.appendChild(sessionDiv);
    }

    // Function to remove a technology element within a session
    function removeTechnology(sessionIndex, techIndex) {
        const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
        const techDiv = document.getElementById(`techDiv-${sessionIndex}-${techIndex}`);

        // Remove the technology div
        techContainer.removeChild(techDiv);
    }

    // Function to add a new technology element within a session
    let techIndex = <?php echo $techIndexCounter; ?>;

    function addTechnology(sessionIndex) {
        techIndex++;
        const techContainer = document.getElementById(`techContainer-${sessionIndex}`);

        // Create a new technology div
        const techDiv = document.createElement("div");
        techDiv.id = `techDiv-${sessionIndex}-${techIndex}`;
        techDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label for="technology-${sessionIndex}-${techIndex}">Technology:</label>
                        <input type="text" class="form-control" name="technology[${sessionIndex}][]" required>
                    </div>
                    <div class="col-md-4">
                        <label for="product-${sessionIndex}-${techIndex}-0">Product:</label>
                        <input type="text" class="form-control" name="product[${sessionIndex}][${techIndex}][]" required>
                    </div>
                    <div class="col-md-4">
                        <label for="technologyLine-${sessionIndex}-${techIndex}-0">Technology Line:</label>
                        <input type="text" class="form-control" name="technologyLine[${sessionIndex}][${techIndex}][]" required>
                    </div>
                    <div class="col-md-8">
                        <button type="button" class="btn btn-primary" onclick="addProductAndLine(${sessionIndex}, ${techIndex})">Add Product and Technology Line</button>
                        <button type="button" class="btn btn-danger" data-remove-index="${techIndex}" onclick="removeTechnology(${sessionIndex}, ${techIndex})">Remove Technology</button>
                    </div>
                </div>
            `;

        // Append the new technology div to the container
        techContainer.appendChild(techDiv);
    }

// Function to add a product and technology line within a technology element
function addProductAndLine(sessionIndex, techIndex) {
    console.log(`Adding product and line for session ${sessionIndex}, tech ${techIndex}`);

    // Find the technology container for the specified session and techIndex
    const techContainer = document.getElementById(`techContainer-${sessionIndex}-${techIndex}`);

    if (techContainer) {
        const lineIndex = techContainer.querySelectorAll(`input[name="product[${sessionIndex}][${techIndex}][]"]`).length;

        // Create a new row for the product and technology line
        const newRow = document.createElement("div");
        newRow.className = "row";

        newRow.innerHTML = `
            <div class="col-md-2">
                <label>Product:</label>
                <input type="text" class="form-control" name="product[${sessionIndex}][${techIndex}][]" required
                    data-session-index="${sessionIndex}" data-tech-index="${techIndex}" data-line-index="${lineIndex}">
            </div>
            <div class="col-md-2">
                <label>Technology Line:</label>
                <input type="text" class="form-control" name="technologyLine[${sessionIndex}][${techIndex}][]" required
                    data-session-index="${sessionIndex}" data-tech-index="${techIndex}" data-line-index="${lineIndex}">
            </div>
            <div class="col-md-4">
                <button type="button" style="margin-top: 6%;" class="btn btn-danger"
                    onclick="removeProductAndLine(${sessionIndex}, ${techIndex}, ${lineIndex})">Remove Product and Technology Line</button>
            </div>
        `;

        // Append the new row to the techContainer
        techContainer.appendChild(newRow);
    }
}



    // Function to remove a product and technology line row within a technology element
    function removeProductAndLine(sessionIndex, techIndex, lineIndex) {
        // Get the technology container
        const techContainer = document.getElementById(`techDiv-${sessionIndex}-${techIndex}`);

        // Get the row to remove
        const rowToRemove = techContainer.querySelector(`.row[data-line-index="${lineIndex}"]`);

        // Remove the row
        techContainer.removeChild(rowToRemove);
    }

    // Function to get the current line index for a technology element
    function getTechLineIndex(sessionIndex, techIndex) {
        if (!techLineIndices.hasOwnProperty(sessionIndex)) {
            techLineIndices[sessionIndex] = {};
        }
        if (!techLineIndices[sessionIndex].hasOwnProperty(techIndex)) {
            techLineIndices[sessionIndex][techIndex] = 0;
        }
        return techLineIndices[sessionIndex][techIndex];
    }

    // Function to increment the line index for a technology element
    function incrementTechLineIndex(sessionIndex, techIndex) {
        if (!techLineIndices.hasOwnProperty(sessionIndex)) {
            techLineIndices[sessionIndex] = {};
        }
        if (!techLineIndices[sessionIndex].hasOwnProperty(techIndex)) {
            techLineIndices[sessionIndex][techIndex] = 0;
        }
        techLineIndices[sessionIndex][techIndex]++;
    }

    // Function to update the line index for each existing technology input
    function updateTechLineIndices() {
        const techContainers = document.querySelectorAll("[id^='techDiv-']");

        techContainers.forEach((techContainer) => {
            const sessionIndex = techContainer.id.split("-")[1];
            const techIndex = techContainer.id.split("-")[2];
            let lineIndex = 0;

            techContainer.querySelectorAll("[data-line-index]").forEach((row) => {
                const currentLineIndex = parseInt(row.getAttribute("data-line-index"));
                if (currentLineIndex > lineIndex) {
                    lineIndex = currentLineIndex;
                }
            });

            techLineIndices[sessionIndex][techIndex] = lineIndex + 1;
        });
    }

    // Add event listener to update tech line indices when the form is submitted
    const eventForm = document.getElementById("eventForm");
    eventForm.addEventListener("submit", updateTechLineIndices);
</script>

</html>
