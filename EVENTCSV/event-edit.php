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
                                <form id="eventForm" method="POST" enctype="multipart/form-data" action="update_event.php">
                                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                    <?php
                                    $event = getEvent($connection, $event_id);
                                    if ($event) {
                                        ?>
                                        <div class="form-group">
                                            <label for="eventTitle">Event Title:</label>
                                            <input type="text" id="eventTitle" name="eventTitle" class="form-control"
                                                required value="<?php echo $event['event_title']; ?>">
                                                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <div id="sessionContainer">
                                        <?php
                                        $sessions = getEventSessions($event_id);
                                        if ($sessions) {
                                            $sessionIndexCounter = 0;
                                            foreach ($sessions as $session) { ?>
                                                <div class="session">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="session-<?php echo $sessionIndexCounter; ?>">Session:</label>
                                                            <input type="text" id="session-<?php echo $sessionIndexCounter; ?>"
                                                                class="form-control" name="session[]"
                                                                value="<?php echo $session['session_title']; ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="schedule-<?php echo $sessionIndexCounter; ?>">Schedule:</label>
                                                            <div class="input-group">
                                                                <input type="date" id="schedule-<?php echo $sessionIndexCounter; ?>"
                                                                    class="form-control date-input" name="date[]"
                                                                    value="<?php echo $session['date1'] ?>">
                                                                <select class="form-control time-select" name="time[]" required>
                                                                    <option value="<?php echo $session['time1'] ?>"><?php echo $session['time1'] ?></option>
                                                                    <option value="06:00">06:00</option>
                                                                    <option value="07:00">07:00</option>
                                                                    <option value="08:00">08:00</option>
                                                                    <option value="09:00">09:00</option>
                                                                    <option value="10:00">10:00</option>
                                                                    <option value="11:00">11:00</option>
                                                                    <option value="12:00">12:00</option>
                                                                </select>
                                                                <select class="form-control time-select" name="time2[]" required>
                                                                    <option value="13:00">13:00</option>
                                                                    <option value="14:00">14:00</option>
                                                                    <option value="15:00">15:00</option>
                                                                    <option value="16:00">16:00</option>
                                                                    <option value="17:00">17:00</option>
                                                                    <option value="18:00">18:00</option>
                                                                    <option value="19:00">19:00</option>
                                                                    <option value="20:00">20:00</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12"  id="techContainer-<?php echo $sessionIndexCounter; ?>">
                                                            <button type="button" class="btn btn-info" onclick="addTechnology(<?php echo $sessionIndexCounter; ?>)">Add Technology</button>    
                                                            <?php
                                                                $technologies = getSessionTechnologies($connection, $session['session_id']);
                                                                    if ($technologies) {
                                                                        $techIndexCounter = 0;
                                                                        foreach ($technologies as $technology) { 
                                                            ?>
                                                            
                                                            <div class="form-group" data-tech-index="<?php echo $techIndexCounter; ?>">
                                                                <?php
                                                                    
                                                                            $techLines = getProductAndTechnologyLines($connection, $technology['technology_id']);
                                                                            if ($techLines) {
                                                                                $lineIndexCounter = 0;
                                                                                foreach ($techLines as $key => $value) {
                                                                                    if ($lineIndexCounter == 0) { ?>
                                                                                        <div class="row">
                                                                                            <div class="col-md-4">
                                                                                                <label>Technology:</label>
                                                                                                <input type="text" class="form-control"
                                                                                                    name="technology[<?php echo $sessionIndexCounter; ?>][]"
                                                                                                    required="" value="<?php echo $technology['technology_name']; ?>">
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <label>Product:</label>
                                                                                                <input type="text" class="form-control"
                                                                                                    name="product[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]"
                                                                                                    required="" data-session-index="<?php echo $sessionIndexCounter; ?>"
                                                                                                    data-tech-index="<?php echo $techIndexCounter; ?>"
                                                                                                    data-line-index="<?php echo $lineIndexCounter; ?>"
                                                                                                    value="<?php echo $value['product_name']; ?>">
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <label>Technology Line:</label>
                                                                                                <input type="text" class="form-control"
                                                                                                    name="technologyLine[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]"
                                                                                                    required data-session-index="<?php echo $sessionIndexCounter; ?>"
                                                                                                    data-tech-index="<?php echo $techIndexCounter; ?>"
                                                                                                    data-line-index="<?php echo $lineIndexCounter; ?>"
                                                                                                    value="<?php echo $value['technology_line']; ?>">
                                                                                            </div>
                                                                                            <div class="col-md-8">
                                                                                                <button type="button" class="btn btn-primary"
                                                                                                    onclick="addProductAndLine(<?php echo $sessionIndexCounter; ?>, <?php echo $techIndexCounter; ?>)">Add Product and Technology Line</button>
                                                                                                <button type="button" class="btn btn-danger"
                                                                                                    data-remove-index="<?php echo $techIndexCounter; ?>"
                                                                                                    onclick="removeTechnology(<?php echo $sessionIndexCounter; ?>, <?php echo $techIndexCounter; ?>)">Remove Technology</button>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php 
                                                                                    }
                                                                                    else { ?>
                                                                                        <div class="row">
                                                                                            <div class="col-md-4"></div>
                                                                                            <div class="col-md-2">
                                                                                                <label>Product:</label>
                                                                                                <input type="text" class="form-control"
                                                                                                    name="product[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]"
                                                                                                    required="" data-session-index="<?php echo $sessionIndexCounter; ?>"
                                                                                                    data-tech-index="<?php echo $techIndexCounter; ?>"
                                                                                                    data-line-index="<?php echo $lineIndexCounter; ?>"
                                                                                                    value="<?php echo $value['product_name']; ?>">
                                                                                            </div>
                                                                                            <div class="col-md-2">
                                                                                                <label>Technology Line:</label>
                                                                                                <input type="text" class="form-control"
                                                                                                    name="technologyLine[<?php echo $sessionIndexCounter; ?>][<?php echo $techIndexCounter; ?>][]"
                                                                                                    required data-session-index="<?php echo $sessionIndexCounter; ?>"
                                                                                                    data-tech-index="<?php echo $techIndexCounter; ?>"
                                                                                                    data-line-index="<?php echo $lineIndexCounter; ?>"
                                                                                                    value="<?php echo $value['technology_line']; ?>">
                                                                                            </div>
                                                                                            <div class="col-md-4"><button type="button" style="margin-top: 6%;" class="btn btn-danger" data-remove-index="<?php echo $lineIndexCounter; ?>" onclick="removeProductAndLine(<?php echo $sessionIndexCounter; ?>, <?php echo $techIndexCounter; ?>, <?php echo $lineIndexCounter; ?>)">Remove Product and Technology Line</button></div>
                                                                                        </div>
                                                                                    <?php 
                                                                                    } 
                                                                                    $lineIndexCounter++; 
                                                                                } 
                                                                            } 
                                                                            $techIndexCounter++;
                                                                ?>
                                                            </div>
                                                            
                                                            <?php    }
                                                                        } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                                $sessionIndexCounter++;
                                            }
                                        }
                                        ?>
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
            sessionIndex++;
            const sessionContainer = document.getElementById("sessionContainer");

            // Create a new session div
            const sessionDiv = document.createElement("div");
            sessionDiv.className = "session";
            sessionDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label for="session-${sessionIndex}">Session:</label>
                        <input type="text" id="session-${sessionIndex}" class="form-control" name="session[]" required"> 
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
       function removeTechnology(sessionIndex, techIndex) {
            console.log(`sessionIndex ${sessionIndex}, techIndex ${techIndex} removed`);

            const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
            const techToRemove = techContainer.querySelector(`.form-group[data-tech-index="${techIndex}"]`);

            if (techToRemove) {
                techToRemove.remove();
            }
        }



        // Function to remove a product and technology line within a technology element
        function removeProductAndLine(sessionIndex, techIndex, lineIndex) {
            console.log(`sessionIndex ${sessionIndex}, techIndex ${techIndex}, ${lineIndex} removed`);
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
                // Calculate the initial lineIndex based on the existing lines
                const existingLines = document.querySelectorAll(`input[name="product[${sessionId}][${techId}][]"][data-session-index="${sessionId}"][data-tech-index="${techId}"][data-line-index]`);
                let maxLineIndex = -1;
                existingLines.forEach((lineElement) => {
                    const lineIndex = parseInt(lineElement.getAttribute("data-line-index"));
                    if (!isNaN(lineIndex) && lineIndex > maxLineIndex) {
                        maxLineIndex = lineIndex;
                    }
                });
                // Set the initial lineIndex as the maximum existing lineIndex + 1
                techLineIndices[sessionId][techId] = maxLineIndex + 1;
            }
        }

        function addTechnology(sessionIndex) {
            // Calculate the next available techIndex based on existing technologies
            const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
            const techDivs = techContainer.querySelectorAll('.form-group[data-tech-index]');
            const techIndex = techDivs.length;

            // Create a new technology element
            const newTechDiv = document.createElement('div');
            newTechDiv.classList.add('form-group');
            newTechDiv.setAttribute('data-tech-index', techIndex); // Set data-tech-index

            // Add your technology input fields and buttons to the newTechDiv here

            // Append the new technology to the container
            techContainer.appendChild(newTechDiv);
        }

        function addTechnology(sessionIndex) {
            // Calculate the next available techIndex based on existing technologies
            const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
            const techDivs = techContainer.querySelectorAll('.form-group[data-tech-index]');
            const techIndex = techDivs.length;

            // Create a new technology element
            const newTechDiv = document.createElement('div');
            newTechDiv.classList.add('form-group');
            newTechDiv.setAttribute('data-tech-index', techIndex); // Set data-tech-index

            // Add technology input fields and buttons to the newTechDiv here
            newTechDiv.innerHTML = `
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
            techContainer.appendChild(newTechDiv);
        }


        // Function to add a product and technology line within a technology element
    function addProductAndLine(sessionIndex, techIndex) {
        console.log(`session Index : ${sessionIndex}, techIndex ${techIndex}`);
        const techContainer = document.getElementById(`techContainer-${sessionIndex}`);
        const techDiv = techContainer.querySelectorAll('.form-group')[techIndex];

        if (techDiv) {
            // Ensure techLineIndices is properly initialized
            initTechLineIndices(sessionIndex, techIndex);

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


</html>
