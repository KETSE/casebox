<?php
namespace ExtDirect;

$apiList = \CB\Config::getApiList();
$API = empty($apiList) ? array() : $apiList;
$API = array_merge(
    $API,
    array(
        'CB_Browser' => array(
            'methods' => array(
                'createFolder'          => array('len' => 1)
                ,'paste'                => array('len' => 1)
                ,'saveFile'             => array('len' => 1, 'formHandler' => true)
                ,'confirmUploadRequest' => array('len' => 1)
                ,'delete'               => array('len' => 1)
                ,'restore'              => array('len' => 1)
                ,'toggleFavorite'       => array('len' => 1)
                ,'takeOwnership'        => array('len' => 1)
                ,'getObjectsForField'   => array('len' => 1)
                ,'subscribe'            => array('len' => 1)
                ,'unsubscribe'          => array('len' => 1)
            )
        )

        ,'CB_Browser_Actions' => array(
            'methods' => array(
                'copy'          => array('len' => 1)
                ,'move'         => array('len' => 1)
                ,'shortcut'     => array('len' => 1)
            )
        )

        ,'CB_Path' => array(
            'methods' => array(
                'getPath'       => array('len' => 1)
                ,'getPidPath'   => array('len' => 1)
            )
        )

        ,'CB_BrowserTree' => array(
            'methods' => array(
                'getChildren'       => array('len' => 1)
                ,'createFolder'     => array('len' => 1)
                ,'delete'           => array('len' => 1)
                ,'rename'           => array('len' => 1)
                ,'getRootProperties'=> array('len' => 1)
            )
        )

        ,'CB_BrowserView' => array(
            'methods' => array(
                'getChildren'       => array('len' => 1)
                ,'createFolder'     => array('len' => 1)
                ,'delete'           => array('len' => 1)
                ,'rename'           => array('len' => 1)
            )
        )

        ,'CB_Favorites' => array(
            'methods'=>array(
                'create'        =>  array('len' => 1)
                ,'read'         =>  array('len' => 1)
                ,'update'       =>  array('len' => 1)
                ,'destroy'      =>  array('len' => 1)
            )
        )

        ,'CB_Tasks' => array(
            'methods'=>array(
                'setUserStatus'    =>  array('len' => 1)
                ,'complete'         =>  array('len' => 1)
                ,'close'            =>  array('len' => 1)
                ,'reopen'           =>  array('len' => 1)
                ,'updateDates'      =>  array('len' => 1)
            )
        )

        ,'CB_Objects' => array(
            'methods'=>array(
                'load'                  =>  array('len'=>1)
                ,'create'               =>  array('len'=>1)
                ,'save'                 =>  array('len'=>1, 'formHandler' => true)
                ,'getAssociatedObjects' =>  array('len'=>1)
                ,'getPluginsData'       =>  array('len'=>1)
                ,'getBasicInfoForId'    =>  array('len'=>1)
                ,'addComment'           =>  array('len'=>1)
                ,'updateComment'        =>  array('len'=>1)
                ,'removeComment'        =>  array('len'=>1)
            )
        )
        ,'CB_Objects_Plugins_Comments' => array(
            'methods'=>array(
                'loadMore'              =>  array('len'=>1)
            )
        )

        ,'CB_Files' => array(
            'methods'=>array(
                'getProperties'         =>  array('len'=>1)
                ,'getContent'           =>  array('len'=>1)
                ,'saveContent'          =>  array('len'=>1)
                ,'restoreVersion'       =>  array('len'=>1)
                ,'deleteVersion'        =>  array('len'=>1)
                ,'getDuplicates'        =>  array('len'=>1)
                ,'checkExistentContents'=>  array('len'=>1)
                ,'saveProperties'       =>  array('len'=>1)
            )
        )

        ,'CB_Templates' => array(
            'methods'=>array(
                'readAll'=> array('len' => 1)
                ,'getTemplatesStructure'=> array('len'=>0)
            )
        )

        ,'CB_User' => array(
            'methods' => array(
                'getLoginInfo'        => array('len' => 0)
                ,'login'              => array('len' => 2)
                ,'logout'             => array('len' => 0)
                ,'setLanguage'        => array('len' => 1)
                ,'setTheme'           => array('len' => 1)
                ,'uploadPhoto'        => array('len' => 1, 'formHandler' => true)
                ,'removePhoto'        => array('len' => 1)
                ,'getAccountData'     => array('len' => 0)
                ,'getProfileData'     => array('len' => 1)
                ,'saveProfileData'    => array('len' => 1)
                ,'saveSecurityData'   => array('len' => 1)
                ,'verifyPassword'     => array('len' => 1)
                ,'verifyPhone'        => array('len' => 1)
                ,'getTSVTemplateData' => array('len' => 1)
                ,'enableTSV'          => array('len' => 1)
                ,'disableTSV'         => array('len' => 0)
            )
        )
        ,'CB_UsersGroups' => array(
            'methods' => array(
                'getChildren'           => array('len' => 1)
                ,'getUserData'          => array('len' => 1)
                ,'getAccessData'        => array('len' => 1)
                ,'saveAccessData'       => array('len' => 1)
                ,'addUser'              => array('len' => 1)
                ,'associate'            => array('len' => 2)
                ,'deassociate'          => array('len' => 2)
                ,'deleteUser'           => array('len' => 1)
                ,'changePassword'       => array('len' => 1, 'formHandler' => true)
                ,'sendResetPassMail'    => array('len' => 1)
                ,'renameUser'           => array('len' => 1)
                ,'renameGroup'          => array('len' => 1)
                ,'disableTSV'           => array('len' => 1)
                ,'setUserEnabled'       => array('len' => 1)
            )
        )

        ,'CB_Security' => array(
            'methods' => array(
                'getUserGroups'         => array('len' => 1)
                ,'createUserGroup'      => array('len' => 1)
                ,'updateUserGroup'      => array('len' => 1)
                ,'destroyUserGroup'     => array('len' => 1)

                ,'searchUserGroups'     => array('len' => 1)

                ,'getObjectAcl'         => array('len' => 1)
                ,'addObjectAccess'      => array('len' => 1)
                ,'updateObjectAccess'   => array('len' => 1)
                ,'destroyObjectAccess'  => array('len' => 1)
                ,'setInheritance'       => array('len' => 1)

                ,'getActiveUsers'       => array('len' => 1)
                ,'removeChildPermissions'=> array('len' => 1)
            )
        )

        ,'CB_System' => array(
            'methods' => array(
                'getCountries'         => array('len' => 0)
                ,'getTimezones'        => array('len' => 0)
            )
        )

        ,'CB_Search' => array(
            'methods' => array(
                'load'       => array('len' => 1)
                ,'query'     => array('len' => 1)
            )
        )

        ,'CB_State_DBProvider' => array(
            'methods' => array(
                'read'               => array('len' => 0)
                ,'set'               => array('len' => 1)
                ,'saveGridViewState' => array('len' => 1)
            )
        )
    )
);

\CB\Cache::set('ExtDirectAPI', $API);
