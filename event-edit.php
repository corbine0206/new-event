<?php
session_start();
include 'connection.php';
$user_id = $_SESSION['user_id'];
$event_id = $_GET['eventID'];

if (!isset($event_id)) {
    header("location: event.php");
}

$connection = openConnection();

// Fetch event details
$eventQuery = "SELECT event_title FROM events WHERE event_id = ?";
$stmt = $connection->prepare($eventQuery);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($eventTitle);
$stmt->fetch();
$stmt->close();

// Fetch session details
$sessionQuery = "SELECT session_id, session_title, technology, technology_line, product_name, speaker, speaker_special, date, timeam, timepm FROM event_sessions WHERE event_id = ?";
$stmt = $connection->prepare($sessionQuery);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($sessionId, $sessionTitle, $technology, $technologyLine, $productName, $speaker, $speakerSpecial, $date, $timeam, $timepm);

$sessions = [];
while ($stmt->fetch()) {
    $sessions[] = [
        'session_id' => $sessionId,
        'session_title' => $sessionTitle,
        'technology' => $technology,
        'technology_line' => $technologyLine,
        'product_name' => $productName,
        'speaker' => $speaker,
        'speaker_special' => $speakerSpecial,
        'date' => $date,
        'timeam' => $timeam,
        'timepm' => $timepm,
    ];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Edit Event</title>
    <?php include 'link.php'; ?>
</head>

<style>
    .buttonRemove {
        padding: auto;
    }
</style>

<body id="page-top">
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
                                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                    <div class="form-group">
                                        <label for="eventTitle">Event Title:</label>
                                        <input type="text" id="eventTitle" name="eventTitle" class="form-control" value="<?php echo htmlspecialchars($eventTitle); ?>" required>
                                    </div>
                                    <div id="sessionContainer">
                                        <?php foreach ($sessions as $session) { ?>
                                            <div class="session">
                                                <input type="hidden" name="session_id[]" value="<?php echo $session['session_id']; ?>">
                                                <div class="form-group">
                                                    <label for="sessionTitle">Session Title:</label>
                                                    <input type="text" name="sessionTitle[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['session_title']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="technology">Technology:</label>
                                                    <input type="text" name="technology[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['technology']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="technologyLine">Technology Line:</label>
                                                    <input type="text" name="technologyLine[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['technology_line']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="productName">Product Name:</label>
                                                    <input type="text" name="productName[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['product_name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="speaker">Speaker:</label>
                                                    <input type="text" name="speaker[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['speaker']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="speakerSpecial">Speaker Specialization:</label>
                                                    <input type="text" name="speakerSpecial[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['speaker_special']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="date">Date:</label>
                                                    <input type="text" name="date[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['date']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="timeam">Time AM:</label>
                                                    <input type="text" name="timeam[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['timeam']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="timepm">Time PM:</label>
                                                    <input type="text" name="timepm[<?php echo $session['session_id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($session['timepm']); ?>" required>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center mt-3">
                                        <button type="submit" form="eventForm" class="btn btn-success">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br><br>
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

</html>
