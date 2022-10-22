<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>

<body>
    <?php
    require_once("helpers.php");
    session_start();

    if (isset($_POST['host'])) {
        $_SESSION['db_conn'] = [];
        $_SESSION['db_conn']['host'] = $_POST['host'];
        $_SESSION['db_conn']['user'] = $_POST['user'];
        $_SESSION['db_conn']['password'] = $_POST['password'];
        $_SESSION['db_conn']['db'] = $_POST['db'];
        $_SESSION['db_conn']['table'] = $_POST['table'];
    }

    ?>

    <form action="" method="POST" class="form">
        <fieldset class="init-form">
            <legend>Log in to your database</legend>
            <div>
                <label for="host">host</label>
                <input type="text" name="host" id="host" value="<?= $_SESSION['db_conn']['host'] ?? "" ?>">
            </div>
            <div>
                <label for="user">user</label>
                <input type="text" name="user" id="user" value="<?= $_SESSION['db_conn']['user'] ?? "" ?>">
            </div>
            <div>
                <label for="password">password</label>
                <input type="password" name="password" id="password">
            </div>
            <div>
                <label for="db">database</label>
                <input type="text" name="db" id="db" value="<?= $_SESSION['db_conn']['db'] ?? "" ?>">
            </div>
            <div>
                <label for="table">table</label>
                <input type="text" name="table" id="table" value="<?= $_SESSION['db_conn']['table'] ?? "" ?>">
            </div>
            <input class="btn" type="submit" value="go">
        </fieldset>
    </form>

    <?php

    if (!empty($_SESSION['db_conn'])) {
        $host =
            $_SESSION['db_conn']['host'];
        $user = $_SESSION['db_conn']['user'];
        $password = $_SESSION['db_conn']['password'];
        $db = $_SESSION['db_conn']['db'];
        $table = $_SESSION['db_conn']['table'];

        $conn = mysqli_connect($host, $user, $password, $db);
        if (!mysqli_connect_errno()) {
            // echo "Połączono z bazą danych";
        } else {
            die("Brak połączenia z bazą danych");
        }

        $res = mysqli_query($conn, "SHOW columns FROM $table");
        $columnsInfo = mysqli_fetch_all($res, MYSQLI_ASSOC);
        $columnsInfoWithoutPrimary =
            array_filter($columnsInfo, function ($col) {
                return $col["Key"] !== "PRI";
            });
        $columns = array_map(function ($col) {
            return $col["Field"];
        }, $columnsInfo);
        $columnsWithoutPrimary =
            array_map(function ($col) {
                return $col["Field"];
            }, $columnsInfoWithoutPrimary);
        $primaryKey = array_filter($columnsInfo, function ($col) {
            return $col["Key"] == "PRI";
        })[0];
        $primaryKeyField = $primaryKey['Field'];

    ?>

        <?php
        if (isset($_POST['delete'])) {
            $id = strip_tags($_POST['delete']);
            mysqli_query($conn, "DELETE FROM $table WHERE $primaryKeyField = $id");
        }


        if (isset($_POST['order'])) {
            $orderPost = strip_tags($_POST['order']);

            [$order, $ascDesc] = explode(" ", $orderPost, 2);
            $res = mysqli_query($conn, "SELECT * FROM $table ORDER BY $order $ascDesc");
        } else {
            $res = mysqli_query($conn, "SELECT * FROM $table");
        }
        $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);

        ?>

        <form action="" method="POST" class='insert-form'>
            <?php
            foreach ($columnsInfo as $col) {
                if ($col['Extra'] !== "auto_increment") {
                    $type = mysqlTypeToHtml($col['Type']);
            ?>
                    <div>
                        <label for="<?= $col['Field'] ?>"><?= $col['Field'] ?></label>
                        <?php

                        if ($type['tag'] == "input") {
                        ?>
                            <input type="<?= $type['type'] ?>" name="<?= $col['Field'] ?>" id="<?= $col['Field'] ?>" required>
                        <?php
                        } else if ($type['tag'] == "textarea") {
                        ?>
                            <textarea name="<?= $col['Field'] ?>" id="<?= $col['Field'] ?>"></textarea>
                        <?php
                        }
                        ?>
                    </div>
            <?php
                }
            }

            if (isset($_POST[reset($columnsInfoWithoutPrimary)['Field']])) {
                $sql =
                    "INSERT INTO $table(" . implode(", ", $columnsWithoutPrimary) . ") 
             VALUES (" . implode(", ", array_fill(0, count($columnsWithoutPrimary), "?")) . ")";

                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    implode("", array_fill(0, count($columnsWithoutPrimary), "s")),
                    ...array_values($_POST)
                );
                print(mysqli_stmt_get_result($stmt));
                mysqli_stmt_execute($stmt);

                if (!mysqli_stmt_errno($stmt)) {
                    print("<p class='message'>Inserted succesfully</p>");
                } else {
                    print("<p class='message error'>Some error has occured</p>");
                }

                mysqli_stmt_close($stmt);
            }

            ?>

            <input class="btn" type="submit" value="insert">
        </form>

        <?php
        printTable($rows);
        ?>
</body>

</html>

<?php
        mysqli_close($conn);
    }
?>