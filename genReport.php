<?php



//time todo: get form _POST
if(isset($_POST["dateStart"])&&isset($_POST["dateEnd"]))
{
    $dateStart=$_POST["dateStart"];
    $dateEnd=$_POST["dateEnd"];
}
else
{
    $months = 24;
    $days = 0;
    $years = 0;

    $dateEnd = date("Y-m-d");
    $dateStart = date("Y-m-d",
        mktime(
            0,
            0,
            0,
            date("m") - $months,
            date("d") - $days,
            date("Y") - $years));
}

try{
    require_once "config_db.php";

    $report = array();

    $qrySens="SELECT * FROM czujnik";
    foreach ( $dbLink->query($qrySens) as $rowSens)
    {
        $paramID = $rowSens["programowy_nr"];
        $qryMeas= $dbLink->prepare("SELECT * FROM pomiar WHERE nr_czujnika=:sensID
                       AND data BETWEEN :dateStart AND :dateEnd");
        $qryMeas->bindParam(':dateStart', $dateStart, PDO::PARAM_STR);
        $qryMeas->bindParam(':dateEnd', $dateEnd, PDO::PARAM_STR);
        $qryMeas->bindParam(':sensID', $paramID, PDO::PARAM_INT);

        $qryMeas->execute();

        foreach ($qryMeas as $rowMeas)
        {
            array_push($report,
                array("nr_czujnika" => $rowMeas["nr_czujnika"],
                    "data" => $rowMeas["data"],
                    "wilgotnosc" =>number_format($rowMeas["wilgotnosc"],2),
                    "temperatura" =>number_format($rowMeas["temperatura"],2)));
        }
        if(isset($_POST["generate"])&&$_POST["generate"]==="gen")
        {

            $fp = fopen('reports/sample.csv', 'wb');
            foreach ($report as $rowMeas)
            {
                $info = array($rowMeas["nr_czujnika"], $rowMeas["data"], $rowMeas["wilgotnosc"], $rowMeas["temperatura"]);
                fputcsv($fp, $info);
            }
            fclose($fp);

            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename="raport.csv"');
            header('Content-Type: text/csv');
            readfile("reports/sample.csv");
            die();
        }
    }


    unset($dbLink);




}
catch(PDOException $e)
{
    echo "Błąd:".$e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="pl">
<link rel="stylesheet" type="text/css" href="stylInterfejs.css">
<head>
    <meta charset="UTF-8">
    <title>Raport</title>
</head>
<body>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group <?php echo (!empty($dateStartErr)) ? 'has-error' : ''; ?>">
        <label>Od:
            <input type="date" name="dateStart" class="form-control" value="<?php echo $dateStart; ?>">
        </label>
        <span class="help-block"><?php echo isset($dateStartErr); ?></span>
    </div>
    <div class="form-group <?php echo (!empty($dateEndErr)) ? 'has-error' : ''; ?>">
        <label>Do:
            <input type="date" name="dateEnd" class="form-control" value="<?php echo $dateEnd; ?>">
        </label>
        <span class="help-block"><?php echo isset($dateEndErr); ?></span>
    </div>

    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Zmień">
        <input type="reset">
    </div>
</form>
<div>
    <table>
        <thead>
            <tr>
                <th>Nr czujnika</th>
                <th>Data pomiaru</th>
                <th>Wilgotność</th>
                <th>Temperatura</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($report as $rowTable)
        {
            echo "<tr>".
                "<td>".$rowTable['nr_czujnika']."</td>".
                "<td>".$rowTable['data']."</td>".
                "<td>".$rowTable['wilgotnosc']."</td>".
                "<td>".$rowTable['temperatura']."</td>".
                "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<div>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="generate" value="gen" />
        <input type="hidden" name="dateStart" value="<?php echo $dateStart; ?>" />
        <input type="hidden" name="dateEnd" value="<?php echo $dateEnd; ?>" />
        <input type="submit" name="gen" value="Generuj raport">
        <input type="button" onclick="location='interfejsGlowny.phtml'" value="Powrót">
    </form>
</div>
</body>
</html>
