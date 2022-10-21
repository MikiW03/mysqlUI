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

    $host = "localhost";
    $user = "root";
    $password = "";
    $db = "biblioteka";
    $table = "ksiazka";



    $conn = mysqli_connect($host, $user, $password, $db);
    if (!mysqli_connect_errno()) {
        // echo "Połączono z bazą danych";
    } else {
        die("Brak połączenia z bazą danych");
    }

    $res = mysqli_query($conn, "SHOW columns FROM $table");
    $columnsInfo = mysqli_fetch_all($res, MYSQLI_ASSOC);
    $columns = array_map(function ($col) {
        return $col["Field"];
    }, $columnsInfo);
    $primaryKey = array_filter($columnsInfo, function ($col) {
        return $col["Key"] == "PRI";
    });
    $primaryKeyField = $primaryKey[0]['Field'];
    print("<pre>");
    // var_dump($columnsInfo);
    print("</pre>");

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
    $books = mysqli_fetch_all($res, MYSQLI_ASSOC);

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
        ?>

        <!-- <div>
            <label for="kategoria">Kategoria</label>
            <select name="id_kategoria" id="kategoria">
                <?php
                $res = mysqli_query($conn, 'SELECT id_kategoria, nazwa FROM kategoria');
                $data = mysqli_fetch_all($res, MYSQLI_ASSOC);
                var_dump($data);
                foreach ($data as $cat) {
                ?>
                    <option value='<?= $cat["id_kategoria"] ?>'>
                        <?= $cat["nazwa"] ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>

        <div>
            <label for="isbn">ISBN</label>
            <input type="number" name="isbn" id="isbn" required>
        </div>

        <div>
            <label for="tytul">Tytuł</label>
            <input type="text" name="tytul" id="tytul" required>
        </div>

        <div>
            <label for="autor">Autor</label>
            <input type="text" name="autor" id="autor" required>
        </div>

        <div>
            <label for="stron">Liczba stron</label>
            <input type="number" name="stron" id="stron" required>
        </div>

        <div>
            <label for="wydawnictwo">Wydawnictwo</label>
            <input type="text" name="wydawnictwo" id="wydawnictwo" required>
        </div>

        <div>
            <label for="rok_wydania">Rok Wydania</label>
            <input type="number" min="1900" max="<?= date('Y') ?>" step="1" value="<?= date('Y') ?>" name="rok_wydania" id="rok_wydania" required>
        </div>

        <div>
            <label for="opis">Opis</label>
            <textarea name="opis" id="opis"></textarea>
        </div> -->

        <?php

        if (isset($_POST['id_kategoria'])) {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO $table(`id_kategoria`, `isbn`, `tytul`, `autor`, `stron`, `wydawnictwo`, `rok_wydania`, `opis`) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iissisis",
                $_POST['id_kategoria'],
                $_POST['isbn'],
                $_POST['tytul'],
                $_POST['autor'],
                $_POST['stron'],
                $_POST['wydawnictwo'],
                $_POST['rok_wydania'],
                $_POST['opis'],
            );

            mysqli_stmt_execute($stmt);

            if (!mysqli_stmt_errno($stmt)) {
                print("<p class='message'>Inserted succesfully</p>");
            } else {
                print("<p class='message error'>Some error has occured</p>");
            }

            mysqli_stmt_close($stmt);
        }

        ?>

        <input type="submit" value="Insert">

    </form>

    <?php
    printTable($books);
    ?>
</body>

</html>

<?php
mysqli_close($conn);
?>