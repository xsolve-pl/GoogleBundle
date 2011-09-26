<?php

namespace AntiMattr\GoogleBundle\Maps;

class JavascriptMap extends AbstractMap
{
    public function render()
    {
        $content = '';
        $content .= '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>';
        $content .= '<div id="map_canvas" style="width: 100%; height: 400px"></div>';
        $content .= '<script type="text/javascript">
            var myLatlng = new google.maps.LatLng(-34.397, 150.644);
        var myOptions = {
              zoom: 8,
                    center: myLatlng,
                      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);
</script>';
        return $content;
    }
}
