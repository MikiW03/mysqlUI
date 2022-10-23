<?php
function mysqlTypeToHtml($mysqlType)
{
    $type = [];
    $size = [];
    preg_match('/\w{1,}/', $mysqlType, $type);
    preg_match('/\(\d{1,}\)/', $mysqlType, $size);

    $type = $type[0];
    if ($size) {
        $size = (int)substr($size[0], 1, -1);
    }

    if ($type == 'varchar') {
        return ['tag' => 'input', 'type' => 'text'];
    } else if ($type == 'int') {
        return ['tag' => 'input', 'type' => 'number'];
    } else if ($type == 'datetime') {
        return ['tag' => 'input', 'type' => 'datetime-local'];
    } else if ($type == 'text') {
        return ['tag' => 'textarea'];
    } else {
        return ['tag' => 'input', 'type' => 'text'];
    }
};

function printTable($data)
{
    if (empty($data)) {
        print("<p style='text-align: center'>Table is empty</p>");
        return;
    };

    if (count($data) > 0) {
        print("<table>");

        print("<tr>");

        print("<th>");
        print("delete entire row");
        print("</th>");

        foreach ($data[0] as $title => $_) {

            print("<th>");
            print("<form action='' method='post'>");


            print("<div class='title'>");
            print($title);
            print("</div>");

            print("<div class='buttons'>");

            print("<button name='order' value='$title asc'>");
            print("asc");
            print("</button>");
            print("<button name='order' value='$title desc'>");
            print("desc");
            print("</button>");

            print("</div>");

            print("</form>");
            print("</th>");
        }
        print("</tr>");

        foreach ($data as $row) {
            print("<tr>");

            print("<td class='delete-btn'>");
            print("<form action='' method='POST'>");
            $id = reset($row);
            print("<button name='delete' value='$id'>X</button>");
            print("</form>");
            print("</td>");

            foreach ($row as $col) {
                print("<td>");
                print($col);
                print("</td>");
            }

            print("</tr>");
        }
        print("</table>");
    }
}
