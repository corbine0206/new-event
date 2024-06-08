<?php
session_start();
include 'connection.php';
$user_id = $_SESSION['user_id'];

$event_id = $_GET['eventID'];
$session_title = $_GET['session_title'];

if (!isset($event_id) || !isset($session_title)) {
    header("location: event.php");
}

$connection = openConnection();

// Fetch attendance data
$attendanceQuery = "SELECT email, dateIn, timeIn FROM attendance WHERE event_id = ? AND session_title = ?";
$stmt = $connection->prepare($attendanceQuery);
$stmt->bind_param("is", $event_id, $session_title);
$stmt->execute();
$stmt->bind_result($email, $date, $time);

$attendances = [];
while ($stmt->fetch()) {
    $attendances[] = [
        'email' => $email,
        'date' => $date,
        'time' => $time,
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
    <title>Participants</title>
    <?php include 'link.php'; ?>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

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
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-calendar"></i> Session: <?php echo htmlspecialchars($session_title); ?></h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Email</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $counter = 1;
                                        foreach ($attendances as $attendance) {
                                            echo "<tr>
                                                <td>" . $counter++ . "</td>
                                                <td>" . htmlspecialchars($attendance['email']) . "</td>
                                                <td>" . htmlspecialchars($attendance['date']) . "</td>
                                                <td>" . htmlspecialchars($attendance['time']) . "</td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

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
    
    <?php include 'script.php'; ?>

</body>
</html>
