<?php

class CludoSettingsSection {
	public string $section_name;
	public array $fields;
	public CludoSettings $settings;
	public CludoApi $api;

	public function __construct(string $section_name, Cludo_Wordpress_Admin $admin) {
		$this->section_name = $section_name;
		$this->fields = [];

		$this->settings = $admin->settings;
		$this->api = $admin->api;
	}

	private function addFieldsToSectionsArray( int $priority ){
		add_filter( 'cludo_settings_sections', function( $sections ){
			$this_sections = [];
			$this_sections[$this->section_name] = $this->fields;

			$sections = array_merge($sections, $this_sections);

			return $sections;
		}, $priority );
	}

	public function addFields($fields, bool $requiresApi = false) : bool {
		if($requiresApi && !$this->api->active){
			return false;
		}

		if(is_callable($fields)){
			$fields = $fields($this);
		}

		if(is_array($fields)){
			$this->fields = array_merge($this->fields, $fields);
			return true;
		}
		else {
			return false;
		}
	}

	public function addField(string $name, $data, bool $requiresApi = false) : bool {
		if($requiresApi && !$this->api->active){
			return false;
		}

		if(is_callable($data)){
			$field = $data($this);
			if(is_array($field)){
				$this->fields[$name] = $field;
			}
		}
		else if(is_array($data)){
			$this->fields[$name] = $data;
		}
		else {
			return false;
		}

		return true;
	}

	public function addSection( bool $requiresApi = false, int $priority = 10 ){
		if($requiresApi && !$this->api->active){
			return false;
		}

		$this->addFieldsToSectionsArray( $priority );
	}
}