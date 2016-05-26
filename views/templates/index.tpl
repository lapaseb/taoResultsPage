<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" href="<?= Template::css('index.css') ?>" />
<script src="<?= Template::js('script.js') ?>"></script>

<div class="taoResultsPage_sidebar">
	<ul class="menu">
		<?php
		// Parcours les sessions d'examen afin de les afficher
		$deliveries = get_deliveries();			
		foreach($deliveries as $value){		
		?>
			<li>
				<a onclick="showDelivery('<?php echo explode("#", $value[0])[1]?>')"><?php echo $value[1]; ?></a>
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
	if(empty($deliveries )){
		die("<h2>Aucun résultat à afficher</h2>");
	}

	reset($deliveries);
	// Parcours les sessions d'examen afin d'afficher leur résultats
	foreach($deliveries as $value){
	?>
		<div class="taoResultsPage_collapsibleContainer_wraper <?php echo explode("#", $value[0])[1]; ?>">
			<h2><?php echo $value[1]; ?></h2>
			<?php
			foreach(get_results_by_delivery($value[0]) as $result){
			?>
				<div class="taoResultsPage_collapsibleContainer" onclick="showResults('<?php echo explode("#", $result["id"])[1] ?>');">
					<div class="taoResultsPage_collapsibleContainer_header">
						<span><?php echo $result["testTakerName"] ?></span>
					</div> <!-- taoResultsPage_collapsibleContainer-header -->
					<div class="taoResultsPage_collapsibleContainer_content">
						<?php
							if((($result["score"] / $result["totalScore"]) * 5 + 1) >= 4){
								echo "<span style='color:green;'>Examen Réussi</span>";
							} else {
								echo "<span style='color:red;'>Examen échoué</span>";
							}
							echo "<div class='taoResultsPage_collapsibleContainer_score'>" . $result["score"] . " / " . $result["totalScore"] . "</div>";
						?>
					</div> <!-- taoResultsPage_collapsibleContainer-content -->
				</div> <!-- taoResultsPage_collapsibleContainer -->
				
				<div class="taoResultsPage_results_wraper">
					<div class="taoResultsPage_results <?php echo explode("#", $result["id"])[1] ?>">
					<?php
					foreach($result["responses_array"] as $reponses){
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