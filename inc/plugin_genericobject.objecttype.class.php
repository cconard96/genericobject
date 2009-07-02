<?php
/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
class PluginGenericObjectType extends CommonDBTM{
	
    function __construct() {
    	$this->table="glpi_plugin_genericobject_types";
    	$this->type=PLUGIN_GENERICOBJECT_TYPE;
    	$this->dohistory=true;
    }
  
 	function getFromDBByType($device_type)
	{
		global $DB;
		$query = "SELECT * FROM `".$this->table."` WHERE device_type=$device_type";
		$result = $DB->query($query);
		if ($DB->numrows($result) > 0)
			$this->fields = $DB->fetch_array($result);	
	}

	function defineTabs($ID, $withtemplate) {
		global $LANG;
		$ong = array ();
		$ong[1] = $LANG['title'][26];
		if($ID>0)
		{
			$ong[2] = $LANG['genericobject']['config'][3];
			$ong[3] = $LANG['rulesengine'][12];
			$ong[4] = $LANG['genericobject']['config'][4];
			$ong[5] = $LANG['genericobject']['config'][7];
			$ong[12] = $LANG['title'][38];	
		}

		return $ong;
	}

	function showForm($target,$ID,$extraparams=array())
	{
		global $LANG;
		if ($ID > 0){
			$this->check($ID,'r');
		} else {
			// Create item 
			$this->check(-1,'w');
			$use_cache=false;
			$this->getEmpty();
		} 

		plugin_genericobject_includeLocales($this->fields["name"]);
		$this->showTabs($ID, '', $_SESSION['glpi_tab'],$extraparams);
		$canedit = $this->can($ID,'w');

		echo "<form name='form' method='post' action=\"$target\">";
		echo "<div class='center' id='tabsbody'>";
		echo "<table class='tab_cadre_fixe' >";
		echo "<tr class='tab_bg_1'><th colspan='2'>";
		echo $LANG['common'][2]." $ID";
		echo "</th></tr>";

		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANG['genericobject']['common'][1]."</td>";
		echo "<td>";
		if (!$ID)
			autocompletionTextField("name","glpi_plugin_genericobject_types","name");
		else
		{
			echo "<input type='hidden' name='name' value='".$this->fields["name"]."'>";
			echo $this->fields["name"];
		}
			
		echo "</td>";
		echo "</tr>";

		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANG['genericobject']['config'][9]."</td>";
		echo "<td>";
		if ($ID)
			echo plugin_genericobject_getObjectName($this->fields["name"]);
		echo "</td>";
		echo "</tr>";

		
		echo "<tr class='tab_bg_1'>";				
		echo "<td>".$LANG['genericobject']['common'][2]."</td>";
		echo "<td>"; 
		if (!$ID)
		{
			$next = plugin_genericobject_getNextDeviceType();
			echo $next;
			echo "<input type='hidden' name='device_type' value='".$next."'>";
		}
		else
		{
			echo $this->fields["device_type"];	
			echo "<input type='hidden' name='device_type' value='".$this->fields["device_type"]."'>";
		}
			
		echo "</td>";
		echo "</tr>";

		echo "<tr class='tab_bg_1'>";				
		echo "<td>".$LANG['common'][60]."</td>";
		echo "<td>"; 
		if (!$ID)
			echo $LANG['choice'][0];
		else
			dropdownYesNo("status",$this->fields["status"]);
		echo "</td>";
		echo "</tr>";

		if ($canedit)
		{
				echo "<tr>";
				echo "<td class='tab_bg_2' colspan='4' align='center'>";

				if (empty ($ID) || $ID < 0) {
					echo "<input type='submit' name='add' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
				} else {
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit'>";
					echo "&nbsp<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit'>";
				}
				echo "</td>";
				echo "</tr>";
		}


		echo "</table></div></form>";
		echo "<div id='tabcontent'></div>";
		echo "<script type='text/javascript'>loadDefaultTab();</script>";
		
	}

	function showBehaviourForm($target,$ID)
	{
		global $LANG;
		if ($ID > 0){
			$this->check($ID,'r');
		} else {
			// Create item 
			$this->check(-1,'w');
			$use_cache=false;
			$this->getEmpty();
		} 

		$canedit = $this->can($ID,'w');

		echo "<form name='behaviour' method='post' action=\"$target\">";
		echo "<div class='center'>";
		echo "<table class='tab_cadre_fixe' >";
		echo "<tr class='tab_bg_1'><th colspan='2'>";
		echo $LANG['genericobject']['config'][3];
		echo "</th></tr>";

		$use = array("use_entity"=>$LANG['Menu'][37],
					"use_recursivity"=>$LANG['entity'][9],
					"use_tickets"=>$LANG['Menu'][31],
					"use_deleted"=>$LANG['ocsconfig'][49],
					"use_notes"=>$LANG['title'][37],
					"use_history"=>$LANG['title'][38],
					"use_template"=>$LANG['common'][14],
					"use_infocoms"=>$LANG['financial'][3],
					"use_documents"=>$LANG['Menu'][27],
					"use_loans"=>$LANG['Menu'][17]);
		foreach($use as $right => $label)
		{
			echo "<tr class='tab_bg_1'>";
			echo "<td>".$LANG['genericobject']['config'][1]." ".$label."</td>";
			if ($right == 'use_recursivity' && !$this->fields['use_entity'])
			{
				echo "<td>";
				echo "<input type='hidden' name='use_recursivity' value='0'>\n";
				echo $LANG['choice'][0];
				echo "</td>";
			}
			else
			{
				echo "<td>";
				dropdownYesNo($right,$this->fields[$right]);
				echo "</td>";
			}
			echo "</tr>";
		}

		if ($canedit)
		{
				echo "<tr>";
				echo "<td class='tab_bg_2' colspan='2' align='center'>";

				echo "<input type='hidden' name='ID' value=\"$ID\">\n";
				echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit'>";
				echo "</td>";
				echo "</tr>";
		}
	}

	function prepareInputForAdd($input)
	{
		$input["name"] = strtolower($input["name"]);
		return $input;
	}
	
	function post_addItem($ID,$input)
	{
		plugin_genericobject_addTable($input["name"]);
		plugin_genericobject_addClassFile($input["name"],plugin_genericobject_getObjectClassByName($input["name"]),$input["device_type"]);
		plugin_genericobject_createAccess($_SESSION["glpiactiveprofile"]["ID"]);
		plugin_genericobject_addNewField($input["device_type"],"name");
		plugin_genericobject_addLinkTable($input["device_type"]);
		return true;
	}

	function prepareInputForUpdate($input)
	{
		$this->getFromDB($input["ID"]);
		if (isset($input["status"]) && $input["status"])
			plugin_genericobject_registerOneType($this->fields);
			
		if (isset($input["use_template"]))
			if ($input["use_template"])
				plugin_genericobject_enableTemplateManagement($this->fields["name"]);	
			else
				plugin_genericobject_disableTemplateManagement($this->fields["name"]);	
		return $input;
	}

	function pre_deleteItem($ID)
	{
		$this->getFromDB($ID);

		//Delete all tables related to the type (dropdowns)
		plugin_genericobject_deleteSpecificDropdownTables($this->fields["device_type"]);

		//Delete relation table
		plugin_genericobject_deleteLinkTable($this->fields["device_type"]);
		
		//Remove class from the filesystem
		plugin_genericobject_deleteClassFile($this->fields["name"]);
		
		//Delete profile informations associated with this type
		plugin_genericobject_deleteTypeFromProfile($this->fields["name"]);
		
		//Table type table in DB
		plugin_genericobject_deleteTable($this->fields["name"]);
		
		//Remove fields from the type_fields table
		plugin_genericobject_deleteAllFieldsByType($this->fields["device_type"]);
		
		return true;
	}
}
?>