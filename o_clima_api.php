<?php

	if(!defined('_PS_VERSION_')) {
		exit;
	}	

	
	if( ! defined('ACTIVE_MODULE_FD') ){
		include_once 'classModuleFD.php';
	}
	
	
	class o_clima_api extends ModuleFD{

		public function __construct(){
			$this->name = 'o_clima_api';
			$this->tab = 'front_office_features';
			$this->version = '1.0.0';
			$this->author = 'Octavio Martinez';
			$this->need_instance = 0;
			$this->ps_versions_compliancy = [
				'min' => '1.6',
				'max' => _PS_VERSION_
			];
			$this->bootstrap = true;
			parent::__construct();
			
			$this->displayName = 'Clima API';
			$this->description = 'Consume apis de Climas';
			$this->confirmUninstall = 'Are you sure you want to Uninstall?';

			if(!Configuration::get('MYMODULE_NAME')) {
				$this->warning = 'No name provided';
			}

			$this->hooks[] = array('displayHeader','1');
			$this->hooks[] = array('displayBanner','1');

		}
		

		/******************
		**** INSTALL ******
		*******************/

		public function install(){
			//$this->installTab();
			
			if(Shop::isFeatureActive()) {
				Shop::setContext(Shop::CONTEXT_ALL);
			}
			return parent::install() && $this->addHooks($this->hooks);
		}

		public function uninstall(){
			//$this->uninstallTab();
			return parent::uninstall() && $this->addHooks($this->hooks, false);
		}

		/****************
		**** HOOKS ******
		*****************/
		
		public function hookDisplayBanner(){
			$reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_._PS_GEOIP_CITY_FILE_);
			$record = $reader->city(Tools::getRemoteAddr());
			$lat = $record->location->latitude;
			$lon = $record->location->longitude;
			$appid = Configuration::get('appid','');//eabc1f88c31ce0171102d8f3a3fc69da
			$units = Configuration::get('units'); //metric
			$url = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=$appid&units=$units";
			//consumo con curl
			
			$ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
			switch ($units) {
				case 'standard': $units = array('temp' => 'K', 'speed' => 'meter/sec'); break;
				case 'metric': $units = array('temp' => 'ºC', 'speed' => 'meter/sec'); break;
				case 'imperial': $units = array('temp' => 'ºF', 'speed' => 'miles/hour'); break;
			}

			$this->context->smarty->assign([
				'result' => json_decode( $result ),
				'units' => $units,
			]);
			return $this->display(__FILE__,'views/templates/hook/clima.tpl');
		}
		public function hookDisplayHeader(){
			$this->context->controller->registerStylesheet('module-o-clima-api-style',
	            'modules/'.$this->name.'/css/card-clima.css',
	            [
	              'media' => 'all',
	              'priority' => 200
	            ]
	        );
			if( Configuration::get('enable_js') == 1){
		        $this->context->controller->registerJavascript('module-o-clima-api-style',
		            'modules/'.$this->name.'/js/position.js',
		            [	
		              'media' => 'all',
		              'priority' => 200
		            ]
		        );
			}
		}

		/************************
		**** CONFIGURATION ******
		************************/

		public function getContent(){
			$output = null;
			if(Tools::isSubmit('submit')) {
				Configuration::updateValue('appid', Tools::getValue('appid'));
				Configuration::updateValue('units', Tools::getValue('units'));
				Configuration::updateValue('enable_js', Tools::getValue('enable_js'));
				$output .= $this->displayConfirmation('Se actualizo la Configuracion del Modulo');
			}
			return $output.$this->displayForm();
		}

		public function displayForm(){
			$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
			
			$inputs = array(
				array(
					'type' => 'text',
					'label' => $this->l('APP ID'),
					'name' => 'appid',
					'desc' => $this->l('API ID for openweathermap')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Units'),
					'name' => 'units',
					'options' => array(
						'query' => array(
							array( 'id' => 'standard', 'name' => 'Standard'),
							array( 'id' => 'metric', 'name' => 'Metric'),
							array( 'id' => 'imperial', 'name' => 'Imperial'),
						),
						'id' => 'id',
						'name' => 'name'

					),
					'desc' => $this->l('Metric system')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Mejorar precision con Javascript'),
					'name' => 'enable_js',
					'options' => array(
						'query' => array(
							array( 'id' => 0, 'name' => 'No Usar'),
							array( 'id' => 1, 'name' => 'Usar Javascript'),
						),
						'id' => 'id',
						'name' => 'name'

					),
					'desc' => $this->l('Metric system')
				),

			);

			$fields_form = array(
				'form' => array(
		            'legend' => array(
						'title' => 'Titulo',
						'icon' => 'icon-cogs'
		            ),
		            'input' => $inputs, 
		            'submit' => array(
		                'name' => 'submit',
		                'title' => $this->trans('Save', array(), 'Admin.Actions')
		            ),
		        ),
        	);

        	$helper = new HelperForm();
	        $helper->module = $this;
	        $helper->table = $this->name;
	        $helper->token = Tools::getAdminTokenLite('AdminModules');
	        $helper->currentIndex = $this->getModuleConfigurationPageLink();
	        
	        $helper->default_form_language = $lang->id;
	        
	        $helper->title = $this->displayName;
	        $helper->show_toolbar = false;
	        $helper->toolbar_scroll = false;
	        
	        $helper->submit_action = 'submit';
	        

			$helper->identifier = $this->identifier;


	        $helper->tpl_vars = array(
	            'languages' => $this->context->controller->getLanguages(),
	            'id_language' => $this->context->language->id,    
	            'fields_value' => array( 
	            	'appid' => Configuration::get('appid',''),
	            	'units' => Configuration::get('units','standard'),
	            	'enable_js' => Configuration::get('enable_js'),
	            )
	        );

	        return $helper->generateForm(array($fields_form));
		}


		/**************
		**** TABS ******
		***************/
		private function installTab(){
			return true;
			/*
			$response = true;

			$subTab = new Tab();
			$subTab->active = 1;
			$subTab->name = array();
			$subTab->class_name = 'OscLinkTab';
			$subTab->icon = 'menu';
			foreach (Language::getLanguages() as $lang) {
				$subTab->name[$lang['id_lang']] = 'Subcategories Cards';
			}

			$subTab->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');
			$subTab->module = $this->name;
			$response &= $subTab->add();

			return $response;*/
		}

		private function uninstallTab(){
			return true;
			/*$response = true;
			$tab_id = (int)Tab::getIdFromClassName('OscLinkTab');
			if(!$tab_id){
				return true;
			}

			$tab = new Tab($tab_id);
			$response &= $tab->delete();
			return $response;*/
		}
	}
		