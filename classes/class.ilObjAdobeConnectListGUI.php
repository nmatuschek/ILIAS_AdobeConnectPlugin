<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
 * List GUI class for Adobe Connect repository object
 *
 * @author Felix
 */
class ilObjAdobeConnectListGUI extends ilObjectPluginListGUI{
    /**
	* Init type
	*/
	function initType()
	{
		$this->setType('xavc');
	}

	/**
	* Get name of gui class handling the commands
    *
    *   @return String
	*/

	function getGuiClass()
	{
		return 'ilObjAdobeConnectGUI';
	}

	/**
	* Get commands
        *
        * this function is called at least twice as we use the result
        * in the overwritten 'insertCommands' method below.
        *
	*/
	function initCommands()
	{
		if(strtolower($_GET['baseClass']) != 'ilpersonaldesktopgui')
		{
			$this->link_enabled = true;
		}

		$this->copy_enabled = false;
		
		$command_array = array();
		$command_array[] = array("permission" => "read", "cmd" => "showContent", "lang_var" => "content");
		
		$command_array[] = array(
			'permission'	=> 'read',
			'cmd'			=> 'showContent',
			'txt'			=> $this->txt('access'),
			'default'		=> true
		);

		$command_array[] = array(
			'permission'	=> 'write',
			'cmd'			=> 'editProperties',
			'txt'			=> $this->txt('properties'),
			'default'		=> false
		);

		return $command_array;
	}
	
	/**
	 * insert all commands into html code
	 *
	 * @access	public
	 * @param	boolean		$a_use_asynch			use_asynch
	 * @param	boolean		$a_get_asynch_commands	get_asynch_commands
	 * @param	string		$a_asynch_url			async url
	 */
	public function insertCommands($a_use_asynch = false, $a_get_asynch_commands = false, $a_asynch_url = '')
	{
		global $ilUser;

		/**
		 *@var  $this->plugin ilPlugin
		 */
		$this->plugin->includeClass('class.ilObjAdobeConnectAccess.php');
		
		if(
			!ilObjAdobeConnectAccess::_hasMemberRole($ilUser->getId(), $this->ref_id) &&
			!ilObjAdobeConnectAccess::_hasAdminRole($ilUser->getId(), $this->ref_id)
		)
		{
			/**
			 * $this->commands is initialized only once. appending the join-button
			 * at this point will produce N buttons for the Nth item
			*/
			$this->commands = array_reverse(
				array_merge(
					$this->initCommands(),
					array(array(
						'permission'	=> 'visible',
						'cmd'			=> 'join',
						'txt'			=> $this->txt('join'),
						'default'		=> true
					),
					array(
						'permission'	=> 'visible',
						'cmd'			=> 'join',
						'txt'			=> $this->txt('join'),
						'default'		=> false
					))
				)
			);

			$this->info_screen_enabled = false;
		}
		else {
		    $this->commands =   $this->initCommands();
		}
        return parent::insertCommands($a_use_asynch, $a_get_asynch_commands, $a_asynch_url);

	}

	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						'alert' (boolean) => display as an alert property (usually in red)
	*						'property' (string) => property name
	*						'value' (string) => property value
	*/
	public function getProperties()
	{
		$props = array();

		$this->plugin->includeClass('class.ilObjAdobeConnect.php');
		$objectData = ilObjAdobeConnect::getObjectData($this->obj_id);

		if($objectData->permanent_room == 1)
		{
			$props[] = array('alert' => false, 
							 'value' => $this->plugin->txt('permanent_room'));
		}
		else
		{
			if((int)$objectData->start_date > time() || (int)$objectData->end_date < time())
			{
				$props[] = array('alert' => false,
								 'value' => $this->plugin->txt('meeting_not_available'));
			}
			else
			{	
				$props[] = array('alert' => false, 'property' => $this->txt('start_date'),
						'value' => ilDatePresentation::formatDate(new ilDateTime( $objectData->start_date, IL_CAL_UNIX)));
				
				$props[] = array('alert' => false, 'property' => $this->txt('duration'),
						'value' => ilDatePresentation::formatPeriod(new ilDateTime( $objectData->start_date, IL_CAL_UNIX ),
																	new ilDateTime( $objectData->end_date, IL_CAL_UNIX )));
			}
		}
		return $props;
	}
}
?>
