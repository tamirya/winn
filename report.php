<?php
/**
 * Created by PhpStorm.
 * User: mofet
 * Date: 03/10/2017
 * Time: 10:41
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
require("simple_html_dom.php");

$sliced_array = array();

for( $j=0;$j<5;$j++ ) {

    $data = array();
    $dateStr = "+".$j." day";
    $day    = date("d-m-Y",strtotime($dateStr));
    $url = "https://www.winner.co.il/mainbook/sport-%D7%9B%D7%93%D7%95%D7%A8%D7%92%D7%9C?date=$day&marketTypePeriod=1%7C100";
// Create DOM from URL or file
    $html = file_get_html($url);
    if( $html === false ){
        continue;
    }

    $ret = $html->find('tr[class=event]');

    foreach ($ret as $i => $element) {
        $type = trim($element->children[3]->plaintext);
        if (strpos($type, 'S') !== false) {

        }else{
            continue;
        }
        $gameIdFromUrl = $element->children[2]->children[0]->children[0]->children[0]->attr['class'];
        $gameId = explode(' ', $gameIdFromUrl);
        $gameId = str_replace("outcome_", "", $gameId[1]);

        //firstName
        $firstName = $element->children[2]->children[0]->children[0]->children[0]->children[0]->children[0]->children[0];
        $firstBet = $element->children[2]->children[0]->children[0]->children[0]->children[0]->children[0]->children[1];
        $firstBet = trim(str_replace("</img>", "", $firstBet->plaintext));

        //second Bet
        $secondName = $element->children[2]->children[0]->children[0]->children[2]->children[0]->children[0]->children[0];
        $secondBet = $element->children[2]->children[0]->children[0]->children[2]->children[0]->children[0]->children[1];

        $secondBet = trim(str_replace("</img>", "", $secondBet->plaintext));

        $diff = abs(floatval($firstBet) - floatval($secondBet));

        $time = $element->children[1]->plaintext;

        if (isset($element->children[3])) {

        }


        $data[] = array(
            'firstName' => trim($firstName->plaintext),
            'firstBet' => trim($firstBet),
            'secondName' => trim($secondName->plaintext),
            'secondBet' => trim($secondBet),
            'diff' => $diff,
            'time' => $day.$time,
            'type' => $type,
            'gameId' => $gameId
        );
    }

    usort($data, 'cmp');
    $sliced_array[] = array_slice($data, 0, 2);
}

function cmp($a, $b)
{
    return $a['diff'] < $b['diff'];
}

function compareFloatNumbers($float1, $float2, $operator='=')
{

    // Check numbers to 5 digits of precision
    $epsilon = 0.00001;

    $float1 = (float)$float1;
    $float2 = (float)$float2;

    switch ($operator)
    {

        // equal
        case "=":
        case "eq":
        {
            if (abs($float1 - $float2) < $epsilon) {
                return true;
            }
            break;
        }

        // greater than
        case ">":
        case "gt":
        {
            if (abs($float1 - $float2) < $epsilon) {
                return false;
            }
            else
            {
                if ($float1 > $float2) {
                    return true;
                }
            }
            break;
        }
        // less than
        case "<":
        case "lt":
        {
            if (abs($float1 - $float2) < $epsilon) {
                return false;
            }
            else
            {
                if ($float1 < $float2) {
                    return true;
                }
            }
            break;
        }
    }

}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.8/js/jquery.tablesorter.min.js"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.8/css/dragtable.mod.min.css"/>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="/index.php">Winner</a>
    </div>
    <ul class="nav navbar-nav">
	  <li><a href="statistics.php">Games Statistics</a></li>
	  <li class="active" ><a href="report.php">Report</a></li>
    </ul>
  </div>
</nav>
<div class="container-fluid">


<table class="table table-hover table-bordered">
    <thead>
    <tr>
        <th>Time</th>
        <th>1</th>
        <th>2</th>
        <th>Diff</th>
    </tr>
    </thead>
    <tbody>
        <?php
        foreach ($sliced_array as $games) {
            foreach ($games as $game){
                echo '<tr>';
                    echo '<td>';
                        echo $game['time'];
                    echo '</td>';
                    echo '<td>';
                        echo $game['firstName'].'-'.$game['firstBet'];
                    echo '</td>';
                    echo '<td>';
                        echo $game['secondName'].'-'.$game['secondBet'];
                    echo '</td>';

                    if( compareFloatNumbers($game['firstBet'] , $game['secondBet'] , '>' ) ){

                        $diff = (float)$game['firstBet'] - (float)$game['secondBet'];

                    }else{
                        $diff = (float)$game['secondBet'] - (float)$game['firstBet'];

                    }
                    echo '<td>';
                        echo $diff;
                    echo '</td>';
                echo '</tr>';
            }
        }

        ?>
    </tbody>
</table>


    <script>
        $(document).ready(function () {
                $(".table").tablesorter({
                    sortList: [[3,1]]
                });

            }
        );
    </script>

</body>
</html>



