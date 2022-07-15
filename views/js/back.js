$(document).ready(function(){
    var datefinupdate=0;
    var currentwidth=0;
    var nbproduct=$("#nb_product").text()*$("#nb_lang").text();
    var products_index_url=index_url;
    products_index_url=products_index_url.replace("cotrac_search-products-indexer","cotrac_search-products-indexer_ajax");
    var gostart=0;
    $("#index_products").click(function() {
        // console.log("test");
        $.ajax({
            type: "GET",
            headers: {
                "Content-Type": "application/json",
                "Public-token": public_token
            },
            url: "https://search.cotrac.fr/api/checkindexing",
            success: function(oRep){
                //console.log(oRep.data/nb_lang);
                gostart=oRep.data/nb_lang;
                console.log("Last start : "+gostart);
                ajaxproducts(gostart);
                $("#index_products").attr("disabled", true);
                $("#force_index_products").attr("disabled", true);
            }
        });
    });

    $("#force_index_products").click(function() {
        ajaxproducts(0);
        $("#index_products").attr("disabled", true);
        $("#force_index_products").attr("disabled", true);
    });

    function ajaxproducts(start){
        // console.log(start);
        console.log(products_index_url+"&start="+start);
        $.ajax({
            type: "GET",
            url: products_index_url+"&start="+start,
            success: function(oRep){
                // console.log('request...');
                data=JSON.parse(oRep);
                console.log(data);
                if(data.finished!=1){
                    currentwidth=Math.ceil((100*(data.next-30))/(nbproduct/nb_lang));
                    // console.log(currentwidth);
                    $(".progress-bar").width(currentwidth+"%");
                    $(".progress-bar").text(currentwidth+"%");
                    next(data.next);
                    $("#indexing-warning").fadeIn(500).show();
                    $(".progress").fadeIn(500).show();
                    $("#indexing-ok").hide();
                }
                else{
                    $("#indexing-warning").hide();
                    $(".progress").hide().fadeOut(1500);
                    $("#indexing-ok").fadeIn(500).show();
                    $("#index_products").attr("disabled", false);
                    $("#force_index_products").attr("disabled", false);
                }
            },
            error: function(oRep){
                console.log(oRep);
                ajaxproducts(start);
            }
        });
    }

    function next(nbnext){
        // setTimeout(ajaxproducts(nbnext), 100);
        ajaxproducts(nbnext);
    }

});