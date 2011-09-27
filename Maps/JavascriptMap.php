<?php

namespace AntiMattr\GoogleBundle\Maps;

class JavascriptMap extends AbstractMap
{
    const API_ENDPOINT = 'http://maps.googleapis.com/maps/api/js?';

    const TYPE_ROADMAP = 'ROADMAP';
    const TYPE_SATELLITE = 'SATELLITE';
    const TYPE_HYBRID = 'HYBRID';
    const TYPE_TERRAIN = 'TERRAIN';

    protected $sensor = false;
    protected $type = self::TYPE_ROADMAP;
    protected $zoom = 1;
    protected $center = null;

    static protected $typeChoices = array(
        self::TYPE_ROADMAP => 'Road Map',
        self::TYPE_SATELLITE => 'Satellite',
        self::TYPE_HYBRID => 'Hybrid',
        self::TYPE_TERRAIN => 'Terrain',
    );

    public function setSensor($sensor)
    {
        $this->sensor = (bool) $sensor;
    }

    public function getSensor()
    {
        return $this->sensor;
    }

    public function setType($type)
    {
        $type = (string) $type;
        if (false === $this->isTypeValid($type)) {
            throw new \InvalidArgumentException($type.' is not a valid Javascript Map Type.');
        }
        $this->type = $type;
    }

    static public function isTypeValid($type)
    {
        return array_key_exists($type, static::$typeChoices);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setZoom($zoom)
    {
        $this->zoom = $zoom;
    }

    public function getZoom()
    {
        return $this->zoom;
    }

    public function setCenter($marker)
    {
        $this->center = $marker;
    }

    public function getCenter()
    {
        if (null !== $this->center)
        {
            return $this->center;
        }
        elseif ($this->hasMarkers())
        {
            $markers = $this->getMarkers();
            return array_shift($markers);
        }
        return false;
    }

    protected function getMarkersJavascript()
    {
        $markers = array();
        if ($this->hasMarkers())
        {
            foreach ($this->getMarkers() as $marker) 
            {
                $markers[] = sprintf('new google.maps.LatLng(%s, %s)', 
                    $marker->getLatitude(), 
                    $marker->getLongitude());
            }
        }
        return implode($markers, ',');
    }

    public function getOptionsJavascript() 
    {
        $center = $this->getCenter();
        $latitude = 0;
        $longitude = 0;

        if ($center)
        {
            $latitude = $center->getLatitude();
            $longitude = $center->getLongitude();
        }
        return sprintf('zoom: %s, center: new google.maps.LatLng(%s,%s), MapTypeId: google.maps.MapTypeId.%s', 
            $this->getZoom(),
            $latitude,
            $longitude,
            $this->getType()
        );
    }

    public function getMarkersInfowindowJavascript()
    {
        $content = '';
        if ($this->hasMarkers())
        {
            foreach ($this->getMarkers() as $marker)
            {
                $meta = $marker->getMeta();
                if (isset($meta['infowindow']))
                {

                }
            }
        }
        return $content;
    }

    public function render()
    {
        $request = static::API_ENDPOINT;
        $request .= $this->getSensor() ? 'sensor=true&' : 'sensor=false&';
        $request = rtrim($request, "& ");

        $content = sprintf('<script type="text/javascript" src="%s"></script>', $request);
        $content .= sprintf('<div id="%s"></div>', $this->getId());
        $content .= '<script type="text/javascript">';
        $content .= sprintf('var markers = [%s]; ', $this->getMarkersJavascript());
        $content .= sprintf('var options = { %s }; ', $this->getOptionsJavascript());
        $content .= sprintf('var map = new google.maps.Map(document.getElementById("%s"), options); ', $this->getId());
        $content .= 'for (index in markers) { ';
        $content .= 'markers[index] = new google.maps.Marker({ position: markers[index], map: map, title: "x"});';
        $content .= "} \n";
        $content .= sprintf('var infowindows = [%s]; ', $this->getMarkersInfowindowJavascript());
        $content .= '</script>';

        return $content;
    }

    
}
