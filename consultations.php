<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultations - Search Records</title>
    <style>
        :root { --sidebar: #1a1a2e; --bg: #e8f5e9; --primary: #003399; --accent: #ffcc00; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .sidebar { width: 260px; background: var(--sidebar); color: white; height: 100vh; position: fixed; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .search-box { margin-bottom: 20px; position: relative; width: 400px; }
        .search-box input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 25px; text-indent: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 15px; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; }
        
        /* Button Style for Consultation Link */
        .btn-consultation {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border: 1px solid var(--primary);
            border-radius: 5px;
            transition: all 0.3s;
        }
        .btn-consultation:hover {
            background-color: var(--primary);
            color: white;
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div style="padding: 20px; text-align: center;">
            <h3 style="color: var(--accent);">AI Health Passport</h3>
        </div>
        <ul style="list-style: none; padding: 0;">
            <li><a href="index.php" style="color: white; text-decoration: none; padding: 15px 25px; display: block;">Dashboard / Records</a></li>
            <li style="background: rgba(255,255,255,0.1);"><a href="consultations.php" style="color: white; text-decoration: none; padding: 15px 25px; display: block;">Consultations</a></li>
            <li><a href="logout.php" style="color: #ff6666; text-decoration: none; padding: 15px 25px; display: block;">Sign Out</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="card">
            <h2 style="color: var(--primary);">Patient Consultation Search</h2>
            <p>Type a Name or ID to find a medical record:</p>
            
            <div class="search-box">
                <input type="text" id="consultationSearch" placeholder="🔍 Search patient records...">
            </div>

            <table id="patientTable">
                <thead>
                    <tr>
                        <th>National ID</th>
                        <th>Full Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($conn, "SELECT national_id, full_name FROM patients");
                    while($row = mysqli_fetch_assoc($query)) {
                        $p_id = htmlspecialchars($row['national_id']);
                        $p_name = htmlspecialchars($row['full_name']);
                        
                        echo "<tr>
                                <td>$p_id</td>
                                <td>$p_name</td>
                                <td>
                                    <a href='patient_record.php?id=$p_id' class='btn-consultation'>Start Consultation</a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

<script>
document.getElementById('consultationSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelector("#patientTable tbody").rows;

    for (let i = 0; i < rows.length; i++) {
        let idVal = rows[i].cells[0].textContent.toLowerCase();
        let nameVal = rows[i].cells[1].textContent.toLowerCase();
        
        if (idVal.includes(filter) || nameVal.includes(filter)) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
});
</script>

</body>
</html>