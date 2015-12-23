<?php

namespace CB\Import;

use CB\Objects;
use CB\Util;
use CB\DB;
use CB\DataModel as DM;
use CB\Import\BareBoneModel as BBM;

/**
 * Vanilla model script
 * designed to be applied on cores
 * instantiated from bare bone core
 */

class VanillaModel extends Base
{

    /**
     * grlobal script cofig that contain all options
     * @var array
     */
    protected $cfg = array(

        /*************************** USERS AND GROUPS **************************/
        'groups' => [
            'Administrators'
            ,'Lawyers'
        ]

        ,'users' => []

        /******************************* THESAURI ******************************/
        ,'thesauri' => array(
            // 'task' => array(
            //     //'tags' => array()
            //     //,'importance ( Critical – High – Medium- Low)
            //     //,'status (new – in progress – completed )

            // ),

            // 'link' => array(
            //     'type' => array(
            //         'Video'
            //         ,'Document'
            //         ,'Article'
            //         ,'Image'
            //         ,'Sound'
            //         ,'Website'
            //     )
            //     //,'tag' => array()
            // )

            // Casebox Vanilla
            'case' => array(
                'type' => array(
                    'Civil'
                    ,'Constitutional'
                    ,'Human Rights'
                    ,'Criminal'
                    ,'Military'
                )
                ,'tags' => array(
                    'Torture'
                    ,'Assault'
                    ,'Murder'
                    ,'Housing'
                    ,'Child'
                    ,'Arrest'
                    ,'Health'
                )
                ,'status' => array(
                    'New'
                    ,'Ongoing'
                    ,'On Hold'
                    ,'Archived'
                    ,'Closed'
                )
                ,'contact role' => array(
                    'Defendant'
                    ,'Government Official'
                    ,'Lawyer'
                    ,'Perpetrator'
                    ,'Relative'
                    ,'Source'
                    ,'Victim'
                    ,'Witness'
                )
            )
            ,'Country' => array(
                'Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica'
                ,'Antigua And Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Ascension', 'Australia', 'Austria', 'Azerbaijan'
                ,'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan'
                ,'Bolivia', 'Bosnia And Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory'
                ,'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Canary Islands'
                ,'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island'
                ,'Cocos (keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo, The Democratic Republic Of The', 'Cook Islands'
                ,'Costa Rica', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'CÔte D\'ivoire', 'Denmark', 'Diego Garcia', 'Djibouti'
                ,'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia'
                ,'Ethiopia', 'European Union', 'Falkland Islands (malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana'
                ,'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece'
                ,'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guinea-bissau', 'Guyana', 'Haiti'
                ,'Heard Island And Mcdonald Islands', 'Holy See (vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland'
                ,'India', 'Indonesia', 'Iran, Islamic Republic Of', 'Iraq', 'Ireland', 'Isle Of Man', 'Israel', 'Italy', 'Jamaica'
                ,'Japan', 'Jersey', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, Democratic People\'s Republic Of', 'Korea, Republic Of'
                ,'Kuwait', 'Kyrgyzstan', 'Lao People\'s Democratic Republic', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya'
                ,'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macao', 'Macedonia, The Former Yugoslav Republic Of', 'Madagascar', 'Malawi'
                ,'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico'
                ,'Micronesia, Federated States Of', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco', 'Mozambique'
                ,'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua'
                ,'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau'
                ,'Palestinian Territory, Occupied', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland'
                ,'Portugal', 'Puerto Rico', 'Qatar', 'Romania', 'Russian Federation', 'Rwanda', 'RÉunion', 'Saint Helena', 'Saint Kitts And Nevis'
                ,'Saint Lucia', 'Saint Pierre And Miquelon', 'Saint Vincent And The Grenadines', 'Samoa', 'San Marino', 'Sao Tome And Principe'
                ,'Saudi Arabia', 'Saudi–Iraqi neutral zone', 'Senegal', 'Serbia', 'Serbien und Montenegro (2003-02-04 - 2006-06-03)', 'Seychelles'
                ,'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia And The South Sandwich Islands'
                ,'Soviet Union', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard And Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland'
                ,'Syrian Arab Republic', 'Taiwan', 'Tajikistan', 'Tanzania, United Republic Of', 'Thailand', 'Timor-leste', 'Togo', 'Tokelau'
                ,'Tonga', 'Trinidad And Tobago', 'Tristan da Cunha', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks And Caicos Islands', 'Tuvalu'
                ,'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela'
                ,'Viet Nam', 'Virgin Islands, British', 'Virgin Islands, U.s.', 'Wallis And Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe'
                ,'Åland Islands'
            )
        )

        /******************************* TEMPLATES ******************************/

        ,'templates' => array(
            'Contact' => array(
                'type' => 'object'

                ,'fields' => array(
                    'first_name' => array('en' => 'First Name')
                    ,'last_name' => array('en' => 'Last Name')
                    ,'title' => array('en' => 'Title')
                    ,'organization' => array('en' => 'Organization')
                    ,'email' => array('en' => 'Email')
                    ,'phone' => array('en' => 'Phone')
                    ,'country' => array('en' => 'Country')
                )
            )

            ,'Organization' => array(
                'type' => 'object'

                ,'fields' => array(
                    'name' => array('en' => 'Name')
                    ,'email' => array('en' => 'Email')
                    ,'phone' => array('en' => 'Phone')
                    ,'country' => array(
                        'en' => 'Country'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/Country'
                        )
                    )
                    ,'website' => array('en' => 'Website')
                    ,'facebook' => array('en' => 'Facebook')
                    ,'twitter' => array('en' => 'Twitter')
                )
            )

            ,'Case' => array(
                'type' => 'case'
                ,'iconCls' => 'icon-briefcase'

                ,'fields' => array(
                    'name' => array('en' => 'Name')
                    ,'status' => array(
                        'en' => 'Case status'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/case/status'
                        )
                    )
                    ,'tags' => array(
                        'en' => 'Tags'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'multiValued' => true
                            ,'scope' => '/case/tags'
                        )
                    )
                    ,'type' => array(
                        'en' => 'Type'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/case/type'
                        )
                    )
                    ,'date' => array(
                        'en' => 'Date'
                        ,'type' => 'date'
                    )
                    ,'description' => array(
                        'en' => 'Description'
                        ,'type' => 'text'
                    )
                    // ,'region' => array(
                    //     'en' => 'Region'
                    //     ,'type' => '_objects'
                    //     ,'cfg' => array(
                    //         'source' => 'tree'
                    //         ,'scope' => '/Region'
                    //     )
                    // )
                    ,'country' => array(
                        'en' => 'Country'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/Country'
                        )
                    )
                    ,'contacts' => array(
                        'en' => 'Case contacts'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'multiValued' => true
                            // ,'scope' => null
                        )
                    )
                )
            )
        )
    );

    /**
     * executing preimporting changes to target core
     * @return void
     */
    protected function prepare()
    {
        //2.  Casebox Users and Groups
        $this->setupUsersGroups();

        // 6.  Thesauri
        echo "\nCreate thesauri items .. ";
        $this->createThesauri(
            BBM::$cfg['thesauriFolderId'],
            $this->cfg['thesauri'],
            '/'
        );

        echo "Done\n";

    }

    /**
     * applying vanilla changes to current core
     * @return void
     */
    public function execute()
    {

        // 3.  File Folder Structure
        $this->structureFilesAndFolders();

        // 4.  Custom Entities
        echo "\nCreate custom templates .. ";
        $this->addCustomTemplates();
        echo "Done\n";

        $this->updateCreateMenus();

    }

    /**
     * add default users and groups
     * @return void
     */
    protected function setupUsersGroups()
    {
        $groups = array(
            'Administrators'
            ,'Lawyers'
        );

        foreach ($groups as $group) {
            echo "creating group '$group' .. ";

            $id = DM\Group::getIdByName($group);

            if (empty($id)) {
                DM\Group::create(array('name' => $group));
            }

            echo "ok\n";
        }
    }

    /**
     * [structureFilesAndFolders description]
     * @return [type] [description]
     */
    protected function structureFilesAndFolders()
    {
    }

    /**
     * create thesauri
     * @return void
     */
    protected function createThesauri($pid, &$th, $prefix)
    {
        $o = new \CB\Objects\Object();

        foreach ($th as $k => &$v) {
            //create $k folder under pid
            echo "creating '$k' .. ";

            $id = $o->create(
                array(
                    'id' => null
                    ,'pid' => $pid
                    ,'template_id' => BBM::$cfg['folderTemplateId']
                    ,'name' => $k
                    ,'data' => array(
                        '_title'  => $k
                    )
                )
            );

            $this->thesauriIds[$prefix . $k] = array('id' => $id);
            echo "ok\n";

            if (Util\isAssocArray($v)) {
                //subfolders
                $this->createThesauri($id, $v, $prefix . $k . '/');

            } else {
                //create thesauri items
                $i = 1;
                foreach ($v as $item) {
                    $o->create(
                        array(
                            'id' => null
                            ,'pid' => $id
                            ,'template_id' => BBM::$cfg['thesauriTemplateId']
                            ,'name' => $item
                            ,'data' => array(
                                "en" => $item
                                ,"iconCls" => "icon-tag-small"
                                ,"visible" => 1
                                ,"order" => $i++
                            )
                        )
                    );
                }
            }
        }
    }

    /**
     * update create menus
     * @return void
     */
    protected function updateCreateMenus()
    {
        $rootId = \CB\Browser::getRootFolderId();

        $pid = Objects::getChildId($rootId, 'System');

        if (!empty($pid)) {
            $pid = Objects::getChildId($pid, 'Menus');
            if (!empty($pid)) {
                $res = DB\dbQuery(
                    'SELECT id
                    FROM tree
                    WHERE pid = $1
                        AND dstatus = 0',
                    $pid
                );

                if ($r = $res->fetch_assoc()) {
                    $o = Objects::getCachedObject($r['id']);
                    $d = $o->getData();
                    $d['data']['menu'] =
                            $this->templateIds['Case'] . ',' .
                            $this->templateIds['Contact'] . ',' .
                            $this->templateIds['Organization'] . ',' .
                            $d['data']['menu'];
                    $o->update($d);
                }
                $res->close();
            }
        }
    }
}
