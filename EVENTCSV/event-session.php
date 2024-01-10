<?php
    session_start();
    include 'connection.php';
    $user_id = $_SESSION['user_id'];

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
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-calendar"></i>SESSION(S)</h6>
                        </div>
                        <div class="card-body">
                            <a href="event-add.php" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50">
                                    <i class="fas fa-plus"></i>
                                </span>
                            </a>
                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Session Name</th>
                                            <th>Date</th>
                                            <th>Time AM</th>
                                            <th>Time PM</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $con = openConnection();
                                        $event_id = $_GET['eventID'];
                                        $strSql = "SELECT DISTINCT session_title, date, timeam, timepm FROM event_sessions where event_id = '$event_id'";
                                        $result = getRecord($con, $strSql);
                                        foreach ($result as $key => $session) {
                                            echo 
                                            '<tr>
                                                <td>' . ($key + 1) . '</td>
                                                <td>' . $session['session_title'] . '</td>
                                                <td>' . $session['date'] . '</td>
                                                <td>' . $session['timeam'] . '</td>
                                                <td>' . $session['timepm'] . '</td>
                                                <td>
                                                <a href="scan.php?eventID=' . $event_id . '&session_title='. $session['session_title'] .'" class="btn btn-info"><i class="fas fa-qrcode"></i></a>
                                                </td>
                                            </tr>';
                                        }
                                        closeConnection($con);

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

    

    <?php include'script.php'; ?>

</body>
</html>

