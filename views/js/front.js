window.addEventListener('load', function () {

    var currentPageNumber = 1; // initialization before all functions
    var currentCategory="";
    var noresultstoadd = false;
    var haveclicked=false;
    var minprice=null;
    var maxprice=null;
    var socket;
    var i;
  
    function debounce(func, wait, immediate, context) {
      var result;
      var timeout = null;
      return function() {
        var ctx = context || this,
          args = arguments;
        var later = function() {
          timeout = null;
          if (!immediate) result = func.apply(ctx, args);
        };
        var callNow = immediate && !timeout;
        // Tant que la fonction est appelée, on reset le timeout.
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) result = func.apply(ctx, args);
        return result;
      };
    }

    function sendmsg() {
      if (typeof socket !== 'undefined') {
        val = $("#inputsearchcotrac").val();
        if (val != "") {
          // console.log(val.length);
          if (val.length >= 2) {
            // console.log(val);
            var content = new Object();
            content["message"] = val;
            content["page"] = currentPageNumber;
            content["status"] = "success";
            if(minprice!=null && minprice!='') content["minprice"] = minprice;
            if(maxprice!=null && maxprice!='') content["maxprice"] = maxprice;
            if(currentCategory!=null && currentCategory!='') content["category"] = currentCategory;
            content["lang"]= language_code;
            content["shop_token"] = public_token;
            socket.send(JSON.stringify(content));
            //console.log("request sent..");
          } else {
            /*$("#results").html("");*/
          }
        } else {
          /* $("#results").html("");
          $("#results").append("<p class="CotracSearchNoResult">Désolé, aucun résultat n'a été trouvé pour cette recherche !</p>"); */
        }
      } else {
        setTimeout(function() {
          sendmsg();
        }, 200);
      }
    }
  
    function sendexecutesearch() {
      if (typeof socket !== 'undefined') {
        val = $("#inputsearchcotrac").val();
        let isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;
        if (val != "") {
          // console.log(val.length);
          if (val.length >= 3) {
            // console.log(val);
            $.ajax({
              method: 'post',
              headers: {
                  "Public-token": public_token,
              },
              data: {
                string: val,
                origin_page: window.location.href,
                is_mobile: isMobile,
              },
              url: "https://search.cotrac.fr/api/stats/executesearch",
              success: function(oRep){
                  //console.log(oRep);
              },
              error: function(oRep){
                  console.log(oRep.responseJSON);
              }
            });
          }
        }
      } else {
        setTimeout(function() {
          sendexecutesearch();
        }, 1500);
      }
    }

    var traiterSendMsg = debounce(sendmsg, 280);
    var traiterExecuteSearch = debounce(sendexecutesearch,3000);
  
    function page_next(){
      currentPageNumber++;
      //console.log("need to load... page " + currentPageNumber);
      sendmsg();
    }
  
    var traiterPageNext = debounce(page_next, 100);
  
    // on déclence une fonction lorsque l'utilisateur utilise sa molette
      $(".wrapper-product").scroll(function() {
        // cette condition vaut true lorsque le visiteur atteint le bas de page
        // si c'est un iDevice, l'évènement est déclenché 150px avant le bas de page
        // || agentID && ($(".wrapper-product").scrollTop() + $(".wrapper-product").height()) + 150 > $(".wrapper-product").height())
        var acurrentcheck=false;
        $('.checkcategory-container input', $('#category-list')).each(function () {
          //console.log($(this).data("product"));
          //console.log(this);
          //console.log($(this));
          if(this.checked){
            //console.log("check detecté");
            acurrentcheck=true;
          }
        });
  
        if(acurrentcheck==false){
          if(currentPageNumber<10){
            //+125 sert à charger 125px avant la fin du scroll
            if($(".wrapper-product").scrollTop()+125 > $("#results").height()-$(".wrapper-product").height()) {
                // on effectue nos traitements
                if(noresultstoadd==false){
                    if(currentPageNumber<10){
                      traiterPageNext();
                    }
                }
            }
          }
        }
    });

    
  const socketOpenListener = (event) => {
    console.log('Connected');
  };
  
  const socketCloseListener = (event) => {
    if (socket) {
      console.log('Disconnected.');
    }
    setTimeout(function() {
      // console.log('tentative...');
      socket = new WebSocket('wss://'+search_enginelink);
      socket.addEventListener('open', socketOpenListener);
      socket.addEventListener('close', socketCloseListener);
      socket.addEventListener('message', socketMessageListener);
    }, 1500);
  };
  
  socketCloseListener();
  
    const socketMessageListener = (event) => {
      //console.log(event.data);
      var response = JSON.parse(event.data);
      //console.log(response);
      //console.log(response.products[0]);
      if (currentPageNumber == 1) {
        $("#results").html("");
      }
  
      //console.log(response.products.length);
      if(response.products.length != 0){
        var categories=new Set();
        var products=new Set();
        $('.search_product', $('#results')).each(function () {
          products.add($(this).data("product").id_product);
        });
        var nbajouts=0;
        for(i=0;i<response.products.length;i++){
          var element=response.products[i];
          //console.log(element);
          if (element !== null) {
            if (element.image_link != null)
              if(!products.has(element.id_product)){
                $("#results").append(
                  $('<div class="card-cotrac col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 search_product slideInUp animated" style="-webkit-transform: translate(0);-moz-transform: translate(0);transform: translate(0);animation-duration: 0.13s;">')
                  //$('<div class="card-cotrac col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 search_product">')
                  .html('<div class="card"><a href="' +element.link + '"><img src="' + element.image_link +
                  '" loading="lazy" class="card-img-top"><div class="card-body"><h5 class="card-title">' + element.name + '</h5><p class="card-text">' + parseFloat(element
                    .price).toFixed(2) + '€</p><input type="button" class="btn btn-primary" value="à propos"></div></a></div>')
                  .data("product",element)
                );
                nbajouts++;
              }
            else
              if(!products.has(element.id_product)){
                $("#results").append(
                  $('<div class="card-cotrac col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 search_product slideInUp animated">')
                  //$('<div class="card-cotrac col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 search_product">')
                  .html('<div class="card"><img src="https://cotrac.fr/img/p/fr-default-home_default.jpg" loading="lazy" class="card-img-top"><div class="card-body"><a href="' +
                  element.link + '" target="_blank"><h5 class="card-title">' + element.name +
                  '</h5></a><p class="card-text">' + parseFloat(element.price).toFixed(2) + '€</p><a href="' + element
                  .link + '" class="btn btn-primary">à propos</a></div></div>')
                  .data("product",element)
                  );
                nbajouts++;
              }
          }
          if(!products.has(element.id_product) && element !== null && currentPageNumber == 1){
            let columns = response.products[i].category.trim().split(' | ');
            
            if(categories.size<10){
              var countcolumns=0;
              while(countcolumns<4){
                var valuetoaddtoset=columns[countcolumns];
                if(typeof valuetoaddtoset !== 'undefined'){
                  if(valuetoaddtoset.includes("MARQUE")==false){
                    categories.add(valuetoaddtoset.replace('|', ''));
                  }
                }
                countcolumns++
              }
              var j;
              $(".category-list").html('');
              const iterator = categories.values();
              for(j=0;j<categories.size;j++){
                $(".category-list").append('<div class="checkcategory-container"><input class="checkcategory" type="checkbox" id="category-'+(j+1)+'" /><label for="category-'+(j+1)+'">'+iterator.next().value+'</label></div>');
              }
            }
          }
          /* <div class="checkcategory-container">
            <input class="checkcategory" type="checkbox" id="category-1" />
            <label for="category-1">Chenilles caoutchouc</label>
          </div> */
        }
        $(".checkcategory").change(function() {
          $('.search_product', $('#results')).each(function () {
            //console.log($(this).data("product"));
            currentCategory="";
            $(this).show();
          });
          if(this.checked) {
            //console.log($(this).attr('id'))
            var id=$(this).attr('id');
            var labelcategory=$("label[for='"+id+"']").html();
            currentCategory=labelcategory;
            $('.search_product', $('#results')).each(function () {
              //console.log($(this).data("product"));
              if(!$(this).data("product").category.includes(labelcategory)){
                $(this).hide();
              }
            });
          }else{
            $('.search_product', $('#results')).each(function () {
              //console.log($(this).data("product"));
              currentCategory="";
              if(!$(this).data("product").category.includes(labelcategory)){
                $(this).show();
              }
            });
          }
        });
        //console.log(categories);
      }
  
      /* let columns = response.products[0].category.trim().split(' | ');
      console.log(columns); */
  
      $(".search_product .card a").click(function (){
        //console.log($(this).parent().parent().data("product").id_product);
        if(haveclicked==false){
          $.ajax({
            method: 'post',
            headers: {
                "Public-token": public_token,
            },
            data: {
                id_product: $(this).parent().parent().data("product").id_product,
            },
            url: "https://search.cotrac.fr/api/stats/clickproduct",
            success: function(oRep){
                //console.log(oRep);
            },
            error: function(oRep){
                console.log(oRep.responseJSON);
            }
          });
        }
        haveclicked=true;
      });
  
      $('.checkcategory-container input[type="checkbox"]').on('change', function() {
        $('.checkcategory-container input[type="checkbox"]').not(this).prop('checked', false);
      });
  
      if(typeof response.suggest_keyword !== 'undefined'){
        if(response.suggest_keyword.length != 0){
          $("#CotracSearchKeywords").html('<span class="CotracSearchKeyword-title">Mots-clés suggérés :</span>');
          response.suggest_keyword.forEach(element => {
            //console.log(element);
            if (element !== null) {
              //if (element.suggest_keyword != null && element.priority != null)  $("#CotracSearchKeywords").append('<span class="CotracSearchKeyword">'+element.suggest_keyword+'</span>');
              if (element.suggest_keyword != null && element.priority != null)  $("#CotracSearchKeywords").append($('<span class="CotracSearchKeyword">').html(element.suggest_keyword).data("keyword",element));
            }
          });
          $(".CotracSearchKeyword").click(function (){
            //console.log($(this).data("keyword"));
            var input = document.getElementById('inputsearchcotrac');
            $("#inputsearchcotrac").val($(this).data("keyword").suggest_keyword);
            const end = input.value.length;
            input.setSelectionRange(end, end);
            input.focus();
            $("#wrapper-product").animate({
              scrollTop: 0,
            }, 200);
            $("#wrapper-product")[0].scrollTo(0, 0);
            noresultstoadd=false;
            currentPageNumber = 1;
            currentCategory = "";
            traiterSendMsg();
            traiterExecuteSearch();
          });
        }else{
          $("#CotracSearchKeywords").html('');
        }
      }
  
      if(typeof response.banner !== 'undefined'){
        var urlbanner='https://search.cotrac.fr'+response.banner.path;
        $("#search_banner").attr('src', urlbanner);
        $("#search_banner").css('display', 'inline');
      }
  
      if (response.products.length == 0) {
        noresultstoadd=true;
      }
      if(response.products.length == 0 && currentPageNumber == 1){
        $("#results").append("<p class=\"CotracSearchNoResult\">Désolé, aucun résultat n'a été trouvé pour cette recherche !</p>");
      }
  
    };
  
    $("#inputsearchcotrac").bind("change paste keyup", function() {
      traiterExecuteSearch();
    });
  
    $("#inputsearchcotrac").keyup(function() {
      // console.log(event);
      switch (event.key) {
        case 'Control':
          break;
        case 'Shift':
          break;
        case 'ArrowLeft':
            break;
        case 'ArrowRight':
          break;
        case 'Insert':
          break;
        case 'Alt':
          break;
        case '*':
          break;
        case ')':
          break;
        case '$':
          break;
        case '€':
          break;
        case '=':
          break;
        case 'ù':
          break;
        case '!':
          break;
        case 'NumLock':
          break;
        case 'AltGraph':
          break;
        case '²':
          break;
        default:
            var deviceAgent = navigator.userAgent.toLowerCase();
            var agentID = deviceAgent.match(/(iphone|ipod|ipad|android)/);
          if(event.altKey==false && event.shiftKey==false){
            // console.log("ok");
            $("#wrapper-product").animate({
              scrollTop: 0,
            }, 200);
            $("#wrapper-product")[0].scrollTo(0, 0);
            setTimeout(function() {
              noresultstoadd=false;
              currentPageNumber = 1;
              currentCategory = "";
              traiterSendMsg();
              traiterExecuteSearch();
            }, 200);
          }else if(agentID!=null){
            noresultstoadd=false;
            $("#wrapper-product").animate({
              scrollTop: $('#results').offset().top,
            }, 200);
            $("#wrapper-product")[0].scrollTo(0, 0);
            currentPageNumber = 1;
            currentCategory = "";
            traiterSendMsg();
            traiterExecuteSearch();
          }
      }
  
    });
  
    // $("input").click(function (){
    //     socketCloseListener();
    // });
  
    setInterval(() => haveclicked=false, 4000);
  
    setInterval(() => socket.send(JSON.stringify({ event: "ping" })), 20000);
  
      $(".modal-backdrop").click(function() {
        //console.log("close popup");
        $('#searchmodal').modal('hide');
        $('#searchmodal').hide();
      });
  
      /* $('.search_product', $('#results')).each(function () {
        console.log($(this).data("product"));
      }); */
  
      $("#min-price").on("change paste keyup", function() {
        minprice=$("#min-price").val();
        //console.log(minprice);
        $("#wrapper-product").animate({
              scrollTop: 0,
            }, 200);
        $("#wrapper-product")[0].scrollTo(0, 0);
        noresultstoadd=false;
        currentPageNumber = 1;
        currentCategory="";
        traiterSendMsg();
      });
      $("#max-price").on("change paste keyup", function() {
        maxprice=$("#max-price").val();
        $("#wrapper-product").animate({
              scrollTop: 0,
            }, 200);
        $("#wrapper-product")[0].scrollTo(0, 0);
        noresultstoadd=false;
        currentPageNumber = 1;
        currentCategory="";
        traiterSendMsg();
      });
  
       /*$('.search_product', $('#results')).each(function () {
         if($("#min-price").val()>$(this).data("product").price){
            // console.log($(this).data("product"));
            $(this).remove();
          } 
        });*/
  
      $("#searchmodal").on("hidden.bs.modal", function () {
        $("#search-widget-input").val($("#inputsearchcotrac").val());
      });
  
  
    $("#search-widget-input").keyup(function() {
      //console.log(event);
      val = $("#search-widget-input").val();
      //console.log(val);
      //console.log(val.length);
      if (val != "") {
        if (val.length >= 2) {
          //console.log($("#search-widget-input"));
          $("#inputsearchcotrac").val(val);
          $('#searchmodal').modal({
            backdrop: true,
            keyboard: true,
            show: true
          });
          $('#myModal').modal('show')
          $(".modal-backdrop").click(function() {
            //console.log("close popup");
            $('#searchmodal').modal('hide');
            $('#searchmodal').hide();
          });
          var input = document.getElementById('inputsearchcotrac');
          const end = input.value.length;
          input.setSelectionRange(end, end);
          input.focus();
          $("#wrapper-product").animate({
            scrollTop: 0,
          }, 200);
          $("#wrapper-product")[0].scrollTo(0, 0);
          noresultstoadd=false;
          currentPageNumber = 1;
          currentCategory = "";
          traiterSendMsg();
        }
      }
    })
  
      $("#search-widget-input").click(function() {
        //console.log(event);
        val = $("#search-widget-input").val();
        //console.log(val);
        //console.log(val.length);
        if (val != "") {
          if (val.length >= 2) {
            $("#inputsearchcotrac").val(val);
            $('#searchmodal').modal({
              backdrop: true,
              keyboard: true,
              show: true
            });
            $(".modal-backdrop").click(function() {
              //console.log("close popup");
              $('#searchmodal').modal('hide');
              $('#searchmodal').hide();
            });
            var input = document.getElementById('inputsearchcotrac');
            const end = input.value.length;
            input.setSelectionRange(end, end);
            input.focus();
            $("#wrapper-product").animate({
              scrollTop: 0,
            }, 200);
            $("#wrapper-product")[0].scrollTo(0, 0);
            noresultstoadd=false;
            currentPageNumber = 1;
            currentCategory = "";
            traiterSendMsg();
          }
        }
      })
  
});