<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
require("simple_html_dom.php");
require("nav.php");
date_default_timezone_set( 'Asia/Jerusalem' );


$conn = connectDB("winn");
$conn->query("SET NAMES 'utf8'");

$selectedDateFrom = isset(  $_GET['from'] ) ? $_GET['from']  : date("d-m-Y",strtotime("-1 day"));
$selectedDateTo =   isset(  $_GET['to'] )   ? $_GET['to']  : date("d-m-Y",strtotime("-1 day"));

$data =array();
if( $selectedDateFrom != NULL or $selectedDateTo != NULL  ){
    $dates = date_range($selectedDateFrom,$selectedDateTo);
    $sqlBulider="";
    foreach ( $dates as $i=>$date ){
        if( $i==0 ){
            $sqlBulider = "SELECT * FROM `games` WHERE `dategame` LIKE '%$selectedDateFrom%' ";
        }else{
            $sqlBulider .= " or`dategame` LIKE '%$date%'";
        }

    }
    $sqlBulider .= " ORDER BY `diff` DESC";

    // SELECT * FROM `games` WHERE `dategame` LIKE '%03-05-2017%' or `dategame` LIKE '%04-05-2017%' ORDER BY `diff` DESC
	$sql = $sqlBulider;
	$result = $conn->query( $sql );
	if ($result->num_rows > 0) {
		$data = array();
		while($row = $result->fetch_assoc()) {
			$data[]= $row; 
		}
	}
}

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
function date_range($first, $last, $step = '+1 day', $output_format = 'd-m-Y' ) {

    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

$conn->close();

if(  empty($data) ) {}
else{
// Get the winning teams
    $url = "https://www.winner.co.il/results/mishtane?from=" . $selectedDateFrom . "&to=" . $selectedDateTo . "&category=2&sport=240&league=&commit=%D7%94%D7%A6%D7%92";
    $html = file_get_html($url);
    $ret = $html->find('li[class=events result]');
    $winningTeams = array();
    foreach ($ret as $elements) {
        foreach ( $elements as $i=>$element ){
            if( $i == 'children' ){
                foreach ( $element as $j=>$node ){
                    if(  $node->tag == 'h3' ){
                        $teamName = $node->children[4]->plaintext;
                    }
                    if( $node->tag == 'ul' and  $node->attr['class'] == 'periods'  ){
                        $childrenArr =   $node->children[0]->children[0]->children ;
                        foreach ( $childrenArr as $child ){
                            if( strpos($child->plaintext, '1X2‬') !== false  ){
                                $teamScore = str_replace("1X2", "", $child->plaintext);
                                $winningTeams[ $teamName ] = $teamScore;
                            }
                        }
                    }
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" >
<head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="620">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script  src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.8/js/jquery.tablesorter.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.8/css/dragtable.mod.min.css"/>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

<!-- Include Required Prerequisites -->
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>


<!-- Include Date Range Picker -->
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>

    <style>
        button#btn-go {
            width: 315px;
            margin-bottom: 15px;
            vertical-align: text-bottom;
            font-size: 25px;
        }
        #datepicker{
            height: 55px;
            padding: 15px;
            background-color: cadetblue;
            color: aliceblue;
            font-size: 15px;
            font-family: arial;
            border-color: darkgray;
        }
        .bootstrap-select:not([class*=col-]):not([class*=form-control]):not(.input-group-btn) {
            width: 180px;
        }
    </style>
</head>
<body>
<?= $nav ?>
<div class="container">
    <div class="row">
        <div class="col-md-3">
	        <input type="text" name="" id="datepicker"  value="<?= $selectedDateFrom  ?> - <?= $selectedDateTo  ?>"  />
        </div>
        <div class="col-md-4">
            <button   class="btn" id="btn-go" >Go</button>
        </div>
        <div class="col-md-4">
            <div class="row">
             <!--   <div class="col-md-6">
                    <select class="selectpicker" id="rate-picker" >
                        <option value="2" >2</option>
                        <option value="1.95" >1.95</option>
                        <option value="1.90" >1.90</option>
                        <option value="1.85" >1.85</option>
                        <option value="1.80" >1.80</option>
                        <option value="1.75" >1.75</option>
                        <option value="1.70" >1.70</option>
                        <option value="1.65" >1.65</option>
                        <option value="1.60" >1.60</option>
                        <option value="1.55" >1.55</option>
                        <option value="1.50" >1.50</option>
                        <option value="1.45" >1.45</option>
                        <option value="1.40" >1.40</option>
                        <option value="1.35" >1.35</option>
                        <option value="1.30" >1.30</option>
                        <option value="1.25" >1.25</option>
                        <option value="1.20" >1.20</option>
                        <option value="1.15" >1.15</option>
                        <option value="1.10" >1.10</option>
                        <option value="1.05" >1.05</option>
                    </select>
                </div>-->
<!--                <div class="col-md-6">
                    <select class="selectpicker" id="rate-picker" >
                        <option value="up" >Up</option>
                        <option value="down" >Down</option>
                    </select>
                </div>-->
            </div>
        </div>
    </div>
	<?php if( isset( $data ) ){  ?>
	<!-- if there is data print table --->
	<table class="table table-hover table-bordered table-responsive">
        <thead>
        <tr>
            <th>Time</th>
            <th>1</th>
            <th>2</th>
            <th>Winning Team</th>
            <th>Profit</th>
            <th>Type</th>
            <th>Diff</th>
        </tr>
        </thead>
        <tbody>
			<?php
                $rate = 10;
                $totalWinningGames =0;
                $winningGamsesSum=0;
                $totalGames=0;
                $gamesRateFilter = 2;
                $directionGamesFlow = ">";
                $rateCounter = array();
                $rateCounterLose = array();

				foreach ($data as $i => $el) {


/*				    $z = 2.1;
				    $y = 1.85;
                    if( compareFloatNumbers($el['first_team_bet'] , $el['second_team_bet'] , "<")  ){

                        if(  $el['first_team_bet'] ==  $z or $el['first_team_bet'] ==  $y  ){

                        }else{
                            continue;
                        }
                    }elseif (  compareFloatNumbers($el['second_team_bet'] , $el['first_team_bet'] , "<")   ){
                        if(  $el['second_team_bet'] == $z or $el['second_team_bet'] ==  $y   ){

                        }else{
                            continue;
                        }
                    }else{
                        if(  $el['second_team_bet'] == $z or $el['first_team_bet'] ==  $y  ){

                        }else{
                            continue;
                        }
                    }*/



/*			    if( $el['game_type']== 'D' or $el['game_type']== '' ){
				        continue;
                    }*/

                    /*
                                        if( compareFloatNumbers($el['first_team_bet'] , $el['second_team_bet'] , "<")  ){

                                            if( compareFloatNumbers( $el['first_team_bet'] , $gamesRateFilter , $directionGamesFlow ) == FALSE ){
                                                continue;
                                            }
                                        }elseif (  compareFloatNumbers($el['second_team_bet'] , $el['first_team_bet'] , "<")   ){
                                            if( compareFloatNumbers( $el['second_team_bet'] , $gamesRateFilter , $directionGamesFlow )== FALSE ){
                                                continue;
                                            }
                                        }*/


                    $totalGames++;
					echo '<tr>';
							echo '<td>'.$el['dategame'].'</td>';
							echo '<td>'.$el['first_team_name'].'-'.$el['first_team_bet'].'</td>';
							echo '<td>'.$el['second_team_name'].'-'.$el['second_team_bet'].'</td>';
                            $winTeam = 0;
							if( compareFloatNumbers($el['first_team_bet'] , $el['second_team_bet'] , "<")  ){
                                $winTeam = 1;
                            }elseif (  compareFloatNumbers($el['second_team_bet'] , $el['first_team_bet'] , "<")  ){
                                $winTeam = 2;
                            }

                            foreach ( $winningTeams as $winningTeamName=>$score ){

                                if (  strpos($winningTeamName, $el['first_team_name']) !== false  and  strpos($winningTeamName, $el['second_team_name']) !== false  ) {

                                    if( strpos($score, $el['first_team_name']) !== false ){

                                        if( $winTeam == 1 ){
                                            $totalWinningGames++;
                                            $rateCounter[] = $el['first_team_bet'];
                                            echo '<td class="win-team" >'.$score.'</td>';
                                            $rateWin =  ($rate *(float)$el['first_team_bet'] );
                                            $winningGamsesSum = $winningGamsesSum + $rateWin;
                                            echo '<td>'. $rateWin .'</td>';
                                        }else{
                                            $rateCounterLose[] = $el['second_team_bet'];
                                            echo '<td>'.$score.'</td>';
                                            echo '<td>0</td>';
                                        }


                                    }elseif( strpos($score, $el['second_team_name']) !== false ){

                                        if( $winTeam == 2 ){
                                            $totalWinningGames++;
                                            $rateCounter[] = $el['second_team_bet'];
                                            echo '<td class="win-team">'.$score.'</td>';
                                            $rateWin =  ($rate *(float)$el['second_team_bet'] );
                                            $winningGamsesSum = $winningGamsesSum + $rateWin;
                                            echo '<td>'. $rateWin .'</td>';
                                        }else{
                                            $rateCounterLose[] = $el['first_team_bet'];
                                            echo '<td>'.$score.'</td>';
                                            echo '<td>0</td>';
                                        }
                                    }elseif ( strpos($score, 'x') !== false ){
                                        $rateCounterLose[] = 'x';
                                        $rateWin =  ($rate *(float)$el['tie_team_bet'] );
                                        echo '<td>X</td>';
                                        echo '<td>'.$rateWin.'</td>';

                                    }elseif ( strpos( $score, 'בוטל' ) ){
                                        echo '<td>בוטל</td>';
                                        echo '<td>0</td>';
                                    }
                                }
                            }

                            if( strlen( $el['game_type']) > 0 ){
                                echo '<td>'.$el['game_type'].'</td>';
                            }else{
                                echo '<td>-</td>';
                            }

                            echo '<td>'.$el['diff'].'</td>';

 					echo '</tr>';
				}
			?>
        </tbody>
		</table>
	<!-- table END--->
	<?php

        $a = array_count_values( $rateCounter );
        $b = array_count_values( $rateCounterLose );
	    var_dump( $a );
	    var_dump( $b );
	} ?>


    <table class="table table-hover table-bordered table-responsive" >
        <thead>
        <tr>
            <th>Total Games</th>
            <th>investment</th>
            <th>Winning Games</th>
            <th>Winning Games Sum</th>
            <th>Losing Games</th>
            <th>Losing Games Sum</th>
        </tr>
        </thead>
        <tbody>
                <tr>
                    <td>
                        <?= $totalGames ?>
                    </td>
                    <td><?= $rate*$totalGames ?></td>
                    <td><?= $totalWinningGames.' ('. (int)( ($totalWinningGames*100) / $totalGames ).'%)' ?></td>
                    <td><?= $winningGamsesSum ?></td>
                    <td><?= ( $totalGames - $totalWinningGames ).'('.(int) ( ($totalGames - $totalWinningGames)*100 / $totalGames ).'%)' ?></td>
                    <td><?= ($totalGames - $totalWinningGames)*10 ?></td>
                </tr>
        </tbody>
    </table>

</div>

    <script>
        $(document).ready(function () {

            $(".table").tablesorter({
                sortList: [[4,1]]
            });

			$('input#datepicker').daterangepicker({
				showDropdowns: true,
				locale: {
				  format: 'DD-MM-YYYY'
				}
			});
			
			$("#btn-go").click(function(){
                var drp = $('#datepicker').data('daterangepicker');
                var from = drp.startDate.format('DD-MM-YYYY');
			    var to =  drp.endDate.format('DD-MM-YYYY');

				window.location.replace("http://localhost/winn/statistics.php?from="+from+"&to="+to);
			});
			 
        });
    </script>
	
</body>
</html>
