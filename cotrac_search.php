<?php
/**
*Module Name : Cotrac Search
*Author : Florentin Ledy
*Mail : florentin.ledy@ig2i.centralelille.fr
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Cotrac_search extends Module
{
    private $templateFile;

	public function __construct()
    {
        $this->need_instance = 0;
        $this->name = 'cotrac_search';
        $this->author = 'Cotrac';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->displayName = 'Module de recherche';
        $this->description = 'Module de recherche';

        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->bootstrap = true;

        $this->templateFile = 'module:cotrac_search/search_header.tpl';

        parent::__construct();
    }

    public function install()
    {

        if (Shop::isFeatureActive())
        {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        
        if (!parent::install())
        {
            return false;
        }
        
        Configuration::updateValue('COTRAC_SEARCH_LIVE_MODE', true);
        Configuration::updateValue('COTRAC_SEARCH_TOKEN', null);
        Configuration::updateValue('COTRAC_SEARCH_TOKEN_PUBLIC', null); 

        $this->registerHook('displayNav1');
        $this->registerHook('displayHeader');
        $this->registerHook('actionProductUpdate');
        $this->registerHook('actionProductDelete');
        $this->registerHook('actionProductSave');

        return true;
        
    }

    public function uninstall()
    {
        if (Shop::isFeatureActive())
        {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::deleteByName('COTRAC_SEARCH_LIVE_MODE');
        Configuration::deleteByName('COTRAC_SEARCH_TOKEN');
        Configuration::deleteByName('COTRAC_SEARCH_TOKEN_PUBLIC');
        $this->unregisterHook('displayNav1');
        $this->unregisterHook('displayHeader');
        $this->unregisterHook('actionProductUpdate');
        $this->unregisterHook('actionProductDelete');
        $this->unregisterHook('actionProductSave');
        if (!parent::uninstall())
        {
            return false;
        }
  
        return true;
    }
    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitCotrac_searchModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $moduleUrl = Tools::getProtocol(Tools::usingSecureMode()) . $_SERVER['HTTP_HOST'] . $this->getPathUri();

        $nbproduct=Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'product WHERE `active`=1');
        $nblang=Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'lang WHERE `active`=1');

        function callAPIGET($token, $public, $url, $websiteurlheader){
            $curl = curl_init();
            // OPTIONS:
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            //     'Content-Type: application/json',
            //     'Authorization: Bearer ' . $token
            // ));     
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Api-token: $token",
                "Public-token: $public",
                "Website-Url: $websiteurlheader",
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

        $token=Configuration::get('COTRAC_SEARCH_TOKEN');
        $public=Configuration::get('COTRAC_SEARCH_TOKEN_PUBLIC');
        $websiteurl = Tools::getProtocol(Tools::usingSecureMode()) . $_SERVER['HTTP_HOST'];
        $result=json_decode(callAPIGET($token,$public,"https://search.cotrac.fr/api/checkkey",$websiteurl));
        // return ('<pre>'.print_r($result).'</pre>');

        $this->context->smarty->assign(
            array(
                'products_index_url' => $moduleUrl . 'cotrac_search-products-indexer.php' . '?token=' . substr(Tools::encrypt('cotrac_search/index'), 0, 10),
                'nb_product' => $nbproduct,
                'public_token' => $public,
                'nb_lang' => $nblang,
                'success' => $result->success,
                'messagefromapi' => $result->message,
            )
        );

        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Fonction par défaut du renderform : 
     * Crée le formulaire qui s'affichera dans la configuration du module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCotrac_searchModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');


        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Création de la structure du formulaire
     */
    protected function getConfigForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activer le module'),
                        'name' => 'COTRAC_SEARCH_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Activer/Desactiver le fonctionnement du module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Activé')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Désactivé')
                            )
                        ),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'desc' => $this->l('Veuillez indiquer le token privé pour accéder à l\'API'),
                        'name' => 'COTRAC_SEARCH_TOKEN',
                        'label' => $this->l('API Private Token'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'desc' => $this->l('Veuillez indiquer le token public pour accéder à l\'API'),
                        'name' => 'COTRAC_SEARCH_TOKEN_PUBLIC',
                        'label' => $this->l('API Public Token'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit_form'
                ),
            ),
        );
        return $fields_form;
    }

     /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'COTRAC_SEARCH_LIVE_MODE' => Configuration::get('COTRAC_SEARCH_LIVE_MODE', true),
            'COTRAC_SEARCH_TOKEN' => Configuration::get('COTRAC_SEARCH_TOKEN', null),
            'COTRAC_SEARCH_TOKEN_PUBLIC' => Configuration::get('COTRAC_SEARCH_TOKEN_PUBLIC', true),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('submit_form')){

            $form_values = $this->getConfigFormValues();

            foreach (array_keys($form_values) as $key) {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    public function getWidgetVariables()
    {

        return array(
            'public_token' => Configuration::get('COTRAC_SEARCH_TOKEN_PUBLIC'),
            'search_enginelink' => "layer1-search.cotrac.fr",
        );
    }

    public function callAPIDEL($token, $url){
        $curl = curl_init();
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
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
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

    public function callAPI($token,$method, $url, $data){
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

    
    //affichage de la bannière
    public function hookdisplayNav1()
    {
        if(Configuration::get('COTRAC_SEARCH_LIVE_MODE')==true)
        {
            if (!$this->isCached($this->templateFile, $this->getCacheId('cotrac_search'))) {
                $this->smarty->assign($this->getWidgetVariables());
            }
            return $this->fetch($this->templateFile, $this->getCacheId('cotrac_search'));
        }
    }

    public function hookdisplayHeader(){
        if(Configuration::get('COTRAC_SEARCH_LIVE_MODE')==true)
        {
            // $this->$smarty->assign('message', 'hello');
            global $smarty;
            $this->context->controller->addCSS($this->_path.'views/css/style.css', 'all');
            $smarty->assign($this->getWidgetVariables()); 
            $this->context->controller->registerJavascript('jquery.min.js', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', ['position' => 'head', 'priority' => 0, 'server' => 'remote', 'attributes' => 'none']);
	        $this->context->controller->registerJavascript('front.js', $this->_path.'views/js/front.js' , ['position' => 'bottom', 'priority' => 50]);
            return $smarty->display(dirname(__FILE__).'/views/modal.tpl');
        }
    }

    public function hookactionProductDelete($params)
    {
        $token=Configuration::get('COTRAC_SEARCH_TOKEN');
        $this->callAPIDEL($token,"https://search.cotrac.fr/api/products?id_product=".$params["id_product"]); //si je veux tcheck le contenu au moment de la suppression il faut créer une erreur donc modifier le param id_product par un truc au piff
        // return ('<pre>'.print_r($result).'</pre>');
    }

    public function hookactionProductUpdate($params)
    {
        $language_list=Language::getLanguages(true, Context::getContext()->shop->id);
                
        foreach($language_list as $lang)
        {
            $idlang=$lang["id_lang"];
            $product=new Product($params["id_product"], false ,$idlang);
            if (Validate::isLoadedObject($product)) {
                if($product->active==true){
                    $img=Product::getCover($params["id_product"]);
                    if(!is_bool($img)){
                        $link = new Link;//because getImageLInk is not static function
                        $productinfo['image_link'] = 'https://'.$link->getImageLink($product->link_rewrite, $img["id_image"], 'home_default');
                    }else{
                        $productinfo['image_link']=NULL;
                    }
                
                    $productinfo['price']=Product::getPriceStatic($params["id_product"],true);
            
                    $productinfo['id_product']=$params["id_product"];
                    $productinfo['reference']=$product->reference;
                    $productinfo['supplier_reference']=$product->supplier_reference;
                    $productinfo['show_price']=$product->show_price;
                    $productinfo['date_upd']=$product->date_upd;
                    $link = new Link;
                    $productinfo['link']=$link->getProductLink($product,null,null,null,$idlang);
                    $productinfo['name']=$product->name;
                    $productinfo['description']=$product->description_short."\n".$product->description;
                    $productinfo['tags']="";
                    $productinfo['language_code']=$lang["language_code"];
                    if(isset(Tag::getProductTags($params["id_product"])[1]))
                    foreach(Tag::getProductTags($params["id_product"])[1] as $tag){
                        $productinfo['tags'].=$tag." ";
                    }
                    $token=Configuration::get('COTRAC_SEARCH_TOKEN');
                    $this->callAPI($token,"POST","https://search.cotrac.fr/api/products",json_encode($productinfo));
                }else{
                    $token=Configuration::get('COTRAC_SEARCH_TOKEN');
                    $this->callAPIDEL($token,"https://search.cotrac.fr/api/products?id_product=".$params["id_product"]);
                }
            }
        }
    }

    public function hookactionProductSave($params)
    {
        $language_list=Language::getLanguages(true, Context::getContext()->shop->id);
                
        foreach($language_list as $lang)
        {
            $idlang=$lang["id_lang"];
            $product=new Product($params["id_product"], false ,$idlang);
            if (Validate::isLoadedObject($product)) {
                if($product->active==true){
                    $img=Product::getCover($params["id_product"]);
                    if(!is_bool($img)){
                        $link = new Link;//because getImageLInk is not static function
                        $productinfo['image_link'] = 'https://'.$link->getImageLink($product->link_rewrite, $img["id_image"], 'home_default');
                    }else{
                        $productinfo['image_link']=NULL;
                    }
                
                    $productinfo['price']=Product::getPriceStatic($params["id_product"],true);
            
                    $productinfo['id_product']=$params["id_product"];
                    $productinfo['reference']=$product->reference;
                    $productinfo['supplier_reference']=$product->supplier_reference;
                    $productinfo['show_price']=$product->show_price;
                    $productinfo['date_upd']=$product->date_upd;
                    $link = new Link;
                    $productinfo['link']=$link->getProductLink($product,null,null,null,$idlang);
                    $productinfo['name']=$product->name;
                    //$productinfo['description']=$product->description_short."\n".$product->description;
                    $productinfo['tags']="";
                    $productinfo['language_code']=$lang["language_code"];
                    if(isset(Tag::getProductTags($params["id_product"])[1]))
                    foreach(Tag::getProductTags($params["id_product"])[1] as $tag){
                        $productinfo['tags'].=$tag." ";
                    }
                    $token=Configuration::get('COTRAC_SEARCH_TOKEN');
                    $this->callAPI($token,"POST","https://search.cotrac.fr/api/products",json_encode($productinfo));
                }else{
                    $token=Configuration::get('COTRAC_SEARCH_TOKEN');
                    $this->callAPIDEL($token,"https://search.cotrac.fr/api/products?id_product=".$params["id_product"]);
                }
            }
        }
    }

}
