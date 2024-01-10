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

    <?php include 'link.php'; ?>

    <!-- Add the following line to include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        $(document).ready(function () {
            // Show Table A by default
            showTable('tableA');

            // Handle tab clicks
            $('#tabA').click(function () {
                showTable('tableA');
            });

            $('#tabB').click(function () {
                showTable('tableB');
            });

            function showTable(tableName) {
                // Hide all tables
                $('.table-container').hide();

                // Show the selected table
                $('#' + tableName).show();
            }
        });
    </script>
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

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="myTabs">
                        <li class="nav-item">
                            <a class="nav-link" id="tabA" href="#">Participants</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tabB" href="#">Survey</a>
                        </li>
                    </ul>

                    <!-- Table A Content -->
                    <div id="tableA" class="table-container">
                        <!-- Table A HTML goes here -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Participants</th>
                                            <th>Answer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $con = openConnection();
                                        $event_id = $_GET['eventID'];
                                        $strSql = "SELECT * from participants where event_id = '$event_id'";
                                        $result = getRecord($con, $strSql);
                                        foreach ($result as $key => $participants) {
                                            echo 
                                            '<tr>
                                                <td>' . ($key + 1) . '</td>
                                                <td>' . $participants['email'] . '</td>
                                                <td>';
                                                if($participants['status'] == 1){
                                                    echo '<span class="badge badge-success"><i class="fa fa-check" aria-hidden="true"></i></span>';
                                                }
                                                elseif($participants['status'] == 0)
                                                echo '<span class="badge badge-danger"><i class="fa fa-times" aria-hidden="true"></i></span>';
                                            echo '</td>';
                                                
                                        }
                                        closeConnection($con);

                                        ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Table B Content -->
                    <div id="tableB" class="table-container">
                        <!-- Table B HTML goes here -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Comment</th>
                                            <th>Suggestion</th>
                                            <th>Similar event</th>
                                            <th>Technology</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
    <?php
    $con = openConnection();
    $event_id = $_GET['eventID'];
    $strSql = "SELECT comment.*, survey.technology_line
               FROM comment
               LEFT JOIN survey ON comment.comment_id = survey.comment_id
               WHERE comment.event_id = '$event_id'";
    $result = getRecord($con, $strSql);

    $prevCommentId = null; // To keep track of the previous comment_id
    foreach ($result as $key => $comment) {
        echo 
        '<tr>
            <td>' . ($key + 1) . '</td>
            <td>' . $comment['comment'] . '</td>
            <td>' . $comment['suggestion'] . '</td>
            <td>' . $comment['similar_event'] . '</td>
            <td>';

        // Check if the comment_id has changed, indicating a new comment
        if ($prevCommentId !== $comment['comment_id']) {
            // Display all technologies for the current comment
            $technologies = array();
            foreach ($result as $tech) {
                if ($tech['comment_id'] === $comment['comment_id']) {
                    $technologies[] = $tech['technology_line'];
                }
            }
            echo implode('<br> ', $technologies);
        }

        echo '</td>
            <td>' . $comment['email'] . '</td>
        </tr>';

        $prevCommentId = $comment['comment_id'];
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
