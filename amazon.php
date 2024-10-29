<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
//
// amzASIN - Get item from Amazon market place with Amazon ASIN &
// return results in xml format
// $response = amzASIN("co.uk", "stargate sg1", "xeniasites-21");
//                      country    ASIN code      amazon tag
// $region -> co.uk = UK, com = USA
// $asin -> ASIN code used to retreive product details
// $tag -> amazon tag for UK = 'yourtag-21' and USA = 'yourtag-20'
// $response -> results in xml format or false if error
//
function amzASIN($region, $asin, $tag) {
	$xml = amz_awsSignedRequest($tag, $region, array(
		"Operation" => "ItemLookup",
		"ItemId" => $asin,
		"ResponseGroup" => "Large",
		"IdType" => "ASIN",
		"Condition" => "All"
	));
if(!$xml->Items) return $xml;	
	foreach($xml->Items->Item as $item){ 
		//$item = $xml->Items->Item;
		$asin = (string) @$item->ASIN;
		$ean = (string) @$item->ItemAttributes->EAN;
		$title = (string) @$item->ItemAttributes->Title;
		$url = (string) @$item->DetailPageURL;
		$image = (string) @trim($item->LargeImage->URL);
		if(empty($image)) $image = (string) @trim($item->MediumImage->URL);
		if(empty($image)) $image = (string) @trim($item->SmallImage->URL);
		if(empty($image)) $image = "";
		$images = @$item->ImageSets;
		$listprice = (string) @$item->ItemAttributes->ListPrice->Amount;
		$manufacturer =  (string) @$item->ItemAttributes->Manufacturer;
		$brand =  (string) @$item->ItemAttributes->Brand;
		$model =  (string) @$item->ItemAttributes->Model;
		$price = (string) @$item->OfferSummary->LowestNewPrice->Amount;
		$code = (string) @$item->OfferSummary->LowestNewPrice->CurrencyCode;
		$qty = (string) @$item->OfferSummary->TotalNew;
		
		$offer = (string) @$item->Offers->Offer->OfferListing->Price->Amount;
		$offersave = (string) @$item->Offers->Offer->OfferListing->AmountSaved->Amount;
		$offerorig = $offer + $offersave;
		if(empty($offer) && !empty($listprice)) $offer = $listprice;
		
		$desc = (string) @$item->EditorialReviews->EditorialReview->Content;

		foreach(@$item->ItemAttributes->Feature as $bullet){
			$bullets[]= (string) $bullet;
		}

		//$bullets = @$item->ItemAttributes->Feature;
		unset($cat, $cats, $c);
		$cat[] = (string) $item->BrowseNodes->BrowseNode->Name;
		if(!empty($item->BrowseNodes->BrowseNode->Ancestors)){
			$cat[] = (string) $item->BrowseNodes->BrowseNode->Ancestors->BrowseNode->Name;
		}
		if((string) $item->ItemAttributes->ProductGroup != "Categories") $cat[] = (string) $item->ItemAttributes->ProductGroup;
		$cat[] = (string) @$item->ItemAttributes->Binding;
		$cats=implode(", ",$cat);
		
	$ratingurl='http://www.amazon.'.$region.'/gp/customer-reviews/widgets/average-customer-review/popover/ref=dpx_acr_pop_?contextId=dpx&asin='.$asin;
	$rate="";
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		libxml_use_internal_errors(true);
    	$dom->loadHTMLFile($ratingurl);
		$spans = $dom->getElementsByTagName('span');
		foreach($spans as $span){
			$rate = str_replace(" out of 5 stars","",trim($span->nodeValue));
			break;
		}
	
		if ($qty !== "0") {
			$response[] = array(
				"asin" => $asin,
				"ean" => $ean,
				"code" => $code,
				"region" => $region,
				"manufacturer" => $manufacturer,
				"brand" => $brand,
				"model" => $model,
				"list price" => number_format((float) ($listprice / 100), 2, '.', ''),
				"offer summery" => number_format((float) ($price / 100), 2, '.', ''),
				"" => "",
				"offer" => number_format((float) ($offer / 100), 2, '.', ''),
				"offer save" => number_format((float) ($offersave / 100), 2, '.', ''),
				"offer orig" => number_format((float) ($offerorig / 100), 2, '.', ''),
				"rating" => $rate,
				"image" => $image,
				"img" => "<img src='".urldecode($image)."' />",
				"images" => $images,
				"url" => urldecode($url),
				"title" => $title,
				"categories" => $cats,
				"description" => $desc,
				"bullets" => $bullets
			);
		}
	}
	
	$response[] = array( "response" => $xml->Items );

	return $response;
}

function amz_getCategory($node) {
    $category = array();
    $category["Category"] = (string)$node->Name;

    if (isset($node->Ancestors)) {
        do {
            $node = $node->Ancestors->BrowseNode;
            if (isset($node->IsCategoryRoot) && isset($node->Ancestors)) {
                $category["Root"] = (string)$node->Ancestors->BrowseNode->Name;
                break;
            }
        } while (isset($node->Ancestors));
    }

    return $category;
}


 
function amz_getPage($url) {
 
	$curl = curl_init($url);
	//curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$html = curl_exec($curl);
	curl_close($curl);
	return $html;
}
 
function amz_awsSignedRequest($tag, $region, $params) {
	$public_key=get_option('amz_amazonaws', '');
	$private_key=get_option('amz_amazonsecret', '');
 
	$method = "GET";
	$host = "ecs.amazonaws." . $region;
	$host = "webservices.amazon." . $region;
	$uri = "/onca/xml";
 
	$params["Service"] = "AWSECommerceService";
	$params["AssociateTag"] = $tag; // Put your Affiliate Code here
	$params["AWSAccessKeyId"] = $public_key;
	$params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
	$params["Version"] = "2013-08-01";
 
	ksort($params);
 
	$canonicalized_query = array();
	foreach ($params as $param => $value) {
		$param = str_replace("%7E", "~", rawurlencode($param));
		$value = str_replace("%7E", "~", rawurlencode($value));
		$canonicalized_query[] = $param . "=" . $value;
	}
 
	$canonicalized_query = implode("&", $canonicalized_query);
 
	$string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;
	$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));
	$signature = str_replace("%7E", "~", rawurlencode($signature));
 
	$request = "http://" . $host . $uri . "?" . $canonicalized_query . "&Signature=" . $signature;
	//echo $request;
	$response = amz_getPage($request);
 
	//var_dump($response);
 
	$pxml = @simplexml_load_string($response);
	if ($pxml === False) {
		return False;// no xml
	} else {
		return $pxml;
	}
}
?>
