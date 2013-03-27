<?php
$customConfigGroups = getCustomGroupsConfig();
$API = empty($customConfigGroups[CB_PROJ.'_api']) ? array() : $customConfigGroups[CB_PROJ.'_api'];
$API = array_merge( $API, array(
	'Auth' => array(
		'methods'=>array(
			'getLoginInfo'	=>	array('len' => 0)
			,'login'		=>	array('len' => 2)
			,'logout'		=>	array('len' => 0)
			,'setLanguage'	=>	array('len' => 1)
		)
	)
	,'Browser' => array(
		'methods' => array(
			'createFolder'		=> array('len' => 1)
			,'paste'		=> array('len' => 1)
			,'saveFile'		=> array('len' => 1, 'formHandler' => true)
			,'confirmUploadRequest'	=> array('len' => 1)
			,'uploadNewVersion'	=> array('len' => 1, 'formHandler' => true)
			,'delete'		=> array('len' => 1)
			,'toggleFavorite'	=> array('len' => 1)
			,'takeOwnership'	=> array('len' => 1)
			,'getObjectsForField'	=> array('len' => 1)
		)
	)

	,'Path' => array(
		'methods' => array(
			'getPath'		=> array('len' => 1)
			,'getPidPath'		=> array('len' => 1)
		)
	)
	
	,'BrowserTree' => array(
		'methods' => array(
			'getChildren'		=> array('len' => 1)
			,'createFolder'		=> array('len' => 1)
			,'delete'		=> array('len' => 1)
			,'rename'		=> array('len' => 1)
			,'getRootProperties'	=> array('len' => 1)
		)
	)
	,'BrowserView' => array(
		'methods' => array(
			'getChildren'		=> array('len' => 1)
			,'getSummaryData' 	=> array('len' => 1)
			,'createFolder'		=> array('len' => 1)
			,'delete'		=> array('len' => 1)
			,'rename'		=> array('len' => 1)
		)
	)
	,'Favorites' => array(
		'methods'=>array(
			'create'		=>	array('len' => 1)
			,'read'			=>	array('len' => 1)
			,'update'		=>	array('len' => 1)
			,'destroy' 		=> 	array('len' => 1)
		)
	)

	,'Search' => array(
		'methods'=>array(
			'searchCases'		=>	array('len' => 1)
			,'searchInCase'		=>	array('len' => 1)
			,'searchObjects'	=>	array('len' => 1)
			//,'saveQuery'		=>	array('len' => 1)
			//,'getSavedQueries'	=>	array('len' => 0)
		)
	)

	,'Calendar' => array(
		'methods'=>array(
			'getEvents'		=>	array('len' => 1)
		)
	)
	
	,'Cases' => array(
		'methods'=>array(
			'getList'			=>	array('len'=>1)
			,'create'			=>	array('len'=>1)
			,'save'			=>	array('len'=>1)
			,'close'			=>	array('len'=>1)
			,'load'			=>	array('len'=>1)
			,'lock'			=>	array('len'=>1)
			,'unlock'			=>	array('len'=>1)
			,'getTasks' 		=> 	array('len'=>2)
			,'transmitCase'		=>	array('len'=>3)
			,'toggleCaseTag'	=> 	array('len' => 1)
			,'updateCaseTags'	=> 	array('len' => 1)
			,'updateFileTags'	=> 	array('len' => 1)
			,'getCaseId'		=> 	array('len' => 1)
			,'toggleFavorite'	=> 	array('len' => 1)
			,'getFavorites'		=> 	array('len' => 0)
			,'changeName'		=> 	array('len' => 1)
			,'getAssociatedCases'	=> 	array('len' => 1)
			,'getAssociatedObjects'	=> 	array('len' => 1)
			,'queryCaseData'	=> 	array('len' => 1)
			,'getCasePropertiesObjectId'=> 	array('len' => 1)
			
		)
	)
	
	,'Tasks' => array(
		'methods'=>array(
			'load'			=>	array('len' => 1)
			,'create'		=>	array('len' => 1)
			,'save'			=>	array('len' => 1, 'formHandler' => true)
			,'saveReminds'		=>	array('len' => 1)
			//,'destroy'		=>	array('len' => 1)
			,'setUserStatus'	=>	array('len' => 1)
			,'complete'		=>	array('len' => 1)
			,'close'		=>	array('len' => 1)
			,'reopen'		=>	array('len' => 1)
			,'getUserTasks'		=> 	array('len' => 1)
			,'getCaseTasks'		=>	array('len' => 1)
			,'getAssociableTasks'	=> 	array('len' => 1)
			,'getTasksByLawyer' 	=> 	array('len' => 0)
			,'browse' 		=> 	array('len' => 1)
		)
	)
	
	,'Objects' => array(
		'methods'=>array(
			'load'				=>	array('len'=>1)
			,'save'			=>	array('len'=>1, 'formHandler' => true)
			//,'destroy'			=>	array('len'=>1)
			,'getViolations'		=>	array('len'=>1)
			,'getAssociatedObjects'		=>	array('len'=>1)
		)
	)

	,'Files' => array(
		'methods'=>array(
			'getProperties'		=>	array('len'=>1)
			,'restoreVersion'		=>	array('len'=>1)
			,'deleteVersion'		=>	array('len'=>1)
			,'merge'			=>	array('len'=>1)
			,'getDuplicates'		=>	array('len'=>1)
		)
	)
	
	,'Thesauri' => array(
		'methods'=>array(
			'create'	=>	array('len'=>1)
			,'read'	=>	array('len'=>1)
			,'update'	=>	array('len'=>1)
			,'destroy'	=>	array('len'=>1)
		)
	)
	
	,'Templates' => array(
		'methods'=>array(
			'getChildren'	=>	array('len'=>1)
			,'saveElement'	=>	array('len'=>1)
			,'deleteElement'	=>	array('len'=>1)
			,'moveElement'	=>	array('len'=>1)
			,'readAll'		=>	array('len'=>1)
			,'loadTemplate'	=>	array('len'=>1)
			,'getTemplatesStructure'	=>	array('len'=>0)
			,'saveTemplate'	=>	array('len'=>1, 'formHandler' => true)
			,'getCaseTypeTempleId'	=>	array('len'=>1)
		)
	)
	
	,'Log' => array(
		'methods' => array(
			'getLastLog' => array('len' => 0)
		)
	)
	,'User' => array(
		'methods' => array(
			'getMainMenuItems' 	=> array('len' => 0)
			,'uploadPhoto' 		=> array('len' => 1, 'formHandler' => true)
		)
	)
	,'UsersGroups' => array(
		'methods' => array(
			'getChildren' 			=> array('len' => 1)
			,'getReadAccessChildren'	=> array('len' => 1)
			,'getUserData'	 		=> array('len' => 1)
			,'getAccessData' 		=> array('len' => 1)
			,'saveUserData'	 		=> array('len' => 1, 'formHandler' => true)
			,'saveAccessData' 		=> array('len' => 1)
			,'addUser'				=> array('len' => 1)
			,'addGroup'			=> array('len' => 1)
			,'deleteGroup'			=> array('len' => 1)
			,'associate'			=> array('len' => 2)
			,'deassociate'			=> array('len' => 2)
			,'deleteUser'			=> array('len' => 1)
			,'changeRole'			=> array('len' => 3)
			,'changePassword' 		=> array('len' => 1, 'formHandler' => true)
			,'changeUsername' 		=> array('len' => 1)
			,'setRoleActive'			=> array('len' => 3)
			,'getUserTags'			=> array('len' => 0)
			,'addUserTag'			=> array('len' => 1)
			,'searchUserTags'		=> array('len' => 1)
			,'searchSysTags' 		=> array('len' => 1)
		)
	)
	,'Security' => array(
		'methods' => array(
			'getUserGroups'		=> array('len' => 0)
			,'createUserGroup'		=> array('len' => 1)
			,'updateUserGroup'		=> array('len' => 1)
			,'destroyUserGroup'		=> array('len' => 1)
			
			,'searchUserGroups'		=> array('len' => 1)
			
			,'getObjectAcl'			=> array('len' => 1)
			,'addObjectAccess'		=> array('len' => 1)
			,'updateObjectAccess'		=> array('len' => 1)
			,'destroyObjectAccess'		=> array('len' => 1)
			
			/*old methods */
			,'getManagedOffices'		=> array('len' => 1)
			,'getOfficeUsers'		=> array('len' => 1)
			,'getCaseLawyers'		=> array('len' => 1)
			,'getCaseLowerLevelUsers'	=> array('len' => 1)
			,'getLowerLevelUsers'	=> array('len' => 1)
			,'initTest'				=> array('len' => 0)
		)
	)
	,'System' => array(
		'methods' => array(
			'tagsGetChildren'			=> array('len' => 1)
			,'getTagPath'				=> array('len' => 1)
			,'tagsSaveElement'			=> array('len' => 1)
			,'tagsMoveElement'			=> array('len' => 1)
			,'tagsDeleteElement'			=> array('len' => 1)
			,'tagsSortChilds'				=> array('len' => 1)
			,'tagGroupsGetChildren'		=> array('len' => 1)
			,'tagsGroupSave'			=> array('len' => 1)
			,'tagsGroupDelete'			=> array('len' => 1)
			,'tagsGroupAddElement'		=> array('len' => 1)
			,'tagsGroupRemoveElement'	=> array('len' => 1)
			,'getTagGroupsTree'			=> array('len' => 1)
			,'templateAssociateToTag'		=> array('len' => 1)
			,'templateDeassociateFromTag'	=> array('len' => 1)
			,'templateMoveToTag'		=> array('len' => 1)
		)
	)
)
);
?>