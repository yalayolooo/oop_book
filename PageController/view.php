// Листинг 12.38
<html>
    <head>
        <title>Добавление заведения</title>
    </head>
    <body>
        <h1>
            Добавление заведения
        </h1>
        <table>
            <tr>
            <td>
                <?php
                print_r($request->getFeedbackString("</td></tr><tr><td>"));
                ?>
            </td>
            </tr>
        </table>
        <form action=""/PageController.php" method="get">
            <input type="hidden" name="submitted" value="yes" />
            <input type="text" name="venue_name" />
        </form>
    </body>
</html>