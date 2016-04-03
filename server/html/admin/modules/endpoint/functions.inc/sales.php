<?php 

function endpoint_sales() {

        $html = '<br /><div style="margin-left:auto; margin-right:auto;">This feature is currently not licensed.  For more information on this feature or to purchase this Add On module please see the information below.</div>';
        $video_link = 'http://schmoozecom.com/endpoint-manager.php';
        $html .= '<div style="margin: 0 auto; width:100%; height:800px;"><object type="text/html" data="' . $video_link . '" style="width:100%; height:100%; margin:1%;"></object></div>';
        $html .= br(2);
        return $html;
}
?>