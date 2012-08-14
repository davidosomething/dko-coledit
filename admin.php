<?php
/**
 * admin.php
 * Admin-side-specific code
 */

class DKOColEdit_Admin extends DKOColEdit
{

  /**
   * __construct
   *
   * @return void
   */
  public function __construct() {
    parent::__construct(); // run plugin.php's construct
    add_action('admin_menu',            array(&$this, 'admin_menu'));
    add_action('admin_init',            array(&$this, 'admin_init'));
    add_action('admin_print_styles',    array(&$this, 'admin_print_styles'));
    add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
    add_action('admin_footer',          array(&$this, 'admin_footer'));

    $types_to_add_columns_to = array('posts', 'pages');
    foreach ($types_to_add_columns_to as $type) {
      add_action('manage_' . $type . '_columns',        array(&$this, 'add_column')); // add new columns to array of columns
      add_action('manage_' . $type . '_custom_column',  array(&$this, 'column_html_callback'), 10, 2);  // define contents of new columns
    }

    add_action('wp_ajax_DKOColEdit_get_post_custom', array(&$this, 'get_post_custom'));
    add_action('wp_ajax_DKOColEdit_get_post_custom_keys', array(&$this, 'get_post_custom_keys'));
    add_action('wp_ajax_DKOColEdit_get_post_custom_keys_starting_with', array(&$this, 'get_post_custom_keys_starting_with'));
    add_action('wp_ajax_DKOColEdit_get_post_custom_values', array(&$this, 'get_post_custom_values'));
    add_action('wp_ajax_DKOColEdit_delete_custom_value', array(&$this, 'delete_custom_value'));
    add_action('wp_ajax_DKOColEdit_update_custom_value', array(&$this, 'update_custom_value'));
    add_action('wp_ajax_DKOColEdit_insert_custom_value', array(&$this, 'insert_custom_value'));

  } // __construct()


  /**
   * render
   * Output a template file from the views folder
   *
   * @param string $template Name of file without php extension
   * @param array $data Associative array of data to pass to the template
   * @return void
   */
  public function render($template, $data) {
    $filepath = __DIR__.'/views/'.$template.'.php';
    if (file_exists($filepath)) include $filepath;
  }


  /**
   * get_post_custom
   * AJAX call this to get a multidimensional array of custom field data
   *
   * @return void
   */
  public function get_post_custom() {
    var_dump($_POST);
    die(); // this is required to return a proper result
  }


  /**
   * get_post_custom_keys
   * AJAX call this to get the keys
   *
   * @return void
   */
  public function get_post_custom_keys() {
    $keys = get_post_custom_keys($_POST['post_id']);
    echo json_encode($keys);
    die(); // this is required to return a proper result
  }


  /**
   * get_post_custom_values
   * AJAX call this to get the keys
   *
   * @return void
   */
  public function get_post_custom_values() {
    $values = get_post_meta($_POST['post_id'], $_POST['key']);
    if ($values) {
      echo json_encode(array('values' => $values));
    }
    else {
      echo json_encode(array());
    }
    die(); // this is required to return a proper result
  }


  /**
   * get_post_custom_keys
   * AJAX call this to get the keys
   *
   * @return void
   */
  public function get_post_custom_keys_starting_with() {
    if ($_POST['q'] == '*') {
      $this->get_post_custom_keys();
    }

    $keys = get_post_custom_keys($_POST['post_id']);

    $found_keys = array();
    foreach ($keys as $key) {
      if (strpos($key, $_POST['q']) === 0) {
        $found_keys[] = $key;
      }
    }

    echo json_encode($found_keys);
    die(); // this is required to return a proper result
  }


  /**
   * delete_custom_value
   * AJAX call this to get the keys
   *
   * @return void
   */
  public function delete_custom_value() {
    // make sure a value is set! If value is blank ALL values for this key
    // will be deleted!
    if (!isset($_POST['value'])) return;
    $_POST['value'] = trim($_POST['value']);
    if (empty($_POST['value'])) return;

    delete_post_meta($_POST['post_id'], $_POST['key'], $_POST['value']);
    die(); // this is required to return a proper result
  }


  /**
   * update_custom_value
   * AJAX call this to get the keys
   *
   * @return void
   */
  public function update_custom_value() {
    if (!empty($_POST['original'])) {
      update_post_meta($_POST['post_id'], $_POST['key'], $_POST['value'], $_POST['original']);
    }
    else {
      update_post_meta($_POST['post_id'], $_POST['key'], $_POST['value']);
    }
    echo json_encode($_POST);
    die(); // this is required to return a proper result
  }


  /**
   * insert_custom_value
   * AJAX call this to get the keys
   *
   * @return void
   */
  public function insert_custom_value() {
    add_post_meta($_POST['post_id'], $_POST['key'], $_POST['value']);
    die(); // this is required to return a proper result
  }


  public function admin_menu() {
  }


  public function admin_init() {
  }


  public function admin_print_styles() {
    wp_enqueue_style('select2', plugin_dir_url(__FILE__).'/js/lib/select2/select2.css');
    wp_enqueue_style(DKOColEdit::slug, plugin_dir_url(__FILE__).'/css/admin.css');
  } // admin_print_styles()


  public function admin_enqueue_scripts($hook) {

    // only load the JavaScript on the listing page
    if ($hook === 'edit.php') {

      wp_enqueue_script('jquery-color');

      // for all scripts (except where explicitly overridden)
      $in_footer    = true;
      $version      = null;

      //
      // ICanHaz.js for templating
      //
      $scriptname   = 'ICanHaz';
      $filepath     = plugin_dir_url(__FILE__).'/js/lib/ICanHaz/ICanHaz.min.js';
      $dependencies = array();
      wp_enqueue_script($scriptname, $filepath, $dependencies, $version, $in_footer);

      //
      // Select2 plugin
      //
      $scriptname   = 'select2';
      $filepath     = plugin_dir_url(__FILE__).'/js/lib/select2/select2.js';
      $dependencies = array('jquery');
      wp_enqueue_script($scriptname, $filepath, $dependencies, $version, $in_footer);

      //
      // My JS
      //
      $scriptname   = DKOColEdit::slug;
      $filepath     = plugin_dir_url(__FILE__).'/js/admin-edit.js';
      $dependencies = array('ICanHaz', 'select2', 'jquery-color');
      wp_enqueue_script($scriptname, $filepath, $dependencies, DKOColEdit::version, $in_footer);


    }

  } // admin_enqueue_scripts()


  /**
   * add_column
   * Modify the array of columns to show on the post listing page edit.php
   *
   * @param array $columns
   * @return array $columns
   */
  public function add_column($columns) {
    $columns[DKOColEdit::slug.'_custom_fields'] = "Custom Fields";
    return $columns;
  } // add_column()


  /**
   * column_html_callback
   * Output HTML for the post listing page edit.php
   *
   * @param string $column_name
   * @param int $post_id
   * @return void
   */
  public function column_html_callback($column_name, $post_id) {
    if ($column_name !== DKOColEdit::slug.'_custom_fields') return;
    $this->render('edit-column-collapsed', array('post_id' => $post_id));
  } // column_html_callback()


  /**
   * admin_footer
   * Callback for admin_footer hook
   *
   * @return void
   */
  public function admin_footer() {

    //
    // load ICH templates
    //
    echo '<script id="DKOColEditFieldTemplate" type="text/html">';
    $this->render('ich-edit-field', array());
    echo '</script>';
    echo '<script id="DKOColAddFieldTemplate" type="text/html">';
    $this->render('ich-add-field', array());
    echo '</script>';


  } // admin_footer()



} // DKOColEdit_Admin
