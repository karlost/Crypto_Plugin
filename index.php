<?php
/*
Plugin Name: Crypto
Plugin URI: 
Description: Example Crypto
Author: Bulid
Author URI: 
Version: 0.1


*/




// instalace tabulky pri aktivaci 
register_activation_hook( __FILE__, 'my_plugin_create_db' );
function my_plugin_create_db() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'cryptoticker_pary';

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		crypto varchar(255) NOT NULL,
		fiat varchar(255) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

wp_enqueue_script('jquery');

//menu v administraci 
add_action("admin_menu", "addMenu");
function addMenu()
{
  add_menu_page("Crypto Ticker", "Crypto Ticker", 4, "example-options", "exampleMenu" );
  add_submenu_page("example_options", "Option 1", "Option 1", 4, "example-option-1", "option1");
}

function exampleMenu()
{
   // zapis do db
echo "

  <h2>Nastaveni Crypto Ticker</h2>

  <form method=\"post\" action=\"admin.php?page=example-options\">
  <label for=\"crypto\">Vyber krypto:</label>

<select id=\"crypto\" name=\"crypto\">
  <option value=\"btc\">Bitcoin</option>
  <option value=\"eth\">Ethereum</option>
  <option value=\"ada\">Cardano</option>
  <option value=\"bnb\">Binance Coin</option>
  <option value=\"xrp\">Ripple</option>
  <option value=\"sol\">Solana</option>
  <option value=\"doge\">DogeCoin</option>
  <option value=\"dot\">PolkaDot</option>
</select>
<label for=\"fiat\">Vyber Fiat:</label>

<select id=\"fiat\" name=\"fiat\">
  <option value=\"usd\">Dollar</option>
  <option value=\"eur\">Euro</option>
  <option value=\"JPY\">Jen</option>
  <option value=\"GBP\">Libra</option>
  <option value=\"RUB\">Rubl</option>
  <option value=\"AUD\">aDollar</option>
  
</select>
 
  <input type=\"submit\">
</form>
";
echo "<br><h3>Pro zobrazeni cen je nutné použit Shortcode [current_prices]</h3>";
// zapis do db z administrace 
	
		

				if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
				
				$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
				
				if ($conn->connect_error) {
				  die("Connection failed: " . $conn->connect_error);
				}
				$crypto = $_POST['crypto'];
				$fiat = $_POST['fiat'];
				
				$sql = "INSERT INTO wp_cryptoticker_pary (crypto,fiat)
				VALUES ('$crypto', '$fiat')";
				
				if ($conn->query($sql) === TRUE) {
				  echo "Pár přidán";
				} else {
				  echo "Error: " . $sql . "<br>" . $conn->error;
				}
				
				$conn->close();
					 }
		
	// mazani z db 

    
   
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
   
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    
    $sql = "SELECT * FROM wp_cryptoticker_pary";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
     
      while($row = $result->fetch_assoc()) {
    $crypto = $row["crypto"];
    $fiat = $row["fiat"];
    $id = $row["id"];
  echo "$crypto-$fiat <a href='admin.php?page=example-options&id=$id'>DELETE</a> <br>";
    
    
}
} else {
  echo "0 results";
}
$conn->close();
 }   

 if(isset($_GET['id'])) {
  
$id = $_GET['id'];

 $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

 if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
 }
 

 $sql = "DELETE FROM wp_cryptoticker_pary WHERE id=$id";
 
 if ($conn->query($sql) === TRUE) {
   echo "Record deleted successfully";
 } else {
   echo "Pár smazán: " . $conn->error;
 }
 
 $conn->close();
}








// api JS


add_action('wp_footer','myscript_in_widget');
function myscript_in_widget(){
    echo "<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>";





$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT crypto,fiat FROM wp_cryptoticker_pary";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

  while($row = $result->fetch_assoc()) {
$crypto = $row["crypto"];
$fiat = $row["fiat"];

     echo "<script>";
    echo "getData('$crypto', 'https://min-api.cryptocompare.com/data/price?fsym=$crypto&tsyms=$fiat');";
    echo "function getData(prefix, url) {";
      echo " $.getJSON(url, function(data) {";
        echo "  $.each(data, function(key, val) {";
          echo "    $('.' + prefix.toLowerCase() + '-' + key.toLowerCase()).html(val);";
          echo "  });";
          echo "  });";
          echo " }";
          echo "</script>";
    
  }
} else {
  echo "0 results";
}
$conn->close();
 }

// shortcode 
 function crypto_shortcode() {

    

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    
    $sql = "SELECT crypto,fiat FROM wp_cryptoticker_pary";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
  
      while($row = $result->fetch_assoc()) {
    $cryptol = strtolower($row["crypto"]);
    $fiatl = strtolower($row["fiat"]);
    $cryptou = strtoupper($row["crypto"]);
    $fiatu = strtoupper($row["fiat"]);
    
    

    echo " <p> $cryptou in  $fiatu = <span class='$cryptol-$fiatl'></span></p>";
  
}
} else {
  echo "0 results";
}
$conn->close();
  
  
}
add_shortcode('current_prices', 'crypto_shortcode'); 

// odstraneni tabulky z db pri deaktivaci pluginu 
register_deactivation_hook( __FILE__, 'my_plugin_remove_database' );
function my_plugin_remove_database() {
     global $wpdb;
     $table_name = $wpdb->prefix . 'cryptoticker_pary';
     $sql = "DROP TABLE IF EXISTS $table_name";
     $wpdb->query($sql);
     delete_option("my_plugin_db_version");
}   
?>









































