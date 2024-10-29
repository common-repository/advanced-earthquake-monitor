<?php
/*
Plugin Name: Advanced Earthquake Monitor 
Version: 1.3
Plugin URI: http://wordpress.org/extend/plugins/AdvancedEarthquakeMonitor
Description: Advanced Earthquake Monitor is a customizable widget that shows lists and maps of recent earthquakes worldwide or in defined regions
Author: <a href="http://www.volcanodiscovery.com">Tom Pfeiffer</a>
Author URI: http://www.volcanodiscovery.com
License: GNU General Public License, version 2
*/

/*  Copyright 2012  Tom Pfeiffer  (email : info [dot] volcanodiscovery [dot] com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



class AdvancedEarthquakeMonitor extends WP_Widget {
	
  private $timesCalled=0;    
  
  function AdvancedEarthquakeMonitor() {

    $widget_ops = array('classname' => 'widget_AdvancedEarthquakeMonitor', 'description' => __( 'Show list and map of earthquakes'),'name'=>'Advanced Earthquake Monitor' );
		parent::WP_Widget('AdvancedEarthquakeMonitor', __('AdvancedEarthquakeMonitor'), $widget_ops);
				
    }
	
	
	function form( $instance ) {
		$this->timesCalled++;
		$instance = wp_parse_args( (array) $instance, array(
                              'region' => '0',
															'lat1' => '',
															'lat2' => '',
															'lon1' => '',
															'lon2' => '',
															'minmag' => '', 
															'noearthquakes' => 'No Earthquakes', 
															'dateformat' => 'D H:i:s (T)', 
															'displayformat' => '{time} {mag},{loc} - {details}', 
															'showonmaptext' => 'Show on map', 
															'detaillinktext' => 'Details', 
															'showtitle' => true , 
															'customtitle' => '',
															'show' => 20, 
															'trim' => 24, 
															'linkable' => true , 
															'sourcelinkable' => true , 
															'newwindow' => true , 
															'liststyle' => '.advancedearthquakemonitor-class0{font-size:90%}
.advancedearthquakemonitor-class1{font-size:90%}
.advancedearthquakemonitor-class2{}
.advancedearthquakemonitor-class3{}
.advancedearthquakemonitor-class4{}
.advancedearthquakemonitor-class5{font-weight:bold}
.advancedearthquakemonitor-class6{font-weight:bold;color:red}
.advancedearthquakemonitor-class7{font-size:110%;font-weight:bold;color:red}
.advancedearthquakemonitor-class8{font-size:120%;font-weight:bold;color:red}
.advancedearthquakemonitor-class9{font-size:130%;font-weight:bold;color:red}',
															 ));
		
		$noearthquakes = esc_attr($instance['noearthquakes']);
		$dateformat = esc_attr($instance['dateformat']);
		$showonmaptext = esc_attr($instance['showonmaptext']);
		$detaillinktext = esc_attr($instance['detaillinktext']);
		$region = intval($instance['region']);
		$lat1 = esc_attr($instance['lat1']);
		$lat2 = esc_attr($instance['lat2']);
		$lon1 = esc_attr($instance['lon1']);
		$lon2 = esc_attr($instance['lon2']);
		$trim = absint($instance['trim']);
		$minmag = esc_attr($instance['minmag']);
		$showtitle = (bool) $instance['showtitle'];
		$customtitle = esc_attr($instance['customtitle']);
		$linkable = (bool) $instance['linkable'];
		$sourcelinkable = (bool) $instance['sourcelinkable'];
		$newwindow = (bool) $instance['newwindow'];
		$displayformat = esc_attr($instance['displayformat']);
    $liststyle = esc_attr($instance['liststyle']);
    
		$show = absint($instance['show']);
		if ( $show < 1 || 100 < $show ) { $show = 20; }
		
    /* JS show hide script */
		$js = '
function aem_sh(el) {
  if (document.getElementById(el).style.display == "none")
		{document.getElementById(el).style.display = "";}
	else {document.getElementById(el).style.display = "none";};

	if (document.getElementById(el+"_1") !=null) {if (document.getElementById(el+"_1").style.display == "none")
					{document.getElementById(el+"_1").style.display ="";}
	else {document.getElementById(el+"_1").style.display = "none";};}
}
function populateSelector(id,arr,curId) {
  if (typeof(arr)=="undefined") return;
  var sel=document.getElementById(id);
  for(i=0;i<arr.length-1;i++) {
    var newOpt=document.createElement("option");
    newOpt.setAttribute("value",arr[i][6]); // using the uid
    if (curId==arr[i][6]) newOpt.setAttribute("selected","selected"); 
    //newOpt.setAttribute("value",arr[i][1]);
    newOpt.innerHTML=arr[i][0];
    sel.appendChild(newOpt);
  }
}
// params are ids of fields
function updateLatLon(sel,lat1,lat2,lon1,lon2) {
  if (typeof(aem_regions)=="undefined") return;
  var uid=document.getElementById(sel).options[document.getElementById(sel).selectedIndex].value;
  var ind=0;
  for (k=0;k<aem_regions.length-1;k++) {
    if (aem_regions[k][6]==uid) {ind=k;break}  
  }
  var region=aem_regions[ind];  
  document.getElementById(lat1).value=region[1];
  document.getElementById(lat2).value=region[2];
  document.getElementById(lon1).value=region[3];
  document.getElementById(lon2).value=region[4];  
}

';
    if ($this->timesCalled==1) echo ('<script type="text/javascript">'.$js.'</script>');
    if ($this->timesCalled==1) echo ('<script type="text/javascript" src="https://earthquakes.volcanodiscovery.com/getRegionSelect.php"></script>');
  

		
    /* Show Title */
		echo "<p><label for='" . $this->get_field_id('showtitle') . "'><input id='" . $this->get_field_id('showtitle') . "' class='checkbox' type='checkbox' name='" . $this->get_field_name('showtitle') . "'";
		if ( $showtitle ) {
			echo ' checked="checked"';
		}
		echo " /> " . esc_html__('Show title') . "</label></p>";
	  /* Text for Custom Title */
		//echo "<p><label for='" . $this->get_field_id('customtitle') ."'>". esc_html__('Title')."</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('customtitle') . "' name='" . $this->get_field_name('customtitle') . "' type='text' value='" . $customtitle . "' /></p>";
		
		
    echo '<h3 onclick="aem_sh(\'aem-form-basics-'.$this->get_field_id('show').'\');">Basic configuration (<span>show/hide</span>)</h3>';
		echo '<div id="aem-form-basics-'.$this->get_field_id('show').'" style="display:block">';
		
		/* Earthquake count */
		echo "<p><label for='". $this->get_field_id('show') . "'>" . esc_html__('Show earthquake count:')."</label>";
		echo "<select id='" . $this->get_field_id('show') . "' name='" . $this->get_field_name('show') . "'>";
		
		$values = array(1,2,5,10,15,20,50,100);
		foreach ($values as $i) {
			echo "<option value='$i'" . ( $show == $i ? "selected='selected'" : '' ) . ">$i</option>";
		}
		echo "</select></p>";
	
	  /* min mag */
		echo "<p><label for='". $this->get_field_id('minmag') . "'>" . esc_html__('Minimum magnitude')."</label>";
		echo "<select id='" . $this->get_field_id('minmag') . "' name='" . $this->get_field_name('minmag') . ">";
		$value = ""; echo "<option value='{$value}'" . ( $minmag == $value? "selected='selected'" : '' ) . ">Any</option>";
		$value = "2"; echo "<option value='{$value}'" . ( $minmag == $value ? "selected='selected'" : '' ) . ">Magnitude 2+</option>";
		$value = "3"; echo "<option value='{$value}'" . ( $minmag == $value ? "selected='selected'" : '' ) . ">Magnitude 3+</option>";
		$value = "4"; echo "<option value='{$value}'" . ( $minmag == $value ? "selected='selected'" : '' ) . ">Magnitude 4+</option>";
		$value = "5"; echo "<option value='{$value}'" . ( $minmag == $value ? "selected='selected'" : '' ) . ">Magnitude 5+</option>";
		echo "</select></p>";
		
    /* select specific predefined region */
    echo "<p><label for='". $this->get_field_id('region') . "'>" . esc_html__('Predefined regions  (suggest more through comments at the plugin page of wp)')."</label>";
		echo '<select id="' . $this->get_field_id('region') . '" name="' . $this->get_field_name('region') . '" onchange="updateLatLon(\''. $this->get_field_id('region') .'\',\''. $this->get_field_id('lat1') .'\',\''. $this->get_field_id('lat2') .'\',\''. $this->get_field_id('lon1') .'\',\''. $this->get_field_id('lon2') .'\')" >';
		$value = ""; echo "<option value='{$value}'" . ( $region == $value? "selected='selected'" : '' ) . ">Select from currently available</option>";
		echo "</select></p>";
    echo '
    <script type="text/javascript">populateSelector("'.$this->get_field_id('region').'",aem_regions,'.intval($region).');</script>
    ';
    
    
    /* Lon 1-2 */
		echo "<p>West/East limits (values from -180 to 180)";
		echo "<input class='widefat' style='width:50px' id='" . $this->get_field_id('lon1') . "' name='" . $this->get_field_name('lon1') . "' type='text' value='" . $lon1 . "' /> / <input class='widefat' style='width:50px' id='" . $this->get_field_id('lon2') . "' name='" . $this->get_field_name('lon2') . "' type='text' value='" . $lon2 . "' /></p>";
		/* Lat 1-2 */
		echo "<p>South/North limits (-90 to 90)";
		echo "<input class='widefat' style='width:50px' id='" . $this->get_field_id('lat1') . "' name='" . $this->get_field_name('lat1') . "' type='text' value='" . $lat1 . "' /> / <input class='widefat' style='width:50px' id='" . $this->get_field_id('lat2') . "' name='" . $this->get_field_name('lat2') . "' type='text' value='" . $lat2 . "' /></p>";
			
		   
    
   // echo '<p style="border:1px solid red">Want to show the earthquake list on a map - look at the <a href="http://wordpress.org/extend/plugins/volcano-widget/" target="_blank" title="Volcano Widget - interactive map of volcanoes and/or earthquakes"><b>VolcanoWidget</b></a> plugin!</p>';
    echo '</div>';
    
    echo '<h3 onclick="aem_sh(\'aem-form-labels-'.$this->get_field_id('show').'\');">Labels (<span>show/hide</span>)</h3>';
		echo '<div id="aem-form-labels-'.$this->get_field_id('show').'" style="display:none">';
		
    /* Text for No Earthquakes */
		echo "<p><label for='" . $this->get_field_id('noearthquakes') ."'>". esc_html__('Text when no earthquakes')."</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('noearthquakes') . "' name='" . $this->get_field_name('noearthquakes') . "' type='text' value='" . $noearthquakes . "' /></p>";
		
    /* Show on map text */
		echo "<p><label for='" . $this->get_field_id('showonmaptext') ."'>". esc_html__('Show on map text')."</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('showonmaptext') . "' name='" . $this->get_field_name('showonmaptext') . "' type='text' value='" . $showonmaptext . "' /></p>";
		
		/* Detail link text */
		echo "<p><label for='" . $this->get_field_id('detaillinktext') ."'>". esc_html__('Detail link text')."</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('detaillinktext') . "' name='" . $this->get_field_name('detaillinktext') . "' type='text' value='" . $detaillinktext . "' /></p>";
		
		echo '</div>';
    
    echo '<h3 onclick="aem_sh(\'aem-form-advanced-'.$this->get_field_id('show').'\');">Advanced configuration (<span>show/hide</span>)</h3>';
		echo '<div id="aem-form-advanced-'.$this->get_field_id('show').'" style="display:none">';
		
			
		/* Linkable? */
		echo "<p><label for='" . $this->get_field_id('linkable') . "'><input id='" . $this->get_field_id('linkable') . "' class='checkbox' type='checkbox' name='" . $this->get_field_name('linkable') . "'";
		if ( $linkable ) {
			echo ' checked="checked"';
		}
		echo " /> " . esc_html__('Make location linkable') . "</label></p>";
	
		/* Source linkable? */
		echo "<p><label for='" . $this->get_field_id('sourcelinkable') . "'><input id='" . $this->get_field_id('sourcelinkable') . "' class='checkbox' type='checkbox' name='" . $this->get_field_name('sourcelinkable') . "'";
		if ( $sourcelinkable ) {
			echo ' checked="checked"';
		}
		echo " /> " . esc_html__('Make links to data source (e.g. USGS)') . "</label></p>";
	
    
    /* New Window? */
		echo "<p><label for='" . $this->get_field_id('newwindow') . "'><input id='" . $this->get_field_id('newwindow') . "' class='checkbox' type='checkbox' name='" . $this->get_field_name('newwindow') . "'";
		if ( $newwindow ) {
			echo ' checked="checked"';
		}
		echo " /> " . esc_html__('Open links in new window') . "</label></p>";
	
	  echo '</div>';
		echo '<h3 onclick="aem_sh(\'aem-form-format-'.$this->get_field_id('show').'\');">Formatting (<span>show/hide</span>)</h3>';
		echo '<div id="aem-form-format-'.$this->get_field_id('show').'" style="display:none">';
		
		/* Date Format */
		echo "<p><label for='" . $this->get_field_id('dateformat') ."'>". esc_html__('Date format, e.g. D H:i:s (T)')."</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('dateformat') . "' name='" . $this->get_field_name('dateformat') . "' type='text' value='" . $dateformat . "' /></p>";
		
		/* Trim count */
		echo "<p><label for='" . $this->get_field_id('trim') ."'>". esc_html__('Trim location string after x characters (0=no trim)')."</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('trim') . "' name='" . $this->get_field_name('trim') . "' type='text' value='" . $trim . "' /></p>";

		
		echo "<hr />";
		echo "<h3>Template for quake list entry</h3>";
		echo "<ul>";
		echo "<li>{loc} Earthquake location</li>";
		echo "<li>{hrtime} Time past since quake</li>";
		echo "<li>{time} Time of last quake</li>";
		echo "<li>{mag} Magnitude</li>";
		echo "<li>{lat} Latitude</li>";
		echo "<li>{lon} Longitude</li>";
		echo "<li>{depth_m} Depth in km</li>";
		echo "<li>{depth_i} Depth in miles</li>";
		echo "<li>{source} Link to data source (e.g. USGS)</li>";
		echo "<li>{details} Link to detail infos and felt-reports on VolcanoDiscovery</li>";
		echo "<li>{map} Maplink text with link to overlay map</li>";
		echo "</ul>";
		echo "<hr />";
		
		/* Display Format */
		echo "<p><label for='" . $this->get_field_id('displayformat') ."'>". esc_html__('Display format')."</label>";
		echo "<input class='widefat' id='" . $this->get_field_id('displayformat') . "' name='" . $this->get_field_name('displayformat') . "' type='text' value='" . $displayformat . "' size=2 /></p>";
		
    /* List styles */
		echo "<p><i><label for='" . $this->get_field_id('liststyle') ."' onclick='document.getElementById(\"".$this->get_field_id("liststyle")."\").style.display=\"\"'>". esc_html__('edit CSS styles for each magnitude class in list - click to edit')."</i></label>";
		echo "<textarea rows='10' class='widefat' style='display:none' id='" . $this->get_field_id('liststyle') . "' name='" . $this->get_field_name('liststyle') . "'>" . $liststyle . "</textarea></p>";
		echo '</div>';
    
		
	}
	
	
	
	function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['noearthquakes'] = trim( strip_tags( stripslashes( $new_instance['noearthquakes'] ) ) );
			$instance['dateformat'] = trim ($new_instance['dateformat']);
			$instance['customtitle'] = trim( strip_tags( stripslashes( $new_instance['customtitle'] ) ) );
			$instance['show'] = absint($new_instance['show']);
			$instance['trim'] = absint($new_instance['trim']);
			$instance['showtitle'] = isset($new_instance['showtitle']);
			$instance['showonmaptext'] = trim( strip_tags( stripslashes( $new_instance['showonmaptext'] ) ) );
			$instance['detaillinktext'] = trim( strip_tags( stripslashes( $new_instance['detaillinktext'] ) ) );
			$instance['linkable'] = isset($new_instance['linkable']);
			$instance['sourcelinkable'] = isset($new_instance['sourcelinkable']);
			$instance['newwindow'] = isset($new_instance['newwindow']);
			$instance['displayformat'] = trim( stripslashes( $new_instance['displayformat'] ) );
			$instance['liststyle'] = trim( stripslashes( $new_instance['liststyle'] ) );
			$instance['minmag'] = $new_instance['minmag'];
			$instance['region'] = $new_instance['region'];
			$instance['lat1'] = $new_instance['lat1'];
			$instance['lat2'] = $new_instance['lat2'];
			$instance['lon1'] = $new_instance['lon1'];
			$instance['lon2'] = $new_instance['lon2'];
			return $instance;
		
	}
	

	
	
		
	
	function checkphpversion() {
			if(!version_compare(PHP_VERSION, '5.2.1', '>=')) {
			$out = '<div class="error" id="messages">';
			$out .= '<p>AdvancedEarthquakeMonitor plugin requires PHP5.2.1 or higher. Your server is running '.phpversion().'.</p>';
			$out .= '</div>';
			echo $out;
				}	
			return;
	}
	

	
  function findBetween($s1,$s2,&$str) {
    if ($s1=='') $pos1=0;
    else $pos1=strpos($str,$s1);
    if ($pos1===false) return false;
    $pos2=strpos($str,$s2,$pos1+strlen($s1));
    if ($pos2===false) return false;
    $val = substr($str,$pos1+strlen($s1),$pos2-$pos1-strlen($s1));
    $str = substr($str,$pos2+strlen($s2));
    return $val;
  }

  function getQuakes($max=20,$minmag='',$lat1='',$lat2='',$lon1='',$lon2='') {
    $url = 'https://earthquakes.volcanodiscovery.com/widget/data/q-table';
    if ($lat1 && $lat2 && $lon1 && $lon2) $url .= '-'.$lat1.'_'.$lat2.'_'.$lon1.'_'.$lon2;
    $url .= '.tbl';
    $content = file_get_contents($url);  
    $quakes = array();
    $day = $this->findBetween('<td colspan="5">','</td>',$content);
    $year = substr($day,-4);
    $parts = explode('<tr id="quake-',$content);
    array_shift($parts);
    $count = 0;
    foreach ($parts as $part) {
      $quake=array();
      $quake['id'] = $this->findBetween('','"',$part);
      $quake['timeUTC'] = $this->findBetween('<td>','</td>',$part);
      $quake['timeUTC'] = substr($quake['timeUTC'],4,-12).$year.' '.substr($quake['timeUTC'],-12);
      $quake['magnitude'] = trim($this->findBetween('M','/',$part));
      if ($quake['magnitude']<$minmag) continue;
      $quake['depth'] = trim($this->findBetween(' ',' -',$part));
      $quake['link'] = trim($this->findBetween('href="','"',$part));
      $quake['location'] = trim($this->findBetween('<td>','<',$part));
      $quake['location'] = rtrim($quake['location'],'[- ');
      
      $quake['lat'] = trim($this->findBetween('showQuake(',',',$part));
      $quake['lon'] = trim($this->findBetween('',',',$part));
      $quake['sourcelink'] = trim($this->findBetween('href="','"',$part));
      $quake['source'] = trim($this->findBetween('>','<',$part));
      $quakes[]=$quake;
      // perhaps the year changes as we go down...
      $day = $this->findBetween('<td colspan="5">','</td>',$part);
      if ($day) $year = substr($day,-4);
      $count++;
      if ($count<$max) continue;
      break;
    }
    return $quakes;
  }	
  
  
	function widget($args, $instance) {
	  date_default_timezone_set ('UTC');
		extract( $args );	
		$this->timesCalled ++; // to chec how many times this is used
		
		echo $before_widget;
		if ($instance['showtitle']) {
			if ($instance['customtitle'] <> '') {
						echo "{$before_title}".$instance["customtitle"]."{$after_title}";
			}
			else {
						echo "{$before_title}".$this->maintitle."{$after_title}";
			}						
		}
		
		
		
		if ($this->timesCalled==1) {
      $preHTML = '

<script type="text/javascript">
var mapPos=[10,20];var terrainView=1;var mapZoom=1;var savedMarkerId=null;var storedMarkers=new Array(); 
function openPopup(lat,lon,id) {
  if (typeof(map)=="undefined") return setTimeout("openPopup("+lat+","+lon+","+id+")",200);
  if (map==null) return setTimeout("openPopup("+lat+","+lon+","+id+")",200);
  if (typeof(mapMarkers["quake-"+id])=="undefined") return setTimeout("openPopup("+lat+","+lon+","+id+")",200);
  mapMarkers["quake-"+id].setVisible(true);
  var id=id||0;
  var lat=lat||0;
  var lon=lon||0;
  var winH=window.innerHeight;
  var h1 = winH-100;
  var h2 = h1-35;
  document.getElementById("popupMap").style.display="";
  document.getElementById("map_canvas").style.height=h2+"px";  
  document.getElementById("mapContainer").style.height=h1+"px"; 
  google.maps.event.trigger(map, "resize");          
  if (id) showQuake(lat,lon,id);
}
function closePopup() {
  document.getElementById("popupMap").style.display="none";
}
function mkRep(id) {
  var id=parseInt(id);
  var src="https://ww2.volcanodiscovery.com/felt-report.php?quakeId="+id;  
  window.open(src,"felt-report","status=0,toolbar=0,location=0,directories=0,menubar=0,resizable=1,scrollbars=1,height=400,width=350");
} 

function downloadJSAtOnload(sF){var el=document.createElement("script");el.src=sF;document.body.appendChild(el);}

function startMap() {
  downloadJSAtOnload("https://earthquakes.volcanodiscovery.com/fileadmin/voldis/js/gmap_widget_34.js");
}

function initGoogleMap() { 
  downloadJSAtOnload("https://maps.google.com/maps/api/js?sensor=false&callback=startMap");     
} 

window[ addEventListener ? "addEventListener" : "attachEvent" ]( addEventListener ? "load" : "onload", initGoogleMap );
</script>
<div id="popupMap" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;display:none;z-index:999999">
<div id="popupMap_backDiv" style="background:#000;filter:alpha(opacity=80);opacity:.8;height:100%;width:100%;position:absolute;top:0px;left:0px;z-index:-1;" onclick="closePopup();return false;"></div>

<div id="mapContainer" style="display:none;margin:25px 10%; border:1px solid #bb202a;padding:5px;background:#fff">
<a name="map"></a><div id="map_canvas" style="margin:10px 0 5px 0;color:000"></div>
<div style="text-align:right;">[<a href="#" onclick="closePopup();return false">hide map</a>]</div>
</div>
</div>';
    } 
    else $preHTML = '';
    
		$quakes = $this->getQuakes(absint($instance['show']),$instance['minmag'],$instance['lat1'],$instance['lat2'],$instance['lon1'],$instance['lon2']);
    //echo (count($quakes).' earthquakes');
		if ($quakes !== FALSE) {
			
			echo $preHTML;
					
			// styles
			echo '
      <style>'.$instance['liststyle'].'</style>
      ';
      
			echo "<ul>\n";		
			$intCount = count($quakes);
			
			if ($intCount == 0) {
				echo "<li>".$instance['noearthquakes']."</li>";
			}
			
			if ($intCount > 0 and $intCount > absint($instance['show']) && absint($instance['show']) <> 0) 
				{ $max = absint($instance['show']); } 
			else 
				{
				  $max = $intCount;
				}
			
			for ($i = 0; $i < $max; $i++) {
			
			$quake =& $quakes[$i];
			
			/* Format display according display format */
						
			$loc = $quake['location'];
      
			if ($instance['trim'] > 0 and strlen($loc) > $instance['trim'] ) 
				{
				  $loc = substr($loc,0,$instance['trim'])."..";
				}
			
			$mag =& $quake['magnitude'];
			$time = strtotime($quake['timeUTC']);
			$hrtime = human_time_diff($time ,  current_time("timestamp"),$gmt = 0 );
			$time = date($instance['dateformat'],$time);
			$lat =& $quake['lat'];
      $lon =& $quake['lon'];
      $depth_m =& $quake['depth'];
      $depth_i = round(substr($depth_m,0,-2) * 0.621371192,1)." miles" ;
			
		
			
			if ($instance['newwindow']) 
				{ $target = "_blank"; } 
			else 
				{ $target = "_top"; }
			
			$showonmaptext=$instance['showonmaptext'];
			if (!$showonmaptext)$showonmaptext='map';
			// map link on location
			if ($instance['linkable'])
				{ $loc = '<a href="#" title="'.$showonmaptext.'" onclick="openPopup('.$lat.','.$lon.','.$quake['id'].');return false">'.$loc.'</a>'; }
			// map link on maplinktext
			$mapLink = '<a href="#" title="'.$showonmaptext.'" onclick="openPopup('.$lat.','.$lon.','.$quake['id'].');return false">'.$showonmaptext.'</a>';
			
			$source = $quake['source'];
			if ($instance['sourcelinkable'])
				{ $source = "<a target='{$target}' href='{$quake['sourcelink']}'>{$source}</a>"; }
      
      $detaillinktext = $instance['detaillinktext'] ;
      if (!$detaillinktext) $detaillinktext='details';
      $detaillink = "<a target='{$target}' href='{$quake['link']}'>{$detaillinktext}</a>";
      
      // JS for map
      $sizes = array(4,6,8,12,14,18,25,30,35,40,40);
      $sClass = ($mag<0?0:floor($mag))+1;
      $w = $sizes[$sClass]; 
      $felt = '';
      if ($mag>2.3) {
        $felt = '<div style="text-align:center"><a href="#" style="font-style:italic;color:red;" title="I felt this quake - click to share your experience with others" onclick="mkRep('.$quake['id'].');return false">I FELT IT</a></div>';
      }
      $js = '<script type="text/javascript">
html=\'<b>Earthquake data:</b><br />'.$time.'<br />Mag. / depth: M'.$mag.' / '.$depth.'<br />Lat / Long: '.$lat.' / '.$lon.'  '.str_replace('\'','\\\'',$quake['location']).'<br />Source: <a href="'.$quake['sourcelink'].'" target="_blank">'.$quake['source'].'</a> - [<a href="'.$quake['link'].'" title="'.$detaillinktext.'">'.$detaillinktext.'</a>]'.$felt.'\';
storedMarkers.push([\'quake-'.$quake['id'].'\','.$lat.','.$lon.',\'M'.$mag.' / '.$depth.' '.$time.' / '.str_replace('\'','\\\'',$quake['location']).'\',html,\'quakes-'.floor($mag).'-24.png\','.$w.','.$w.','.round($w/2).','.round($w/2).',124]);   
</script>';
      
			/* Parse user string */
			$display = $instance['displayformat'];
			if (!$display) $display='{time} M{mag} - {loc} ({map})';
			$variable = array("{loc}","{mag}","{time}","{lat}","{lon}","{lat}","{depth_m}","{depth_i}","{hrtime}","{source}","{details}","{map}");
			$replace = array("{$loc}","{$mag}","{$time}","{$lat}","{$lon}","{$lat}","{$depth_m}","{$depth_i}","{$hrtime}","{$source}","{$detaillink}","{$mapLink}");
			$parseddisplay = str_replace($variable, $replace, $display);
			
			//echo '<li id="quake-'.$quake['id'].'">'.$parseddisplay.$js.'</li>';
			echo '<li class="advancedearthquakemonitor-class'.($mag<1?'0':floor($mag)).'">'.$parseddisplay.$js.'</li>';
					
			}
			
			echo "</ul>";
			
			
			
		} else echo "Feed error in Earthquakedata"; 
		
    
    echo $after_widget;
		
	}
}


add_action('admin_notices', array('AdvancedEarthquakeMonitor','checkphpversion'));
add_action( 'widgets_init', 'widget_AdvancedEarthquakeMonitor_widget_init' );

	function widget_AdvancedEarthquakeMonitor_widget_init() {
		register_widget('AdvancedEarthquakeMonitor');
	}

?>