<?php  

    namespace CB;
              
    $dir = getDir();

    include dirname(__FILE__).'/config.php';
    $timezone = \CB\config\timezone;
    date_default_timezone_set( empty($timezone) ? 'Europe/Chisinau' : $timezone );

    require_once('lib/DB.php');
    
    $counter = 0;
    
    DB\connect();     
    error_reporting(0);
        
    if($dir == 'import'){               

        $tesaurus = getTesaurus();
        
        $existing_ids = array();
        foreach($tesaurus as $t) $existing_ids[] = $t['id'] * 1;
        
        $file = null;
        if(isset($_FILES['file'])){
            if($_FILES['file']['name']=='') report(array('success'=>false, 'type'=>'nofile'));        
            if($_FILES['file']['type']!='application/vnd.ms-excel' || !preg_match('#csv$#',$_FILES['file']['name'])) report(array('success'=>false, 'type'=>'wrongtype'));
            $file = file($_FILES['file']['tmp_name']);
        }else{
            if($file = file('test.csv')){            
            }else
                report(array('success'=>false, 'type'=>'nofile'));
        }
        
        $array = rebuildImportFile($file);
       
        $ids = array();
        foreach($array as $row){   
            // id: 2   pid: 1
            if(check($row['id'],'int') && check($row['pid'],'int')){
                if(in_array($row['id'] * 1, $existing_ids)){
                    update($row);
                }else
                    insert_with_id($row);
            }                
                
            // id: 2   delete row
            if(check($row['id'],'int') && $row['type']=='d')
                delete($row);
                
            // id:    pid: 1  new row
            if(check($row['id'],'empty')  && check($row['pid'],'int') )    
                insert($row);
                
            // id: b   pid: 1            
            if(check($row['id'],'text') && check($row['pid'],'int')){
                if(!isset($ids[$row['id']])){
                    $ids[$row['id']] = insert($row);
                    continue;
                }
            }
            // id: b   pid: a 
            if(check($row['id'],'text') && check($row['pid'],'text')){                                
                if(isset($ids[$row['pid']])) $row['pid'] = $ids[$row['pid']];                
                if(isset($ids[$row['id']])) $row['id'] = $ids[$row['id']];
                
                // id: 2   pid: 1 
                if(check($row['id'],'int') && check($row['pid'],'int')){
                    insert($row);
                    continue;
                }
                if(check($row['id'],'text')){
                    $ids[$row['id']] = insert($row);
                    continue;                    
                }
            }
            // id:    pid: a
            if(check($row['id'],'empty') && check($row['pid'],'text')){
                if(isset($ids[$row['pid']])) $row['pid'] = $ids[$row['pid']];
                insert($row);                
            }
        }  
        report(array('success'=>true, 'node'=>$node));
    }else{       
        if(empty($_GET['node'])) exit(0);
        
        $n = $_GET['node'];
        $filename = $_SERVER['SERVER_NAME'].'_'.date('Y-m-d_Hi');
        
        $tesaurus = getTesaurus();
        $node = array2tree($tesaurus, $n);        
       
        
        //$header = "id;pid;type;l1;l2;l3;l4;l5;l6;l7;l8\n";
        $header = "id;pid;type;l1;l2;l3;l4\n";
        $csv = getCsvFromBranch(array(0=>$node));    
        
        sendCsv2Browser($filename, $header . $csv );
    }

    
    function report($array){
        echo json_encode($array);
        die();
    }
    // Import functions ------------  
    
    function check($item, $option){
        if($option == 'empty') return ($item == '' ? 1 : 0);
        if($option == 'int') return preg_match('#[0-9]{1,}#', $item);
        if($option == 'text') return preg_match('#[a-zA-Z]{1,}#', $item);
    }
    function update($array){
        global $count; $count++;
        
        
        if($array['type']=='F') $array['type'] = 0; else $array['type'] = 1;        
                
        $langs = array();
        foreach($array as $k=>$v)            
            if(preg_match('#^l[0-9]{1}$#', $k))
                $langs[] = $k.'="'.$v.'"';        
              
        $res = DB\mysqli_query_params('update `tags` set '.implode(',',$langs).', `pid`="'.$array['pid'].'", `order` = '.$count.'
            where id='.$array['id']) or die( DB\mysqli_query_error() );
    
    }
    function delete($array){            
        $res = DB\mysqli_query_params('DELETE from tags WHERE id='.$array['id']) or die( DB\mysqli_query_error() );
    }
    function insert($array){ 
        global $count; $count++;
           
        if($array['type']=='F') $array['type'] = 0; else $array['type'] = 1;     
        
        $langs = array();
        $keys = array();
        foreach($array as $k=>$v)            
            if(preg_match('#^l[0-9]{1}$#', $k)){
                $langs[$k] = '"'.$v.'"'; 
                $keys[] = $k;  
            }
                    
        $query = 'INSERT INTO tags (`order`, pid, type,'.implode(',',$keys).') VALUES ('.$count.','.$array['pid'].', '.$array['type'].', '.implode(',',$langs).')';        
        $res = DB\mysqli_query_params($query) or die( mysqli_query_error() );
        
        return mysqli_insert_id($GLOBALS['dbh']);    
    }
    function insert_with_id($array){  
        global $count; $count++;
        
        if($array['type']=='F') $array['type'] = 0; else $array['type'] = 1;     
        
        $langs = array();
        $keys = array();
        foreach($array as $k=>$v)            
            if(preg_match('#^l[0-9]{1}$#', $k)){
                $langs[$k] = '"'.$v.'"'; 
                $keys[] = $k;  
            }
                    
        $query = 'INSERT INTO tags (`order`, id, pid, type,'.implode(',',$keys).') VALUES ('.$count.','.$array['id'].','.$array['pid'].', '.$array['type'].', '.implode(',',$langs).')';        
        $res = DB\mysqli_query_params($query) or die( DB\mysqli_query_error() );
        
        return mysqli_insert_id($GLOBALS['dbh']);    
    }
    
    function rebuildImportFile($file){
        $headers = getImportFileHeaders($file[0]);
        unset($file[0]);
       
        $array = array();
        foreach($file as $k=>$line){
            $temp = mb_split(';', $line);
            $tempArray = array();            
            foreach($temp as $j=>$t){         
                $t = mb_ereg_replace('[\n"]{1,}','', $t);
                
                $tempArray[$headers[$j]] = mb_ereg_replace('[\n]{1,}','', $t);                  
            }
            $tempArray['csv'] =  mb_ereg_replace('[\n]{1,}','', $line);

            $array[] = $tempArray;            
        }
        return $array;
    }
    
    function getImportFileHeaders($file){
        $headers = mb_split(';' ,$file);
        
        $headKeys = array();
        foreach($headers as $k=>$h)
            $headKeys[$k] = mb_ereg_replace('[\n]{1,}','', $h);
                
        return $headKeys;
    }

    // -----------------------------    

    function getDir(){
        $dir = false;
        if(empty($_GET['dir'])) $dir = 'export';
        else($dir = array_search($_GET['dir'], array('import'=>'import', 'export'=>'export')));  
        if(!$dir) die();
        
        return $dir;
    }

    function getTesaurus(){
        $res = DB\mysqli_query_params('select id,pid, 
            CASE 
                  WHEN type = 0
                     THEN "F"
                  ELSE "" 
             END AS TYPE, l1, l2, l3, l4 from tags') or die( DB\mysqli_query_error() );
        $nodes = array(0 => array('id' => 0, 'pid' => 0));    
        while($r = $res->fetch_assoc()){
            $nodes[$r['id']] = $r;
            if($nodes[$r['id']]['pid']=='')
                $nodes[$r['id']]['pid'] = 0;
            $nodes[$r['id']]['csv'] = implode(';', $nodes[$r['id']]);
        }  
        return $nodes;  
    }

    function getCsvFromBranch($array){        
        $csv = '';        
        foreach($array as $key=>$branch){
            $csv .= $branch['csv'];            
            $csv .= "\n";
                        
            if(isset($branch['childs']))
                $csv .= getCsvFromBranch($branch['childs']);            
        }
        return $csv;
    }

    function array2tree($array, $i = null){
        $tree=array(0=>array('id'=>0, 'pid'=>0));
        $temp=array(0=>&$tree[0]);
         
        foreach ($array as $val)
        {
            $parent = &$temp[ $val['pid'] ];
            if (!isset($parent['childs'])) { $parent['childs'] = array(); }        
            $parent['childs'][$val['id']] = $val;                
            $temp[$val['id']] = &$parent['childs'][$val['id']];          
        }        
        if($i == null)
            return $temp;     
        else
            return $temp[$i];    
    }

    function sendCsv2Browser($filename, $content){
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=thesaurus_'.$filename.'.csv');
        header("Pragma: no-cache");  
        header("Expires: 0");      

        echo $content;        
    }
?>