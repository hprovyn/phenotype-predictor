<?php
/*
  $Id$

  YSEQ HirisPlex VIEW
  https://phenotype.yseq.net/index.php

  Copyright (c) 2021 YSEQ

  Released under the GNU General Public License
*/

session_start();
if (!file_exists("status.json")) {
	file_put_contents("status.json","{}");
}
$hg19 = array();
$hg38 = array();
$productLinks = array();
$snpPhenoTags = array();
getAutosomalSNPList();

function reverse_bases($str) {
	return reverse($str[0]) . '/' . reverse($str[2]);
}

function reverse($char) {
	if ($char == 'A') {
		return 'T';
	} else if ($char == 'T') {
		return 'A';
	} else if ($char == 'G') {
		return 'C';
	} else if ($char == 'C') {
		return 'G';
	} else {
		return $char;
	}
}

function count_alleles($str, $allele) {
	$count = 0;
	if ($str[0] == $allele) {
		$count += 1;
	}
	if ($str[2] == $allele) {
		$count += 1;
	}
	return $count;
}

?>
<img src='images/PhenotypingBanner.png'/>
<script src="phenotypeToAvatar.js"></script>
<script src="https://www.yseq.net/ext/jquery/jquery-3.5.1.min.js"></script>
<div>
<?php 
function unzipIfNecessary($zipped_in, $extracted_dir) {
	$filetype = exec("file " . $zipped_in);
	if (stripos($filetype, "gzip compressed data") !== false) {
		updateSessionStatus("Unzipping Archive");
		exec("mkdir " . $extracted_dir);
		$a = exec("gunzip -ck " . $zipped_in . " > " . $extracted_dir . "/out");
	} else {
		if (stripos($filetype, "Zip archive data") !== false) {
			updateSessionStatus("Unzipping Archive");
			$a = exec("unzip -o ". $zipped_in . " -d " . $extracted_dir);
		} else {
			exec("mkdir " . $extracted_dir);
			exec("mv " . $zipped_in . " " . $extracted_dir . "/out");
		}
	}
}

function getSafeFileName($file) {
	return str_replace(" ", '\\ ', $file);
}

function hasFileOfType($extracted_dir, $type) {
	$files = scandir($extracted_dir, 1);
	$diff = array_diff($files, array("..", "."));
	foreach($diff as $file) {
		$filetype = exec("file " . $extracted_dir . "/" . getSafeFileName($file));
		if (stripos($filetype, $type) !== false) {
			return $file;
		}
	}
	return false;
}

function getAutosomalSNPList() {
	global $hg19, $hg38, $productLinks, $snpPhenoTags;
	$the_snps = array();
	if ($file = fopen("autosomalSNPpositions.csv", "r")) {
		if (!feof($file)) {
			$firstline = fgets($file);
		}
		while (!feof($file)) {
			$line = fgets($file);
			$exploded = explode(",", $line);
			if ($exploded[0] != "") {
				$chr = $exploded[0];
				$this_hg38 = $chr . ":" . $exploded[1];
				$this_hg19 = $chr . ":" . $exploded[2];
				$productId = trim($exploded[4]);
				
				$productLink = 'https://www.yseq.net/product_info.php?products_id=' . $productId;
				$tags = array();
				if ($exploded[5] != "") {
					foreach (explode(" ", trim($exploded[5])) as $htag) {
						if ($htag != "") {
							array_push($tags, $htag);
						}
					}
				}
                                if ($exploded[6] != "") {
					foreach (explode(" ", trim($exploded[6])) as $hexporttag) {
						if ($hexporttag != "") {
							array_push($tags, $hexporttag);
						}
					}
				}
                                if ($exploded[7] != "") {
					foreach (explode(" ", trim($exploded[7])) as $othertag) {
						if ($othertag != "") {
							array_push($tags, $othertag);
						}
					}
				}
				$removed = false;
				foreach (array("hirisplex_export","polish_skin") as $skin) {
					if (in_array($skin, $tags)) {
						$removed = true;
						unset($tags[array_search($skin, $tags)]);
					}
				}
				if ($removed == true) {
					array_push($tags, 'skin');
				}
				$snp_name = rtrim($exploded[3]);
				$hg19[$this_hg19] = $snp_name;
				$hg38[$this_hg38] = $snp_name;
				if ($productId != "") {					
					$productLinks[$snp_name] = $productLink;
					$snpPhenoTags[$snp_name] = $tags;
				}
			}
		}
	}
}

function getFormattedPhenoTags($phenoTags) {
	$phenoFormats = array("skin" => "<span style='background-color:red'>skin</span>",
		              "hair_color" => "<span style='background-color:blue'>hair color</span>",
			      "hair_shade" => "<span style='background-color:cyan'>hair shade</span>",
			      "hair_curl" => "<span style='background-color:green'>hair curliness</span>",
			      "eye_color" => "<span style='background-color:orange'>eye color</span>",
			      "balding" => "<span style='background-color:yellow'>balding</span>");
	$phenoImages = array("skin" => "<img src='images/icons8-hand-64.png' title='skin' style='width:24px'></img>",
		             "hair_color" => "<img src='images/icons8-red-hair-48.png' title='hair color' style='width:24px'></img>",
			     "hair_shade" => "<img src='images/hair_shade.png' title='hair shade' style='width:24px'></img>",
			     "hair_curl" => "<img src='images/icons8-curly-hair-48.png' title='hair curliness' style='width:24px'></img>",
			     "eye_color" => "<img src='images/icons8-eye-48.png' title='eye color' style='width:24px'></img>",
			     "balding" => "<img src='images/icons-bald-48.png' title='balding' style='width:24px'></img>");
	$output = "";

	foreach($phenoTags as $tag) {
		$output = $output . " " . $phenoImages[$tag];
	}
	return $output;
}
	
function countChrY($file) {
	return exec("awk '$0~/^chrY/ {count+=1;} END {print count;}' " . $file);
}

function processASCII($extracted_dir, $ascii, $separator) {
	$safename =  $extracted_dir . "/" . getSafeFileName($ascii);
	$safedest = $extracted_dir . "/" . "safe.txt";
	exec('mv ' . $safename . " " . $safedest);
	global $hg19, $hg38;
	$hg19_reads = array();
	$hg38_reads = array();
	$alleles = array();
	$xCount = 0;
	$yCount = 0;
	if ($file = fopen($safedest,"r")) {
		
		while(!feof($file)) {
			$firstline = fgets($file);
			$isAncestry = false;
			$chrXlabel = "X";
			$chrYlabel = "Y";
			if (strpos($firstline, "AncestryDNA") != false) {
				$isAncestry = true;
				$chrXlabel = "24";
				$chrYlabel = "25";
			}
			while(!feof($file)) {
				$line = fgets($file);
				$exploded = explode($separator, $line);
                                
				$snp = $exploded[0];
				$chrLabel = $exploded[1];
				if ($chrLabel == $chrYlabel) {
					$yCount = $yCount + 1;
					$chrLabel = 'chrY';
				} else {
					if ($chr == $chrXlabel) {
						$xCount = $xCount + 1;
						$chrLabel = 'chrX';
					} else {
						$chr = 'chr' . $chrLabel;
					}
				}
				$pos = $exploded[2];
				$chr_pos = $chr . ":" . $pos;
				$allele = $exploded[3];
				if ($isAncestry) {
					$allele = $allele . $exploded[4];
				}
				if (strpos($allele, "0") === false) {

	                		if (array_key_exists($chr_pos, $hg19)) {
						$hg19_reads[$chr_pos] = $allele;
					}
					if (array_key_exists($chr_pos, $hg38)) {
						$hg38_reads[$chr_pos] = $allele;
					}
				}
			}
		}
		fclose($file);
	}
	if (count($hg38_reads) > count($hg19_reads)) {
		foreach($hg38_reads as $key => $value) {
			$alleles[$hg38[$key]] = $value[0] . "/" . $value[1];
		}

	} else {

		foreach($hg19_reads as $key => $value) {
			$alleles[$hg19[$key]] = $value[0] . "/" . $value[1];
		}
	}
	unlink($safedest);
        return array($alleles,$xCount,$yCount);
}

function processFTDNA($extracted_dir, $ascii) {
	$separator = ",";
	$safename =  $extracted_dir . "/" . getSafeFileName($ascii);
	$safedest = $extracted_dir . "/" . "safe.txt";
	exec('mv ' . $safename . " " . $safedest);
	global $hg19, $hg38;
	$hg19_reads = array();
	$hg38_reads = array();
	$alleles = array();
	$xTotal = 0;
	$xHetero = 0;
	if ($file = fopen($safedest,"r")) {
		
		while(!feof($file)) {
			$line = fgets($file);
			$exploded = explode($separator, $line);
                                
			$snp = $exploded[0];
			$chr = 'chr' . $exploded[1];
			$allele = $exploded[3];
			if ($chr == 'chrX' and strpos($allele, "-") == false) {
				$xTotal = $xTotal + 1;
				if ($allele[0] != $allele[1]) {
					$xHetero = $xHetero + 1;
				}
			}
			$pos = $exploded[2];
			$chr_pos = $chr . ":" . $pos;
			$allele = $exploded[3];
			
	                if (array_key_exists($chr_pos, $hg19)) {
				$hg19_reads[$chr_pos] = $allele;
			}
			if (array_key_exists($chr_pos, $hg38)) {
				$hg38_reads[$chr_pos] = $allele;
			}
		}
		fclose($file);
	}
	if (count($hg38_reads) > count($hg19_reads)) {
		foreach($hg38_reads as $key => $value) {
			$alleles[$hg38[$key]] = $value[0] . "/" . $value[1];
		}

	} else {

		foreach($hg19_reads as $key => $value) {
			$alleles[$hg19[$key]] = $value[0] . "/" . $value[1];
		}
	}
	unlink($safedest);
        return array($alleles,$xTotal,$xHetero);
}
if (!isset($_FILES["23"]) and !isset($_POST['alleles'])) {
	echo '<div align="center" id="avatar"></div>';
	echo "<script>const timer = ms => new Promise(res => setTimeout(res, ms));
	async function randomize() {
          for (var i = 0; i < 100; i++) {
             
                
		var url = getRandomAvatarURL(); 
                document.getElementById('avatar').innerHTML = '';
                addAvatarToDiv(url,'avatar','200','200');
                await timer(1500);
}
}
randomize()
</script>";
}
?>

<br><br>
<b>Upload a raw data file containing phenotyping alleles:</b>&nbsp;&nbsp;&nbsp;(50 MB max size)
<br><br>
      <form id="file" action="" method="POST" enctype="multipart/form-data">
	 <input type="file" name="23" onchange="startPoll(); form.submit()"/>
         <div style="text-align:center"><span class="status"></span><div hidden=true id="load"><img height="40" width="40" src="https://phenotype.yseq.net/images/spinner-5.gif"/></div></div>
         <input hidden="true" type="submit"/>
      </form>
<br><br>

Supported formats: 23andMe, AncestryDNA, Family Tree DNA Family Finder. Optionally in zip / gzip compression.<br>
<br>
<br><small>
<b>Disclaimer:</b><br>
We've written this Phenotype Predictor tool to visualize and verify the statistical model that others have published. <br>
YSEQ has not contributed any phenotyping data or created any statistical model by ourselves. <br>
We have observed that the results derived from the used models can sometimes be misleading or even plain wrong. <br>
<br>
<b>Privacy Policy:</b><br>
The uploaded files and the submitted data is only temporarily stored on the server for processing and will be automatically deleted when the analysis is finished. <br>
YSEQ will not collect any data or forward it to any third party. <br>
However we cannot give any warranties about the reliability of the results and YSEQ is not responsible for any leaked data that may be captured through illegal eavesdropping or through unintended malfunctioning of the website.<br>
<br>
<b>Open Source:</b><br>
The source code for this Phenotyping Predictor will be published on GitHub. The usage is free, without any warranties.<br>
<br>
<b>Please cite:</b><br>
Hunter Provyn, Thomas Krahn (2021), Phenotyping predictor. <a href="https://phenotype.yseq.net">https://phenotype.yseq.net</a><br>
<br>
<b>Contact us:</b><br>
<a href="https://www.yseq.net/contact_us.php">YSEQ</a><br>
<br>
<br>  
</small>  
<script>function startPoll() {
document.getElementById("load").style.display='block';
var sessionId = "<?php echo session_id();?>";
(function() {
	var status = $('.status'),
		poll = function() {
			$.ajax({
			url: 'https://phenotype.yseq.net/status.json',
				dataType: 'json',
				type: 'get',
				success: function(data) {
					if (!(sessionId in data)) {

						status.text('Uploading');
					}
					else {
						if (data[sessionId].uploaded) {
							status.text(data[sessionId].info);
						}
					}
				},
				error: function() {
				}
			});
		},
		pollInterval = setInterval(function() {
			poll();
		}, 3000);
	poll();
})();
}
</script>

<?php
function removeSessionStatus() {
	$statuses = json_decode(file_get_contents("status.json"),true);
	unset($statuses[session_id()]);
	file_put_contents("status.json", json_encode($statuses));
}
function updateSessionStatus($update) {
	if (file_exists("status.json")) {
		$statuses = json_decode(file_get_contents("status.json"),true);
	} else {
		$statuses = array();
	}
	$statuses[session_id()] = array("uploaded"=>true, "info"=>$update);
	file_put_contents("status.json", json_encode($statuses));
}

$uploads_root = "/var/local/phenotype_predictor";

if(isset($_FILES['23'])) {
	$errors= array();
		       $file_name = $_FILES['23']['name'];
		       $file_size =$_FILES['23']['size'];
		             $file_tmp =$_FILES['23']['tmp_name'];
		             $file_type=$_FILES['23']['type'];
			           $file_ext=strtolower(end(explode('.',$_FILES['23']['name'])));
			           
			           $extensions= array("zip", "vcf", "gz", "csv", "txt");
				         
				         if(in_array($file_ext,$extensions)=== false){
						          $errors[]="extension not allowed, please choose a ZIP file.";
							        }
				         
				         if($file_size > 52428800){
						          $errors[]='File size must be under 50 MB';
							        }
				         
				   if(empty($errors)==true){
					         $rando = rand(1,999999);
						 $upload_base_dir = $uploads_root . "/raw";
						 $upload_dir = $upload_base_dir . "/" . $rando;
						 mkdir($upload_dir);
						 move_uploaded_file($file_tmp,$upload_dir."/file.zip");
						 $unzipped_base_dir = $uploads_root . "/extracted";
						 $unzipped_dir = $unzipped_base_dir . "/" . $rando;
						 $zipped_file = $upload_dir."/file.zip";
						 unzipIfNecessary($zipped_file, $unzipped_dir);
						 unlink($zipped_file);
						 rmdir($upload_dir);
						 $hasASCII = hasFileOfType($unzipped_dir, "ASCII text");
						 if ($hasASCII !== false) {
							 $procOut = processASCII($unzipped_dir, $hasASCII, "\t");
							 $alleles = $procOut[0];
							 $xCount = $procOut[1];
							 $yCount = $procOut[2];
						 } else {
							 $isFTDNA_FF = hasFileOfType($unzipped_dir, "RSID sidtune");
							 if ($isFTDNA_FF !== false) {

								 $procOut = processFTDNA($unzipped_dir, $isFTDNA_FF);
								 $alleles = $procOut[0];
								 $xTotal = $procOut[1];
								 $xHetero = $procOut[2];
								 #echo "x hetero " . $xHetero . ", x total " . $xTotal . " ratio = " . $xHetero / $xTotal . "<br>";
							 } else {
								 echo 'Error: No ASCII file found in uploaded file / root directory of uploaded archive';
							 }

						 }
						 exec("rm -r " . $unzipped_dir);
				   } else {
					   print_r($errors);
				   }
}
if (isset($_POST['alleles'])) {
	$alleles = array();
	$key_pairs = explode(',', $_POST['alleles']);
	foreach($key_pairs as $key_pair) {
		$splt = explode('=', $key_pair);
		$snp = $splt[0];
		$allele = $splt[1];
		$alleles[$snp] = $allele;
	}
}
if (isset($alleles)) {

function array_to_csv_download($array, $filename = 'export.csv', $delimiter=',') {
	header('Content-Type: application/csv');
	header('Content-Disposition: attachment; filename='.$filename);
	$f = fopen('php://output','w');
	foreach ($array as $line) {
		fputcsv($f, $line, $delimiter);
	}
	fclose($f);
	exit();
}



if (isset($xCount)) {
	if ($yCount > 150) {
		$sex = "M";
	} else {
		$sex = "F";
	}
}

if (isset($xTotal)) {
	if ($xHetero / $xTotal > 0.10) {
		$sex = "F";
	} else {
		$sex = "M";
	}
}

if (isset($_POST['sex'])) {
	$sex = $_POST['sex'];
}
  $skin_markers_list = " ('rs1805007', 'rs4911414', 'rs1015362', 'rs6058017', 'rs731236', 'rs1393350', 'rs12203592', 'rs16891982', 'rs12896399', 'rs4778138', 'rs4778241', 'rs1800407', 'rs12913832')";

  $hirisplex_markers_list = " ('rs312262906', 'rs11547464', 'rs885479', 'rs1805008', 'rs1805005', 'rs1805006', 'rs1805007', 'rs1805009', 'rs201326893', 'rs2228479', 'rs1110400', 'rs28777', 'rs16891982', 'rs12821256', 'rs4959270', 'rs12203592', 'rs1042602', 'rs1800407', 'rs2402130', 'rs12913832', 'rs2378249', 'rs12896399', 'rs1393350', 'rs683', 'rs3114908', 'rs1800414', 'rs10756819', 'rs2238289', 'rs17128291', 'rs6497292', 'rs1129038', 'rs1667394', 'rs1126809', 'rs1470608', 'rs1426654', 'rs6119471', 'rs1545397', 'rs6059655', 'rs12441727', 'rs3212355', 'rs8051733', 'rs1385699', 'rs11803731', 'rs17646946', 'rs7349332')";
  $skin_markers_array_format = str_replace(' (','[',$skin_markers_list);
  $skin_markers_array_format = str_replace(')',']',$skin_markers_array_format);
  $skin_markers_array_format = str_replace("'",'"', $skin_markers_array_format);
  
  $hirisplex_markers_array_format = str_replace(' (','[',$hirisplex_markers_list);
  $hirisplex_markers_array_format = str_replace(')',']',$hirisplex_markers_array_format);
  $hirisplex_markers_array_format = str_replace("'",'"', $hirisplex_markers_array_format);

  $skin_markers_array = json_decode($skin_markers_array_format);
  $hirisplex_markers_array = json_decode($hirisplex_markers_array_format);
  $all_markers_array = array_values(array_unique(array_merge($skin_markers_array, $hirisplex_markers_array)));

  $all_markers_sql = json_encode(array_values($all_markers_array));
  $all_markers_sql = str_replace('[',' (', $all_markers_sql);
  $all_markers_sql = str_replace(']',')', $all_markers_sql);
  $all_markers_sql = str_replace('"',"'",$all_markers_sql);
  $hirisplexMinorAlleles = array('rs312262906'=>'I', 'rs1042602'=>'T', 'rs10756819'=>'G', 'rs1110400'=>'C', 'rs1126809'=>'A', 'rs1129038'=>'G', 'rs11547464'=>'A', 'rs12203592'=>'T', 'rs12441727'=>'A', 'rs12821256'=>'G', 'rs12896399'=>'T', 'rs12913832'=>'T', 'rs1393350'=>'T', 'rs1426654'=>'G', 'rs1470608'=>'A', 'rs1545397'=>'T', 'rs1667394'=>'C', 'rs16891982'=>'C', 'rs17128291'=>'C', 'rs1800407'=>'A', 'rs1800414'=>'C', 'rs1805005'=>'T', 'rs1805006'=>'A', 'rs1805007'=>'T', 'rs1805008'=>'T', 'rs1805009'=>'C', 'rs201326893'=>'A', 'rs2228479'=>'A', 'rs2238289'=>'C', 'rs2378249'=>'C', 'rs2402130'=>'G', 'rs28777'=>'C', 'rs3114908'=>'T', 'rs3212355'=>'A', 'rs4959270'=>'A', 'rs6059655'=>'T', 'rs6119471'=>'C', 'rs6497292'=>'C', 'rs683'=>'G', 'rs8051733'=>'C', 'rs885479'=>'T');
  
  $hirisplexReverse = array('rs683', 'rs12913832', 'rs2238289', 'rs6497292', 'rs1129038', 'rs2378249', 'rs3212355', 'rs12821256', 'rs885479', 'rs1042602', 'rs1800407', 'rs1393350', 'rs17128291', 'rs1470608', 'rs6119471', 'rs6059655', 'rs8051733');
  $polishOnlyReverse = array('rs4778138', 'rs4778241', 'rs6058017');
  $hirisplexReverse = array_merge($hirisplexReverse, $polishOnlyReverse);

?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
Change Gender (optional)&nbsp;&nbsp;
<select name="sex" id="sex" onchange="this.form.submit()">
<option value="M" <?php if($sex == "M") { echo 'selected'; }?> >Male</option>
<option value="F" <?php if($sex == "F") { echo 'selected'; }?> >Female</option>
<input type="hidden" name="alleles" value="<?php $output = array();
foreach($alleles as $key => $value) {
	array_push($output, $key . "=" . $value);
}

echo implode(",", $output); ?>">
</select>
</form><?php
	  $hirisplexAlleleNumbers = array();
	  $hirisplex_alleles = array();
	  $hirisplex_alleles2 = array();
	  foreach (array_values($hg19) as $hirisplexMarker) {
		  if (array_key_exists($hirisplexMarker, $alleles)) {

			  $allele_number = 0;

			  $the_allele = $alleles[$hirisplexMarker];
			  if (in_array($hirisplexMarker, $hirisplexReverse)) {
				  $hirisplex_reversed = reverse_bases($the_allele);
			  } else {
				  $hirisplex_reversed = $the_allele;
			  }
			  if (array_key_exists($hirisplexMarker, $hirisplexMinorAlleles)) {
				  $hirisplex_allele_numbers[$hirisplexMarker] = count_alleles($hirisplex_reversed, $hirisplexMinorAlleles[$hirisplexMarker]);
			  }
			  $hirisplex_alleles2[$hirisplexMarker] = $hirisplex_reversed;

		  }
	  }

    $allele_list_contents = "HIrisPlex Format:<br>";

    $allele_list_contents .= "
    <div>
      <br>
        <table  align=\"center\" border=\"1\" width=\"80%\">
          <tbody>
            <tr style=\"font-size:8pt;\">
              <th> <font color=\"darkblue\">#</font> </th>
              <th> <font color=\"darkblue\">Gene</font> </th>
              <th> <font color=\"darkblue\">SNP</font> </th>
              <th> <font color=\"darkblue\">Allele</font> </th>
              <th> <font color=\"darkblue\">No. of Alleles</font> </th>
            </tr>
            <tr>
              <td>1</td>
              <td><i>MC1R</i></td>
              <td>rs312262906</td>
              <td>" . $hirisplex_alleles2['rs312262906'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs312262906'] . "</td>
            </tr>
            <tr>
              <td>2</td>
              <td><i>MC1R</i></td>
              <td>rs11547464</td>
              <td>" . $hirisplex_alleles2['rs11547464'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs11547464'] . "</td>
            </tr>
            <tr>
              <td>3</td>
              <td><i>MC1R</i></td>
              <td>rs885479</td>
              <td>" . $hirisplex_alleles2['rs885479'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs885479'] . "</td>
            </tr>
            <tr>
              <td>4</td>
              <td><i>MC1R</i></td>
              <td>rs1805008</td>
              <td>" . $hirisplex_alleles2['rs1805008'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1805008'] . "</td>
            </tr>
            <tr>
              <td>5</td>
              <td><i>MC1R</i></td>
              <td>rs1805005</td>
              <td>" . $hirisplex_alleles2['rs1805005'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1805005'] . "</td>
            </tr>
            <tr>
              <td>6</td>
              <td><i>MC1R</i></td>
              <td>rs1805006</td>
              <td>" . $hirisplex_alleles2['rs1805006'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1805006'] . "</td>
            </tr>
            <tr>
              <td>7</td>
              <td><i>MC1R</i></td>
              <td>rs1805007</td>
              <td>" . $hirisplex_alleles2['rs1805007'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1805007'] . "</td>
            </tr>
            <tr>
              <td>8</td>
              <td><i>TUBB3</i></td>
              <td>rs1805009</td>
              <td>" . $hirisplex_alleles2['rs1805009'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1805009'] . "</td>
            </tr>
            <tr>
              <td>9</td>
              <td><i>MC1R</i></td>
              <td>rs201326893</td>
              <td>" . $hirisplex_alleles2['rs201326893'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs201326893'] . "</td>
            </tr>
            <tr>
              <td>10</td>
              <td><i>MC1R</i></td>
              <td>rs2228479</td>
              <td>" . $hirisplex_alleles2['rs2228479'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs2228479'] . "</td>
            </tr>
            <tr>
              <td>11</td>
              <td><i>MC1R</i></td>
              <td>rs1110400</td>
              <td>" . $hirisplex_alleles2['rs1110400'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1110400'] . "</td>
            </tr>
            <tr>
              <td>12</td>
              <td><i>SLC45A2</i></td>
              <td>rs28777</td>
              <td>" . $hirisplex_alleles2['rs28777'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs28777'] . "</td>
            </tr>
            <tr>
              <td>13</td>
              <td><i>SLC45A2</i></td>
              <td>rs16891982</i></td>
              <td>" . $hirisplex_alleles2['rs16891982'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs16891982'] . "</td>
            </tr>
            <tr>
              <td>14</td>
              <td><i>KITLG</i></td>
              <td>rs12821256</td>
              <td>" . $hirisplex_alleles2['rs12821256'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs12821256'] . "</td>
            </tr>
            <tr>
              <td>15</td>
              <td><i>LOC105374875</i></td>
              <td>rs4959270</td>
              <td>" . $hirisplex_alleles2['rs4959270'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs4959270'] . "</td>
            </tr>
            <tr>
              <td>16</td>
              <td><i>IRF4</i></td>
              <td>rs12203592</td>
              <td>" . $hirisplex_alleles2['rs12203592'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs12203592'] . "</td>
            </tr>
            <tr>
              <td>17</td>
              <td><i>TYR</i></td>
              <td>rs1042602</td>
              <td>" . $hirisplex_alleles2['rs1042602'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1042602'] . "</td>
            </tr>
            <tr>
              <td>18</td>
              <td><i>OCA2</i></td>
              <td>rs1800407</td>
              <td>" . $hirisplex_alleles2['rs1800407'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1800407'] . "</td>
            </tr>
            <tr>
              <td>19</td>
              <td><i>SLC24A4</i></td>
              <td>rs2402130</td>
              <td>" . $hirisplex_alleles2['rs2402130'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs2402130'] . "</td>
            </tr>
            <tr>
              <td>20</td>
              <td><i>HERC2</i></td>
              <td>rs12913832</td>
              <td>" . $hirisplex_alleles2['rs12913832'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs12913832'] . "</td>
            </tr>
            <tr>
              <td>21</td>
              <td><i>PIGU</i></td>
              <td>rs2378249</td>
              <td>" . $hirisplex_alleles2['rs2378249'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs2378249'] . "</td>
            </tr>
            <tr>
              <td>22</td>
              <td><i>LOC105370627</i></td>
              <td>rs12896399</td>
              <td>" . $hirisplex_alleles2['rs12896399'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs12896399'] . "</td>
            </tr>
            <tr>
              <td>23</td>
              <td><i>TYR</i></td>
              <td>rs1393350</td>
              <td>" . $hirisplex_alleles2['rs1393350'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1393350'] . "</td>
            </tr>
            <tr>
              <td>24</td>
              <td><i>TYRP1</i></td>
              <td>rs683</td>
              <td>" . $hirisplex_alleles2['rs683'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs683'] . "</td>
            </tr>
            <tr>
              <td>25</td>
              <td><i>ANKRD11</i></td>
              <td>rs3114908</td>
              <td>" . $hirisplex_alleles2['rs3114908'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs3114908'] . "</td>
            </tr>
            <tr>
              <td>26</td>
              <td><i>OCA2</i></td>
              <td>rs1800414</td>
              <td>" . $hirisplex_alleles2['rs1800414'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1800414'] . "</td>
            </tr>
            <tr>
              <td>27</td>
              <td><i>BNC2</i></td>
              <td>rs10756819</td>
              <td>" . $hirisplex_alleles2['rs10756819'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs10756819'] . "</td>
            </tr>
            <tr>
              <td>28</td>
              <td><i>HERC2</i></td>
              <td>rs2238289</td>
              <td>" . $hirisplex_alleles2['rs2238289'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs2238289'] . "</td>
            </tr>
            <tr>
              <td>29</td>
              <td><i>SLC24A4</i></td>
              <td>rs17128291</td>
              <td>" . $hirisplex_alleles2['rs17128291'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs17128291'] . "</td>
            </tr>
            <tr>
              <td>30</td>
              <td><i>HERC2</i></td>
              <td>rs6497292</td>
              <td>" . $hirisplex_alleles2['rs6497292'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs6497292'] . "</td>
            </tr>
            <tr>
              <td>31</td>
              <td><i>HERC2</i></td>
              <td>rs1129038</td>
              <td>" . $hirisplex_alleles2['rs1129038'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1129038'] . "</td>
            </tr>
            <tr>
              <td>32</td>
              <td><i>HERC2</i></td>
              <td>rs1667394</td>
              <td>" . $hirisplex_alleles2['rs1667394'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1667394'] . "</td>
            </tr>
            <tr>
              <td>33</td>
              <td><i>TYR</i></td>
              <td>rs1126809</td>
              <td>" . $hirisplex_alleles2['rs1126809'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1126809'] . "</td>
            </tr>
            <tr>
              <td>34</td>
              <td><i>OCA2</i></td>
              <td>rs1470608</td>
              <td>" . $hirisplex_alleles2['rs1470608'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1470608'] . "</td>
            </tr>
            <tr>
              <td>35</td>
              <td><i>SLC24A5</i></td>
              <td>rs1426654</td>
              <td>" . $hirisplex_alleles2['rs1426654'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1426654'] . "</td>
            </tr>
            <tr>
              <td>36</td>
              <td><i>ASIP</i></td>
              <td>rs6119471</td>
              <td>" . $hirisplex_alleles2['rs6119471'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs6119471'] . "</td>
            </tr>
            <tr>
              <td>37</td>
              <td><i>OCA2</i></td>
              <td>rs1545397</td>
              <td>" . $hirisplex_alleles2['rs1545397'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs1545397'] . "</td>
            </tr>
            <tr>
              <td>38</td>
              <td><i>RALY</i></td>
              <td>rs6059655</td>
              <td>" . $hirisplex_alleles2['rs6059655'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs6059655'] . "</td>
            </tr>
            <tr>
              <td>39</td>
              <td><i>OCA2</i></td>
              <td>rs12441727</td>
              <td>" . $hirisplex_alleles2['rs12441727'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs12441727'] . "</td>
            </tr>
            <tr>
              <td>40</td>
              <td><i>MC1R</i></td>
              <td>rs3212355</td>
              <td>" . $hirisplex_alleles2['rs3212355'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs3212355'] . "</td>
            </tr>
            <tr>
              <td>41</td>
              <td><i>DEF8</i></td>
              <td>rs8051733</td>
              <td>" . $hirisplex_alleles2['rs8051733'] . "</td>
              <td>" . $hirisplex_allele_numbers['rs8051733'] . "</td>
            </tr>
          </tbody>
        </table>
    </div>
    <br>
";
    $raw_skin_alleles = array();
    $skin_contents = "<div align=\"center\" id='avatar'></div>";
    foreach(array_keys($hirisplex_alleles2) as $snp) {
	    if(in_array($snp,$skin_markers_array)) {
		    $raw_skin_alleles[$snp] = $hirisplex_alleles2[$snp];
	    }
    }

    require_once("skin.php");
    $prediction = predict($raw_skin_alleles);
    $acknowledgements = "<br><font size='1'><br><b>Acknowledgements:</b><br><br>Avatars are produced using a modified version of Pablo Stanley and Fang-Pen Lin's <a href='https://github.com/fangpenlin/avataaars'>avataaars</a><br><br>Hair color, shade and eye color predictions are made according to formulas from the <a href='https://hirisplex.erasmusmc.nl'>HIrisPlex system</a> however the absolute probabilities may change due to optimizations of the reference database<br><br>L. Chaitanya, K. Breslin, S. Zuñiga, L. Wirken, E. Pospiech, M. Kukla-Bartoszek, T. Sijen, P. de Knijff, F. Liu, W. Branicki, M. Kayser, S. Walsh. The HIrisPlex-S system for eye, hair and skin colour prediction from DNA: Introduction and forensic developmental validation. Forensic Science International Genetics https://doi.org/10.1016/j.fsigen.2018.04.004<br><br>S. Walsh, L. Chaitanya, K. Breslin, C. Muralidharan, A. Bronikowska, E. Pospiech, J. Koller, L. Kovatsi, A. Wollstein, W. Branicki, F. Liu, M. Kayser, Global skin colour prediction from DNA. Human Genetics, 2017. 136(7): p. 847-863.<br><br>S. Walsh, L. Chaitanya, L. Clarisse, L. Wirken, J. Draus-Barini, L. Kovatsi, H. Maeda, T. Ishikawa, T. Sijen, P. de Knijff, W. Branicki, F. Liu, M. Kayser, Developmental validation of the HIrisPlex system: DNA-based eye and hair colour prediction for forensic and anthropological usage. Forensic Science International: Genetics. 2014 Mar;9:150-61.<br><br>phenotype icons from <a href='https://icons8.com'>icons8</a><br></font";
    $skin_contents .= "<br>Hair Color<br><br><div id='hair_color'></div><br>Hair Shade<br><br><div id='hair_shade'></div><br>Eye Color<br><br><div id='eye'></div><br>Skin Color<br><br><div id='skin_color'></div><br>Hair Curliness<br><br><div id='curliness'></div><br>Freckling<br><br><div id='freckling'></div><br>Sunburn vs Tanning<br><br><div id='sunburn_tanning'></div><br>";
    $scriptToGetAvatarURL .= "<script>var skin_color = JSON.parse('". json_encode($prediction["skin color"]) . "');";
    $scriptToGetAvatarURL .= "var freckle_preds = JSON.parse('" . json_encode($prediction["freckling"]) . "');";    
    $scriptToGetAvatarURL .= "var burning_tanning_preds = JSON.parse('" . json_encode($prediction["burning/tanning"]) . "');";
    $scriptToGetAvatarURL .= "var alleles = JSON.parse('" . json_encode($hirisplex_allele_numbers) ."');";
    $scriptToGetAvatarURL .= "var sex = '" . $sex . "';";
    $scriptToGetAvatarURL .= "var curlyPreds = {'Curly': 0, 'Straight':1}; var cPred = predictCurly(alleles); if (cPred[0]) {curlyPreds['Curly'] = cPred[1]; curlyPreds['Straight'] = 1 - cPred[1];};";
    $scriptToGetAvatarURL .= "var baldingPreds = {'Balding':0}; if (alleles.hasOwnProperty('rs1385699')) {if (alleles['rs1385699'] > 0) {baldingPreds = {'Balding':1}} else {baldingPreds = {'Balding':0}}}";
    $scriptToGetAvatarURL .= "var eyeHairShadePreds = getAllPredictions(alleles); var url = getAvatarURL({'skin color':skin_color, 'freckling':freckle_preds}, eyeHairShadePreds,curlyPreds,baldingPreds,sex); addAvatarToDiv(url,'avatar','200','200');";
    $scriptToGetAvatarURL .= "addTableToDiv('hair_color', eyeHairShadePreds['hair_color']); addTableToDiv('hair_shade', eyeHairShadePreds['hair_shade']); addTableToDiv('eye',eyeHairShadePreds['eye']); addTableToDiv('skin_color', skin_color); addTableToDiv('curliness', curlyPreds); addTableToDiv('freckling', freckle_preds); addTableToDiv('sunburn_tanning',burning_tanning_preds); </script>";

    $skin_contents .= $scriptToGetAvatarURL;

# HIrisplexS

	$hirisplexs_order = array('rs312262906', 'rs11547464', 'rs885479', 'rs1805008', 'rs1805005', 'rs1805006', 'rs1805007', 'rs1805009', 'rs201326893', 'rs2228479', 'rs1110400', 'rs28777', 'rs16891982', 'rs12821256', 'rs4959270', 'rs12203592', 'rs1042602', 'rs1800407', 'rs2402130', 'rs12913832', 'rs2378249', 'rs12896399', 'rs1393350', 'rs683', 'rs3114908', 'rs1800414', 'rs10756819', 'rs2238289', 'rs17128291', 'rs6497292', 'rs1129038', 'rs1667394', 'rs1126809', 'rs1470608', 'rs1426654', 'rs6119471', 'rs1545397', 'rs6059655', 'rs12441727', 'rs3212355', 'rs8051733');

	$hirisplexs_header = "sampleid,rs312262906_A,rs11547464_A,rs885479_T,rs1805008_T,rs1805005_T,rs1805006_A,rs1805007_T,rs1805009_C,rs201326893_A,rs2228479_A,rs1110400_C,rs28777_C,rs16891982_C,rs12821256_G,rs4959270_A,rs12203592_T,rs1042602_T,rs1800407_A,rs2402130_G,rs12913832_T,rs2378249_C,rs12896399_T,rs1393350_T,rs683_G,rs3114908_T,rs1800414_C,rs10756819_G,rs2238289_C,rs17128291_C,rs6497292_C,rs1129038_G,rs1667394_C,rs1126809_A,rs1470608_A,rs1426654_G,rs6119471_C,rs1545397_T,rs6059655_T,rs12441727_A,rs3212355_A,rs8051733_C";


# HIrisplex
	$hirisplex_order = array('rs312262906','rs11547464','rs885479','rs1805008','rs1805005','rs1805006','rs1805007','rs1805009','rs201326893','rs2228479','rs1110400','rs28777','rs16891982','rs12821256','rs4959270','rs12203592','rs1042602','rs1800407','rs2402130','rs12913832','rs2378249','rs12896399','rs1393350','rs683');

	$hirisplex_header = "sampleid,rs312262906_A,rs11547464_A,rs885479_T,rs1805008_T,rs1805005_T,rs1805006_A,rs1805007_T,rs1805009_C,rs201326893_A,rs2228479_A,rs1110400_C,rs28777_C,rs16891982_C,rs12821256_G,rs4959270_A,rs12203592_T,rs1042602_T,rs1800407_A,rs2402130_G,rs12913832_T,rs2378249_C,rs12896399_T,rs1393350_T,rs683_G";


# Irisplex

	$irisplex_order = array('rs12913832', 'rs1800407', 'rs12896399', 'rs16891982', 'rs1393350', 'rs12203592');

	$irisplex_header = "sampleid,rs12913832_T,rs1800407_A,rs12896399_T,rs16891982_C,rs1393350_T,rs12203592_T";



# Replace variants for testing
	$hirisplex_order = $hirisplexs_order;
	$hirisplex_header = $hirisplexs_header;


	$marker_string = $hirisplex_header  . "\nYSEQ" . $sample_id . ",";
	$csv_marker_string = $hirisplex_header  . "\\nYSEQ" . $sample_id . ",";

	foreach ($hirisplex_order as &$marker) {
		if (preg_match('/^$/', $hirisplex_allele_numbers[$marker])) { $hirisplex_allele_numbers[$marker] = "NA"; }
		$marker_string .= $hirisplex_allele_numbers[$marker] . ",";
	    $csv_marker_string .= $hirisplex_allele_numbers[$marker] . ",";
	    
	}

	unset($marker); // break the reference with the last element
    	
	
	$marker_string = preg_replace('/,$/',"", $marker_string);
	$csv_marker_string = preg_replace('/,$/',"", $csv_marker_string);
	
	

    $allele_list_contents .= "The following CSV can be imported into the <a href=\"https://hirisplex.erasmusmc.nl\">HIrisPlex Online Calculator</a>:<br>";
    
    
	$allele_list_contents .= "<form action=\"https://hirisplex.erasmusmc.nl/\" method=\"POST\" id=\"hirisplex\" name=\"hirisplex\" target=\"_blank\">";
	$allele_list_contents .= "<textarea id=\"hirisplex_csv\" name=\"hirisplex_csv\" rows=\"4\" cols=\"50\">\n";
	
	$allele_list_contents .= $marker_string;
	$allele_list_contents .= "</textarea>\n";
	$allele_list_contents .= '<script>function exportCSV(csvData){ 
		
		const rows = [["name1", "city1","some other info"],["name2","city2","more info"]]; let csvContent = "data:text/csv;charset=utf-8," + csvData; var encodedUri = encodeURI(csvContent); window.open(encodedUri);}</script>';
	$allele_list_contents .= "<input type=\"submit\" id=\"hirisplex_submit_button\" value=\"Download CSV\" name=\"hirisplex_submit_button\" onclick=\"exportCSV(document.getElementById('hirisplex_csv').innerHTML,'hirisplex','','Please wait...');\">\n";	
	$allele_list_contents .= "</form>\n";
	
	
	
	
	
    $csvfilename = $sample_id . "_HIrisPlex_YSEQ.csv";







	echo $skin_contents;

	$snipper_alleles = array();	
	$snipper_reversed = array(     'rs1805007', 
	                               'rs1805008', 
	                               'rs4778138', 
	                               'rs7495174', 
	                               'rs12896399', 
	                               'rs1375164', 
	                               'rs1408799', 
	                               'rs16891982', 
	                               'rs4778138', 
	                               'rs4778232', 
	                               'rs4778241', 
	                               'rs8024968',
	                               'rs1129038',
	                               'rs1015362',
	                               'rs3829241',
	                               'rs916977');
	                               
	$snipper_hair_order = array('rs1129038', 'rs11547464', 'rs12913832', 'rs12931267', 'rs1805006', 'rs1805007', 'rs1805008', 'rs1805009', 'rs28777', 'rs35264875', 'rs4778138', 'rs7495174' );

	$snipper_skin_order = array('rs10777129', 'rs13289', 'rs1408799', 'rs1426654', 'rs1448484', 'rs16891982', 'rs2402130', 'rs3829241', 'rs6058017', 'rs6119471' );

	$snipper_eye_order = array('rs12913832', 'rs1129038', 'rs1015362', 'rs11636232', 'rs12203592', 'rs12592730', 'rs12896399', 'rs1375164', 'rs1393350', 'rs1408799', 'rs1667394', 'rs16891982', 'rs1800407', 'rs26722', 'rs4778138', 'rs4778232', 'rs4778241', 'rs6058017', 'rs683', 'rs7183877', 'rs7495174', 'rs8024968', 'rs916977');

        $snipperMarkers = array_unique(array_merge($snipper_hair_order, $snipper_skin_order, $snipper_eye_order));

	  foreach ($snipperMarkers as $snipperMarker) {
		  if (array_key_exists($snipperMarker, $alleles)) {

			  $the_allele = $alleles[$snipperMarker];
			  if (in_array($snipperMarker, $snipper_reversed)) {
				  $snipper_allele = reverse_bases($the_allele);
			  } else {
				  $snipper_allele = $the_allele;
			  }
        

			  $snipper_alleles[$snipperMarker] = $snipper_allele;
		
		  }
	  }

    $allele_list_contents .= "<a href=\"http://mathgene.usc.es/snipper/hairclassifier.html\">Snipper Format (Hair)</a> <i>Choose '4 populations (red, blond, brown, or black hair), no haplotype'</i>:<br>";


	$marker_string ="";
	foreach ($snipper_hair_order as &$marker) {
		if (preg_match('/^$/', $snipper_alleles[$marker])) { $snipper_alleles[$marker] = "N/N"; }
		$marker_string .= $snipper_alleles[$marker];
	    
	}

	unset($marker); // break the reference with the last element
	
	$marker_string = preg_replace('/\//',"", $marker_string);
    $allele_list_contents .= $marker_string . "<br>";

    $allele_list_contents .= "<a href=\"http://mathgene.usc.es/snipper/skinclassifier.html\">Snipper Format (Skin)</a>:<br>";


	$marker_string ="";
	foreach ($snipper_skin_order as &$marker) {
		if (preg_match('/^$/', $snipper_alleles[$marker])) { $snipper_alleles[$marker] = "N/N"; }
		$marker_string .= $snipper_alleles[$marker];
	    
	}

	unset($marker); // break the reference with the last element
	
	$marker_string = preg_replace('/\//',"", $marker_string);
    $allele_list_contents .= $marker_string . "<br>";

    $allele_list_contents .= "<a href=\"http://mathgene.usc.es/snipper/eyeclassifier.html\">Snipper Format (Eye Color)</a> <i>Choose '23 markers, no haplotype'</i>:<br>";


	$marker_string ="";
	foreach ($snipper_eye_order as &$marker) {
		if (preg_match('/^$/', $snipper_alleles[$marker])) { $snipper_alleles[$marker] = "N/N"; }
		$marker_string .= $snipper_alleles[$marker];
	    
	}

	unset($marker); // break the reference with the last element
	
	$marker_string = preg_replace('/\//',"", $marker_string);
    $allele_list_contents .= $marker_string . "<br>";


	$allele_list_contents .= "<br><br>";

	$allele_list_contents .= "The following markers are missing in your data, but could improve your phenotyping prediction.<br><br><table align='center' border='1' width='40%'><tr><td>SNP</td><td>Phenotypes</td><td>Info</td><td>$ = available at YSEQ</td></tr>";
	foreach ($productLinks as $product => $link) {
		if (array_key_exists($product, $alleles) == false) {
			$allele_list_contents .= "<tr><td>" . $product . "</td><td>" . getFormattedPhenoTags(array_values($snpPhenoTags[$product])) . "</td><td><a href='https://www.ncbi.nlm.nih.gov/snp/?term=" . $product . "'>dbSNP</a></td><td><a href='" . $link . "'>$</td></tr>";
		}
	}
	$allele_list_contents .= "</table>";


      echo $allele_list_contents;
      
      if ( $hirisplex_alleles2['rs12913832'] == "" )
      {
          echo "<br>";
          echo "<b>Why does my avatar wear sunglasses?</b><br>";
          echo "Answer: Inputs missing HERC2 rs12913832 will not produce an eye colour prediction result.<br>";
      }
      
      echo $acknowledgements;



      echo "</div>";
    }
?>

</div>
