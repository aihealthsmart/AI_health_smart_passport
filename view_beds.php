<?php
// 1. Include your database connection file
include 'db.php'; 

// 2. Write the SQL query
$sql = "SELECT id, word_name, total_beds, occupied_beds, last_updated FROM bed_management";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bed Management System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f4f4f4; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-low { color: green; font-weight: bold; }
        .status-full { color: red; font-weight: bold; }
    </style>
</head>
<body>

    <h2>Hospital Bed Management</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ward Name</th>
                <th>Total Beds</th>
                <th>Occupied</th>
                <th>Available</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                // 3. Loop through each row of the database
                while($row = mysqli_fetch_assoc($result)) {
                    $available = $row['total_beds'] - $row['occupied_beds'];
                    
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . $row["word_name"] . "</td>";
                    echo "<td>" . $row["total_beds"] . "</td>";
                    echo "<td>" . $row["occupied_beds"] . "</td>";
                    echo "<td>" . $available . "</td>";
                    echo "<td>" . $row["last_updated"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No data found</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
// 4. Close the connection
mysqli_close($conn);
?>