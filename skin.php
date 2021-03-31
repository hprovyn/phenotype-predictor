<?php



#$skin_alleles = array("rs12913832"=>2, "rs1800407"=>0, "rs4778241"=>1);

function getSumForKeys($arr, $keys) {
	$sum = 0;
	foreach($keys as $key) {
		$sum += $arr[$key];
	}
	return $sum;
}

function getPhenoPotentialAdjustedProbs($pheno_sums, $pheno_mins, $pheno_maxes, $alleles) {
	$adjusted = array();
	foreach(array_keys($pheno_sums) as $pheno) {
		$min_sum = getSumForKeys($pheno_mins[$pheno], array_keys($alleles));
		$max_sum = getSumForKeys($pheno_maxes[$pheno], array_keys($alleles));
		$adjusted[$pheno] = ($pheno_sums[$pheno] - $min_sum) / ($max_sum - $min_sum);
	}
	return $adjusted;
}

function square($pheno_sums) {
	$squared = array();
	foreach(array_keys($pheno_sums) as $pheno) {
		$squared[$pheno] = $pheno_sums[$pheno] * $pheno_sums[$pheno];
	}
	return $squared;
}

function normalize($pheno_sums) {
	$normalized = array();
	foreach(array_keys($pheno_sums) as $pheno) {
		$normalized[$pheno] = $pheno_sums[$pheno] / array_sum($pheno_sums);
	}
	return $normalized;
}
#echo '<br>';
#
#

function predict($raw_skin_alleles) {

$a = file_get_contents("skin_color_aggregate_data.tsv");
$lines = explode("\n",$a);
$snp_array = array();

$snp = null;
$snp_alleles = array();

foreach($lines as $line) {
	$tabs = explode("\t",$line);
	$first = $tabs[0];
	if (substr($first,0,2)=="rs") {
		$snp = $first;
		$snp_array[$snp] = array();
		$snp_alleles[$snp] = array($tabs[1][0],$tabs[3][0]);
	} else {
		$counts = explode("\t", $line);
		$phenotype = $counts[0];
		$snp_array[$snp][$phenotype] = array($counts[1],$counts[2],$counts[3]);
	}
}

$skin_alleles = array();
foreach(array_keys($raw_skin_alleles) as $snp) {
	$chars = str_split($raw_skin_alleles[$snp]);
	$a1 = $chars[0];
	$a2 = $chars[2];
	if ($a1 == $a2) {
		if ($snp_alleles[$snp][0] == $a1) {
			$skin_alleles[$snp] = 0;
		} else {
			if ($snp_alleles[$snp][1] == $a1) {
				$skin_alleles[$snp] = 2;
#			} else {
#				echo 'error ' . $snp . ' input: ' . $a1 . $a2 . ', expects: ' . $snp_alleles[$snp][0] . ' or ' . $snp_alleles[$snp][1] . '<br>';
			}
		}
	} else {
		if (in_array($a1, $snp_alleles[$snp]) and in_array($a2, $snp_alleles[$snp])) {
			$skin_alleles[$snp] = 1;
		} else {
			echo 'error ' . $snp . ' input: ' . $a1 . $a2 . ', expects: ' . $snp_alleles[$snp][0] . ' or ' . $snp_alleles[$snp][1];
		}
	}
}

#print_r($snp_array);

$snp_probabilities = array();
foreach(array_keys($snp_array) as $key) {
	$probabilities = array();
	$snp_stats = $snp_array[$key];
	foreach (array_keys($snp_stats) as $key2) {
		$probabilities[$key2] = array();
		$pheno_counts = $snp_stats[$key2];
		#echo $key . " " . $key2 . "<br>";
		#print_r($pheno_counts);
		#echo "<br>";
		$sum = $pheno_counts[0] + $pheno_counts[1] + $pheno_counts[2];
		if ($sum == 0) {$sum = 1;}
		foreach(array(0,1,2) as $i) {
			$probabilities[$key2][$i] = $pheno_counts[$i]/$sum;

		}	
	}
	$snp_probabilities[$key] = $probabilities;
}
#print_r($snp_probabilities);

$groups = array("skin color"=>array("Light/pale","Moderate","Dark/olive"), "burning/tanning"=>array("High susceptibility to sunburns","Initial sunburns","Moderate tanning","Quick tanning"), "freckling"=>array("Severe freckling", "Moderate freckling", "Non-freckled skin"));

$snp_cond_probs = array();

$phenotype_snp_allele_minimums = array();
$phenotype_snp_allele_maximums = array();

foreach(array_keys($groups) as $group) {
	$group_phenos = array_values($groups[$group]);
	#init phenotype allele maxes nested arrays
	foreach(array_values($group_phenos) as $pheno) {
		$phenotype_snp_allele_maximums[$pheno] = array();
		$phenotype_snp_allele_minimums[$pheno] = array();
	}

	$snp_cond_probs[$group] = array();
 #       echo 'group phenos<br>';
	foreach(array_keys($snp_probabilities) as $snp) {
		$cond_probs = array();
		foreach(array(0,1,2) as $i) {
			$cond_probs[$i] = array();
	                $sum = 0;		
			foreach(array_values($group_phenos) as $pheno) {
				$sum = $sum + $snp_probabilities[$snp][$pheno][$i];
			}
		
			if ($sum == 0) {$sum = 1;}
			foreach(array_values($group_phenos) as $pheno) {
				$cond_probs[$i][$pheno] = $snp_probabilities[$snp][$pheno][$i] / $sum;
			}

		}
		foreach(array_values($group_phenos) as $pheno) {
			$phenotype_snp_allele_maximums[$pheno][$snp] = max($cond_probs[0][$pheno],$cond_probs[1][$pheno],$cond_probs[2][$pheno]);
			$phenotype_snp_allele_minimums[$pheno][$snp] = min($cond_probs[0][$pheno],$cond_probs[1][$pheno],$cond_probs[2][$pheno]);
		}
#		echo $snp . " " . $pheno . "<br>";
#		print_r($cond_probs);
		$snp_cond_probs[$group][$snp] = $cond_probs;
#		echo "<br>";
	}
}	
	
$group_predictions = array();	
	
foreach(array_keys($groups) as $group) {
	$group_phenos = array_values($groups[$group]);
	$pheno_sums = array();
	
	foreach(array_values($group_phenos) as $pheno) {
		$pheno_sums[$pheno] = 0;
		foreach(array_keys($skin_alleles) as $snp) {
                        $allele = $skin_alleles[$snp];
			$pheno_sums[$pheno] = $pheno_sums[$pheno] + $snp_cond_probs[$group][$snp][$allele][$pheno];
		}
	}	
	#print_r($pheno_sums);
        #echo "<br>";
	#echo "<br>";
	#echo "<br>Potential Adjusted ";
	$adjusted = getPhenoPotentialAdjustedProbs($pheno_sums, $phenotype_snp_allele_minimums, $phenotype_snp_allele_maximums, $skin_alleles);
	#print_r($adjusted);
	#echo "<br>";
	#$normalized = normalize($pheno_sums);
	#$normalizedPotential = normalize($adjusted);
	$normalizedSquarePotential = normalize(square($adjusted));
#	echo '<br>normalized: ';
#	print_r($normalized);
#	echo '<br>normalized potential: ';
#	print_r($normalizedPotential);
#	echo '<br>normalized square potential: ';
	$group_predictions[$group] = $normalizedSquarePotential;
}
return $group_predictions;
}
?>
