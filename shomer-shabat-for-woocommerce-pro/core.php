<?php
/*
 * Plugin Name: Shomer Shabat for WooCommerce Pro
 * Plugin URI: https://www.web.com
 * Description: Sell products and keep the shabat
 * Version: 1.0.1
 * Author: Anastasia Gatsenko
 * Author URI: https://www.web.com
 * Requires wordpress at least: 5.3
 */


add_action('wp_enqueue_scripts','script_init');

function script_init() {
    wp_enqueue_script('jquery');
}

if ( !defined( 'ABSPATH' ) ) exit;

// Act on plugin activation
register_activation_hook( __FILE__, "activate_myplugin" );

// Act on plugin de-activation
register_deactivation_hook( __FILE__, "deactivate_myplugin" );

// Activate Plugin
function activate_myplugin() {

	// Execute tasks on Plugin activation

	// Insert DB Tables
	init_db_myplugin();
}

// De-activate Plugin
function deactivate_myplugin() {

  $table_name = $wpdb->prefix . "disable_add_to_cart";

  $sql = "DROP TABLE IF EXISTS $table_name";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );

  //  Calendar method

  $calendar_table_name = $wpdb->prefix . "calendar_disable_add_to_cart";

  $calendar_sql = "DROP TABLE IF EXISTS $calendar_table_name";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $calendar_sql );
}

// Initialize DB Tables
function init_db_myplugin() {

  global $wpdb;
  $table_name = $wpdb->prefix . "disable_add_to_cart";
  $charset_collate = $wpdb->get_charset_collate();

  if( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

  $sql = "CREATE TABLE $table_name (
  		  id mediumint(9) NOT NULL AUTO_INCREMENT,
  		  from_day varchar(255) NULL,
        from_hours varchar(255) NULL,
        from_minutes varchar(255) NULL,
        to_day varchar(255) NULL,
        to_hours varchar(255) NULL,
        to_minutes varchar(255) NULL,
        phrase varchar(255) NULL,
        from_offset varchar(255) NULL,
        to_offset varchar(255) NULL,
  		  UNIQUE KEY id (id)
  		) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

  //  Calendar method

  $calendar_table_name = $wpdb->prefix . "calendar_disable_add_to_cart";
  if( $wpdb->get_var( "show tables like '$calendar_table_name'" ) != $calendar_table_name ) {

  $calendar_sql = "CREATE TABLE $calendar_table_name (
  		  id mediumint(9) NOT NULL AUTO_INCREMENT,
        active_method varchar(255) NULL,
  		  start_date varchar(255) NULL,
        finish_date varchar(255) NULL,
  		  UNIQUE KEY id (id)
  		) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $calendar_sql );
  }
}


// Admin part of plugin

 add_action('admin_menu', 'plugin_setup_menu');

function plugin_setup_menu(){
    add_menu_page( 'Shomer Shabat for WooCommerce', 'Shomer Shabat for WooCommerce', 'manage_options', 'disable-add-to-cart-plugin', 'plugin_init' );


}

// Ajax action


add_action( 'wp_ajax_delete_range', 'delete_range' );
add_action( 'wp_ajax_nopriv_delete_range', 'delete_range' );
add_action( 'wp_ajax_handle_request', 'handle_request' );
add_action( 'wp_ajax_nopriv_handle_request', 'handle_request' );

function handle_request() {
  global $wpdb;
  $table_name = $wpdb->prefix . "disable_add_to_cart";
  $myData = $_POST['all_data'];
  for ($i = 0; $i < count($myData); $i++) {
    $row_id = strval($myData[$i]["id"]);
    $checkIfExists = $wpdb->get_var("SELECT ID FROM $table_name WHERE id = $row_id");

    $data = array(
      "from_day" => $myData[$i]["from_day"],
      "from_hours" => $myData[$i]["from_hours"],
      "from_minutes" => $myData[$i]["from_minutes"],
      "to_day" => $myData[$i]["to_day"],
      "to_hours" => $myData[$i]["to_hours"],
      "to_minutes" => $myData[$i]["to_minutes"],
      "phrase" => $myData[$i]["phrase"],
      "from_offset" => $myData[$i]["from_offset"],
      "to_offset" => $myData[$i]["to_offset"]
    );
    if ($checkIfExists == NULL) {
      $wpdb->insert($table_name, $data);
    }else{
      $wpdb->update($table_name, $data, array('id'=>$row_id));
    }
  }

  //  Calendar method

  $calendar_table_name = $wpdb->prefix . "calendar_disable_add_to_cart";
  $calendar_myData = $_POST['calendar_data'];
  for ($i = 0; $i < count($calendar_myData); $i++) {
    $calendar_row_id = strval($calendar_myData[$i]["id"]);
    $calendar_checkIfExists = $wpdb->get_var("SELECT ID FROM $calendar_table_name WHERE id = $calendar_row_id");
    $calendar_data = array(
      "active_method" => $calendar_myData[$i]["active_method"],
      "start_date" => $calendar_myData[$i]["start_date"],
      "finish_date" => $calendar_myData[$i]["finish_date"]
    );
    if ($calendar_checkIfExists == NULL) {
      $wpdb->insert($calendar_table_name, $calendar_data);
    }else{
      $wpdb->update($calendar_table_name, $calendar_data, array('id'=>$calendar_row_id));
    }
  }


}


function delete_range(){
  global $wpdb;
  $table_name = $wpdb->prefix . "disable_add_to_cart";
  $row_id = $_POST['row_id'];
  $wpdb->delete( $table_name, array( 'id' => $row_id ) );
}

function my_admin_scripts() {
  $localize = array(
      'ajaxurl' => admin_url( 'admin-ajax.php' )
  );
  wp_enqueue_script( 'ajax-script', plugin_dir_url( __FILE__ ) . '/ajax.js', array( 'jquery' ) );
  wp_localize_script( 'ajax-script', 'ajax_script', $localize);
}
add_action( 'admin_enqueue_scripts', 'my_admin_scripts' );

// Admin frontend

function plugin_init(){

  // Disable add to cart data

  global $wpdb;
  $table_name = $wpdb->prefix . "disable_add_to_cart";
  $num_rows= $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
  $phrase_init = $wpdb->get_var("SELECT phrase FROM $table_name WHERE id = '1'");
//  Calendar method
  $calendar_table_name = $wpdb->prefix . "calendar_disable_add_to_cart";
  $calendar_num_rows= $wpdb->get_var("SELECT COUNT(*) FROM $calendar_table_name");
  $active_method = $wpdb->get_var("SELECT active_method FROM $calendar_table_name WHERE id = '1'");
    ?>
<style type="text/css">
.rtl #range_form p.submit {
  text-align: left;
}
.rtl #wpbody {
  direction: ltr;
  padding: 0 20px;
}
.rtl #wpcontent {
  margin-right: 160px;
  margin-left: 0;
}
.wp-core-ui .notice.is-dismissible.disable_success {
  margin-left: 0px;
  height: 0;
  opacity: 0;
}

.wp-core-ui .notice.is-dismissible.disable_success.active {
  height: auto;
  opacity: 1;
}

.range_wrap{
  display: flex;
  align-items: center;
  margin: 10px 0;
  padding: 10px;
  border-bottom: 1px solid #cfcfcf;
  width: fit-content;
}
.range_wrap .dashicons, .range_wrap .dashicons-before:before {
  text-decoration: none;
}

.range_wrap > div:nth-child(1) {
  /* max-width: 250px; */
  width: 100%;

}

.range_wrap > div:nth-child(2) {
  margin-left: 20px;
}

.range_wrap > div:nth-child(1) > div {
  padding: 6px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.range_wrap > div:nth-child(1) div {
  display: flex;
}
.range_wrap select,
.range .calendar_date {
  /* margin: 0 8px; */
}

.range .calendar_date {
  max-width: 200px;
  width: 100%;
}

.range .calendar_wrap {
  max-width: 300px;
}

.range .calendar_wrap label {
  margin: 6px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.range.calendar .week_method,
.range.calendar .shabbat_method,
.range.shabbat .week_method,
.range.week .calendar_method,
.range.week .shabbat_method,
.range.shabbat .calendar_method{
  display: none;
  cursor: not-allowed;
  position: relative;
  /* display: inline-block; */
  width: 300px;
}
.range.calendar .week_method::after,
.range.week .calendar_method::after,
.range.shabbat .calendar_method::after,
.range.shabbat .week_method::after{
  display: none;
  content: "";
  cursor: not-allowed;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgb(240 240 241 / 60%);
}
.range .label_wrap label{
  display: inline-block;
  width: 100%;
}

.choose_method {
  margin: 4px 0;
}

</style>

<div id="setting-error-settings_updated" class="disable_success notice notice-success settings-error is-dismissible">
<p><strong>Settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>

<form method='post' action='' id="range_form">
  <h2>Choose the range from what day you want to disable "Add to cart" button</h2>
  <br>
  <div class="range calendar">
    <div class="label_wrap" data-method="<?php echo $active_method; ?>">
      <label class="choose_method label_calendar"><input type="radio" name="method" value="calendar"> Calendar method </label>
      <label class="choose_method label_week_range"><input type="radio" name="method" value="week"> Week range method </label>
      <label class="choose_method label_week_range"><input type="radio" name="method" value="shabbat"> Shabbat method </label>
    </div>
    <?php
  if ( $calendar_num_rows > 0) {?>
    <div class="calendar_method">
      <h4>Choose date and time from calendar</h4>
      <?php
    $calendar_result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $calendar_table_name"));
      if ( ! empty( $calendar_result ) ) {
          foreach($calendar_result as $row) {
            $row_id = $row->id;
            $start_date = $row->start_date;
            $finish_date = $row->finish_date;  ?>
              <div class="calendar_wrap" data-row_id="<?php echo $row_id; ?>">
                <div>
                  <div>
                      <label>Start date: <input type="datetime-local" name="calendar-start[]" class="calendar_date start_date" data-calendar_id="<?php echo $row_id; ?>" value="<?php echo $start_date; ?>"></label>
                  </div>
                  <div>
                      <label>Finish date: <input type="datetime-local" name="calendar-finish[]" class="calendar_date finish_date" data-calendar_id="<?php echo $row_id; ?>" value="<?php echo $finish_date; ?>"></label>
                  </div>
                </div>
              </div>
          <?php
          }
      }?>
  </div>
  <?php
}else{?>
    <div class="calendar_method">
      <h4>Choose date and time from calendar</h4>
      <div class="calendar_wrap" data-row_id="1">
        <div>
          <div>
              <label>Start date: <input type="datetime-local" name="calendar-start[]" class="calendar_date start_date" data-calendar_id="1"></label>
          </div>
          <div>
              <label>Finish date: <input type="datetime-local" name="calendar-finish[]" class="calendar_date finish_date" data-calendar_id="1"></label>
          </div>
        </div>
      </div>
    </div>
    <?php
    }
if ( $num_rows > 0) {?>
  <div class="week_method">
    <h4>Choose date of the week and time</h4>
    <?php
  $result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name"));
  $first_row = $wpdb->get_var("SELECT MIN(id) FROM $table_name");
  $last_row = $wpdb->get_var("SELECT MAX(id) FROM $table_name");
    if ( ! empty( $result ) ) {
        foreach($result as $row) {
          $row_id = $row->id;
          $from_day_init = $row->from_day;
          $to_day_init = $row->to_day;
          $from_hours_init = $row->from_hours;
          $from_minutes_init = $row->from_minutes;
          $to_hours_init = $row->to_hours;
          $to_minutes_init = $row->to_minutes;
          $from_offset = $row->from_offset;
          $to_offset = $row->to_offset;
          $from_time_init = $from_hours_init .":". $from_minutes_init;
          $to_time_init = $to_hours_init .":". $to_minutes_init;
?>

          <div class="range_wrap <?php if ($first_row==$row_id){echo "first_range";} ?>" data-row_id="<?php echo $row_id; ?>">
            <div>
              <div>From:
                <div>
                  <select class="from_day" name="from_day[]" data-selected="<?php if($from_day_init){echo $from_day_init;}?>">
                    <option value="0">Sunday</option>
                    <option value="1">Monday</option>
                    <option value="2">Tuesday</option>
                    <option value="3">Wednesday</option>
                    <option value="4">Thursday</option>
                    <option value="5">Friday</option>
                    <option value="6">Saturday</option>
                  </select>
                  <input type="time" class="from_time" name="from_time[]"  <?php if($from_time_init){ ?> value="<?php echo $from_time_init; ?>" <?php }?>>
                </div>
              </div>
              <div>To:<div>
                <select class="to_day" name="to_day[]" data-selected="<?php if($to_day_init){echo $to_day_init;}?>">
                  <option value="0">Sunday</option>
                  <option value="1">Monday</option>
                  <option value="2">Tuesday</option>
                  <option value="3">Wednesday</option>
                  <option value="4">Thursday</option>
                  <option value="5">Friday</option>
                  <option value="6">Saturday</option>
                </select>
                <input type="time" class="to_time" name="to_time[]"  <?php if($to_time_init){ ?> value="<?php echo $to_time_init; ?>" <?php }?>>
              </div>
            </div>
          </div>
          <?php if ($first_row==$row_id): ?>
            <div>
              <a href="#" class="clear_range" data-row_id="1">
                <span class="dashicons dashicons-undo"></span>
              </a>
            </div>
          <?php endif; ?>
          <?php if ($first_row!==$row_id): ?>
            <div>
              <a href="#" class="delete_range" data-row_id="<?php echo $row_id; ?>">
                <span class="dashicons dashicons-trash"></span>
              </a>
            </div>
          <?php endif; ?>
        </div>
        <?php if ($last_row==$row_id): ?>
          <p><a href="#" class="button button-secondary add_new_range">Add new range</a></p>
        <?php endif;?>
        <?php
        }
    }?>
</div>
<?php
}else{?>
<div class="calendar_method">
  <h4>Choose date and time from calendar</h4>
  <div class="calendar_wrap" data-row_id="0">
    <div>
      <div><label>Start date:</label>
        <div>
          <input type="date" name="calendar-start[]" class="calendar_date start_date" data-calendar_id="0">
          <input type="time" class="from_time" name="from_time[]" >
        </div>
      </div>
      <div><label>Finish date:</label>
        <div>
          <input type="date" name="calendar-finish[]" class="calendar_date finish_date" data-calendar_id="0">
          <input type="time" class="to_time" name="to_time[]" >
        </div>
      </div>
    </div>
  </div>
</div>
<div class="week_method">
  <h4>Choose date of the week and time</h4>
  <div class="range_wrap first_range" data-row_id="1">
    <div>
      <div>From:
        <div>
          <select class="from_day" name="from_day[]" data-selected="">
            <option value="0">Sunday</option>
            <option value="1">Monday</option>
            <option value="2">Tuesday</option>
            <option value="3">Wednesday</option>
            <option value="4">Thursday</option>
            <option value="5">Friday</option>
            <option value="6">Saturday</option>
          </select>
          <input type="time" class="from_time" name="from_time[]" >
        </div>
      </div>
      <div>To:<div>
        <select class="to_day" name="to_day[]" data-selected="">
          <option value="0">Sunday</option>
          <option value="1">Monday</option>
          <option value="2">Tuesday</option>
          <option value="3">Wednesday</option>
          <option value="4">Thursday</option>
          <option value="5">Friday</option>
          <option value="6">Saturday</option>
        </select>
        <input type="time" class="to_time" name="to_time[]" >
      </div>
    </div>
  </div>
  <div>
    <a href="#" class="clear_range" data-row_id="1">
      <span class="dashicons dashicons-undo"></span>
    </a>
  </div>
</div>
<p><a href="#" class="button button-secondary add_new_range">Add new range</a></p>
</div>
<?php }

    $from_offset = $wpdb->get_var("SELECT from_offset FROM $table_name WHERE id = '1'");
    $to_offset = $wpdb->get_var("SELECT to_offset FROM $table_name WHERE id = '1'");

?>
<div class="shabbat_method">
  <h4>Choose the time when Shabbat starts / ends</h4>
  <div class="calendar_wrap" data-row_id="1">
    <div>
      <div>
          <label>Start hours before sunset: <input type="number" min="0" max="24" <?php if($from_offset){ ?> id="has_value" value="<?php echo $from_offset; ?>" <?php }?> class="from_offset" name="from_offset" ></label>
      </div>
      <div>
          <label>Finish hours after sunset: <input type="number" min="0" max="24" <?php if($to_offset){ ?> id="has_value" value="<?php echo $to_offset; ?>" <?php }?>  class="to_offset" name="to_offset" ></label>
      </div>
    </div>
  </div>
</div>


</div>
<p>What do you want to write instead of "Add to cart"</p>
  <input type="text" name="phrase" class="phrase" placeholder="Type here..." value="<?php echo $phrase_init; ?>">
  <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
</form>
<div id="results"></div>
    <?php
}

    function get_sunset_time(DateTime $day, float $lat, float $lng) {
        // Get sun information
        $sun_info = date_sun_info($day->getTimestamp(), $lat, $lng);

        foreach ($sun_info as $key => $val) {
            $time = new DateTime('@' . $val);
            $time->setTimezone($day->getTimeZone());
            $sun_info[$key] = $time->format('m/d/Y h:i a');
        }

        $sunset_time = new DateTime($sun_info['sunset']);
        return $sunset_time;
    }

    function what_weekday(DateTime $today) {
            return $today->format('N');
        }

    function is_during_biblical_day(DateTime $day_starts, DateTime $day_ends) {
        // Right now
        $now = new DateTime();

        if ($day_starts < $now) {
            // We know the day has started
            if ($day_ends < $now) {
                // It is after the day now
                return false;
            } else {
                // It is still during the day
                return true;
            }
        } else if ($day_starts > $now) {
            // It is before the day
            return false;
        } else {
            // This should never happen... :)
            return false;
        }
    }

    function get_day_begins_ends(DateTime $prior_day, DateTime $day, float $lat, float $lng, int $from_offset, int $to_offset) {

        // Calculate sunrise and sunset
        $prior_day_sunset = get_sunset_time($prior_day, $lat, $lng);
        if ($from_offset != 0)
        $prior_day_sunset->sub(new DateInterval("PT{$from_offset}H"));
        $day_sunset = get_sunset_time($day, $lat, $lng);
        if ($to_offset != 0)
        $day_sunset->add(new DateInterval("PT{$to_offset}H"));
        //echo "<p>Day will begin at {$prior_day_sunset->format('m/d/Y h:i a')} </p>";
        //echo "<p>Day will end at {$day_sunset->format('m/d/Y h:i a')} </p>";

        return array(
            'begins' => $prior_day_sunset,
            'ends' => $day_sunset
        );
    }

    function get_is_sabbath(string $weekday, float $lat, float $lng, int $from_offset, int $to_offset) {

         $rev_sabbath_day = new DateTime();
         $sabbath_day = new DateTime();

         // The 6th day (rev Sabbath, the evening when the Sabbath starts).
         if ($weekday == '5') {
             $sabbath_day->add(new DateInterval('P1D'));
         // The 7th day (the Sabbath)
         } else if ($weekday == '6') {
             $rev_sabbath_day->sub(new DateInterval('P1D'));
         }

         $sabbath_times = get_day_begins_ends($rev_sabbath_day, $sabbath_day, $lat, $lng, $from_offset, $to_offset);
         return is_during_biblical_day($sabbath_times['begins'], $sabbath_times['ends']);
    }

    function is_sabbath(float $lat, float $lng, int $from_offset, int $to_offset) {

            // See if it is currently the 6th or 7th day (Fri. or Sat.)
            $now = new DateTime();
            $weekday = what_weekday($now);

            // This is a performance optimization:
            // It will only calculate the Sabbath times if it
            // is near the Sabbath day (rev-Shabbat) or the day-of.
            if ($weekday == '5' || $weekday == '6') {
                return get_is_sabbath($weekday, $lat, $lng, $from_offset, $to_offset);
            } else {
                return $is_sabbath_value = false;
            }
        }

    function is_holy_day($holy_days, float $lat, float $lng) {
              foreach ($holy_days as $day) {
                  $rev_day = clone $day;
                  $rev_day->sub(new DateInterval('P1D'));
                  $holy_day_times = get_day_begins_ends($rev_day, $day, $lat, $lng);
                  if (is_during_biblical_day($holy_day_times['begins'], $holy_day_times['ends'])) {
                      return true;
                  }
              }
              return false;
          }

    function is_sabbath_or_holy_day($holy_days, float $lat, float $lng) {
        $is_sabbath_day = is_sabbath($lat, $lng);

        if ($is_sabbath_day == true) {
            return true;
        } else {
            $is_holy_day = is_holy_day($holy_days, $lat, $lng);
            return $is_holy_day;
        }
    }

function myscript() {

  // Disable add to cart data

  global $wpdb;
  $table_name = $wpdb->prefix . "disable_add_to_cart";
  $num_rows= $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
  $phrase_init = $wpdb->get_var("SELECT phrase FROM $table_name WHERE id = '1'");
  $from_offset_db = $wpdb->get_var("SELECT from_offset FROM $table_name WHERE id = '1'");
  $to_offset_db = $wpdb->get_var("SELECT to_offset FROM $table_name WHERE id = '1'");
  if ($from_offset_db) $from_offset = (int)$from_offset_db; else $from_offset = 0;
  if ($to_offset_db) $to_offset = (int)$to_offset_db; else $to_offset = 0;
  // calendar method
  $calendar_table_name = $wpdb->prefix . "calendar_disable_add_to_cart";
  $calendar_num_rows= $wpdb->get_var("SELECT COUNT(*) FROM $calendar_table_name");
  $active_method = $wpdb->get_var("SELECT active_method FROM $calendar_table_name WHERE id = '1'");
  $is_sabbath = is_sabbath(31.771959, 35.217018, $from_offset, $to_offset);
  if ($is_sabbath) $sabbath = 'true'; else $sabbath = 'false';
  ?>
  <div id="disable_add_to_cart_data" style="display: none;">
    <p class="data-active_method"><?php echo $active_method; ?></p>
    <p class="data-is_sabbath"><?php echo $sabbath; ?></p>
    <p class="data-from_offset"><?php echo $from_offset; ?></p>
    <p class="data-to_offset"><?php echo $to_offset; ?></p>
    <!-- Calendar method -->
    <?php for ($i = 0; $i < $calendar_num_rows; $i++) {
    $calendar_row_id = strval($i+1);
    $start_date = $wpdb->get_var("SELECT start_date FROM $calendar_table_name WHERE id = $calendar_row_id");
    $finish_date = $wpdb->get_var("SELECT finish_date FROM $calendar_table_name WHERE id = $calendar_row_id");



  ?>
    <p class="data-start_date"><?php echo $start_date; ?></p>
    <p class="data-finish_date"><?php echo $finish_date; ?></p>


  <?php } ?>

  <!-- Week method -->

  <?php for ($i = 0; $i < $num_rows; $i++) {
  $row_id = strval($i+1);
  $from_day_init = $wpdb->get_var("SELECT from_day FROM $table_name WHERE id = $row_id");
  $to_day_init = $wpdb->get_var("SELECT to_day FROM $table_name WHERE id = $row_id");

  $from_hours_init = $wpdb->get_var("SELECT from_hours FROM $table_name WHERE id = $row_id");
  $from_minutes_init = $wpdb->get_var("SELECT from_minutes FROM $table_name WHERE id = $row_id");
  $to_hours_init = $wpdb->get_var("SELECT to_hours FROM $table_name WHERE id = $row_id");
  $to_minutes_init = $wpdb->get_var("SELECT to_minutes FROM $table_name WHERE id = $row_id");

?>
  <p class="data-from-day"><?php echo $from_day_init; ?></p>
  <p class="data-to-day"><?php echo $to_day_init; ?></p>
  <p class="data-phrase"><?php echo $phrase_init; ?></p>
  <p class="data-from-hours"><?php echo $from_hours_init; ?></p>
  <p class="data-from-minutes"><?php echo $from_minutes_init; ?></p>
  <p class="data-to-hours"><?php echo $to_hours_init; ?></p>
  <p class="data-to-minutes"><?php echo $to_minutes_init; ?></p>

<?php } ?>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="text/javascript">
     jQuery(document).ready(function($){
       if($(".add_to_cart_button")||$(".single_add_to_cart_button")){
         let phrase = $('.data-phrase').text();
         function disable_add_to_cart(){
           console.log("Add to cart disabled");
           $(".add_to_cart_button").css("display","none");
           $(".single_add_to_cart_button").css("display","none");
           if (phrase.length>0)
           {
           $( `<p>${phrase}</p>`).insertAfter(".add_to_cart_button");
           $( `<p>${phrase}</p>`).insertAfter(".single_add_to_cart_button");
           }
         }
        if ($(".data-active_method").text() == "week") {
          for (let i = 0; i < $('.data-from-day').length; i++) {
             let from_day = $($('.data-from-day')[i]).text();
             let to_day = $($('.data-to-day')[i]).text();
             let from_hours = $($('.data-from-hours')[i]).text();
             let from_minutes = $($('.data-from-minutes')[i]).text();
             let to_hours = $($('.data-to-hours')[i]).text();
             let to_minutes = $($('.data-to-minutes')[i]).text();

             let date = new Date();
             let day_of_week = date.getDay();
             let current_hour = date.getHours();
             let current_minute = date.getMinutes();
             let range=[];

             if ((from_day == to_day && from_hours==to_hours)||(from_day == to_day && from_hours<to_hours)){
                 range+=parseInt(from_day);
             }
             else if (from_day == to_day && from_hours>to_hours) {
               for(let i=from_day;i<=6;i++)
               {
                 range+=parseInt(i);
               }
               for(let i=0;i<=to_day;i++)
               {
                 range+=parseInt(i);
               }
             }
             else{
              if (from_day < to_day) {
               for(let i=from_day;i<=to_day;i++)
               {
                 range+=parseInt(i);
               }
             }
             else {
               for(let i=from_day;i<=6;i++)
               {
                 range+=parseInt(i);
               }
               for(let i=0;i<=to_day;i++)
               {
                 range+=parseInt(i);
               }
             }
           }

        for(let i=0;i<range.length;i++)  {
          if (day_of_week==range[i]){
            if (i==0 && i==range.length-1){
              if (current_hour==from_hours)
              {
                if(current_minute>=from_minutes)
                {
                  disable_add_to_cart();
                }
              }
              else if (current_hour>from_hours && current_hour<to_hours)
              {
                disable_add_to_cart();
              }
              else if (current_hour==to_hours) {
                if(current_minute<=to_minutes)
                {
                  disable_add_to_cart();
                }
              }
            }
            else if (i==0) //1st day of range, check beginning time
            {
              if (current_hour==from_hours)
              {
                if(current_minute>=from_minutes)
                {
                  disable_add_to_cart();
                }
              }
              else if (current_hour>from_hours)
              {
                disable_add_to_cart();
              }
            }
            else if (i==range.length-1)
            {
              if (current_hour==to_hours)
              {
                if(current_minute<=to_minutes)
                {
                  disable_add_to_cart();
                }
              }
              else if (current_hour<to_hours)
              {
                disable_add_to_cart();
              }
            }
            else {
              disable_add_to_cart();
            }
          }
        }
      }
    }else if($(".data-active_method").text() == "calendar"){
      // Disable add to cart with calendar
      for (let i = 0; i < $('.data-start_date').length; i++) {
         let start_date = $($('.data-start_date')[i]).text();
         let finish_date = $($('.data-finish_date')[i]).text();
         let date = new Date();
         let current_date = date.toISOString();
         console.log(current_date>=start_date, current_date<=finish_date);
           if (current_date>=start_date && current_date<=finish_date) {
            disable_add_to_cart();
           }
       }
    }
    else if($(".data-active_method").text() == "shabbat"){
      if ($(".data-is_sabbath").text() == "true") disable_add_to_cart();
    }
    }
  });

</script>
<?php
}
add_action( 'wp_footer', 'myscript' );

function myscript_jquery() {
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_head' , 'myscript_jquery' );
