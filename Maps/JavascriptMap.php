<?php

namespace AntiMattr\GoogleBundle\Maps;
use AntiMattr\GoogleBundle\Maps\Marker;
/**
 * JavascriptMap 
 * 
 * @uses AbstractMap
 * @package GoogleBundle
 * @author Marcin Dryka <marcin@dryka.pl> 
 */
class JavascriptMap extends AbstractMap
{
    const API_ENDPOINT = 'http://maps.googleapis.com/maps/api/js?'; //Google API endpoint

    // Google Maps supported types:
    const TYPE_ROADMAP = 'ROADMAP';     
    const TYPE_SATELLITE = 'SATELLITE';
    const TYPE_HYBRID = 'HYBRID';
    const TYPE_TERRAIN = 'TERRAIN';

    // Google Map Center
    protected $center = false;

    // Default map type
    protected $type = self::TYPE_ROADMAP;

    // Default do not fit to markers
    protected $fitToMarkers = false;

    // Google Maps types array
    static protected $typeChoices = array(
        self::TYPE_ROADMAP => 'Road Map',
        self::TYPE_SATELLITE => 'Satellite',
        self::TYPE_HYBRID => 'Hybrid',
        self::TYPE_TERRAIN => 'Terrain',
    );

    // Javascript click callback for map
    protected $clickCallback = null;

    /**
     * setType Set Google Map type 
     * available options:
     *   - JavascriptMap::TYPE_ROADMAP
     *   - JavascriptMap::TYPE_SATELLITE
     *   - JavascriptMap::TYPE_HYBRID
     *   - JavascriptMap::TYPE_TERRAIN
     * 
     * @param string $type 
     * @return void
     */
    public function setType($type)
    {
        $type = (string) $type;
        if (false === $this->isTypeValid($type)) {
            throw new \InvalidArgumentException($type.' is not a valid Javascript Map Type.');
        }
        $this->type = $type;
    }

    /**
     * isTypeValid Validate Google Map type
     * 
     * @param mixed $type 
     * @static
     * @return bool
     */
    static public function isTypeValid($type)
    {
        return array_key_exists($type, static::$typeChoices);
    }

    /**
     * getType get Google Map type
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * setCenter Set map center
     * 
     * @param mixed $marker 
     * @return void
     */
    public function setCenter(Marker $marker)
    {
        $this->center = $marker;
    }

    /**
     * getCenter Get map center
     * 
     * @return void
     */
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

    /**
     * setFitToMarkers If set to true, Google Map is fit to markers (also calculate zoom)
     * 
     * @param bool $value 
     * @return void
     */
    public function setFitToMarkers($value)
    {
        $this->fitToMarkers = (bool) $value;
    }

    /**
     * getFitToMarkers Get fitToMarkers setting value
     * 
     * @return bool
     */
    public function getFitToMarkers()
    {
        return $this->fitToMarkers;
    }

    /**
     * renderContainer Render HTML container
     * 
     * @return string
     */
    public function renderContainer()
    {
        return sprintf('<div id="%s"></div>', $this->getId());
    }

    /**
     * renderJavascript Render Javascripts settings
     * 
     * @return string
     */
    public function renderJavascript()
    {
        $latitude = $this->center ? $this->center->getLatitude() : 0;
        $longitude = $this->center ? $this->center->getLongitude() : 0;
        //Initialize
        $content = sprintf('GoogleBundle.Map("%s").initialize({ zoom: %d, center: new google.maps.LatLng( %F, %F ), MapTypeId: google.maps.MapTypeId.%s }); ',
            $this->id, $this->zoom, $latitude, $longitude, $this->type);

        //Add markers
        if ($this->hasMarkers())
        {
            foreach ($this->getMarkers() as $marker)
            {
                $meta = $marker->getMeta();
                $infowindow = isset($meta['infowindow']) ? $meta['infowindow'] : '';

                $content .= sprintf('GoogleBundle.Map("%s").addMarker(%F, %F, "%s"); ',
                    $this->id, $marker->getLatitude(), $marker->getLongitude(), $infowindow);
            }
        }

        if ($this->fitToMarkers)
        {
            $content .= sprintf('GoogleBundle.Map("%s").fitToMarkers(); ', $this->id);
        }

        if (!empty($this->clickCallback))
        {
            $content .= sprintf('GoogleBundle.Map("%s").click(%s); ', $this->id, $this->clickCallback);
        }
        return $content;
    }
    
    /**
     * setClickCallback Set Javascript click callback (Javascript function)
     * 
     * @param string $callback Javascript function with one argument (coordinates). 
     * @return void
     */
    public function setClickCallback($callback)
    {
        $this->clickCallback = $callback;
    }

    /**
     * getGoogleMapLibrary Get HTML script tag with google map api library
     * 
     * @return string
     */
    public function getGoogleMapLibrary()
    {
        $request = static::API_ENDPOINT;
        $request .= $this->getSensor() ? 'sensor=true&' : 'sensor=false&';
        $request = rtrim($request, "& ");
        return $request;
    }

    /**
     * render Google Map container and settings
     * 
     * @return string
     */
    public function render()
    {
        //Get HTML container 
        $content = $this->renderContainer();

        //Get required Javascript 
        $content .= '<script type="text/javascript">';
        $content .= $this->renderJavascript();
        $content .= '</script>';

        return $content;
    }
}
