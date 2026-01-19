<?php

class CludoSettingsField {
	private $settings;
	private $value;
	private $section;
	private $key;
	private $field_data;
	private $option_name;

	function __construct(string $section, string $key, $field_data = [], $settings = false){
		$this->option_name = CLUDO_WP_PLUGIN_NAME;

		$this->section = $section;
		$this->key = $key;

		if($settings === false){
			$this->settings = new CludoSettings();
		}
		else {
			$this->settings = $settings;
		}

		$this->field_data = array_merge( apply_filters( 'cludo_settings_input_default_args', [
			'title'         => '',
			'type'          => 'text',
			'required'      => false,
			'desc'          => '',
			'default'       => '',
			'multiple'      => false,
			'options'       => [],
			'no_table_wrap' => false,
		] ), $field_data );

		$this->value = $this->getValue();
	}

	public function getValue() {
		$defaultValue = ! empty( $this->field_data['default'] ) ? $this->field_data['default'] : '';

		return $this->settings->get($this->section, $this->key, $defaultValue);
	}

	private function getAttr(){
		$attr_multiple = $this->field_data['multiple'] ? '[]' : '';
		$attr          = "id='{$this->key}' name='{$this->option_name}[{$this->section}][{$this->key}]{$attr_multiple}' class='widefat'";

		return $attr;
	}

	public function textarea(){
		$attr = $this->getAttr();
		$value = $this->value;

		return "<textarea $attr>$value</textarea>";
	}

	public function checkbox(){
		$attr = $this->getAttr();
		$value = $this->value;
		$checked = checked( 'on', $value, false );

		return "<input type='checkbox' $attr $checked>";
	}

	public function radio($value){
		$attr = $this->getAttr();
		$val = esc_attr($value);

		$checked = !empty($value) && $value == $this->value ? 'checked' : '';

		return "<input type='radio' $attr value='$val' $checked>";
	}

	public function select(){
		$attr = $this->getAttr();
		$value = $this->value;

		$options  = '';
		$multiple = $this->field_data['multiple'] ? 'multiple="true"' : '';
		foreach ( $this->field_data['options'] as $option_key => $option_value ) {
			if ( $multiple && is_array( $value ) ) {
				$options .= "<option " . ( in_array( $option_key, $value ) ? 'selected' : '' ) . " value='{$option_key}'>{$option_value}</option>";
			} else {
				$options .= "<option " . selected( $value, $option_key, false ) . " value='{$option_key}'>{$option_value}</option>";
			}
		}
		return "<select type='checkbox' $attr $multiple>{$options}</select>";
	}

	public function title(){
		return "<h2 class='cludo__settings-field-title'>{$this->field_data['title']}</h2>";
	}

	public function html(){
		return "<div class='cludo__settings-field-html' data-key='{$this->key}'>{$this->field_data['default']}</div>";
	}

	public function description(){
		return "<p class='cludo__settings-field-description'>{$this->field_data['default']}</p>";
	}

	public function input(){
		$attr = $this->getAttr();
		$value = $this->value;

		return "<input type='{$this->field_data['type']}' $attr value='$value'>";
	}

	public function render(){
		// Create label.
		$required = '';
		if ( true === $this->field_data['required'] ) {
			$required = '<span style="color:red">*</span>';
		}

		$label = "<label class='{$this->field_data['type']}' for='{$this->key}'>{$this->field_data['title']} $required</label>";

		switch ( $this->field_data['type'] ) {
			case 'textarea' :
				$field = $this->textarea();
				break;
			case 'checkbox' :
				$field = $this->checkbox();
				break;
			case 'select' :
				$field = $this->select();
				break;
			case 'title' :
				$field = $this->title();
				break;
			case 'html' :
				$field = $this->html();
				break;
			case 'description' :
				$field = $this->description();
				break;
			default :
				$field = $this->input();
				break;
		}

		// Create description.
		if ( ! empty( $this->field_data['desc'] ) ) {
			$field .= "<span class='cludo__input_desc'>{$this->field_data['desc']}</span>";
		}

		$table_pre  = "<table class='form-table {$this->field_data['type']} {$this->key}'><tbody><tr><th scope='row'>{$label}</th><td class='form-table__input'>";
		$table_post = "</td></tr></tbody></table>";

		if($this->field_data['no_table_wrap']){
			$table_pre  = '';
			$table_post = '';
		}

		return "{$table_pre}<div>{$field}</div>{$table_post}";
	}
}