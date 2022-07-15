<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
  />
<div class="modal slideInDown animated" tabindex="-1" role="dialog" id="searchmodal" style="-webkit-transform: translate(0);
   -moz-transform: translate(0);
   transform: translate(0);animation-duration: 0.3s;display:none;">
   {* slideOutUp on close*}
  <div class="modal-dialog" role="document">

    <div class="container-fluid main-wrapper">

      <header class="container-fluid d-flex justify-content-center justify-content-lg-start CotracSearchHeader">
        <h1 class="col-12 col-md-10 col-xl-2 mt-4 mt-md-0 text-center text-lg-left">
        <a href="https://creaprod.cotrac.fr/fr/">
            <img class="LogoCotracSearchHeader" src="/modules/cotrac_search/views/img/logo-cotrac_2021.svg"
              alt="logo COTRAC" />
            <span class="CotracSearchTitleHidden">COTRAC</span>
          </a>
        </h1>

        <div class="col-12 col-md-10 CotracSearchHeaderWrapper">
          <div class="CotracSearchInput">
            <label for="inputsearchcotrac">Votre recherche : </label>
            <input id="inputsearchcotrac" type="search" />
          </div>
          
          <div class="reassurance">
            
            <div class="CotracSearchFreeShipping">
              <img class="img-reassurance" src="https://cotrac.fr/modules/blockreassurance/views/img/img_perso/livraison_gratuite_degrade.png" alt="réassurance livraison gratuite" />
              <p>Livraison gratuite <a href="https://cotrac.fr/content/1-livraison-retour"><span>dès 80€* </span></a>HT</p>
            </div>

            <div class="CotracSearchServiceClient">
              <img class="img-reassurance" src="https://cotrac.fr/modules/blockreassurance/views/img/img_perso/service_client_degrade.png" alt="réassurance service client" />
              <p>Service client : <span>03 20 17 03 60</span></p>
            </div>

          </div>
        </div>

        <div class="closeButton">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </header>

      <section class="container-fluid container-card" id="containerSearching">
        <div class="row">
          <div class="left-column col-12 col-lg-2">
            <div class="filter-category">
              <fieldset>
                <legend>Catégories:</legend>

                <div class="category-list">
                </div>

              </fieldset>
            </div>

            <div class="filter-price">
              <fieldset>
                <legend>Prix :</legend>

                <div class="CotracSearch-filter-price d-flex justify-content-between">
                  <input type="number" name="min-price" id="min-price" placeholder="0" min="0">
                  <div class="range-separator"></div>
                  <input type="number" name="max-price" id="max-price" placeholder="22000" min="1">
                  <label for="max-price">€</label>
                </div>
              </fieldset>
            </div>

          </div>

          <div class="col-12 col-lg-10">
            <div class="row">
                <div id="CotracSearchKeywords">
                  <span class="CotracSearchKeyword-title">Mots-clés suggérés :</span>
                </div>
              </div>
          </div>
          <div class="col-12 col-lg-10 wrapper-product" id="wrapper-product">
            <div class="row" >
                <div class="col-12 justify-content-center" id="results"> {*****RESULTAT PAR DÉFAUT*****}
                  
                </div>
            </div>
          </div>
        </div>
      </section>

      <footer class="container CotracSearchFooter">
        <div class="row">
          <div class="col-12 col-lg-10 offset-lg-2">
            <img id="search_banner" src="" alt="publicité COTRAC" style="display:none;" />
          </div>
        </div>
      </footer>
    </div>
  </div>
</div>

{* <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> *}
<script>
var search_enginelink = '{$search_enginelink}';
var public_token = '{$public_token}';
var language_code = '{$language.language_code}';
</script>