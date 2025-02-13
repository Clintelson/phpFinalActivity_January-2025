<?php

session_start();

if (!isset($_SESSION['ID'])) {
    header('location: auth/signin.php');
} 

include('../database/database.php');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM information";

if (!empty($search)) {
  $query .= " WHERE firstName LIKE '%$search%' 
                OR middleName LIKE '%$search%' 
                OR lastName LIKE '%$search%' 
                OR age LIKE '%$search%' 
                OR sex LIKE '%$search%' 
                OR birthdate LIKE '%$search%' 
                OR bloodType LIKE '%$search%' 
                OR religion LIKE '%$search%' 
                OR yearLevel LIKE '%$search%' 
                OR idNumber LIKE '%$search%' 
                OR email LIKE '%$search%'";
}

$result = $conn->query($query);

if(isset($_POST['Delete'])) {

    $id = $_POST['del'];

    $del = mysqli_query($conn,"DELETE FROM information WHERE ID ='$id'");

    if($del) {
        echo "<script>
        alert ('Success');
        document.location.href='index.php';
        </script>"; 

    } else {
       echo"<script>
        alert ('failed');
        document.location.href ='index.php';
        </script>";         

    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = $_POST['user'];
    $password = $_POST['pass'];
    $confirmPassword = $_POST['confpass'];

    if (empty($username) || empty($password)) {
        
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        
        $error = "Passwords do not match.";
    } else {
        $sql = "INSERT INTO user (Username, Password) 
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            header('location: index.php');
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/output.css">
    <title>viewdata</title>

    <style>
    table {
        border-collapse: collapse;
        width: 100%;
        text-align: center;
        margin-top: 20px;
    }

    td,
    th {
        border: 1px solid rgb(185, 185, 185);
        border-left: none;
        border-right: none;
        padding: 15px;
    }

    .input {
        outline: none;
        padding: 7px 10px 7px 10px;
        border: solid 2px gray;
        border-radius: 17px;
    }

    .error-message {
        color: red;
        font-size: 14px;
        text-align: center;
    }
    </style>
</head>

<body class="w-screen h-dvh bg-gray-400 p-10 font-mono flex flex-col gap-2 md:gap-5 relative">

    <!-- modal -->
    <div id="modal" class="w-full h-dvh overflow-auto hidden absolute inset-0 bg-black bg-opacity-40"
        style="z-index: 1;">

        <div
            class="w-full max-w-96 border-2 p-10 rounded-xl bg-[#00aeae] fixed right-1/2 bottom-1/2 transform translate-x-1/2 translate-y-1/2">
            <button class="text-black absolute text-4xl font-bold right-7 top-2 hover:scale-110"
                onclick="closeModal()">&times;</button>

            <form method="POST" class="flex flex-col gap-5 mt-2">

                <div class="flex flex-col">
                    <label for="user">Username</label>
                    <input id="user" name="user" type="text" class="input">
                </div>

                <div class="flex flex-col">
                    <label for="pass">Password</label>
                    <input id="pass" name="pass" type="password" class="pass input">
                </div>

                <div class="flex flex-col">
                    <label for="confpass">Confirm Password</label>
                    <input id="confpass" name="confpass" type="password" class="pass input">

                    <div class="mt-2 ml-1 w-full flex items-center gap-1 text-right">
                        <input id="show" type="checkbox" onclick="myFunction()"
                            class="w-4 h-4 accent-blue-500 rounded-md border-gray-300 focus:ring focus:ring-blue-300">
                        <label for="show">Show Password</label>
                    </div>
                </div>

                <?php if (!empty($error)) : ?>
                <div class="error-message w-full flex items-center justify-center">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <button type="submit" name="crt"
                    class="w-full h-12 bg-[#CC5500] hover:border rounded-3xl py-3 mt-5 hover:bg-transparent">
                    Create
                </button>

            </form>

        </div>
    </div>

    <div class="w-full h-20 flex flex-row gap-2 items-center justify-between relative">

        <div class="flex flex-row gap-2 items-center relative">
            <button onclick="window.location.href='auth/logout.php'" class="h-10 w-10 hidden md:block">
                <img src="../public/img/logout.svg" alt="logout">
            </button>
            <form method="GET" action=""
                class="bg-white flex flex-row w-fit px-3 py-1 border border-1 border-black rounded-3xl">
                <input type="text" name="search" class="bg-transparent outline-none"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="w-10 px-2 py-1 border-l-2 flex justify-center items-center">
                    <img src="../public/img/search.svg" alt="search">
                </button>
            </form>
        </div>

        <h1 class="text-[clamp(2rem,5vw,3rem)] absolute left-1/2 -top-5">DATA</h1>

        <div class="flex flex-row gap-2 items-center">
            <button onclick="window.location.href='form.php'"
                class=" h-10 w-24 bg-orange-400 rounded-lg hover:bg-transparent hover:border hover:border-black transform duration-300 flex items-center justify-center">
                ADD DATA
            </button>

            <button id="openModal" onclick="window.location.href='index.php?createAccount'"
                class=" h-10 w-36 bg-orange-400 rounded-lg hover:bg-transparent hover:border hover:border-black transform duration-300 flex items-center justify-center">
                CREATE ACCOUNT
            </button>
        </div>

    </div>

    <div class="block md:hidden">
        <button onclick="window.location.href='auth/logout.php'" class="h-10 w-10">
            <img src="../public/img/logout.svg" alt="logout">
        </button>
    </div>
    <table class="overflow-x-auto">
        <tr>
            <th>#</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Birthdate</th>
            <th>Blood Type</th>
            <th>Religion</th>
            <th>Year Level</th>
            <th>ID Number</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php
            if ($result->num_rows > 0) {
                $count = 1; 
                while ($items = $result->fetch_assoc()) {
            ?>
        <tr>
            <td><?php echo $count++; ?></td>
            <td><?php echo $items["FirstName"]; ?></td>
            <td><?php echo $items["MiddleName"]; ?></td>
            <td><?php echo $items["LastName"]; ?></td>
            <td><?php echo $items["Age"]; ?></td>
            <td><?php echo $items["Sex"]; ?></td>
            <td><?php echo $items["Birthdate"]; ?></td>
            <td><?php echo $items["bloodType"]; ?></td>
            <td><?php echo $items["Religion"]; ?></td>
            <td><?php echo $items["yearLevel"]; ?></td>
            <td><?php echo $items["idNumber"]; ?></td>
            <td><?php echo $items["email"]; ?></td>
            <td>
                <form action=" " method="POST" class="flex gap-1">
                    <input type="hidden" name="del" value="<?php echo $items["ID"]; ?>">
                    <button type="submit" name="Delete" class="hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                            class="size-6 text-red-600">
                            <path fill-rule="evenodd"
                                d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- update button -->
                    <button type="button" class="hover:scale-110"
                        onclick="window.location.href='update.php?id=<?php echo $items['ID']; ?>'">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                            class="size-6 text-blue-700">
                            <path
                                d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                            <path
                                d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                        </svg>
                    </button>
                </form>
            </td>
        </tr>
        <?php
                  }
              } else {
                  echo "<tr><td colspan='13'>No data found</td></tr>";
              }

              $conn->close();
            ?>
    </table>

    <script>
    var modal = document.getElementById("modal");
    var btn = document.getElementById("openModal");

    // Open modal and update the URL
    btn.onclick = function() {
        modal.style.display = "block";
        history.pushState({
            modalOpen: true
        }, '', '?createAccountModal=true');
    };

    // Close modal and update the URL
    function closeModal() {
        modal.style.display = "none";
        history.pushState({}, '', 'index.php');
    }

    // Close modal when clicking outside it
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    };

    // Handle browser back/forward button
    window.onpopstate = function(event) {
        if (event.state && event.state.modalOpen) {
            modal.style.display = "block";
        } else {
            modal.style.display = "none";
        }
    };

    // Ensure modal stays open on page load if URL has the parameter
    window.onload = function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('createAccountModal') === 'true') {
            modal.style.display = "block";
        }
    };

    function myFunction() {
        const passwordFields = document.querySelectorAll(".pass");
        passwordFields.forEach(field => {
            if (field.type === "password") {
                field.type = "text";
            } else {
                field.type = "password";
            }
        });
    }
    </script>
</body>

</html>