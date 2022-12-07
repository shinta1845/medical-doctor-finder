<?php
session_start();
require_once "database.php";
$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username!";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password!";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_username);

            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            $_SESSION["username"] = $username;
                            // get patient's type and redirect to the right home page
                            $sql = "SELECT type FROM users WHERE username = ?";
                            if ($stmt = $mysqli->prepare($sql)) {
                                $stmt->bind_param("s", $param_username);
                                $param_username = $username;
                                if ($stmt->execute()) {
                                    $stmt->store_result();
                                    if ($stmt->num_rows == 1) {
                                        $stmt->bind_result($type);
                                        if ($stmt->fetch()) {
                                            if ($type == "patient") {
                                                header("location: ../patient/home.php");
                                            } else if ($type == "doctor") {
                                                header("location: ../doctor/home.php");
                                            } else if ($type == "admin") {
                                                header("location: ../admin/home.php");
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $password_err = "The password you entered was not valid!";
                        }
                    }
                } else {
                    $username_err = "No account found with that username!";
                }
            } else {
                echo "Oops! Something went wrong, please try again later.";
            }

            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}
?>