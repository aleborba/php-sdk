<?php

require '../src/meli.php';

// Create our Application instance (replace this with your appId and secret).
$meli = new Meli(array(
  'appId'   => getenv('MeliPHPAppId'),
  'secret'  => getenv('MeliPHPSecret'),
));

$PMSToolId = getenv('PMSToolId');

$paging = "";
if(isset($_REQUEST['offset'])) {
	$paging = $_REQUEST['offset'];
}


if(isset($_REQUEST['q'])){
	
	$query = $_REQUEST['q'];
	
	$search = $meli->get('/sites/#{siteId}/search',array(
	'q' => $query,
	'offset' => $paging,
	));
  $search = $search['json'];
	$currenciesJSON = $meli->get('/currencies');
  $currenciesJSON = $currenciesJSON["json"];
  $currencies = array();
  foreach ($currenciesJSON as &$currency) {
    $currencies[$currency["id"]] = $currency;
  }
}

function add_or_change_parameter($parameter, $value) 
{ 
  $params = array(); 
  $output = "?"; 
  $firstRun = true; 
  foreach($_GET as $key=>$val) 
  { 
   if($key != $parameter) 
   { 
    if(!$firstRun) 
    { 
     $output .= "&"; 
    } 
    else 
    { 
     $firstRun = false; 
    } 
    $output .= $key."=".urlencode($val); 
   } 
  } 
  if(!$firstRun) 
   $output .= "&"; 
  $output .= $parameter."=".urlencode($value); 
  return htmlentities($output); 
 } 

?>
<!doctype html>
<html>
  <head>
	<meta charset="UTF-8"/>
    <title>MeliPHP SDK - Example Search</title>
    <style type="text/css">
		LI { display: block }
		LI:before {
			content: counter(item) ". ";
			counter-increment: item;
		}
	</style>
  </head>
  <body>
  	<h1>MeliPHP SDK - Example Search</h1>
    
    <form>
    	<input name="q" value="<?php echo $query; ?>" />
    	<input type="submit" name="search" value="search"/>
    </form>
    <p>Showing <?php 
    	echo  ($search['paging']['offset'] + 1) . '-';
    	echo  ($search['paging']['offset']+$search['paging']['limit']) .' de ';
    	echo  $search['paging']['total']
    	?> <br>
    	Total pages: <?php echo ceil($search['paging']['total']/$search['paging']['limit'])?> <br>
		<!-- Paginado -->    	
		<?php
		if ($search['paging']['offset'] > 0) {
			echo '<a href="' . add_or_change_parameter('offset', max(0,$search['paging']['offset']-$search['paging']['limit'])) . '">Previous page</a>&nbsp;&nbsp;';
		}
		if ($search['paging']['offset'] + $search['paging']['limit'] < $search['paging']['total']) {
			echo '<a href="' . add_or_change_parameter('offset', $search['paging']['offset']+$search['paging']['limit']) . '">Next page</a>';
		}

    	?>	
    </p>
    <?php
    echo '<ol style="counter-reset: item ' . ($search['paging']['offset']) . '">'
    ?>
	
	<?php
		foreach ($search['results'] as &$searchItem) {
		   echo '<li><img src="'.$searchItem['thumbnail'].'"><a href="http://pmstrk.mercadolibre.cl/jm/PmsTrk?
tool='.$PMSToolId.'&go=/jm/item?site=MLA$$id=' . substr($searchItem['id'],3). '">'. $searchItem['title'].'</a>&nbsp;'.$currencies[$searchItem["currency_id"]]["symbol"] .'&nbsp;'. number_format ( $searchItem["price"] , $currencies[$searchItem["currency_id"]]["decimal_places"] ).'</li>';
  }
	?>
    </ol>
  </body>
</html>