<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Ticket</title>
    <link rel="stylesheet" href="css/ticket_style.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="container">
        <h2>Assign Ticket</h2>
        <form action="process_assign_ticket.php" method="post">
            <label for="ticket">Select a Ticket:</label>
            <select name="ticket" id="ticket">
                <?php
                // Include database connection
                include 'db-connect.php';

                // SQL query to fetch tickets
                $sql = "SELECT ticket_id, task_name FROM ticketing_table WHERE status = 'Open'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row["ticket_id"] . '">' . $row["task_name"] . '</option>';
                    }
                } else {
                    echo '<option value="">No open tickets found</option>';
                }

                // Close connection
                $conn->close();
                ?>
            </select>

            <label for="assigned_to">Assign To:</label>
            <input type="text" id="assigned_to" name="assigned_to" placeholder="Enter user's name">

            <input type="submit" value="Assign">
        </form>
    </div>
</body>
</html>
