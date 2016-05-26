<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" href="<?= Template::css('index.css') ?>" />
<script src="<?= Template::js('script.js') ?>"></script>

<div class="taoResultsPage_sidebar">
	<ul class="menu">
		<?php
		// Parcours les sessions d'examen afin de les afficher
		$deliveries_formated = get_deliveries_resume();
		foreach($deliveries_formated as $value){
		?>
			<li>
				<a onclick="showDelivery('<?php echo $value[2]. "_resume"; ?>')"><?php echo $value[0]; ?></a>
			</li>
		<?php 
		}
		?>
		<script>$('.menu li:first a').click();</script>
	</ul>
</div> <!-- taoResultsPage_sidebar -->

<div class="taoResultsPage_content">
	<?php
	//Si le tableau est vide on affiche une erreur
	if(empty($deliveries_formated )){
		die("<h2>Aucun résultat à afficher</h2>");
	}

	reset($deliveries_formated);
	// Parcours les sessions d'examen afin d'afficher leur résultats
	foreach($deliveries_formated as $value){
	?>
		<div class="taoResultsPage_collapsibleContainer_wraper <?php echo $value[2]. "_resume"; ?>">
			<h2><?php echo $value[0]; ?></h2>
			
			<?php
			$filtredResults = get_results_by_delivery_resume($value[1]);

			foreach($filtredResults as $result){
			?>
					<div class="taoResultsPage_collapsibleContainer" onclick="showResults('<?php echo explode("#", $result["id"])[1] . "_resume"; ?>');">
						<div class="taoResultsPage_collapsibleContainer_header">
							<span><?php echo $result["testTakerName"] ?></span>
						</div> <!-- taoResultsPage_collapsibleContainer-header -->
						<div class="taoResultsPage_collapsibleContainer_content">
							<?php
								if((($result["score"] / $result["totalScore"]) * 5 + 1) >= 4){
								?>
									<span style='color:green;'>Examen Réussi</span>
								<?php
								} else {
								?>
									<span style='color:red;'>Examen échoué</span>
								<?php
								}
								?>
								
								<div class='taoResultsPage_collapsibleContainer_score'><?php echo $result["score"];?> / <?php echo $result["totalScore"];?></div>
								<?php
								$index = 0;
								foreach($result["results"] as $result_part){
									if (isset($value[3][$index])){
										$name =  $value[3][$index];
									} else {
										$name = "Partie sans nom";
									}
										echo "<p>" . $name . " : " . $result_part["score"] . " / " . $result_part["totalScore"] . "</p>";
									
									$index++;
								}
							?>
						</div> <!-- taoResultsPage_collapsibleContainer-content -->
					</div> <!-- taoResultsPage_collapsibleContainer -->
					
					<div class="taoResultsPage_results_wraper">
						<div class="taoResultsPage_results <?php echo explode("#", $result["id"])[1] . "_resume"; ?>">
						<?php
						$index = 0;
						foreach($result["results"] as $result_part){
						?>
							<h3 class="part_title"><?php if (isset($value[3][$index])){echo $value[3][$index]; } else { echo "Partie sans nom"; }?></h3>
						<?php
							foreach($result_part["responses_array"] as $reponses){
						?>
								<div class="taoResultsPage_results_container">
									<div class="taoResultsPage_results_header">
										<h3><?php echo $reponses["label"] ?></h3>
										<span><?php echo $reponses["score"] ?> / <?php echo $reponses["totalScore"] ?></span>
									</div> <!-- taoResultsPage_results_header -->
									<div class="taoResultsPage_results_content">
										<table>
											<?php
											foreach($reponses["responses"] as $response){
											?>
												<tr>
													<td><?php echo $response["name"] ?></td>
													<td><?php echo $response["value"] ?></td>
												</tr>
											<?php
											}
											?>
										</table>
									</div> <!-- taoResultsPage_results_content -->
								</div> <!-- taoResultsPage_results_container -->
								
						<?php
							}
						$index++;
						}
						?>
						</div> <!-- taoResultsPage_results -->
					</div> <!-- taoResultsPage_results-wraper -->
			<?php
			}
			?>
		</div> <!-- taoResultsPage_collapsibleContainer-wraper -->
	<?php
	}
	?>
</div> <!-- taoResultsPage_content -->
<?php
Template::inc('footer.tpl', 'tao');
?>