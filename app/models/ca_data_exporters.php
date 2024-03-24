<?php
/** ---------------------------------------------------------------------
 * app/models/ca_data_exporters.php
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2012-2024 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * @package CollectiveAccess
 * @subpackage models
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */
require_once(__CA_LIB_DIR__.'/ModelSettings.php');
require_once(__CA_LIB_DIR__.'/Export/BaseExportFormat.php');
require_once(__CA_LIB_DIR__.'/ApplicationPluginManager.php');
require_once(__CA_LIB_DIR__.'/Logging/KLogger/KLogger.php');
require_once(__CA_APP_DIR__.'/helpers/configurationHelpers.php');

BaseModel::$s_ca_models_definitions['ca_data_exporters'] = array(
	'NAME_SINGULAR' 	=> _t('data exporter'),
	'NAME_PLURAL' 		=> _t('data exporters'),
	'FIELDS' 			=> array(
		'exporter_id' => array(
			'FIELD_TYPE' => FT_NUMBER, 'DISPLAY_TYPE' => DT_HIDDEN,
			'IDENTITY' => true, 'DISPLAY_WIDTH' => 10, 'DISPLAY_HEIGHT' => 1,
			'IS_NULL' => false,
			'DEFAULT' => '',
			'LABEL' => _t('CollectiveAccess id'), 'DESCRIPTION' => _t('Unique numeric identifier used by CollectiveAccess internally to identify this exporter')
		),
		'exporter_code' => array(
			'FIELD_TYPE' => FT_TEXT, 'DISPLAY_TYPE' => DT_FIELD,
			'DISPLAY_WIDTH' => 40, 'DISPLAY_HEIGHT' => 1,
			'IS_NULL' => true,
			'DEFAULT' => '',
			'LABEL' => _t('exporter code'), 'DESCRIPTION' => _t('Unique alphanumeric identifier for this exporter.'),
			'UNIQUE_WITHIN' => []
			//'REQUIRES' => array('is_administrator')
		),
		'table_num' => array(
			'FIELD_TYPE' => FT_NUMBER, 'DISPLAY_TYPE' => DT_HIDDEN,
			'DONT_USE_AS_BUNDLE' => true,
			'DISPLAY_WIDTH' => 40, 'DISPLAY_HEIGHT' => 1,
			'IS_NULL' => false,
			'DEFAULT' => '',
			'LABEL' => _t('exporter type'), 'DESCRIPTION' => _t('Indicates type of item exporter is used for.'),
			'BOUNDS_CHOICE_LIST' => []
		),
		'settings' => array(
			'FIELD_TYPE' => FT_VARS, 'DISPLAY_TYPE' => DT_OMIT,
			'DISPLAY_WIDTH' => 88, 'DISPLAY_HEIGHT' => 15,
			'IS_NULL' => false,
			'DEFAULT' => '',
			'LABEL' => _t('Settings'), 'DESCRIPTION' => _t('exporter settings')
		),
	)
);

class ca_data_exporters extends BundlableLabelableBaseModelWithAttributes {
	use ModelSettings {
		setSetting as traitSetSetting;
	}
	
	# ---------------------------------
	# --- Object attribute properties
	# ---------------------------------
	# Describe structure of content object's properties - eg. database fields and their
	# associated types, what modes are supported, et al.
	#

	# ------------------------------------------------------
	# --- Basic object parameters
	# ------------------------------------------------------
	# what table does this class represent?
	protected $TABLE = 'ca_data_exporters';

	# what is the primary key of the table?
	protected $PRIMARY_KEY = 'exporter_id';

	# ------------------------------------------------------
	# --- Properties used by standard editing scripts
	#
	# These class properties allow generic scripts to properly display
	# records from the table represented by this class
	#
	# ------------------------------------------------------

	# Array of fields to display in a listing of records from this table
	protected $LIST_FIELDS = array('exporter_id');

	# When the list of "list fields" above contains more than one field,
	# the LIST_DELIMITER text is displayed between fields as a delimiter.
	# This is typically a comma or space, but can be any string you like
	protected $LIST_DELIMITER = ' ';

	# What you'd call a single record from this table (eg. a "person")
	protected $NAME_SINGULAR;

	# What you'd call more than one record from this table (eg. "people")
	protected $NAME_PLURAL;

	# List of fields to sort listing of records by; you can use
	# SQL 'ASC' and 'DESC' here if you like.
	protected $ORDER_BY = array('exporter_id');

	# If you want to order records arbitrarily, add a numeric field to the table and place
	# its name here. The generic list scripts can then use it to order table records.
	protected $RANK = '';

	# ------------------------------------------------------
	# Hierarchical table properties
	# ------------------------------------------------------
	protected $HIERARCHY_TYPE				=	null;
	protected $HIERARCHY_LEFT_INDEX_FLD 	= 	null;
	protected $HIERARCHY_RIGHT_INDEX_FLD 	= 	null;
	protected $HIERARCHY_PARENT_ID_FLD		=	null;
	protected $HIERARCHY_DEFINITION_TABLE	=	null;
	protected $HIERARCHY_ID_FLD				=	null;
	protected $HIERARCHY_POLY_TABLE			=	null;

	# ------------------------------------------------------
	# Change logging
	# ------------------------------------------------------
	protected $UNIT_ID_FIELD = null;
	protected $LOG_CHANGES_TO_SELF = true;
	protected $LOG_CHANGES_USING_AS_SUBJECT = array(
		"FOREIGN_KEYS" => array(

		),
		"RELATED_TABLES" => array(

		)
	);

	# ------------------------------------------------------
	# Labeling
	# ------------------------------------------------------
	protected $LABEL_TABLE_NAME = 'ca_data_exporter_labels';

	# ------------------------------------------------------
	# ID numbering
	# ------------------------------------------------------
	protected $ID_NUMBERING_ID_FIELD = 'exporter_code';	// name of field containing user-defined identifier
	protected $ID_NUMBERING_SORT_FIELD = null;			// name of field containing version of identifier for sorting (is normalized with padding to sort numbers properly)
	protected $ID_NUMBERING_CONTEXT_FIELD = null;		// name of field to use value of for "context" when checking for duplicate identifier values; if not set identifer is assumed to be global in scope; if set identifer is checked for uniqueness (if required) within the value of this field


	# ------------------------------------------------------
	# $FIELDS contains information about each field in the table. The order in which the fields
	# are listed here is the order in which they will be returned using getFields()

	protected $FIELDS;

	/**
	 * Caches
	 */
	public static $s_exporter_cache = [];
	public static $s_exporter_item_cache = [];
	public static $s_mapping_check_cache = [];
	public static $s_instance_cache = [];
	public static $s_variables = [];

	protected $opo_app_plugin_manager;
	# ------------------------------------------------------
	/**
	 *
	 */
	public function __construct($id=null, ?array $options=null) {
		$this->opo_app_plugin_manager = new ApplicationPluginManager();
		BaseModel::$s_ca_models_definitions['ca_data_exporters']['FIELDS']['table_num']['BOUNDS_CHOICE_LIST'] = array_flip(caGetPrimaryTables(true));
		global $_ca_data_exporters_settings;
		parent::__construct($id, $options);

		// settings
		$this->initSettings();
	}
	# ------------------------------------------------------
	/**
	 *
	 */
	protected function initSettings() {
		$va_settings = [];

		$va_settings['exporter_format'] = array(
			'formatType' => FT_TEXT,
			'displayType' => DT_SELECT,
			'width' => 40, 'height' => 1,
			'takesLocale' => false,
			'default' => '',
			'options' => $this->getAvailableExporterFormats(),
			'label' => _t('Exporter format'),
			'description' => _t('Set exporter type, i.e. the format of the exported data. Currently supported: XML, MARC21 and CSV.')
		);

		$va_settings['wrap_before'] = array(
			'formatType' => FT_TEXT,
			'displayType' => DT_FIELD,
			'width' => 70, 'height' => 6,
			'takesLocale' => false,
			'default' => '',
			'label' => _t('Wrapping text before export'),
			'description' => _t('If this exporter is used for an item set export (as opposed to a single item), the text set here will be inserted before the first item. This can for instance be used to wrap a repeating set of XML elements in a single global element. The text has to be valid for the current exporter format.')
		);

		$va_settings['wrap_after'] = array(
			'formatType' => FT_TEXT,
			'displayType' => DT_FIELD,
			'width' => 70, 'height' => 6,
			'takesLocale' => false,
			'default' => '',
			'label' => _t('Wrapping text after export'),
			'description' => _t('If this exporter is used for an item set export (as opposed to a single item), the text set here will be inserted after the last item. This can for instance be used to wrap a repeating set of XML elements in a single global element. The text has to be valid for the current exporter format.')
		);

		$va_settings['wrap_before_record'] = array(
			'formatType' => FT_TEXT,
			'displayType' => DT_FIELD,
			'width' => 70, 'height' => 6,
			'takesLocale' => false,
			'default' => '',
			'label' => _t('Wrapping text before record export'),
			'description' => _t('The text set here will be inserted before each record-level export.')
		);

		$va_settings['wrap_after_record'] = array(
			'formatType' => FT_TEXT,
			'displayType' => DT_FIELD,
			'width' => 70, 'height' => 6,
			'takesLocale' => false,
			'default' => '',
			'label' => _t('Wrapping text after record export'),
			'description' => _t('The text set here will be inserted after each record-level export.')
		);

		$va_settings['typeRestrictions'] = array(
			'formatType' => FT_TEXT,
			'displayType' => DT_FIELD,
			'width' => 70, 'height' => 6,
			'takesLocale' => false,
			'default' => '',
			'label' => _t('Type restrictions'),
			'description' => _t('If set, this mapping will only be available for these types. Multiple types are separated by commas or semicolons.')
		);

		$va_settings['locale'] = array(
			'formatType' => FT_TEXT,
			'displayType' => DT_FIELD,
			'width' => 40, 'height' => 1,
			'takesLocale' => false,
			'default' => '',
			'label' => _t('Locale'),
			'description' => _t('Locale code to use to get the field values when mapping-specific locale is not set. If not set, the system/user default is used.')
		);

		// if exporter_format is set, pull in format-specific settings
		if($vs_format = $this->getSetting('exporter_format')) {
			if (is_array($va_format_settings = ca_data_exporters::getFormatSettings($vs_format))) {
				$va_settings += $va_format_settings;
			}
		}
		
		$this->setAvailableSettings($va_settings);
	}
	# ------------------------------------------------------
	/**
	 * Clear all exporter in-memory caches
	 */
	public static function clearCaches() : void {
		self::$s_exporter_cache = [];
		self::$s_exporter_item_cache = [];
		self::$s_mapping_check_cache = [];
		self::$s_instance_cache = [];
		self::$s_variables = [];
	}
	# ------------------------------------------------------
	/**
	 *	Return list of exporter format codes. 
	 */
	public function getAvailableExporterFormats() : array {
		return [
			'XML' => 'XML',
			'MARC' => 'MARC',
			'CSV' => 'CSV',
			'JSON' => 'JSON',
			'CTDA' => 'CTDA'
		];
	}
	# ------------------------------------------------------
	/**
	 * Return settings list for format
	 *
	 * @param string $format Format code
	 *
	 * @return array Array of settings. Empty array if format is invalid.
	 */
	static public function getFormatSettings(string $format) : array {
		return (isset(BaseExportFormat::$s_format_settings[$format]) ? BaseExportFormat::$s_format_settings[$format] : []);
	}
	# ------------------------------------------------------
	/**
	 * Override BaseModel::set() to prevent setting of table_num field for existing records
	 *
	 * @param array|string $fields Array of fields and values to set, or name of field to set
	 * @param string Value to set field, when $fields is a string.
	 * @param array $options. Options passed through to BundlableLabelableBaseModelWithAttributes::set()
	 *
	 * @return bool
	 */
	public function set($fields, $value="", $options=null) {
		if ($this->getPrimaryKey()) {
			if(!is_array($fields))  { $fields = [$fields => $value]; }
			$fields_proc = [];
			foreach($fields as $field => $fvalue) {
				if (!in_array($ffield, ['table_num'])) {
					$fields_proc[$field] = $fvalue;
				}
			}
			if (!sizeof($fields_proc)) { $fields_proc = null; }
			$rc = parent::set($fields_proc, null, $options);

			$this->initSettings();
			return $rc;
		}

		$rc = parent::set($fields, $value, $options);

		$this->initSettings();
		return $rc;
	}
	# ------------------------------------------------------
	/**
	 * Override BaseModel::delete() because the cascading delete implemented there
	 * doesn't properly remove related items if they're organized in a ad-hoc hierarchy.
	 */
	public function delete($pb_delete_related=false, $pa_options=null, $pa_fields=null, $pa_table_list=null) {
		if($pb_delete_related) {
			$this->removeAllItems();
		}

		return parent::delete($pb_delete_related, $pa_options, $pa_fields, $pa_table_list);
	}
	# ------------------------------------------------------
	/**
	 * Get all exporter items items for this exporter
	 * @param array $pa_options available options are:
	 *                          onlyTopLevel
	 *                          includeSettingsForm
	 *                          id_prefix
	 *                          orderForDeleteCascade
	 *							dontIncludeVariables
	 * @return array array of items, keyed by their primary key
	 */
	public function getItems($pa_options=[]) {
		if (!($vn_exporter_id = $this->getPrimaryKey())) { return null; }

		$vb_include_settings_form = isset($pa_options['includeSettingsForm']) ? (bool)$pa_options['includeSettingsForm'] : false;
		$vb_only_top_level = isset($pa_options['onlyTopLevel']) ? (bool)$pa_options['onlyTopLevel'] : false;
		$vb_order_for_delete_cascade = isset($pa_options['orderForDeleteCascade']) ? (bool)$pa_options['orderForDeleteCascade'] : false;
		$vb_dont_include_vars = caGetOption('dontIncludeVariables',$pa_options);

		$vs_id_prefix = isset($pa_options['id_prefix']) ? $pa_options['id_prefix'] : '';

		$vo_db = $this->getDb();

		$va_conditions = [];
		if($vb_only_top_level) {
			$va_conditions[] = "AND parent_id IS NULL";
		}

		if($vb_dont_include_vars) {
			$va_conditions[] = "AND element not like \"_VARIABLE_:%\"";
		}

		if($vb_order_for_delete_cascade) {
			$vs_order = "parent_id DESC";
		} else {
			$vs_order = "`rank` ASC";
		}

		$qr_items = $vo_db->query("
			SELECT * FROM ca_data_exporter_items
			WHERE exporter_id = ?
			" . join(" ",$va_conditions) . "
			ORDER BY ".$vs_order."
		",$vn_exporter_id);

		$va_items = [];
		while($qr_items->nextRow()) {
			$va_items[$vn_item_id = $qr_items->get('item_id')] = $qr_items->getRow();

			if ($vb_include_settings_form) {
				$t_item = new ca_data_exporter_items($vn_item_id);
				$va_items[$vn_item_id]['settings'] = $t_item->getHTMLSettingForm(array('settings' => $t_item->getSettings(), 'id' => "{$vs_id_prefix}_setting_{$vn_item_id}"));
			}
		}

		return $va_items;
	}
	# ------------------------------------------------------
	/**
	 * Return top level options from loaded importer
	 *
	 * @param array $options Options passed through to ca_data_exporters::getItems()
	 *
	 * @return array 
	 */
	public function getTopLevelItems(?array $options=null) : array {
		if(!is_array($options)) { $options = []; }
		return $this->getItems(array_merge(['onlyTopLevel' => true],$options));
	}
	# ------------------------------------------------------
	/**
	 * Add new exporter item to this exporter.
	 * @param int $parent_id parent id for the new record. can be null
	 * @param string $element name of the target element
	 * @param string $source value for 'source' field. this will typicall be a bundle name
	 * @param array $settings array of user settings
	 * @return ca_data_exporter_items BaseModel representation of the new record
	 */
	public function addItem($parent_id=null, $element, $source, $settings=[]) {
		if (!($exporter_id = $this->getPrimaryKey())) { return null; }

		$t_item = new ca_data_exporter_items();
		$t_item->set('parent_id',$parent_id);
		$t_item->set('exporter_id',$exporter_id);
		$t_item->set('element',$element);
		$t_item->set('source',$source);

		foreach($settings as $key => $value) {
			$t_item->setSetting($key, $value);
		}

		$t_item->insert();

		if ($t_item->numErrors()) {
			$this->errors = array_merge($this->errors, $t_item->errors);
			return false;
		}
		return $t_item;
	}
	# ------------------------------------------------------
	/**
	 * Remove item from this exporter and delete
	 *
	 * @param int $item_id primary key of the item to remove
	 *
	 * @return bool True on success.
	 */
	public function removeItem(int $item_id) : bool {
		if (!($exporter_id = $this->getPrimaryKey())) { return null; }

		$t_item = new ca_data_exporter_items($item_id);
		if ($t_item->getPrimaryKey() && ($t_item->get('exporter_id') == $exporter_id)) {
			$t_item->delete(true);

			if ($t_item->numErrors()) {
				$this->errors = array_merge($this->errors, $t_item->errors);
				return false;
			}
			return true;
		}
		return false;
	}
	# ------------------------------------------------------
	/**
	 * Remove all items from this exporter
	 * @return boolean success state
	 */
	public function removeAllItems() : ?bool {
		if (!($vn_exporter_id = $this->getPrimaryKey())) { return null; }

		$va_items = $this->getItems(array('orderForDeleteCascade' => true));
		$t_item = new ca_data_exporter_items();

		foreach($va_items as $vn_item_id => $va_item) {
			$t_item->load($vn_item_id);
			$t_item->delete(true);

			if ($t_item->numErrors()) {
				$this->errors = array_merge($this->errors, $t_item->errors);
				return false;
			}
		}
		return true;
	}
	# ------------------------------------------------------
	/**
	 * Check if exporter with code (and optionally table name/num exists
	 *
	 * @param string $exporter_code 
	 * @param mixed $table Optional numeric table number or name
	 * @param array $options No options are currently supported.
	 *
	 * @return bool
	 */
	static public function exporterExists(string $exporter_code, ?string $table=null, ?array $options=null) {
		$d = ['exporter_code' => $exporter_code];
		if (!is_null($table)) { $d['table_num'] = Datamodel::getTableName($table); }
		return (self::find($d, ['returnAs' => 'count']) > 0);
	}
	# ------------------------------------------------------
	/**
	 * Return list of available data exporters
	 *
	 * @param int $pn_table_num
	 * @param array $pa_options
	 *		countOnly = return number of exporters available rather than a list of exporters
	 *
	 * @return mixed List of exporters, or integer count of exporters if countOnly option is set
	 */
	static public function getExporters($pn_table_num=null, $pa_options=null) {
		if($ps_type_code = caGetOption('recordType', $pa_options)) {
			if(is_numeric($ps_type_code)) {
				$ps_type_code = caGetListItemIdno($ps_type_code);
			}
		}

		$o_db = new Db();

		$t_exporter = new ca_data_exporters();

		$va_sql_params = $va_sql_wheres = [];
		if((int)$pn_table_num) {
			$va_sql_wheres[] = "(de.table_num = ?)";
			$va_sql_params[] = (int)$pn_table_num;
		}


		$vs_sql_wheres = sizeof($va_sql_wheres) ? " WHERE ".join(" AND ", $va_sql_wheres) : "";

		$qr_res = $o_db->query("
			SELECT *
			FROM ca_data_exporters de
			{$vs_sql_wheres}
		", $va_sql_params);

		$va_exporters = [];
		$va_ids = [];

		if (isset($pa_options['countOnly']) && $pa_options['countOnly']) {
			return (int)$qr_res->numRows();
		}

		while($qr_res->nextRow()) {
			$va_row = $qr_res->getRow();
			if($ps_type_code) {
				$t_exporter = new ca_data_exporters($va_row['exporter_id']);
				$va_restrictions = $t_exporter->getSetting('typeRestrictions');
				if(is_array($va_restrictions) && sizeof($va_restrictions)) {
					if(!in_array($ps_type_code, $va_restrictions)) {
						continue;
					}
				}
			}

			$va_ids[] = $vn_id = $va_row['exporter_id'];

			$va_exporters[$vn_id] = $va_row;

			$t_instance = Datamodel::getInstanceByTableNum($va_row['table_num'], true);
			$va_exporters[$vn_id]['exporter_type'] = $t_instance->getProperty('NAME_PLURAL');
			$va_exporters[$vn_id]['exporter_type_singular'] = $t_instance->getProperty('NAME_SINGULAR');

			$va_exporters[$vn_id]['settings'] = caUnserializeForDatabase($va_row['settings']);
			$va_exporters[$vn_id]['last_modified_on'] = $t_exporter->getLastChangeTimestamp($vn_id, array('timestampOnly' => true));
		}

		$va_labels = $t_exporter->getPreferredDisplayLabelsForIDs($va_ids);
		foreach($va_labels as $vn_id => $vs_label) {
			$va_exporters[$vn_id]['label'] = $vs_label;
		}

		return $va_exporters;
	}
	# ------------------------------------------------------
	/**
	 * Returns list of available data exporters as HTML form element
	 */
	static public function getExporterListAsHTMLFormElement($ps_name, $pn_table_num=null, $pa_attributes=null, $pa_options=null) {
		$va_exporters = ca_data_exporters::getExporters($pn_table_num, $pa_options);

		$va_opts = [];
		foreach($va_exporters as $va_importer_info) {
			$va_opts[$va_importer_info['label']." (".$va_importer_info['exporter_code'].")"] = $va_importer_info['exporter_code'];
		}
		ksort($va_opts);
		return caHTMLSelect($ps_name, $va_opts, $pa_attributes, $pa_options);
	}
	# ------------------------------------------------------
	/**
	 * Returns count of available data exporters
	 */
	static public function getExporterCount($table=null) {
		return ca_data_exporters::getExporters($table, ['countOnly' => true]);
	}
	# ------------------------------------------------------
	/**
	 * Set setting values. You must call insert() or update() to write the settings to the database.
	 *
	 * @param string $setting
	 * @param string $valud
	 */
	public function setSetting(string $setting, string $value) {
		$current_settings = $this->getAvailableSettings();
		
		if(($setting === 'exporter_format') && $value) {
			if (is_array($format_settings = ca_data_exporters::getFormatSettings($value))) {
				$current_settings += $format_settings;
			}
			$this->setAvailableSettings($current_settings);
		}
		return $this->traitSetSetting($setting, $value);
	}
	# ------------------------------------------------------
	/**
	 * Export a set of records across different database entities with different mappings.
	 * This is usually used to construct RDF graphs or similar structures, hence the name of the function.
	 * @param string $ps_config Path to a configuration file that defines how the graph is built
	 * @param string $ps_filename Destination filename (we can't keep everything in memory here)
	 * @param array $pa_options
	 *		showCLIProgressBar = Show command-line progress bar. Default is false.
	 *		includeDeleted = Export deleted records that match criteria. [Default is false]
	 *		logDirectory = path to directory where logs should be written
	 *		logLevel = KLogger constant for minimum log level to record. Default is KLogger::INFO. Constants are, in descending order of shrillness:
	 *			KLogger::EMERG = Emergency messages (system is unusable)
	 *			KLogger::ALERT = Alert messages (action must be taken immediately)
	 *			KLogger::CRIT = Critical conditions
	 *			KLogger::ERR = Error conditions
	 *			KLogger::WARN = Warnings
	 *			KLogger::NOTICE = Notices (normal but significant conditions)
	 *			KLogger::INFO = Informational messages
	 *			KLogger::DEBUG = Debugging messages
	 * @return boolean success state
	 */
	static public function exportRDFMode($ps_config, $ps_filename, $pa_options=[]) {

		$vs_log_dir = caGetOption('logDirectory',$pa_options);
		if(!file_exists($vs_log_dir) || !is_writable($vs_log_dir)) {
			$vs_log_dir = caGetTempDirPath();
		}

		if(!($vn_log_level = caGetOption('logLevel',$pa_options))) {
			$vn_log_level = KLogger::INFO;
		}

		$o_log = new KLogger($vs_log_dir, $vn_log_level);

		$vb_show_cli_progress_bar = (isset($pa_options['showCLIProgressBar']) && ($pa_options['showCLIProgressBar']));

		if(!($o_config = Configuration::load($ps_config))) {
			return false;
		}

		$vs_wrap_before = $o_config->get('wrap_before');
		$vs_wrap_after = $o_config->get('wrap_after');
		$va_nodes = $o_config->get('nodes');

		if($vs_wrap_before) {
			file_put_contents($ps_filename, $vs_wrap_before."\n", FILE_APPEND);
		}

		$va_records_to_export = [];

		if(is_array($va_nodes)) {
			foreach($va_nodes as $va_mapping) {
				$va_mapping_names = explode("/",$va_mapping['mapping']);
				foreach($va_mapping_names as $vs_mapping_name) {
					if(!($t_mapping = ca_data_exporters::loadExporterByCode($vs_mapping_name))) {
						print _t("Invalid mapping %1",$vs_mapping_name)."\n";
						return;
					}
				}

				$vn_table = $t_mapping->get('table_num');
				$vs_key = Datamodel::primaryKey($vn_table);

				$vs_search = isset($va_mapping['restrictBySearch']) ? $va_mapping['restrictBySearch'] : "*";
				$o_search = caGetSearchInstance($vn_table);
				$o_result = $o_search->search($vs_search);

				if ($vb_show_cli_progress_bar) {
					print CLIProgressBar::start($o_result->numHits(), $vs_msg = _t('Adding %1 mapping records to set',$va_mapping['mapping']));
				}

				while($o_result->nextHit()) {
					if ($vb_show_cli_progress_bar) {
						print CLIProgressBar::next(1, $vs_msg);
					}

					$va_records_to_export[$vn_table."/".$o_result->get($vs_key)] = $va_mapping['mapping'];

					if(is_array($va_mapping['related'])) {
						foreach($va_mapping['related'] as $va_related_nodes) {
							if(!$t_related_mapping = ca_data_exporters::loadExporterByCode($va_related_nodes['mapping'])) {
								continue;
							}

							$vn_rel_table = $t_related_mapping->get('table_num');
							$vs_rel_table = Datamodel::getTableName($vn_rel_table);
							$vs_rel_key = Datamodel::primaryKey($vn_rel_table);

							$va_restrict_to_types = is_array($va_related_nodes['restrictToTypes']) ? $va_related_nodes['restrictToTypes'] : null;
							$va_restrict_to_rel_types = is_array($va_related_nodes['restrictToRelationshipTypes']) ? $va_related_nodes['restrictToRelationshipTypes'] : null;


							$va_related = $o_result->get($vs_rel_table,array('returnAsArray' => true, 'restrictToTypes' => $va_restrict_to_types, 'restrictToRelationshipTypes' => $va_restrict_to_rel_types));
							foreach($va_related as $va_rel) {
								if(!isset($va_records_to_export[$vn_rel_table."/".$va_rel[$vs_rel_key]])) {
									$va_records_to_export[$vn_rel_table."/".$va_rel[$vs_rel_key]] = $t_related_mapping->get('exporter_code');
								}
							}
						}
					}
				}

				if ($vb_show_cli_progress_bar) {
					print CLIProgressBar::finish();
				}
			}
		}

		if ($vb_show_cli_progress_bar) {
			print CLIProgressBar::start(sizeof($va_records_to_export), $vs_msg = _t('Exporting record set'));
		}

		foreach($va_records_to_export as $vs_key => $vs_mapping) {
			$va_split = explode("/",$vs_key);
			$va_mappings = explode("/", $vs_mapping);

			foreach($va_mappings as $vs_mapping) {
				$vs_item_export = ca_data_exporters::exportRecord($vs_mapping,$va_split[1],array('rdfMode' => true, 'logger' => $o_log, 'includeDeleted' => caGetOption('includeDeleted', $pa_options, false)));
				file_put_contents($ps_filename, trim($vs_item_export)."\n", FILE_APPEND);
			}

			if ($vb_show_cli_progress_bar) {
				print CLIProgressBar::next(1, $vs_msg);
			}
		}

		if($vs_wrap_after) {
			file_put_contents($ps_filename, $vs_wrap_after."\n", FILE_APPEND);
		}

		if ($vb_show_cli_progress_bar) {
			print CLIProgressBar::finish();
		}

		return true;
	}
	# ------------------------------------------------------
	/**
	 * Export a record set as defined by the given search expression and the table_num for this exporter.
	 * This function wraps the record-level exports using the settings 'wrap_before' and 'wrap_after' if they are set.
	 * @param string $ps_exporter_code defines the exporter to use
	 * @param string $ps_expression A valid search expression
	 * @param string $ps_filename Destination filename (we can't keep everything in memory here)
	 * @param array $pa_options
	 *		showCLIProgressBar = Show command-line progress bar. Default is false.
	 *		includeDeleted = Export deleted records that match criteria. [Default is false]
	 *		logDirectory = path to directory where logs should be written
	 *		logLevel = KLogger constant for minimum log level to record. Default is KLogger::INFO. Constants are, in descending order of shrillness:
	 *			KLogger::EMERG = Emergency messages (system is unusable)
	 *			KLogger::ALERT = Alert messages (action must be taken immediately)
	 *			KLogger::CRIT = Critical conditions
	 *			KLogger::ERR = Error conditions
	 *			KLogger::WARN = Warnings
	 *			KLogger::NOTICE = Notices (normal but significant conditions)
	 *			KLogger::INFO = Informational messages
	 *			KLogger::DEBUG = Debugging messages
	 * @return boolean success state
	 */
	static public function exportRecordsFromSearchExpression($ps_exporter_code, $ps_expression, $ps_filename, $pa_options=[]) {

		ca_data_exporters::$s_exporter_cache = [];
		ca_data_exporters::$s_exporter_item_cache = [];
		if(!$t_mapping = ca_data_exporters::loadExporterByCode($ps_exporter_code)) { return false; }

		$o_search = caGetSearchInstance($t_mapping->get('table_num'));
		$o_result = $o_search->search($ps_expression, ['showDeleted' => caGetOption('includeDeleted', $pa_options, false)]);

		return self::exportRecordsFromSearchResult($ps_exporter_code, $o_result, $ps_filename, $pa_options);
	}

	# ------------------------------------------------------
	/**
	 * Export a record set as defined by the given search expression and the table_num for this exporter.
	 * This function wraps the record-level exports using the settings 'wrap_before' and 'wrap_after' if they are set.
	 *
	 * @param string $ps_exporter_code defines the exporter to use
	 * @param SearchResult $po_result An existing SearchResult object
	 * @param string $ps_filename Destination filename (we can't keep everything in memory here)
	 * @param array $pa_options
	 *		individualFiles = For XML and JSON exports, output data one record per-file, using $ps_filename as a path to a directory into which to write the files. [Default is false]
	 *		filenameTemplate = When individualFiles option is set, may contain a template used to name each file. [Default is ^<table>.idno]
	 *		includeDeleted = Export deleted records that match criteria. [Default is false]
	 * 		progressCallback = callback function for asynchronous UI status reporting
	 *		showCLIProgressBar = Show command-line progress bar. Default is false.
	 *		logDirectory = path to directory where logs should be written
	 *		logLevel = KLogger constant for minimum log level to record. Default is KLogger::INFO. Constants are, in descending order of shrillness:
	 *			KLogger::EMERG = Emergency messages (system is unusable)
	 *			KLogger::ALERT = Alert messages (action must be taken immediately)
	 *			KLogger::CRIT = Critical conditions
	 *			KLogger::ERR = Error conditions
	 *			KLogger::WARN = Warnings
	 *			KLogger::NOTICE = Notices (normal but significant conditions)
	 *			KLogger::INFO = Informational messages
	 *			KLogger::DEBUG = Debugging messages
	 * @return boolean success state
	 */
	static public function exportRecordsFromSearchResult($ps_exporter_code, $po_result, $ps_filename=null, $pa_options=[]) {
		if(!($po_result instanceof SearchResult)) { return false; }
		
		$individual_files = caGetOption('individualFiles', $pa_options, false);
		$filename_template = caGetOption('filenameTemplate', $pa_options, null);
		$include_deleted = caGetOption('includeDeleted', $pa_options, false);

		$vs_log_dir = caGetOption('logDirectory',$pa_options);
		if(!file_exists($vs_log_dir) || !is_writable($vs_log_dir)) {
			$vs_log_dir = caGetTempDirPath();
		}

		if(!($vn_log_level = caGetOption('logLevel',$pa_options))) {
			$vn_log_level = KLogger::INFO;
		}

		$o_log = new KLogger($vs_log_dir, $vn_log_level);


		$o_config = Configuration::load();


		ca_data_exporters::$s_exporter_cache = [];
		ca_data_exporters::$s_exporter_item_cache = [];

		$vb_show_cli_progress_bar = (isset($pa_options['showCLIProgressBar']) && ($pa_options['showCLIProgressBar']));
		$po_request = caGetOption('request', $pa_options, null);
		$vb_have_request = ($po_request instanceof RequestHTTP);

		if(!$t_mapping = ca_data_exporters::loadExporterByCode($ps_exporter_code)) {
			return false;
		}

		$va_errors = ca_data_exporters::checkMapping($ps_exporter_code);
		if(sizeof($va_errors)>0) {
			if ($po_request && isset($pa_options['progressCallback']) && ($ps_callback = $pa_options['progressCallback'])) {
				$ps_callback($po_request, 0, -1, _t('Export failed: %1',join("; ",$va_errors)), 0, memory_get_usage(true), 0);
			}
			return false;
		}

		$o_log->logInfo(_t("Starting SearchResult-based multi-record export for mapping %1.", $ps_exporter_code));

		$vn_start_time = time();

		$vs_wrap_before = $t_mapping->getSetting('wrap_before');
		$vs_wrap_after = $t_mapping->getSetting('wrap_after');
		$vs_export_format = $t_mapping->getSetting('exporter_format');
		
		if($vs_export_format === 'CSV') { $individual_files = false; } // no individual file output with CSV

		$t_instance = Datamodel::getInstanceByTableNum($t_mapping->get('table_num'));
		$vn_num_items = $po_result->numHits();

		$o_log->logInfo(_t("SearchResult contains %1 results. Now calling single-item export for each record.", $vn_num_items));

		if(!$individual_files && $vs_wrap_before) {
			file_put_contents($ps_filename, $vs_wrap_before."\n", FILE_APPEND);
		}

		if($vs_export_format == 'JSON'){
			$va_json_data = [];
		}

		if ($vb_show_cli_progress_bar) {
			print CLIProgressBar::start($vn_num_items, _t('Processing search result'));
		}

		if ($po_request && isset($pa_options['progressCallback']) && ($ps_callback = $pa_options['progressCallback'])) {
			if($vn_num_items>0) {
				$ps_callback($po_request, 0, $vn_num_items, _t("Exporting result"), (time() - $vn_start_time), memory_get_usage(true), 0);
			} else {
				$ps_callback($po_request, 0, -1, _t('Found no records to export'), (time() - $vn_start_time), memory_get_usage(true), 0);
			}
		}
		$vn_num_processed = 0;


		if (!$individual_files && $t_mapping->getSetting('CSV_print_field_names')) {
			$va_header = $va_header_sources = [];
			$va_mapping_items = $t_mapping->getItems();
			foreach($va_mapping_items as $vn_i => $va_mapping_item) {
				$va_settings = caUnserializeForDatabase($va_mapping_item['settings']);
				$va_header_sources[(int)$va_mapping_item['element']] = $va_settings['_id'] ? $va_settings['_id'] : $va_mapping_item['source'];
			}
			ksort($va_header_sources);
			foreach($va_header_sources as $vn_element => $vs_source) {
				$va_tmp = explode(".", $vs_source);
				$vs_label = null;
				if ($t_table = Datamodel::getInstanceByTableName($va_tmp[0], true)) {
					$vs_label = $t_table->getDisplayLabel($vs_source);
				}
				if (!$vs_label) {
					$vs_label = $vs_source;
				}
				$va_header[] = $vs_label;

			}
			$vs_delimiter = $t_mapping->getSetting('CSV_delimiter') ?: ",";
			$vs_enclosure = $t_mapping->getSetting('CSV_enclosure') ?: '"';

			file_put_contents($ps_filename, $vs_enclosure . join($vs_enclosure.$vs_delimiter.$vs_enclosure,$va_header) . $vs_enclosure."\n", FILE_APPEND);
		}

		if($individual_files && !$filename_template) {
			$table = $po_result->tableName();
			$filename_template = "^{$table}.".Datamodel::getTableProperty($table, 'ID_NUMBERING_ID_FIELD');
		}

		$i = 0;
		while($po_result->nextHit()) {
			// clear caches every once in a while. doesn't make much sense to keep them around while exporting
			if((++$i % 1000) == 0) {
				SearchResult::clearCaches();
				ca_data_exporters::clearCaches();
			}

			if($vb_have_request) {
				if(!caCanRead($po_request->getUserID(), $t_instance->tableNum(), $po_result->get($t_instance->primaryKey()))) {
					continue;
				}
			}

			$vs_item_export = ca_data_exporters::exportRecord($ps_exporter_code, $po_result->get($t_instance->primaryKey()), ['logger' => $o_log, 'singleRecord' => $individual_files, 'includeDeleted' => caGetOption('includeDeleted', $pa_options, false)]);
			
			if($individual_files) {
				$individual_filename = preg_replace("![^\pL\d_\-]+!u", '_', $po_result->getWithTemplate($filename_template));
				file_put_contents("{$ps_filename}/{$individual_filename}.".strtolower($vs_export_format), $vs_wrap_before.$vs_item_export.$vs_wrap_after);
			} elseif($vs_export_format == 'JSON'){
				array_push($va_json_data, json_decode($vs_item_export));
			} else {
				file_put_contents($ps_filename, $vs_item_export."\n", FILE_APPEND);
			}

			if ($vb_show_cli_progress_bar) {
				print CLIProgressBar::next(1, _t("Exporting records..."));
			}

			$vn_num_processed++;

			if ($vb_have_request && isset($pa_options['progressCallback']) && ($ps_callback = $pa_options['progressCallback'])) {
				$ps_callback($po_request, $vn_num_processed, $vn_num_items, _t("Exporting ... [%1/%2]", $vn_num_processed, $vn_num_items), (time() - $vn_start_time), memory_get_usage(true), $vn_num_processed);
			}
		}

		if(!$individual_files) {
			if($vs_wrap_after) {
				file_put_contents($ps_filename, $vs_wrap_after."\n", FILE_APPEND);
			}

			if(!$vs_export_format == 'JSON'){
				file_put_contents($ps_filename, json_encode($va_json_data), FILE_APPEND);
			}
		}

		if ($vb_show_cli_progress_bar) {
			print CLIProgressBar::finish();
		}

		if ($po_request && isset($pa_options['progressCallback']) && ($ps_callback = $pa_options['progressCallback'])) {
			$ps_callback($po_request, $vn_num_items, $vn_num_items, _t('Export completed'), (time() - $vn_start_time), memory_get_usage(true), $vn_num_processed);
		}

		return true;
	}

	# ------------------------------------------------------
	/**
	 * Export set items
	 *
	 * @param string $ps_exporter_code defines the exporter to use
	 * @param int $pn_set_id primary key of set to export
	 * @param string $ps_filename Destination filename (we can't keep everything in memory here)
	 * @param array $pa_options
	 * 		progressCallback = callback function for asynchronous UI status reporting
	 *		includeDeleted = Export deleted records that match criteria. [Default is false]
	 *		showCLIProgressBar = Show command-line progress bar. Default is false.
	 *		logDirectory = path to directory where logs should be written
	 *		logLevel = KLogger constant for minimum log level to record. Default is KLogger::INFO.
	 * @return boolean success state
	 */
	static public function exportRecordsFromSet($ps_exporter_code, $pn_set_id, $ps_filename, $pa_options=null) {
		ca_data_exporters::$s_exporter_cache = [];
		ca_data_exporters::$s_exporter_item_cache = [];

		if(!($t_mapping = ca_data_exporters::loadExporterByCode($ps_exporter_code))) { return false; }
		if(sizeof(ca_data_exporters::checkMapping($ps_exporter_code))>0) { return false; }

		$t_set = new ca_sets();

		if(!$t_set->load($pn_set_id)) { return false; }

		$va_row_ids = array_keys($t_set->getItems(array('returnRowIdsOnly' => true)));
		$o_result = $t_set->makeSearchResult($t_set->get('table_num'), $va_row_ids);
		return self::exportRecordsFromSearchResult($ps_exporter_code, $o_result, $ps_filename, $pa_options);
	}
	# ------------------------------------------------------
	/**
	 * Export set of records from a given SearchResult object to an array of strings with the individual exports, keyed by primary key.
	 * The behavior is tailored towards the needs of the OAIPMHService.
	 *
	 * @param string $ps_exporter_code defines the exporter to use
	 * @param SearchResult $po_result search result as object
	 * @param  array $pa_options
	 * 		'start' =
	 *   	'limit' =
	 * @return array exported data
	 */
	static public function exportRecordsFromSearchResultToArray($ps_exporter_code, $po_result, $pa_options=null) {
		$vn_start = isset($pa_options['start']) ? (int)$pa_options['start'] : 0;
		$vn_limit = isset($pa_options['limit']) ? (int)$pa_options['limit'] : 0;

		ca_data_exporters::$s_exporter_cache = [];
		ca_data_exporters::$s_exporter_item_cache = [];

		if(!($po_result instanceof SearchResult)) { return false; }
		if(!($t_mapping = ca_data_exporters::loadExporterByCode($ps_exporter_code))) { return false; }
		if(sizeof(ca_data_exporters::checkMapping($ps_exporter_code))>0) { return false; }

		$t_instance = Datamodel::getInstanceByTableNum($t_mapping->get('table_num'));

		if (($vn_start > 0) && ($vn_start < $po_result->numHits())) {
			$po_result->seek($vn_start);
		}
		
		if(!($o_log = caGetOption('logger', $pa_options, null))) {
			$vs_log_dir = caGetOption('logDirectory',$pa_options);
			if(!file_exists($vs_log_dir) || !is_writable($vs_log_dir)) {
				$vs_log_dir = caGetTempDirPath();
			}

			if(!($vn_log_level = caGetOption('logLevel',$pa_options))) {
				$vn_log_level = KLogger::INFO;
			}

			$o_log = new KLogger($vs_log_dir, $vn_log_level);
			
			$pa_options['logger'] = $o_log;
		}
		

		$va_return = [];
		$vn_i = 0;
		while($po_result->nextHit()) {
			if ($vn_limit && ($vn_i >= $vn_limit)) { break; }

			$vn_pk_val = $po_result->get($t_instance->primaryKey());
			$va_return[$vn_pk_val] = ca_data_exporters::exportRecord($ps_exporter_code,$vn_pk_val, $pa_options);

			$vn_i++;
		}

		return $va_return;
	}
	# ------------------------------------------------------
	/**
	 * Check export mapping for format-specific errors
	 * @param string $ps_exporter_code code identifying the exporter
	 *
	 * @return array Array of errors. Array has size 0 if no errors occurred.
	 */
	static public function checkMapping(string $exporter_code) : array {
		if(isset(ca_data_exporters::$s_mapping_check_cache[$exporter_code]) && is_array(ca_data_exporters::$s_mapping_check_cache[$exporter_code])) {
			return ca_data_exporters::$s_mapping_check_cache[$exporter_code];
		} else {
			$t_mapping = new ca_data_exporters();
			if(!$t_mapping->load(array('exporter_code' => $exporter_code))) {
				return array(_t("Invalid mapping code"));
			}

			if($o_export = self::getExportFormatInstance($t_mapping->getSetting('exporter_format'))) {
				return ca_data_exporters::$s_mapping_check_cache[$exporter_code] = $o_export->getMappingErrors($t_mapping);
			}
			return [_t("Invalid exporter format")];
		}
	}
	# ------------------------------------------------------
	/**
	 * Export a single record using the mapping defined by this exporter and return as string
	 * @param string $ps_exporter_code defines the exporter to use
	 * @param int $pn_record_id Primary key of the record to export. Record type is determined by the table_num field for this exporter.
	 * @param array $pa_options
	 *        singleRecord = Gives a signal to the export format implementation that this is a single record export. For certain formats
	 *        	this might trigger different behavior, for instance the XML export format prepends the item-level output with <?xml ... ?>
	 *        	in those cases.
	 *        rdfMode = Signals the implementation that this is an RDF mode export
	 *		  includeDeleted = Export deleted records that match criteria. [Default is false]
	 *        logDirectory = path to directory where logs should be written
	 *		  logLevel = KLogger constant for minimum log level to record. Default is KLogger::INFO. Constants are, in descending order of shrillness:
	 *			KLogger::EMERG = Emergency messages (system is unusable)
	 *			KLogger::ALERT = Alert messages (action must be taken immediately)
	 *			KLogger::CRIT = Critical conditions
	 *			KLogger::ERR = Error conditions
	 *			KLogger::WARN = Warnings
	 *			KLogger::NOTICE = Notices (normal but significant conditions)
	 *			KLogger::INFO = Informational messages
	 *			KLogger::DEBUG = Debugging messages
	 *		  logger = Optional ready-to-use instance of KLogger to use for logging/debugging
	 * @return string Exported record as string
	 */
	static public function exportRecord($ps_exporter_code, $pn_record_id, $pa_options=[]) {
		// The variable cache is valid for the whole record export.
		// It's being modified in ca_data_exporters::processExporterItem
		// and then reset here if we move on to the next record.
		ca_data_exporters::$s_variables = [];

		$o_log = caGetOption('logger', $pa_options);

		// only set up new logging facilities if no existing one has been passed down
		if(!$o_log || !($o_log instanceof KLogger)) {
			$vs_log_dir = caGetOption('logDirectory', $pa_options);
			if(!file_exists($vs_log_dir) || !is_writable($vs_log_dir)) {
				$vs_log_dir = caGetTempDirPath();
			}

			if(!($vn_log_level = caGetOption('logLevel',$pa_options))) {
				$vn_log_level = KLogger::INFO;
			}

			$o_log = new KLogger($vs_log_dir, $vn_log_level);
		}

		// make sure we pass logger to item processor
		$pa_options['logger'] = $o_log;

		ca_data_exporters::$s_instance_cache = [];

		$t_exporter = ca_data_exporters::loadExporterByCode($ps_exporter_code);
		if(!$t_exporter) {
			$o_log->logError(_t("Failed to load exporter with code '%1' for item with ID %2", $ps_exporter_code, $pn_record_id));
			return false;
		}
		
		$pa_options['settings'] = $t_exporter->getSettings();

		$va_type_restrictions = $t_exporter->getSetting('typeRestrictions');
		if(is_array($va_type_restrictions) && sizeof($va_type_restrictions)) {
			$t_instance = Datamodel::getInstance($t_exporter->get('table_num'));
			$t_instance->load($pn_record_id);
			if(!in_array($t_instance->getTypeCode(), $va_type_restrictions)) {
				$o_log->logError(_t("Could not run exporter with code '%1' for item with ID %2 because a type restriction is in place", $ps_exporter_code, $pn_record_id));
				return false;
			}
		}

		$o_log->logInfo(_t("Successfully loaded exporter with code '%1' for item with ID %2", $ps_exporter_code, $pn_record_id));

		$va_export = [];

		foreach($t_exporter->getTopLevelItems() as $va_item) {			
			// get variables
			if(preg_match("/^_VARIABLE_:(.*)$/", $va_item['element'], $va_matches)) {
				if(!$t_instance) {
					$t_instance = Datamodel::getInstance($t_exporter->get('table_num'));
					$t_instance->load($pn_record_id);
				}
				ca_data_exporters::$s_variables[$va_matches[1]] = $t_instance->get($va_item['source']);
			}
		}
		foreach($t_exporter->getTopLevelItems() as $va_item) {
			$va_export = array_merge($va_export, $t_exporter->processExporterItem($va_item['item_id'], $t_exporter->get('table_num'), $pn_record_id, $pa_options));
		}

		$o_log->logInfo(_t("The export tree for exporter code '%1' and item with ID %2 is now ready to be processed by the export format (i.e. transformed to XML, for example).", $ps_exporter_code, $pn_record_id));
		$o_log->logDebug(print_r($va_export,true));
		
		if(!($o_export = self::getExportFormatInstance($t_exporter->getSetting('exporter_format')))) {
			return null;	
		}

		$o_export->setLogger($o_log);

		// if someone wants to mangle the whole tree ... well, go right ahead
		$o_manager = new ApplicationPluginManager();

		$o_manager->hookExportRecord(array('exporter_instance' => $t_exporter, 'record_id' => $pn_record_id, 'export' => &$va_export));


		$vs_wrap_before = $t_exporter->getSetting('wrap_before_record');
		$vs_wrap_after = $t_exporter->getSetting('wrap_after_record');

		if($vs_wrap_before || $vs_wrap_after) {
			$pa_options['singleRecord'] = false;
		}

		$vs_export = $o_export->processExport($va_export,$pa_options);

		if(strlen($vs_wrap_before)>0) {
			$vs_export = $vs_wrap_before."\n".$vs_export;
		}

		if(strlen($vs_wrap_after)>0) {
			$vs_export = $vs_export."\n".$vs_wrap_after;
		}
		return $vs_export;
	}
	# ------------------------------------------------------
	/**
	 * Processes single exporter item for a given record
	 *
	 * @param int $pn_item_id Primary of exporter item
	 * @param int $pn_table_num Table num of item to export
	 * @param int $pn_record_id Primary key value of item to export
	 * @param array $pa_options
	 *		ignoreContext = don't switch context even though context may be set for current item
	 *		includeDeleted = Export deleted records that match criteria. [Default is false]
	 *		relationship_type_id, relationship_type_code, relationship_typename =
	 *			if this export is a sub-export (context-switch), we have no way of knowing the relationship
	 *			to the 'parent' element in the export, so there has to be a means to pass it down to make it accessible
	 * 		attribute_id = signals that this is an export relative to a specific attribute instance
	 * 			this triggers special behavior that allows getting container values in a kind of sub-export
	 *			it's really only useful for Containers but in theory can be any attribute
	 *		logger = KLogger instance to use for logging. This option is mandatory!
	 * 		offset =
	 * @return array Item info
	 */
	public function processExporterItem($pn_item_id,$pn_table_num,$pn_record_id,$pa_options=[]) {
		$include_deleted = caGetOption('includeDeleted', $pa_options, false);
		$o_log = caGetOption('logger',$pa_options); // always set by exportRecord()

		$vb_ignore_context = caGetOption('ignoreContext',$pa_options);
		$current_context = caGetOption('currentContext',$pa_options);
		$vn_attribute_id = caGetOption('attribute_id',$pa_options);

		$o_log->logInfo(_t("Export mapping processor called with parameters [exporter_item_id:%1 table_num:%2 record_id:%3]", $pn_item_id, $pn_table_num, $pn_record_id));

		if (MemoryCache::contains("exporter_item_{$pn_item_id}")) {
			$t_exporter_item = MemoryCache::fetch("exporter_item_{$pn_item_id}");
		} else {
			$t_exporter_item = ca_data_exporters::loadExporterItemByID($pn_item_id);
			MemoryCache::save("exporter_item_{$pn_item_id}", $t_exporter_item);
		}
	
		if (!($t_instance = ca_data_exporters::loadInstanceByID($pn_record_id, $pn_table_num, $pa_options))) {
			//throw new ApplicationException(_t("Record with ID %1 does not exist in table %2", $pn_record_id, $pn_table_num));
			return [];
		}
		
		$settings = $t_exporter_item->getSettings();
		$return_raw_data = (bool)$settings['returnRawData'];

		$vb_strip_newlines = caGetOption('stripNewlines',$pa_options, $t_exporter_item->getSetting('stripNewlines'));

		// switch context to a different set of records if necessary and repeat current exporter item for all those selected records
		// (e.g. hierarchy children or related items in another table, restricted by types or relationship types)
		if(!$vb_ignore_context && ($contexts = $settings['context'])) {
			if(!is_array($contexts)) { 
				$contexts = [$contexts, $settings]; 
			}
			
			$cur_table_num = $pn_table_num;
			$cur_record_id = $pn_record_id;
			
			$vs_key = $t_instance->primaryKey();
			$va_related = [[$vs_key => $pn_record_id]];
			
			for($i=0; $i < sizeof($contexts); $i = $i + 2) {
				$context = $contexts[$i];
				if(!is_array($context_settings = $contexts[$i+1])) { $context_settings = []; }
				
				$va_filter_types = $context_settings['filterTypes'];	
				if (!is_array($va_filter_types) && $va_filter_types) { $va_filter_types = [$va_filter_types]; }
				
				$va_restrict_to_types = $context_settings['restrictToTypes'];		
				if (!is_array($va_restrict_to_rel_types = $context_settings['restrictToRelationshipTypes'])) { $va_restrict_to_rel_types = []; }
				$va_restrict_to_rel_types = array_merge($va_restrict_to_rel_types, caGetOption('restrictToRelationshipTypes', $pa_options, []));
			
				$va_restrict_to_bundle_vals = $context_settings['restrictToBundleValues'];
				$va_check_access = $context_settings['checkAccess'];
				$va_sort = $context_settings['sort'];


				$ids = array_map(function($v) use ($vs_key) {
					return $v[$vs_key];
				}, $va_related);
				$qrl = caMakeSearchResult($cur_table_num, $ids);

				while($qrl->nextHit()) {
					$t_rel = $qrl->getInstance();
					
					$vn_new_table_num = $vs_new_table_name = $vs_key = null;
					if (sizeof($tmp = explode('.', $context)) == 2) {
						// convert <table>.<spec> contexts to just <spec> when table is present
						$vn_new_table_num = Datamodel::getTableNum($tmp[0]);
				
						if ($cur_table_num != $vn_new_table_num) {
							$vs_new_table_name = Datamodel::getTableName($tmp[0]);
							$context = $tmp[1];
				
							$vs_key = Datamodel::primaryKey($tmp[0]);
						} else {
							$vn_new_table_num = null;
						}
					} elseif($vn_new_table_num = Datamodel::getTableNum($context)) { // switch to new table
						$vs_key = Datamodel::primaryKey($context);
						$vs_new_table_name = Datamodel::getTableName($context);
					} else { // this table, i.e. hierarchy context switch
						$vs_key = $t_rel->primaryKey();
					}
			
					$vb_context_is_related_table = false;
					$va_related = null;
					$vb_force_context = false;

					$o_log->logInfo(_t("Initiating context switch to '%1' for mapping ID %2 and record ID %3. The processor now tries to find matching records for the switch and calls itself for each of those items.", $context, $pn_item_id, $cur_record_id));

					switch($context) {
						case 'children':
							$va_related = $t_rel->getHierarchyChildren();
							break;
						case 'parent':
							$va_related = [];
							if($vs_parent_id_fld = $t_rel->getProperty("HIERARCHY_PARENT_ID_FLD")) {
								$va_related[] = [
									$vs_key => $t_rel->get($vs_parent_id_fld)
								];
							}
							break;
						case 'ancestors':
						case 'hierarchy':
							$va_parents = $t_rel->get("{$vs_new_table_name}.hierarchy.{$vs_key}", ['returnAsArray' => true, 'restrictToTypes' => $va_filter_types]);

							$va_related = [];
							foreach(array_unique($va_parents) as $vn_pk) {
								$va_related[] = [
									$vs_key => intval($vn_pk)
								];
							}
							break;
						case 'ca_sets':
							$t_set = new ca_sets();
							$va_set_options = [];
							if(isset($va_restrict_to_types[0])) {
								// the utility used below doesn't support passing multiple types so we just pass the first.
								// this should be enough for 99.99% of the actual use cases anyway
								$va_set_options['setType'] = $va_restrict_to_types[0];
							}
							$va_set_options['checkAccess'] = $va_check_access;
							$va_set_options['setIDsOnly'] = true;
							$va_set_ids = $t_set->getSetsForItem($cur_table_num,$t_rel->getPrimaryKey(),$va_set_options);
							$va_related = [];
							foreach(array_unique($va_set_ids) as $vn_pk) {
								$va_related[] = array($vs_key => intval($vn_pk));
							}
							break;
						case 'ca_list_items.firstLevel':
							if($t_rel->tableName() == 'ca_lists') {
								$va_related = [];
								$va_items_legacy_format = $t_rel->getListItemsAsHierarchy(null,array('maxLevels' => 1, 'dontIncludeRoot' => true));
								$vn_new_table_num = Datamodel::getTableNum('ca_list_items');
								$vs_key = 'item_id';
								foreach($va_items_legacy_format as $va_item_legacy_format) {
									$va_related[$va_item_legacy_format['NODE']['item_id']] = $va_item_legacy_format['NODE'];
								}
								break;
							} else {
								return [];
							}
							break;
						default:
							if($vn_new_table_num) {
								$va_options = array(
									'restrictToTypes' => $va_restrict_to_types,
									'restrictToRelationshipTypes' => $va_restrict_to_rel_types,
									'restrictToBundleValues' => $va_restrict_to_bundle_vals,
									'checkAccess' => $va_check_access,
									'sort' => $va_sort,
									'showDeleted' => $include_deleted = caGetOption('includeDeleted', $context_settings, false)
								);
								$pa_options['includeDeleted'] = $include_deleted;
								$o_log->logDebug(_t("Calling getRelatedItems with options: %1.", print_r($va_options,true)));

								$va_related = $t_rel->getRelatedItems($context, $va_options);
								$vb_context_is_related_table = true;
							} else { // container or invalid context
								$va_context_tmp = explode('.', $context);
								if(sizeof($va_context_tmp) != 2) {
									$o_log->logError(_t("Invalid context %1. Ignoring this mapping.", $context));
									return [];
								}

								$va_attrs = $t_rel->getAttributesByElement($va_context_tmp[1]);

								$va_info = [];

								if(is_array($va_attrs) && sizeof($va_attrs)>0) {

									$o_log->logInfo(_t("Switching context for element code: %1.", $va_context_tmp[1]));
									$o_log->logDebug(_t("Raw attribute value array is as follows. The mapping will now be repeated for each (outer) attribute. %1", print_r($va_attrs,true)));

									$vn_i = 0;
									foreach($va_attrs as $vo_attr) {
										$va_attribute_export = $this->processExporterItem($pn_item_id,$cur_table_num,$cur_record_id,
											array_merge(array('currentContext' => $context, 'ignoreContext' => true, 'attribute_id' => $vo_attr->getAttributeID(), 'offset' => $vn_i, 'includeDeleted' => caGetOption('includeDeleted', $context_settings, false)), $pa_options)
										);

										$va_info = array_merge($va_info, $va_attribute_export);
										$vn_i++;
									}
								} else {
									$o_log->logInfo(_t("Switching context for element code %1 failed. Either there is no attribute with that code attached to the current row or the code is invalid. Mapping is ignored for current row.", $va_context_tmp[1]));
								}
								return $va_info;

							}
							break;
					}
				}
				$cur_table_num = $vn_new_table_num;
			}
			
			$va_info = [];
			

			if(is_array($va_related) && sizeof($va_related)) {
				$o_log->logDebug(_t("The current mapping will now be repeated for these items: %1", print_r($va_related,true)));
				if(!$vn_new_table_num) { $vn_new_table_num = $pn_table_num; }

				foreach($va_related as $va_rel) {
					// if we're dealing with a related table, pass on some info the relationship type to the context-switched invocation of processExporterItem(),
					// because we can't access that information from the related item simply because we don't exactly know where the call originated
					if($vb_context_is_related_table) {
						$pa_options['relationship_typename'] = $va_rel['relationship_typename'];
						$pa_options['relationship_type_code'] = $va_rel['relationship_type_code'];
						$pa_options['relationship_type_id'] = $va_rel['relationship_type_id'];
					}
					$va_rel_export = $this->processExporterItem($pn_item_id,$vn_new_table_num,$va_rel[$vs_key],array_merge(array('ignoreContext' => true), $pa_options));
					$va_info = array_merge($va_info,$va_rel_export);
				}
			} else {
				$o_log->logDebug(_t("No matching related items found for last context switch"));
			}

			return $va_info;
		}
		// end switch context

		// Don't prevent context switches for children of context-switched exporter items. This way you can
		// build cascades for jobs like exporting objects related to the creator of the record in question.
		unset($pa_options['ignoreContext']);

		$va_item_info = [];

		$vs_source = $t_exporter_item->get('source');
		$vs_element = $t_exporter_item->get('element');
		$vb_repeat = $settings['repeat_element_for_multiple_values'];
		$deduplicate = $settings['deduplicate'];

		if($vs_skip_if_empty = $settings['skipIfEmpty']) {
			if(!(strlen($t_instance->get($vs_source))>0)) {
				return [];
			}
		}
		
		// if omitIfEmpty is set and get() returns nothing, we ignore this exporter item and all children
		if($vs_omit_if_empty = $settings['omitIfEmpty']) {
			if(!(strlen($t_instance->get($vs_omit_if_empty))>0)) {
				return [];
			}
		}
		
		// if omitIfNotEmpty is set and get() returns a value, we ignore this exporter item and all children
		if($vs_omit_if_not_empty = $settings['omitIfNotEmpty']) {
			if(strlen($t_instance->get($vs_omit_if_not_empty))>0) {
				return [];
			}
		}

		// always return URL for export, not an HTML tag
		$get_options = ['returnURL' => true];

		if($vs_delimiter = $t_exporter_item->getSetting("delimiter")) {
			$get_options['delimiter'] = $vs_delimiter;
		}

		if($vs_template = $settings['template']) {
			$get_options['template'] = $vs_template;
		}
		if($filter_non_primary_representations = ($settings['filterNonPrimaryRepresentations'] ?? 1)) {
			$get_options['filterNonPrimaryRepresentations'] = $filter_non_primary_representations;
		}

		if(($vs_locale = $settings['locale']) || ($vs_locale = caGetOption('locale', $pa_options['settings'], null))) {
			// the global UI locale for some reason has a higher priority
			// than the locale setting in BaseModelWithAttributes::get
			// which is why we unset it here and restore it later
			global $g_ui_locale;
			$vs_old_ui_locale = $g_ui_locale;
			$g_ui_locale = null;
			
			$get_options['locale'] = $vs_locale;
		}
		
		// AttributeValue settings that are simply passed through by the exporter
	
		if($settings['convertCodesToDisplayText'] ?? null) {
			$get_options['convertCodesToDisplayText'] = true;		// try to return text suitable for display for system lists stored in intrinsics (ex. ca_objects.access, ca_objects.status, ca_objects.source_id)
			// this does not affect list attributes
		} else {
			$get_options['convertCodesToIdno'] = true;				// if display text is not requested try to return list item idno's... since underlying integer ca_list_items.item_id values are unlikely to be useful in an export context
		}

		foreach([
			'convertCodesToIdno', 'returnIdno', 'start_as_iso8601', 'end_as_iso8601', 'timeOmit', 'stripTags',
			'dontReturnValueIfOnSameDayAsStart', 'returnAllLocales'
		] as $opt) {
			$get_options[$opt] = (bool)($settings[$opt] ?? null);
		}

		if($vs_date_format = $settings['dateFormat']) {
			$get_options['dateFormat'] = $vs_date_format;
		}
		if($settings['coordinatesOnly'] ?? null) {
			$get_options['path'] = true;
		}
		
		if ($va_filter_types = $settings['filterTypes']) {
		    $get_options['filterTypes'] = is_array($va_filter_types) ? $va_filter_types : [$va_filter_types];
		}
		if ($va_restrict_to_types = $settings['restrictToTypes']) {
		    $get_options['restrictToTypes'] = is_array($va_restrict_to_types) ? $va_restrict_to_types : [$va_restrict_to_types];
		}
		if ($va_restrict_to_relationship_types = $settings['restrictToRelationshipTypes']) {
		    $get_options['restrictToRelationshipTypes'] = is_array($va_restrict_to_relationship_types) ? $va_restrict_to_relationship_types : [$va_restrict_to_relationship_types];
		}
		
		$vs_skip_if_expr = $settings['skipIfExpression'] ?? null;
		$va_expr_tags = caGetTemplateTags($vs_skip_if_expr);

		// context was switched to attribute
		if($vn_attribute_id) {
			$t_attr = new ca_attributes($vn_attribute_id);
			$o_log->logInfo(_t("Processing mapping in attribute mode for attribute_id = %1.", $vn_attribute_id));
			$vs_relative_to = "{$t_instance->tableName()}.{$t_attr->getElementCode()}";

			if($vs_template) { // if template is set, run through template engine as <unit>
				$vn_offset = (int) caGetOption('offset', $pa_options, 0);

				$vs_get_with_template = trim($t_instance->getWithTemplate("
					<unit relativeTo='{$vs_relative_to}' start='{$vn_offset}' length='1'>
						{$vs_template}
					</unit>
				"));

				if($vs_get_with_template) {
					$va_item_info[] = array(
						'text' => $vs_get_with_template,
						'element' => $vs_element,
						'template' => $vs_template
					);
				}
			} elseif($vs_source) { // trying to find the source only makes sense if the source is set
				if(preg_match("/^_CONSTANT_:(.*)$/", $vs_source, $va_matches)) {
					$o_log->logDebug(_t("This is a constant in attribute mode. Value for this mapping is '%1'", trim($va_matches[1])));

					$this->getVariableValues($t_instance, $va_expr_tags ?? [], $vs_relative_to);
					if(!$vs_skip_if_expr || ($vs_skip_if_expr && !ExpressionParser::evaluate($vs_skip_if_expr, ca_data_exporters::$s_variables))) {
						$o_log->logDebug(_t("This is a constant. Value for this mapping is '%1'", trim($va_matches[1])));
		
						$va_item_info[] = array(
							'text' => trim($va_matches[1]),
							'element' => $vs_element
						);
					}
				} else {					
					$va_values = $t_attr->getAttributeValues();
					$va_src_tmp = explode('.', $vs_source);
					$vs_modifier = null;
					
					if($t_attr->get('table_num') == Datamodel::getTableNum($va_src_tmp[0])) {
						array_shift($va_src_tmp);
					}
					if(sizeof($va_src_tmp) == 2) {
						$vs_source = $va_src_tmp[0];
						$vs_modifier = $va_src_tmp[1];
					} elseif(sizeof($va_src_tmp) == 1) {
						$vs_source = $va_src_tmp[0];
					}
					
					$o_log->logDebug(_t("Trying to find code %1 in value array for the current attribute.", $vs_source));
					$o_log->logDebug(_t("Value array is %1.", print_r($va_values, true)));

					foreach ($va_values as $vo_val) {
					    if ($vo_val->getElementCode() !== $vs_source)  { continue; }
					
						$va_display_val_options = is_array($get_options) ? $get_options : []; 
						switch($vo_val->getDatatype()) {
							case __CA_ATTRIBUTE_VALUE_LIST__: 
								// figure out list_id -- without it we can't pull display values
								$t_element = new ca_metadata_elements($vo_val->getElementID());
								$va_display_val_options = array('list_id' => $t_element->get('list_id'));

								if ($settings['returnIdno'] || $settings['convertCodesToIdno']) {
									$va_display_val_options['output'] = 'idno';
								} elseif ($settings['convertCodesToDisplayText']) {
									$va_display_val_options['output'] = 'text';
								}
								if($vs_modifier && ($item_id = $vo_val->getItemID()) && ($t_item = ca_list_items::findAsInstance(['item_id' => $item_id]))) {
									$vs_display_value = $t_item->get('ca_list_items.'.$vs_modifier);
								} else {
									$vs_display_value = $vo_val->getDisplayValue($va_display_val_options);
								}
								$o_log->logDebug(_t("Found value %1.", $vs_display_value));

								break;
							case __CA_ATTRIBUTE_VALUE_LCSH__:
								switch($vs_modifier) {
									case 'text':
									default:
										$vs_display_value = $vo_val->getDisplayValue(['text' => true]);
										break;
									case 'id':
										$vs_display_value = $vo_val->getDisplayValue(['n' => true]);
										break;
									case 'url':
										$vs_display_value = $vo_val->getDisplayValue(['idno' => true]);
										break;
								}
								break;
							case __CA_ATTRIBUTE_VALUE_INFORMATIONSERVICE__:
								switch($vs_modifier) {
									case 'text':
									default:
										$vs_display_value = $vo_val->getDisplayValue();
										break;
									case 'uri':
									case 'url':
										$vs_display_value = $vo_val->getUri();
										break;
								}
								break;
							default:
								$o_log->logDebug(_t("Trying to match code from array %1 and the code we're looking for %2.", $vo_val->getElementCode(), $vs_source));
								if ($vo_val->getElementCode() == $vs_source) {
									if(is_a($vo_val, 'AuthorityAttributeValue')) {
										if($vs_modifier) {
											$t_instance = $vo_val->getAuthorityInstance();
											$vs_display_value = $t_instance->get($vs_modifier, $va_display_val_options);
											if ($return_raw_data) { $vs_display_value_raw = $vs_display_value; }
										} else {
											$vs_display_value = $vo_val->getDisplayValue($va_display_val_options);
											if ($return_raw_data) { $vs_display_value_raw = $vo_val->getDisplayValue(); }
										}
									} else {
										$vs_display_value = $vo_val->getDisplayValue($va_display_val_options);
										if ($return_raw_data) { $vs_display_value_raw = $vo_val->getDisplayValue(); }
										$o_log->logDebug(_t("Found value %1.", $vs_display_value));
									}	
								}
								break;
						}

						if($vs_omit_if_empty && !strlen($vs_display_value)) { continue; }
						//if($vs_omit_if_not_empty && strlen($vs_display_value)) { continue; }
						$va_item_info[] = [
							'text' => $vs_display_value,
							'text_raw' => $return_raw_data ? $vs_display_value_raw: null,
							'element' => $vs_element,
						];
					}
				}
			} else { // no source in attribute context probably means this is some form of wrapper, e.g. a MARC field
				$va_item_info[] = array(
					'element' => $vs_element,
				);
			}
		} else if($vs_source) {
			$o_log->logDebug(_t("Source for current mapping is %1", $vs_source));
			$va_matches = [];
			// CONSTANT value
			if(preg_match("/^_CONSTANT_:(.*)$/",$vs_source, $va_matches)) {
				$this->getVariableValues($t_instance, $va_expr_tags, '');
				if(!$vs_skip_if_expr || !ExpressionParser::evaluate($vs_skip_if_expr, ca_data_exporters::$s_variables)) {
					$o_log->logDebug(_t("This is a constant. Value for this mapping is '%1'", trim($va_matches[1])));
	
					$va_item_info[] = array(
						'text' => trim($va_matches[1]),
						'element' => $vs_element,
					);
				}
			} else if(in_array($vs_source, array("relationship_type_id", "relationship_type_code", "relationship_typename"))) {
				if(isset($pa_options[$vs_source]) && strlen($pa_options[$vs_source])>0) {
					$o_log->logDebug(_t("Source refers to relationship type information. Value for this mapping is '%1'", $pa_options[$vs_source]));
					$va_item_info[] = array(
						'text' => $pa_options[$vs_source],
						'element' => $vs_element,
					);
				}
			} else {
				$va_values = $t_instance->get($vs_source, array_merge($get_options, ['returnAsArray' => true]));
				if($vs_omit_if_empty) {
					$x = $t_instance->get($vs_omit_if_empty, array_merge($get_options, ['returnAsArray' => true]));
					if(is_array($x)) { $x = array_filter($x, 'strlen'); }
					if(!$x || (is_array($x) && !sizeof($x))) { $va_values = []; }
				}
				if($vs_skip_if_expr) {
					$this->getVariableValues($t_instance, $va_expr_tags, '');
					$filtered_values = [];
					foreach($va_values as $value) {
						if(!ExpressionParser::evaluate($vs_skip_if_expr, array_merge(ca_data_exporters::$s_variables, [$vs_source => $value]))) {
							$filtered_values[] = $value;
						}
						$va_values = $filtered_values;
					}
				}
				if(!is_array($va_values) && strlen($va_values)) { $va_values = [$va_values]; }
				
				if(is_array($va_values) && sizeof($va_values)) {
					if(!$vb_repeat) {
						if($deduplicate) { $va_values = array_unique($va_values); } 
	
						$vs_get = join(caGetOption('delimiter', $get_options, ';', ['castTo' => 'string']), is_array($va_values) ? $va_values : [$va_values]);
						$o_log->logDebug(_t("Source is a simple get() for some bundle. Value for this mapping is '%1'", $vs_get));
						$o_log->logDebug(_t("get() options are: %1", print_r($get_options,true)));
	
						$va_item_info[] = [
							'text' => $vs_get,
							'text_raw' => $return_raw_data ? $t_instance->get($vs_source) : null,
							'element' => $vs_element,
						];
					} else { // user wants current element repeated in case of multiple returned values
						if($deduplicate) { $va_values = array_unique($va_values); } 
						
						if($return_raw_data) { $va_values_raw = $t_instance->get($vs_source, ['returnAsArray' => true]); }
						$o_log->logDebug(_t("Source is a get() that should be repeated for multiple values. Value for this mapping is '%1'. It includes the custom delimiter ';#;' that is later used to split the value into multiple values.", $va_values));
						$o_log->logDebug(_t("get() options are: %1", print_r($get_options,true)));
	
						foreach($va_values as $vn_i => $vs_text) {
							// handle skipIfExpression setting
							if($vs_skip_if_expr) {
								// Add current value as variable "value", accessible in expressions as ^value
								$va_vars = array_merge(['value' => $vs_text, 'value_raw' => $return_raw_data ? $va_values_raw[$vn_i] : null], ca_data_exporters::$s_variables);
					
								if(is_array($va_expr_tags)) {
									foreach($va_expr_tags as $vs_expr_tag) {
										$va_v = $t_instance->get($vs_expr_tag, ['convertCodesToIdno' => true, 'returnAsArray' => true]);
										$va_vars[$vs_expr_tag] = $va_v[$vn_i];
									}
								}
								if(ExpressionParser::evaluate($vs_skip_if_expr, $va_vars)) {
									continue;
								}
							}
							
							// When delimiter is set with repeat we make repeating values out of single values by exploding on delimiter
							// This allows for synthesis of repeating values from a template.
							if($vs_delimiter && $vb_repeat) {	
								$tvals = array_map("trim", explode($vs_delimiter, $vs_text));
								if($deduplicate) { $tvals = array_unique($tvals); }
								foreach($tvals as $tval) {
									$va_item_info[] = array(
										'element' => $vs_element,
										'text' => $tval,
										'text_raw' => $tval
									);
								}
							} else {
								$va_item_info[] = array(
									'element' => $vs_element,
									'text' => $vs_text,
									'text_raw' => $return_raw_data ? $va_values_raw[$vn_i] : null
								);
							}
						}
					}
				}
			}
		} else if($vs_template) {
			// templates without source are probably just static text, but you never know
			// -> run them through processor anyway
			$vs_proc_template = caProcessTemplateForIDs($vs_template, $pn_table_num, array($pn_record_id), []);

			$o_log->logDebug(_t("Current mapping has no source but a template '%1'. Value from extracted via template processor is '%2'", $vs_template, $vs_proc_template));

			$va_item_info[] = array(
				'element' => $vs_element,
				'text' => $vs_proc_template,
				'template' => $vs_template
			);		
		} else { // no source, no template -> probably wrapper
			$o_log->logDebug(_t("Current mapping has no source and no template and is probably an XML/MARC wrapper element"));

			$va_item_info[] = array(
				'element' => $vs_element,
			);
		}

		// reset UI locale if we unset it
		if($vs_locale) {
			$g_ui_locale = $vs_old_ui_locale;
		}

		$o_log->logDebug(_t("We're now processing other settings like default, prefix, suffix, skipIfExpression, filterByRegExp, maxLength, plugins and replacements for this mapping"));
		$o_log->logDebug(_t("Local data before processing is: %1", print_r($va_item_info,true)));

		// handle other settings and plugin hooks
		$vs_default = $settings['default'];
		$vs_prefix = $settings['prefix'];
		$vs_suffix = $settings['suffix'];
		$vs_regexp = $settings['filterByRegExp'];	
		$vn_max_length = $settings['maxLength'];

		$vs_original_values = $settings['original_values'];
		$vs_replacement_values = $settings['replacement_values'];
		
		$apply_regular_expressions = $settings['applyRegularExpressions'];
		
		$va_replacements = ca_data_exporter_items::getReplacementArray($vs_original_values,$vs_replacement_values);

		foreach($va_item_info as $vn_key => &$va_item) {
			$this->opo_app_plugin_manager->hookExportItemBeforeSettings(array('instance' => $t_instance, 'exporter_item_instance' => $t_exporter_item, 'export_item' => &$va_item));

			// handle dontReturnValueIfOnSameDayAsStart
			if(caGetOption('dontReturnValueIfOnSameDayAsStart', $get_options, false)) {
				if(strlen($va_item['text']) < 1) {
					unset($va_item_info[$vn_key]);
				}
			}
			
			if ($vs_regexp && preg_match("!{$vs_regexp}!", $va_item['text'])) { 
				unset($va_item_info[$vn_key]);
				continue; 
			}

			// handle skipIfExpression setting for non-repeating 
			if($vs_skip_if_expr && !$vb_repeat) {
				// Add current value as variable "value", accessible in expressions as ^value
				$va_vars = ca_data_exporters::$s_variables;
				$va_vars['value'] = $va_item['text'];
				$va_vars['value_raw'] = $return_raw_data ? $va_item['text_raw'] : null;
				if(is_array($va_expr_tags)) {
					foreach($va_expr_tags as $vs_expr_tag) {
						if($vs_expr_tag == $vs_source) { 
							$va_vars[$vs_expr_tag] = $va_item['text'];
							continue;
						}
						if(isset($va_vars[$vs_expr_tag])) { continue; }
						$va_vars[$vs_expr_tag] = $t_instance->get($vs_expr_tag, ['convertCodesToIdno' => true]);
					}
				}
				
				if(ExpressionParser::evaluate($vs_skip_if_expr, $va_vars)) {
					unset($va_item_info[$vn_key]);
					continue;
				}
			}

			// do regex replacements
			if(is_array($apply_regular_expressions) && sizeof($apply_regular_expressions)) {
				$va_item['text'] = ca_data_exporter_items::_processAppliedRegexes($va_item['text'], $apply_regular_expressions);
			}
			
			// do text replacements
			$va_item['text'] = ca_data_exporter_items::replaceText($va_item['text'],$va_replacements);
			
			// do templates
			if (isset($va_item['template']) || (isset($get_options['template']) && $get_options['template'])) {
				$va_item['text'] = caProcessTemplate($va_item['text'], ca_data_exporters::$s_variables);
			}

			// if text is empty, fill in default
			// if text isn't empty, respect prefix and suffix
			if(strlen($va_item['text'])==0) {
				if($vs_default) $va_item['text'] = $vs_default;
			} else if((strlen($vs_prefix)>0) || (strlen($vs_suffix)>0)) {
				$va_item['text'] = $vs_prefix.$va_item['text'].$vs_suffix;
			}

			if($vn_max_length && (strlen($va_item['text']) > $vn_max_length)) {
				$va_item['text'] = substr($va_item['text'], 0, $vn_max_length)." ...";
			}

			// if returned value is null then we skip the item
			$this->opo_app_plugin_manager->hookExportItem(array('instance' => $t_instance, 'exporter_item_instance' => $t_exporter_item, 'export_item' => &$va_item));
	
			if($vb_strip_newlines) { $va_item['text'] = preg_replace("![\n\r]+!", '', $va_item['text']); }
		}

		$o_log->logInfo(_t("Extracted data for this mapping is as follows:"));

		foreach($va_item_info as $va_tmp) {
			$o_log->logInfo(sprintf("    element:%-20s value: %-10s",$va_tmp['element'],$va_tmp['text']));
		}

		$va_children = $t_exporter_item->getHierarchyChildren();

		if(is_array($va_children) && sizeof($va_children)>0) {

			$o_log->logInfo(_t("Now proceeding to process %1 direct children in the mapping hierarchy", sizeof($va_children)));

			foreach($va_children as $va_child) {
				foreach($va_item_info as &$va_info) {
					$va_child_export = $this->processExporterItem($va_child['item_id'],$pn_table_num,$pn_record_id,$pa_options);
					$va_info['children'] = array_merge((array)$va_info['children'],$va_child_export);
				}
			}
		}
		
		
		if ($settings['omitIfNoChildren']) {
		    if (sizeof(array_filter($va_info['children'], function($v) { return substr($v['element'], 0, 1) !== '@'; })) === 0) {
		        return [];
		    }
		}

		return $va_item_info;
	}
	# ------------------------------------------------------
	/**
	 *
	 */
	static public function loadExporterByCode($ps_exporter_code) {
		if(isset(ca_data_exporters::$s_exporter_cache[$ps_exporter_code])) {
			return ca_data_exporters::$s_exporter_cache[$ps_exporter_code];
		} else {
			$t_exporter = new ca_data_exporters();
			if($t_exporter->load(array('exporter_code' => $ps_exporter_code))) {
				return ca_data_exporters::$s_exporter_cache[$ps_exporter_code] = $t_exporter;
			}
		}
		return false;
	}
	# ------------------------------------------------------
	/**
	 *
	 */
	static public function loadExporterItemByID($pn_item_id) {
		if(isset(ca_data_exporters::$s_exporter_item_cache[$pn_item_id])) {
			return ca_data_exporters::$s_exporter_item_cache[$pn_item_id];
		} else {
			$t_item = new ca_data_exporter_items();
			if($t_item->load($pn_item_id)) {
				return ca_data_exporters::$s_exporter_item_cache[$pn_item_id] = $t_item;
			}
		}
		return false;
	}
	# ------------------------------------------------------
	/**
	 * @param int $pn_record_id
	 * @param int $pn_table_num
	 * @return BundlableLabelableBaseModelWithAttributes|bool|null
	 */
	static public function loadInstanceByID($pn_record_id,$pn_table_num, $pa_options=null) {
		unset($pa_options['start']);
		unset($pa_options['limit']);
		$cache_key = caMakeCacheKeyFromOptions($pa_options, "{$pn_record_id}/{$pn_table_num}");
		
		if(sizeof(ca_data_exporters::$s_instance_cache)>4096) {
			array_splice(ca_data_exporters::$s_instance_cache, 0, 1024);
		}

		if(isset(ca_data_exporters::$s_instance_cache[$cache_key])) {
			return ca_data_exporters::$s_instance_cache[$cache_key];
		} else {
			if (!($table = Datamodel::getTableName($pn_table_num))) { return false; }
			
			$include_deleted = caGetOption('includeDeleted', $pa_options, false);
			
			$t_instance = null;
			if (is_numeric($pn_record_id)) {
				// Try numeric id
				$t_instance = $table::find($pn_record_id, array_merge($pa_options, ['includeDeleted' => $include_deleted, 'returnAs' => 'firstModelInstance', 'start' => 0, 'limit' => null]));
			}
			if(!$t_instance) {
				$t_instance = $table::find([Datamodel::getTableProperty($table, 'ID_NUMBERING_ID_FIELD') => $pn_record_id], array_merge($pa_options, ['includeDeleted' => $include_deleted, 'returnAs' => 'firstModelInstance', 'start' => 0, 'limit' => null]));
			}
			
			if (!$t_instance) {
				return false;
			}
			return ca_data_exporters::$s_instance_cache[$cache_key] = $t_instance;
		}
	}
	
	# ------------------------------------------------------
	/**
	 * Load exporter configuration from XLSX file
	 * @param string $ps_source file path for source XLSX
	 * @param array $pa_errors call-by-reference array to store and "return" error messages
	 * @param array $pa_options options
	 * @return ca_data_exporters BaseModel representation of the new exporter. false/null if there was an error.
	 */
	public static function loadExporterFromFile($source_file, &$errors, $options=null) {
		global $g_ui_locale_id;
		$locale_id = (isset($options['locale_id']) && (int)$options['locale_id']) ? (int)$options['locale_id'] : $g_ui_locale_id;

        $log_dir = caGetOption('logDirectory', $options, __CA_APP_DIR__."/log");
		if(!file_exists($log_dir) || !is_writable($log_dir)) {
			$log_dir = caGetTempDirPath();
		}

		if(!($log_level = caGetOption('logLevel',$options))) {
			$log_level = KLogger::INFO;
		}

		$o_log = new KLogger($log_dir, $log_level);


		$o_sheet = DelimitedDataParser::load($source_file, ['worksheet' => 0]);

		$row_num = 0;
		$settings = $ids = $errors = [];

		while ($o_sheet->nextRow()) {
			if ($row_num++ == 0) {	// skip first row (headers)
				continue;
			}
			
			$row = $o_sheet->getRow();
			$mode = strtolower((string)$row[0]);

			switch($mode) {
				case 'mapping':
				case 'constant':
				case 'variable':
				case 'repeatmappings':
				case 'template':
					$id = $row[1]; 
					$parent = $row[2];
					$element = $row[3];
					$source = $row[4];
					$options_json = $row[5];
					$orig_values = $row[7];
					$replacement_values = $row[8];

					if($id) {
						$ids[] = $id;
					}

					if(($mode !== 'template') && $parent) {
						if(!in_array($parent, $ids) && ($parent != $id)) {
							$errors[] = $m = _t("Warning: skipped mapping at row %1 because parent id was invalid", $row_num);
							$o_log->logWarn($m);
							continue(2);
						}
					}

					if (!$element) {
						$errors[] = $m = _t("Warning: skipped mapping at row %1 because element was not defined", $row_num);
						$o_log->logWarn($m);
						continue(2);
					}

                    $original_values = preg_split("![\n\r]{1}!", mb_strtolower((string)$orig_values));
                    array_walk($original_values, function(&$v) { $v = trim($v); });
                    $replacement_values = preg_split("![\n\r]{1}!", (string)$replacement_values);
                    array_walk($replacement_values, function(&$v) { $v = trim($v); });

					if ($mode === 'constant') {
						if(strlen($source)<1) { // ignore constant rows without value
							continue(2);
						}
						$source = "_CONSTANT_:{$source}";
					}

					if ($mode === 'variable') {
						if(preg_match("/^[A-Za-z0-9\_\-]+$/", $element)) {
							$element = "_VARIABLE_:{$element}";
						} else {
							$errors[] = $m = _t("Variable name %1 at row %2 is invalid. It should only contain ASCII letters, numbers, hyphens and underscores. The variable was not created.", $element, $row_num);
							$o_log->logError($m);
							continue(2);
						}

					}

					$mapping_options = null;
					if ($options_json) {
						$errors = [];
						$mapping_options = @json_decode($options_json, true);
						if (is_null($mapping_options)) {
							$errors[] = $m = _t("Warning: options for element %1 at row %2 are not in proper JSON", $element, $row_num);
							$o_log->logWarn($m);
						} elseif(!caValidateJSON($mapping_options,  __CA_LIB_DIR__.'/Export/Schema/ExporterItemOptions.json', $errors)) {
							$o_log->logWarn(_t("JSON for element %1 at row %2 does not conform to schema: %3", $element, $row_num, join('; ', $errors)));
						}
					}
					
					if(isset($mapping_options['original_values']) && ((is_array($mapping_options['original_values']) && sizeof($mapping_options['original_values'])) || strlen($mapping_options['original_values']))) {
						$original_values = is_array($mapping_options['original_values']) ? $mapping_options['original_values'] : [$mapping_options['original_values']];
					}
					if(isset($mapping_options['replacement_values']) && ((is_array($mapping_options['replacement_values']) && sizeof($mapping_options['replacement_values'])) || strlen($mapping_options['replacement_values']))) {
						$replacement_values = is_array($mapping_options['replacement_values']) ? $mapping_options['replacement_values'] : [$mapping_options['replacement_values']];
					}

					$mapping_options['_id'] = $id;	// stash ID for future reference

					$key = ((strlen($id) > 0) ? $id : md5($row_num));

					$mapping[$key] = array(
						'parent_id' => $parent,
						'element' => $element,
						'source' => ($mode == "repeatmappings" ? null : $source),
						'options' => $mapping_options,
						'original_values' => $original_values,
						'replacement_values' => $replacement_values,
						'skip' => ($mode === 'template')
					);

					// allow mapping repetition
					if($mode == 'repeatmappings') {
						if(strlen($source) < 1) { // ignore repitition rows without value
							continue(2);
						}

						$new_items = [];

						$mapping_items_to_repeat = preg_split('/[,;]/', $source);

						foreach($mapping_items_to_repeat as $mapping_item_to_repeat) {
							$mapping_item_to_repeat = trim($mapping_item_to_repeat);
							if(!is_array($mapping[$mapping_item_to_repeat])) {
								$errors[] = $m = _t("Couldn't repeat mapping item %1",$mapping_item_to_repeat);
							    $o_log->logError($m);
								continue;
							}

							// add item to repeat under current item
							$new_items[$key."_:_".$mapping_item_to_repeat] = $mapping[$mapping_item_to_repeat];
							$new_items[$key."_:_".$mapping_item_to_repeat]['parent_id'] = $key;
							
							unset($new_items[$key."_:_".$mapping_item_to_repeat]['skip']);

							// Find children of item to repeat (and their children) and add them as well, preserving the hierarchy
							// the code below banks on the fact that hierarchy children are always defined AFTER their parents
							// in the mapping document.

							$keys_to_lookup = [$mapping_item_to_repeat];

							foreach($mapping as $item_key => $item) {
								if(in_array($item['parent_id'], $keys_to_lookup, true)) {
									$keys_to_lookup[] = $item_key;
									$new_items[$key."_:_".$item_key] = $item;
									$new_items[$key."_:_".$item_key]['parent_id'] = $key . ($item['parent_id'] ? "_:_".$item['parent_id'] : "");
									
									unset($new_items[$key."_:_".$item_key]['skip']);
								}
							}
						}

						$mapping = $mapping + $new_items;
					}

					break;
				case 'setting':
					$setting_name = $row[1]; 
					$setting_value = $row[2];

					switch($setting_name) {
						case 'typeRestrictions':		// older mapping worksheets use "inputTypes" instead of the preferred "inputFormats"
							$settings[$setting_name] = preg_split("/[;,]/u", $setting_value);
							break;
						default:
							$settings[$setting_name] = $setting_value;
							break;
					}

					break;
				default: // if 1st column is empty, skip
					continue(2);
					break;
			}
		}

		// Try to extract replacements from second sheet in file
		// An exception will be thrown if there's no second sheet
		try {
			$o_sheet = DelimitedDataParser::load($source_file, ['worksheet' => 1]);
			$row_num = 0;
			while($o_sheet->nextRow()) {
				if ($row_num == 0) {	// skip first row (headers)
					$row_num++;
					continue;
				}
				
				$row = $o_sheet->getRow();
				$mapping_num = trim((string)$row[0]);

				if(strlen($mapping_num)<1) {
					continue;
				}

				$search = $row[1];
				$replace = $row[2];

				if(!isset($mapping[$mapping_num])) {
					$errors[] = $m = _t("Warning: Replacement sheet references invalid mapping number '%1'. Ignoring row.",$mapping_num);
					$o_log->logWarn($m);
					continue;
				}


				if(!$search) {
					$errors[] = $m = _t("Warning: Search must be set for each row in the replacement sheet. Ignoring row for mapping '%1'",$mapping_num);
				    $o_log->logWarn($m);
					continue;
				}

				// Look for replacements
				foreach($mapping as $k => &$v) {
					if(preg_match("!\_\:\_".$mapping_num."$!",$k)) {
						$v['options']['original_values'][] = $search;
						$v['options']['replacement_values'][] = $replace;
					}
				}

				$mapping[$mapping_num]['options']['original_values'][] = $search;
				$mapping[$mapping_num]['options']['replacement_values'][] = $replace;

				$row_num++;
			}
		} catch(\PhpOffice\PhpSpreadsheet\Exception $e) {
			// Noop, because we don't care: mappings without replacements are still valid
		} catch(Exception $e) {
			// Noop, because we don't care: mappings without replacements are still valid
		}

		// Do checks on mapping
		if (!$settings['code']) {
			$errors[] = $m = _t("Error: You must set a code for your mapping!");
		    $o_log->logError($m);
			return;
		}

		if (!($t_instance = Datamodel::getInstanceByTableName($settings['table']))) {
			$errors[] = $m = _t("Error: Mapping target table %1 is invalid!", $settings['table']);
			$o_log->logError($m);
			return;
		}

		if (!$settings['name']) { $settings['name'] = $settings['code']; }

		$t_exporter = new ca_data_exporters();

		// Remove any existing mapping with this code
		if ($t_exporter->load(['exporter_code' => $settings['code']])) {
			$t_exporter->delete(true, ['hard' => true]);
			if ($t_exporter->numErrors()) {
				$errors[] = $m = _t("Could not delete existing mapping for %1: %2", $settings['code'], join("; ", $t_exporter->getErrors()));
				$o_log->logError($m);
				return;
			}
		}
		
		// Create new mapping
		$t_exporter->set('exporter_code', $settings['code']);
		$t_exporter->set('table_num', $t_instance->tableNum());

		$name = $settings['name'];

		unset($settings['code']);
		unset($settings['table']);
		unset($settings['name']);

		foreach($settings as $k => $v) {
			$t_exporter->setSetting($k, $v);
		}
		$t_exporter->insert();

		if ($t_exporter->numErrors()) {
			$errors[] = $m = _t("Error creating exporter: %1", join("; ", $t_exporter->getErrors()));
			$o_log->logError($m);
			return;
		}

		$t_exporter->addLabel(array('name' => $name), $locale_id, null, true);

		if ($t_exporter->numErrors()) {
			$errors[] = $m = _t("Error creating exporter name: %1", join("; ", $t_exporter->getErrors()));
			$o_log->logError($m);
			return;
		}

		$id_map = [];
		foreach($mapping as $mapping_id => $info) {
			$item_settings = [];

			if (is_array($info['options'])) {
				foreach($info['options'] as $k => $v) {
					switch($k) {
						case 'replacement_values':
						case 'original_values':
							if(is_array($v) && (sizeof($v)>0)) {
								$item_settings[$k] = join("\n",$v);
							}
							break;
						default:
							$item_settings[$k] = $v;
							break;
					}

				}
			}
			
			if (is_array($info['original_values']) && sizeof($info['original_values'])) {
			    $item_settings['original_values'] .= "\n".join("\n", $info['original_values']);
			    if (is_array($info['replacement_values']) && sizeof($info['replacement_values'])) {
			        $item_settings['replacement_values'] .= "\n".join("\n", $info['replacement_values']);  
			    }  
			}

			$parent_id = null;
			if($info['parent_id']) { $parent_id = $id_map[$info['parent_id']]; }

			if(!$info['skip']) {
				$t_item = $t_exporter->addItem($parent_id,$info['element'],$info['source'],$item_settings);

				if ($t_exporter->numErrors()) {
					$errors[] = $m = _t("Error adding item to exporter: %1", join("; ", $t_exporter->getErrors()));
					$o_log->logError($m);
					return;
				}

				$id_map[$mapping_id] = $t_item->getPrimaryKey();
			}
		}

		$mapping_errors = ca_data_exporters::checkMapping($t_exporter->get('exporter_code'));

		if(is_array($mapping_errors) && sizeof($mapping_errors)>0) {
			$errors = array_merge($errors,$mapping_errors);
			foreach($errors as $e) {
				$o_log->logError($e);
			}
			return false;
		}

		return $t_exporter;
	}
	# ------------------------------------------------------
	/**
	 * Write exporter to Excel (XLSX) file.
	 *
	 * @param string $exporter_code
	 * @param string $file
	 * @return bool
	 */
	static public function writeExporterToFile($exporter_code, $file) {
		if (!($exporter = self::loadExporterByCode($exporter_code))) {
		    throw new ApplicationException(_t('Exporter mapping %1 does not exist', $exporter_code));
		}
		
	    $a_to_z = range('A', 'Z');
		
	    $workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	    $o_sheet = $workbook->getActiveSheet();
	    $columntitlestyle = array(
			'font'=>array(
					'name' => 'Arial',
					'size' => 12,
					'bold' => true),
			'alignment'=>array(
					'horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
					'vertical'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
					'wrap' => true,
					'shrinkToFit'=> true),
			'borders' => array(
					'allborders'=>array(
							'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)));
        $cellstyle = array(
                'font'=>array(
                        'name' => 'Arial',
                        'size' => 11,
                        'bold' => false),
                'alignment'=>array(
                        'horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrap' => true,
                        'shrinkToFit'=> true),
                'borders' => array(
                        'allborders'=>array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));

        $o_sheet->getParent()->getDefaultStyle()->applyFromArray($cellstyle);
        $o_sheet->setTitle(substr($exporter_code, 0, 31));

        $col = 0;
        $line = 1;
        foreach(["Rule type","ID","Parent ID","Element","Source","Options","Notes"] as $h) {
            $o_sheet->setCellValue($a_to_z[$col].$line,$h);
            $o_sheet->getStyle($a_to_z[$col].$line)->applyFromArray($columntitlestyle);
            $col++;
        }
        
        
        // Settings
        if (!is_array($settings = $exporter->getSettings())) { 
            $settings = [];
        }
        $settings['code'] = $exporter->get('exporter_code');
        $settings['name'] = $exporter->getLabelForDisplay();
        $settings['table'] = Datamodel::getTableName($exporter->get('table_num'));
        $line++;
        foreach($settings as $k => $v) {
            $o_sheet->setCellValue($a_to_z[0].$line, "Setting");
            $o_sheet->setCellValue($a_to_z[1].$line, $k);
            $o_sheet->setCellValue($a_to_z[2].$line, $v);
            $line++;
        }
        
        // Mappings
        if (is_array($items = $exporter->getItems())) {
            $ids = [];
            foreach($items as $item) {
                $item_settings = caUnserializeForDatabase($item['settings']);
                $id = $item_settings['_id'];
                unset($item_settings['_id']);
                
                $line++;
                $source = $item['source'];
                
                if (preg_match("!^_CONSTANT_:!", $source)) {
                    $o_sheet->setCellValue($a_to_z[0].$line, "Constant");
                    $source = preg_replace("!^_CONSTANT_:!", "", $item['source']);
                } else {
                    $o_sheet->setCellValue($a_to_z[0].$line, "Mapping");
                    $source = $item['source'];
                }
                $ids[$item['item_id']] = $id;
                $parent_id = isset($ids[$item['parent_id']]) ? $ids[$item['parent_id']] : '';
                $o_sheet->setCellValue($a_to_z[1].$line, $id);
                $o_sheet->setCellValue($a_to_z[2].$line, $parent_id);
                $o_sheet->setCellValue($a_to_z[3].$line, $item['element']);
                $o_sheet->setCellValue($a_to_z[4].$line, $source);
                $o_sheet->setCellValue($a_to_z[5].$line, (is_array($item_settings) && sizeof($item_settings)) ? json_encode($item_settings) : '');
            }
        }
        
        // set column width to auto for all columns where we haven't set width manually yet
        foreach(range('A','Z') as $c) {
            if ($o_sheet->getColumnDimension($c)->getWidth() == -1) {
                $o_sheet->getColumnDimension($c)->setAutoSize(true);	
            }
        }
        
        $o_writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook);
 	    $o_writer->save($file);
        return true;   
	}
	# ------------------------------------------------------
	/**
	 * Get name for exporter target table
	 * @return null|string
	 */
	public function getTargetTableName() {
		if(!$this->getPrimaryKey()) { return null; }

		return Datamodel::getTableName($this->get('table_num'));
	}
	# ------------------------------------------------------
	/**
	 * Get instance for exporter target table
	 * @return BaseModel|null
	 */
	public function getTargetTableInstance() {
		if(!$this->getPrimaryKey()) { return null; }

		return Datamodel::getInstance($this->get('table_num'));
	}
	# ------------------------------------------------------
	/**
	 * Get instance of export format writer
	 *
	 * @param string $format Code for supported format. Valid values are XML, MARC, CSV, ExifTool, JSON, CTDA.
	 *
	 * @return BaseExportFormat Export format writer instance, or null if invalid format is specified.
	 */
	public static function getExportFormatInstance(string $format) : ?BaseExportFormat {
		// TODO: we may want to auto-load this
		switch(strtoupper($format)) {
			case 'XML':
				$o_export = new ExportXML();
				break;
			case 'MARC':
				$o_export = new ExportMARC();
				break;
			case 'CSV':
				$o_export = new ExportCSV();
				break;
			case 'EXIFTOOL':
				$o_export = new ExportExifTool();
				break;
			case 'JSON':
				$o_export = new ExportJSON();
				break;
			case 'CTDA':
				$o_export = new ExportCTDA();
				break;
			default:
				return null;
		}

		return $o_export;
	}
	# ------------------------------------------------------
	/**
	 * Get file extension for downloadable files, depending on the format
	 *
	 * @return string
	 */
	public function getFileExtension() : ?string {
		if($o_export = self::getExportFormatInstance($this->getSetting('exporter_format'))) {
			return $o_export->getFileExtension($this->getSettings());
		}
		return null;
	}
	# ------------------------------------------------------
	/**
	 * Get content type for downloadable file
	 *
	 * @return string
	 */
	public function getContentType() : ?string {
		if($o_export = self::getExportFormatInstance($this->getSetting('exporter_format'))) {
			return $o_export->getContentType($this->getSettings());
		}
		return null;
	}
	# ------------------------------------------------------
	/**
	 *
	 */
	private function getVariableValues($t_instance, array $tags, string $context) {
		$vars = [];
		if(is_array($tags)) {
			foreach($tags as $tag) {
				$va_v = $t_instance->get(($context ? "{$context}." : '').$tag, ['convertCodesToIdno' => true, 'returnAsArray' => true]);
				$vars[$tag] = ca_data_exporters::$s_variables[$tag] = $va_v[0];
			}
		}
		return $vars;
	}
	# ------------------------------------------------------
}
