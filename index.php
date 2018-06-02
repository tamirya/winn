<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
require("simple_html_dom.php");
require("nav.php");
date_default_timezone_set( 'Asia/Jerusalem' );

$hebrewDates = array(
		'Sunday'=>'ראשון',
		'Monday'=>'שני',
		'Tuesday'=>'שלישי',
		'Wednesday'=> 'רביעי',
		'Thursday'=> 'חמישי',
		'Friday'=> 'שישי',
		'Saturday'=>'שבת'
	);

$market = array(
    "1.05" => 0,
    "1.10" => 0,
    "1.15" => 0,
    "1.20" => 0,
    "1.25" => 0,
    "1.30" => 0,
    "1.35" => 0,
    "1.40" => 0,
    "1.45" => 0,
    "1.50" => 0,
    "1.55" => 0,
    "1.60" => 0,
    "1.65" => 0,
    "1.70" => 0,
    "1.75" => 0,
    "1.80" => 0,
    "1.85" => 0,
    "1.90" => 0,
    "1.95" => 0,
);

$selectedDate = isset(  $_GET['date'] ) ? $_GET['date']  : date("d-m-Y");
$url = "https://www.winner.co.il/mainbook/sport-%D7%9B%D7%93%D7%95%D7%A8%D7%92%D7%9C?date=$selectedDate&marketTypePeriod=1%7C100";
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
     
//dates
$oneDate    = date("d-m-Y");
$twoDate    = date("d-m-Y",strtotime("+1 day"));
$threeDate  = date("d-m-Y",strtotime("+2 day"));
$fourDate   = date("d-m-Y",strtotime("+3 day"));
$fiveDate   = date("d-m-Y",strtotime("+4 day"));
$sixDate    = date("d-m-Y",strtotime("+5 day"));

$oneDateDay   =  date('l', strtotime( $oneDate ));
$twoDateDay   =  date('l', strtotime( $twoDate ));
$threeDateDay =  date('l', strtotime( $threeDate ));
$fourDateDay  =  date('l', strtotime( $fourDate ));
$fiveDateDay  =  date('l', strtotime( $fiveDate ));
$sixDateDay   =  date('l', strtotime( $sixDate ));


//dates links
$oneDateLink = $baseUrl."/winn/index.php?date=$oneDate";
$twoDateLink = $baseUrl."/winn/index.php?date=$twoDate";
$threeDateLink = $baseUrl."/winn/index.php?date=$threeDate";
$fourDateLink = $baseUrl."/winn/index.php?date=$fourDate";
$fiveDateLink = $baseUrl."/winn/index.php?date=$fiveDate";
$sixDateLink = $baseUrl."/winn/index.php?date=$sixDate";

// Create DOM from URL or file
$html = file_get_html( $url );
if(  !is_object($html) ){
	$selectedDate = $twoDate;
	$url = "https://www.winner.co.il/mainbook/sport-%D7%9B%D7%93%D7%95%D7%A8%D7%92%D7%9C?date=$selectedDate&marketTypePeriod=1%7C100";
	$html = file_get_html( $url );
}
$ret = $html->find('tr[class=event]');

$data = array();
foreach ($ret as $i => $element) {
    $gameIdFromUrl = $element->children[2]->children[0]->children[0]->children[0]->attr['class'];
    $gameId = explode(' ', $gameIdFromUrl);
    $gameId =  str_replace("outcome_","", $gameId[1] );

    //firstName
    $firstName = $element->children[2]->children[0]->children[0]->children[0]->children[0]->children[0]->children[0];
    $firstBet = $element->children[2]->children[0]->children[0]->children[0]->children[0]->children[0]->children[1];
    $firstBet = trim(str_replace("</img>", "", $firstBet->plaintext));

    $tieBet = $element->children[2]->children[0]->children[0]->children[1]->children[0]->children[0]->children[1];
    $tieBet = trim(str_replace("</img>", "", $tieBet->plaintext));

    //second Bet
    $secondName = $element->children[2]->children[0]->children[0]->children[2]->children[0]->children[0]->children[0];
    $secondBet = $element->children[2]->children[0]->children[0]->children[2]->children[0]->children[0]->children[1];

    $secondBet = trim(str_replace("</img>", "", $secondBet->plaintext));

    $diff = abs(floatval($firstBet) - floatval($secondBet));
    $time = trim( $element->children[1]->plaintext );
    $type = trim($element->children[3]->plaintext);
		
    $data[] = array(
        'firstName' => trim($firstName->plaintext),
        'firstBet' => trim($firstBet),
        'tieBet'=>$tieBet,
        'secondName' => trim($secondName->plaintext),
        'secondBet' => trim($secondBet),
        'diff' => $diff,
        'time' => $time,
        'type' => $type,
        'gameId'=>$gameId
    );
}

usort($data, 'cmp');

function cmp($a, $b)
{
    return $a['diff'] < $b['diff'];
}

/*$conn = connectDB("winn");
$conn->query("SET NAMES 'utf8'");

$gamesData = array();

if( $conn->query("SELECT * FROM `games` where dategame = '$selectedDate' ")->num_rows == 0 ){

foreach ( $data as $v ) {

    $first_team_name = $v['firstName'] ;   
    $first_team_bet = $v['firstBet'];
    $tieBet = $v['tieBet'];
    $second_team_name = $v['secondName'];
    $second_team_bet  = $v['secondBet'];
    $gamediff =  $v['diff'];
    $gametime = $v['time'];
    $gameId   = $v['gameId'];
    $gameType = $v['type'];

    $sql ="INSERT INTO games (first_team_name,first_team_bet,second_team_name,second_team_bet,tie_team_bet,diff,time,dategame,game_id_url,game_type) 
           VALUES ( '$first_team_name' , '$first_team_bet' , '$second_team_name' , '$second_team_bet' ,'$tieBet', '$gamediff' , '$gametime' ,'$selectedDate' , '$gameId','$gameType' ) ";

    if ($conn->query($sql) === TRUE) {

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }  
}

}else{

    /// Check if game exsits in DB
        foreach ($data as $v) {
            $gameId   = $v['gameId'];
            $sql = "SELECT * FROM `games` where dategame = '$selectedDate' and game_id_url = '$gameId'";

            if( $conn->query( $sql )->num_rows == 0 ){
                // insert game to db
                $first_team_name = $v['firstName'] ;   
                $first_team_bet = $v['firstBet'];
                $second_team_name = $v['secondName'];
                $second_team_bet  = $v['secondBet'];
                $tieBet = $v['tieBet'];
                $gamediff =  $v['diff'];
                $gametime = $v['time'];
                $gameType = $v['type'];

                $sql ="INSERT INTO games (first_team_name,first_team_bet,second_team_name,second_team_bet,tie_team_bet,diff,time,dategame,game_id_url,game_type) 
           VALUES ( '$first_team_name' , '$first_team_bet' , '$second_team_name' , '$second_team_bet' ,'$tieBet', '$gamediff' , '$gametime' ,'$selectedDate' , '$gameId','$gameType' ) ";

                if ($conn->query($sql) === TRUE) {

                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                } 
            }
        }
    
   $re =  $conn->query("SELECT game_id_url,diff FROM `games` where dategame= '$selectedDate' ");
    if ($re->num_rows > 0) {

        // output data of each row
        while($row = $re->fetch_assoc()) {
            $gamesData[$row['game_id_url']] = $row['diff'] ;
        }
    }
           
}

$conn->close();*/

function connectDB($dbname){
    $servername = "localhost";
    $username = "root";
    $password = "";

    // Create connection
    $conn = new mysqli($servername, $username, $password,$dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //echo "Connected successfully";
    return $conn;
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
    <meta http-equiv="refresh" content="620">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.8/js/jquery.tablesorter.min.js"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.8/css/dragtable.mod.min.css"/>

    <style>
        .highest {
            background-color: aquamarine;
        }
        .gameUp{
            color: crimson;
        }
        .gameDown{
            color: orange;
        }
		.dates{
			padding: 0;
			font-size: 15px;
			margin-bottom: 25px;
		}

.dateSelected{
    background-color: antiquewhite;
    border-radius: 11px;
    text-align: center;
}

.notSelected{
	background-color: transparent;
    border-radius: 141px;
    width: 62%;
    display: block;
    padding: 5px;
    text-align: center;
}
.div-width{
    list-style-type: none;
    display: inline;
    float: left;
    margin-right: 84px;
    margin-bottom: 20px;
	}
.div-width:last-child{
	margin-right: 0;
}
li.div-width a {
    display: inline;
	padding: 5px;
}
.tr-mark{
     background-color: yellow;
}
    </style>
</head>
<body>
<?= $nav ?>
<div class="container">

<ul class="dates">
	
	<li class="div-width">
		<a  class="<?= $selectedDate == $oneDate ? "dateSelected" : "notSelected" ?>" href="<?= $oneDateLink ?>" target="" >  <?= $hebrewDates[ $oneDateDay ] .'-'. $oneDate  ?> </a>
	</li>
	<li class="div-width">
		 <a class="<?= $selectedDate == $twoDate ? "dateSelected" : "notSelected" ?>"  href="<?=  $twoDateLink ?>" target="" >  <?= $hebrewDates[ $twoDateDay ].'-'. $twoDate  ?> </a>
	</li>
	<li class="div-width">
		<a  class="<?= $selectedDate == $threeDate ? "dateSelected" : "notSelected" ?>" href="<?= $threeDateLink ?>" target="" > 	<?= $hebrewDates[  $threeDateDay ] .'-'. $threeDate  ?> </a>
	</li>
	<li class="div-width">
		<a  class="<?= $selectedDate == $fourDate ? "dateSelected" : "notSelected" ?>" href="<?= $fourDateLink ?>" target="" > 	<?= $hebrewDates[  $fourDateDay ].'-'.$fourDate  ?> </a>
	</li>
	<li class="div-width">
		<a  class="<?= $selectedDate == $fiveDate ? "dateSelected" : "notSelected" ?>" href="<?= $fiveDateLink ?>" target="" > 	<?= $hebrewDates[ $fiveDateDay ] .'-'. $fiveDate  ?> </a>
	</li>
	<li class="div-width">
		<a  class="<?= $selectedDate == $sixDate ? "dateSelected" : "notSelected" ?>" href="<?= $sixDateLink ?>" target="" > 	<?= $hebrewDates[ $sixDateDay ].'-'. $sixDate  ?> </a>
	</li> 		
</ul>

    <table class="table table-hover table-bordered table-responsive">
        <thead>
        <tr>
            <th>Time</th>
            <th>1</th>
            <th>2</th>
            <th>X</th>
            <th>Diff</th>
            <th>Diff UP/DOWN</th>
            <th>Type</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($data as $i => $el) {
            $highest = "";
            $currDiff = (float)$el['diff'];
            $gameDiff = isset( $gamesData[ $el['gameId'] ] ) ? (float)$gamesData[ $el['gameId'] ] : 0;
            $diffStats ='-';

            if( $el['type']== 'D' or $el['type']== '' ){
                continue;
            }

            echo '<tr class="clickable" >';
            echo '<td>' . $el['time'] .'</td>';

            if ($el['firstBet'] > $el['secondBet']) {

                if (isset($market[$el['secondBet']])) {
                    if ($market[$el['secondBet']] == 0) {
                        $highest = "highest";
                        $market[$el['secondBet']] = 1;
                    }
                }

                if ($highest != "") {
                    echo('<td class="highest">' . $el['secondName'] . '-' . $el['secondBet'] . '</td>');
                } else {
                    echo('<td>' . $el['secondName'] . '-' . $el['secondBet'] . '</td>');
                }

                echo('<td>' . $el['firstName'] . '-' . $el['firstBet'] . '</td>');

            } else {

                if (isset($market[$el['firstBet']])) {
                    if ($market[$el['firstBet']] == 0) {
                        $highest = "highest";
                        $market[$el['firstBet']] = 1;
                    }
                }


                if ($highest != "") {
                    echo('<td class="highest">' . $el['firstName'] . '-' . $el['firstBet'] . '</td>');
                } else {
                    echo('<td>' . $el['firstName'] . '-' . $el['firstBet'] . '</td>');
                }

                echo('<td>' . $el['secondName'] . '-' . $el['secondBet'] . '</td>');
            }

            echo('<td>' . $el['tieBet'] . '</td>');

            if ($el['diff'] > 8) {

                if( compareFloatNumbers($currDiff , $gameDiff , '>' ) ){
                    echo('<td class="highest gameUp">');
                    echo $el['diff'].' UP ('.$gameDiff.' )';
                    $diffStats = (float)$el['diff'] - (float)$gameDiff;
                }else{
                   if( compareFloatNumbers($currDiff , $gameDiff , '<' ) ){
                    echo('<td class="highest gameDown">');
                    echo $el['diff'] ." Down($gameDiff)";
                    $diffStats = (float)$gameDiff - (float)$el['diff'];
                   }else{
                    echo('<td class="highest">');
                    echo $el['diff'] ." ($gameDiff)";
                   }

                }
                echo '</td>';

            } else {

                if( compareFloatNumbers($currDiff , $gameDiff , '>' ) ){
                     echo('<td class="gameUp" >');
                     echo $el['diff'].' UP( '.$gameDiff.' )';
                     $diffStats = (float)$el['diff'] - (float)$gameDiff;
                }else{
                    if( compareFloatNumbers($currDiff , $gameDiff , '<' ) ){
                        echo '<td class="gameDown" >';
                        echo $el['diff']." Down($gameDiff)";
                        $diffStats = (float)$gameDiff - (float)$el['diff'];
                    }else{
                        echo '<td>';
                        echo $el['diff']." ($gameDiff)"; ;
                    }

                }
                echo '</td>';
            }

            echo '<td>'.$diffStats.'</td>';

            echo('<td>' . $el['type'] . '</td>');
            echo '</tr>';
        }

        ?>
        </tbody>
    </table>
</div>


    <script>
        $(document).ready(function () {
                $(".table").tablesorter({
                    sortList: [[4,0]]
                });

                $("table tr.clickable").click(function() {
                    
                    $(this).toggleClass('tr-mark');
                });

            }
        );
    </script>
</body>
</html>
