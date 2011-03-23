<?php

/*
Plugin Name: Ligue1 Table
Description: Get the latest Ligue1 Table.
Plugin URI: http://www.ligue1classement.com/demo/
Version: 1.0
Author: Gaetano Caruana
Author URI: http://www.ligue1classement.com
*/

define( 'LIGUE1_TABLE_PLUGIN_DIR', WP_PLUGIN_DIR . '/ligue1-table' );
define( 'LIGUE1_TABLE_PLUGIN_URL', plugins_url( $path = '/ligue1-table' ) );

class ligue1_table_class
{
    function ligue1_table_class()
    {
    }

	function get_data_from_url()
	{
		  $url = "http://www.ligue1classement.com/cache.php";
		  $ch = curl_init();
		  $timeout = 5;
		  curl_setopt($ch,CURLOPT_URL,$url);
		  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		  $data = curl_exec($ch);
		  curl_close($ch);
		  
		  //if curl did not return data or simple return 1 - use file_get_contents
		  if (trim($data) == "" || $data == 1)
		  {
		  		$data = file_get_contents("http://www.ligue1classement.com/cache.php");
		  }
		  
		  return $data;
	}


    # initialited only from shortcode or widget
    # -----------------------------------------
    function init()
    {
        $this->options = get_option('ligue1_table_options');

    
        if ( $this->options->last_scraped)
        {
			$diff = time() - $this->options->last_scraped;
			if ($diff > (60)) //every minute
			{
				//do a call since it has been more than an hour since you last checked for data
	        	$this->options->last_scraped = time();
	        	$this->options->ligue1_table_data = $this->get_data_from_url();
    			update_option('ligue1_table_options', $this->options);   			
			}
        }
        else
        {
        	//do the first time call
        	$this->options->last_scraped = time();
        	$this->options->ligue1_table_data = $this->get_data_from_url();
        	$this->options->showlink = 0;
    		update_option('ligue1_table_options', $this->options);
        }
       
    }


    function staticbar()
    {
        global $ligue1_table_class;
    }


    # plugin install && uninstall
    # ---------------------------
    function install()
    {
    }

    function uninstall()
    {
        delete_option('ligue1_table_options');
    }
    
    
       # admin panel options
    # -------------------
    function admin_options()
    {
        add_options_page('Ligue1 table', 'Ligue1 Table', 'manage_options', __FILE__, array($this, 'set_admin_options'));
    }

    function set_admin_options()
    {
        $this->options = get_option('ligue1_table_options');

        if ($_POST['LIGUE1_TABLE_SUBMIT'])
        {
            $this->options->showlink = $_POST['showlink'] ? 1 : 0;

            update_option('ligue1_table_options', $this->options);

            ?>
            <div class="updated"><p>
            Update <b>successful</b>.
            </p></div>
            <?php
        }
    ?>
    <div class="wrap">
    <div id="icon-edit" class="icon32"></div>
    <h2>Ligue1 Table </h2>
    <form name="LIGUE1_FORM" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="LIGUE1_TABLE_SUBMIT" value="1">
    <p></p>
    <label>
    <input name="showlink" class="checkbox" type="checkbox" <?php echo $this->options->showlink ? 'checked' : '' ?>/> Show link to ligue1classement.com.</label><br />
    <p></p>
    <label>
	   	Whilst you are free to switch off this link and continue using our service for free, I would highly appreciate if you keep our link. We have invested time in writing this plugin and will continue to put time into maintaining this service free.
	   	<br />	   	<br />
	   	
	   	Thank you
    <label>
    </div>
    <p class="submit">
    <input type="submit" name="Submit" value="Update Options" />
    </p>
    </form>

    <?php

    }
}
    

# widget stuff
# ------------

class LIGUE1_TABLE_widget_init extends wp_widget
{
    function LIGUE1_TABLE_widget_init()
    {
        $widget_ops = array('classname' => 'ligue1_table_widget', 'description' => __( 'Get the latest Ligue1 Table.', 'ligue1_table_widget2') );
        $this->WP_Widget(false, __('Ligue1 Table', 'ligue1_table_widget2'), $widget_ops);
    }

    function widget($args, $instance)
    {
        global $ligue1_table_class;

        $ligue1_table_class->init();

        extract($args);

        echo $before_widget;
        echo $before_title . $title . $after_title;

		//replace %%IMG_PATH%%
		$data = $ligue1_table_class->options->ligue1_table_data;
		$data = str_replace("%%IMG_PATH%%", LIGUE1_TABLE_PLUGIN_URL."/img", $data);
		echo $data;

		$this->options = get_option('ligue1_table_options');
		
		if ($this->options->showlink)
		{
?>

<table style="text-align:center;margin-top:10px;font-size:12px;width:100%">
<tr>
<td>
<a href="http://www.ligue1classement.com" title="ligue1 classement">Ligue1 Classement</a>
</td>
</tr>
</table>

<?php
		}

        echo $after_widget;
    }

    function update( $new_instance, $old_instance )
    {
        return $new_instance;
    }

    function form( $instance )
    {

    }
}

add_action('widgets_init', 'ligue1_table_widget');
function LIGUE1_TABLE_widget()
{
    register_widget('ligue1_table_widget_init');
}

$ligue1_table_class = new ligue1_table_class();

if ($ligue1_table_class)
{
    register_activation_hook(__file__, array($ligue1_table_class, 'install'));
    register_deactivation_hook(__file__, array($ligue1_table_class, 'uninstall'));
    
    add_action('admin_menu', array($ligue1_table_class, 'admin_options'));    
    
}

?>
