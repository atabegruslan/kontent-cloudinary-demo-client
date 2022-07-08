<?php

// https://github.com/Kentico/kontent-delivery-sdk-php

require __DIR__ . '/vendor/autoload.php';

use Kentico\Kontent\Delivery\DeliveryClient;
use Carbon\Carbon;
use DevCoder\DotEnv;

(new DotEnv(__DIR__ . '/.env'))->load();

$projectId = getenv('PROJECT_ID');

// Initializes an instance of the DeliveryClient client
$client = new DeliveryClient($projectId);

$codename = isset($_GET['codename']) ? $_GET['codename'] : 'demo1';
$item = $client->getItem($codename);

$lastModified = new Carbon($item->system->lastModified);

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://deliver.kontent.ai/{$projectId}/items/{$codename}",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));
$response = curl_exec($curl);
curl_close($curl);
$response = json_decode($response);
$elements = $response->item->elements;
reset($elements);
$first_key = key($elements);
$first_key = snakeToCamel($first_key);
$assets = json_decode($item->{$first_key});

function snakeToCamel($input)
{
  return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Preview</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container">
		<div class="jumbotron">
			<h1><?php echo $item->system->name; ?></h1>
		</div>

		<table class="table table-striped">
		  <tbody>
		    <tr>
		      <th>Type</th>
		      <td><?php echo $item->system->type; ?></td>
		    </tr>
		    <tr>
		      <th>Status</th>
		      <td><?php echo $item->system->workflowStep; ?></td>
		    </tr>
		    <tr>
		      <th>Post ID</th>
		      <td><?php echo $item->system->id; ?></td>
		    </tr>
		    <tr>
		      <th>Post Slug</th>
		      <td><?php echo $item->system->codename; ?></td>
		    </tr>
		    <tr>
		      <th>Post Last Modified</th>
		      <td><?php echo $lastModified->toDateTimeString(); ?></td>
		    </tr>
		  </tbody>
		</table>

		<?php foreach($assets as $asset): ?>
			<div class="row">
				<div class="col-md-12">
					<p>Public ID: <?php echo $asset->public_id; ?></p>
					<p>Version: <?php echo $asset->version; ?></p>
					<img src="<?php echo $asset->url; ?>" class="img-thumbnail" alt="<?php echo $asset->name; ?>">
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</body>

<?php
echo '<pre>';
print_r($item);
echo '</pre>';
?>
