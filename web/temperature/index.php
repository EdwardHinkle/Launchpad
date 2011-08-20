<?php
include('inc.php');

$last = $db->query('SELECT * FROM last_temperature');
$sensors = array();
while($sensor = $last->fetch(PDO::FETCH_OBJ))
        $sensors[$sensor->sensor] = array('date'=>$sensor->date, 'temperature'=>$sensor->temperature);

$names = array(
        'Outside' => 'Outside',
        'Inside' => 'Inside',
        'Closet' => 'Server Closet'
);
$colors = array(
        'Outside' => '43c739',
        'Inside' => '08afef',
        'Closet' => '80158a'
);

?>
<html>
<head>
	<title>Launchpad Temperature</title>
	<style type="text/css">
	body {
		background: #333;
		color: white;
		font-family: Verdana, arial, sans-serif;
		text-align: center;
	}
	.sensor {
		margin-top: 40px;
		margin-bottom: 30px;
	}
	.label {
		font-size: 22pt;
	}
	.temperature {
		font-size: 50pt;
		font-weight: bold;
	}
	.date {
		font-size: 10pt;
	}
	table {
		width: 850px;
		margin: 0 auto;
	}
	td {
		text-align: center;
	}
	</style>
</head>
<body>

<table><tr>
<td class="temperatures" valign="top">

<?php
foreach(array('Outside', 'Inside') as $sensor)
{
?>
	<div class="sensor">
		<div class="label"><?=$names[$sensor]?></div>
		<div class="temperature" style="color: #<?=$colors[$sensor]?>;"><?=sprintf('%0.1f', $sensors[$sensor]['temperature'])?>&deg;F</div>
	</div>
<?php
}
?>
<div class="date"><?=date('n/d g:ia', strtotime($sensors['Outside']['date']))?></div>

</td>
<td>
	<img src="http://aaron.pk/temperature/graph-day.png" /><br /><br />
	<img src="http://aaron.pk/temperature/graph-month.png" />
</td>
</tr></table>

</body>
</html>