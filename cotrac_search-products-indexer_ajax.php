<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
require_once __DIR__ . '/../../config/config.inc.php';
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/cotrac_search.php';
$t0 = time();

if (substr(Tools::encrypt('cotrac_search/index'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('cotrac_search')) {
    http_response_code(401);
    $resp = array(
        "status" => "error",
        "message" => "Bad token",
    );
    exit(json_encode($resp));
}

// if (Configuration::get('COTRAC_SEARCH_LIVE_MODE')==false) {
//     http_response_code(401);
//     $resp = array(
//         "status" => "error",
//         "message" => "Le module est désactivé, veuillez l'activer dans la configuration du module..",
//     );
//     exit(json_encode($resp));
// }

if(isset($_GET["start"])){
    $start=$_GET["start"];
}else{
    $start=0;
}

Shop::setContext(Shop::CONTEXT_ALL);

$count = 0;
$finished=0;
$idlang=1;
$products = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'product WHERE `active`=1 LIMIT 30 OFFSET '.$start);

$language_list=Language::getLanguages(true, Context::getContext()->shop->id);


foreach($language_list as $lang)
{
    $idlang=$lang["id_lang"];
    foreach($products as $p){
        
        $productinfo=array();
        

        $product = new Product($p['id_product'], false ,$idlang);
        //     echo "<pre>";
        // print_r($product);
        // echo "</pre>";
        // if(isset($product)){
        if (Validate::isLoadedObject($product)) {
            $img=Product::getCover($p['id_product']);
            if(!is_bool($img)){
                $link = new Link;//because getImageLInk is not static function
                $productinfo['image_link'] = 'https://'.$link->getImageLink($product->link_rewrite, $img["id_image"], 'home_default');
            }else{
                $productinfo['image_link']=NULL;
            }
        

            $productinfo['price']=Product::getPriceStatic($p['id_product'],true);

            $productinfo['id_product']=$p['id_product'];
            $productinfo['reference']=$product->reference;
            $productinfo['supplier_reference']=$product->supplier_reference;
            $productinfo['show_price']=$product->show_price;
            $productinfo['date_upd']=$product->date_upd;
            $link = new Link;
            $productinfo['link']=$link->getProductLink($product,null,null,null,$idlang);
            $productinfo['name']=$product->name;
            // $productinfo['description']=$product->description_short."\n".$product->description;
            $productinfo['tags']="";
            $productinfo['language_code']=$lang["language_code"];
            $productinfo['category']="";
            $categories=$product->getCategories();
            foreach($categories as $category_id){
                $cate=new Category($category_id, $idlang);
                $productinfo['category'].=$cate->name." | ";
            }
            if(isset(Tag::getProductTags($p['id_product'])[1]))
            foreach(Tag::getProductTags($p['id_product'])[1] as $tag){
                $productinfo['tags'].=$tag." ";
            }
            $token=Configuration::get('COTRAC_SEARCH_TOKEN');
            // echo (callAPI($token,"POST","https://search.cotrac.fr/api/products",json_encode($productinfo)));
            callAPI($token,"POST","https://search.cotrac.fr/api/products",json_encode($productinfo));
            $count++;
        }
        // echo "<pre>";
        // print_r($productinfo);
        // echo "</pre>";
    }
}
$t1 = time();
$finaltime=$t1-$t0;
$next=$start+30;
if($count<=1) $finished=1;      
$resp = array(
    "status" => "success",
    "time" => $finaltime,
    "finished" => $finished,
    // "next" => $next+1,
    "next" => $next,
    "nb_finished" => $count,
    "nb_lang" => count($language_list),
    "asked" => $start,
);
http_response_code(200);
echo (json_encode($resp));

function callAPI($token,$method, $url, $data){
    $curl = curl_init();
    switch ($method){
    case "POST":
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
    case "PUT":
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
        break;
    default:
        if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    //     'Content-Type: application/json',
    //     'Authorization: Bearer ' . $token
    // ));     
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Api-token: $token",
        'Content-Type: application/json',
     ));
    //debut debug
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //fin debug
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
    $result = curl_exec($curl);
    if(!$result){die("Connection Failure");}
    curl_close($curl);
    return $result;
  }