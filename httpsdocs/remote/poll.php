<?php
namespace ExtDirect;

echo json_encode(
    array(
        'type'=>'event'
        ,'name'=>'message'
        ,'data'=>'Successfully polled at: '. date('g:i:s a')
    ),
    JSON_UNESCAPED_UNICODE
);
