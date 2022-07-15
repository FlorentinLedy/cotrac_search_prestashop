{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
  <h3><i class="icon-cogs"></i> Index </h3>
  <div id="indexing-warning" class="alert alert-warning" style="display: none">
	Indexation en cours. Veuillez ne pas quitter cette page
  </div>
  <div id="indexing-ok" class="alert alert-success" style="display: none">
	Indexation terminée !
  </div>

  <div class="row">
	  <button class="btn btn-default" id="index_products">
	  	Indexer tous les produits (<b id="nb_product">{$nb_product}</b>)
	  </button>
	  <button class="btn btn-danger" id="force_index_products">
	  	Reconstruire l'indexation (<b id="nb_product">{$nb_product}</b>)
	  </button>
	<div class="progress" style="display: none">
		<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">0%</div>
	</div>
  </div>
  <br>
  {* <div class="row">
	<div class="alert alert-info">
	  Vous pouvez définir une tâche cron qui va reconstruire l'index des produits en utilisant l'URL suivante :
	  <br>
	  <strong id="products_index_url">{$products_index_url}</strong>
	  <br>
	</div>
  </div> *}
  <div class="row">
	<div class="alert alert-info"><b>{$nb_product}</b> produits seront indexés dans <b id="nb_lang">{$nb_lang}</b> langue(s). Le temps de traitement est estimé à <b>{(($nb_product*$nb_lang*0.00358)/60)|round:0}</b> heure(s)</div>
  </div>
</div>
{if $success == false}
<div id="indexing-error" class="alert alert-danger" style="display: block;">
Erreur avec une ou plusieurs clés API : <span id="errormsg">{$messagefromapi}</span>
</div>
{else}
<div id="indexing-ok" class="alert alert-success" style="display: block;">
Clés API valides et module relié !
</div>
{/if}

{* <script>
function checkifstarted(){
	$.ajax({
		type: "GET",
		headers: {"Content-Type": "application/json","Public-token": {$public_token}},
		url: "https://search.cotrac.fr/api/checkkey/",
		success: function(oRep){
			data=JSON.parse(oRep);
			console.log(data);
		}
	});
}
</script> *}

<script>
var public_token="{$public_token}";
var nb_lang="{$nb_lang}";
var index_url = "{$products_index_url}";
</script>