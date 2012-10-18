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
    const API_ENDPOINT = 'http://maps.googleapis.com/maps/api/js?';

    const TYPE_ROADMAP = 'ROADMAP';
    const TYPE_SATELLITE = 'SATELLITE';
    const TYPE_HYBRID = 'HYBRID';
    const TYPE_TERRAIN = 'TERRAIN';

    /**
     * @var \AntiMattr\GoogleBundle\Maps\Marker $center
     */
    protected $center;

    /**
     * @var string $type
     */
    protected $type = self::TYPE_ROADMAP;

    /**
     * @var boolean $sensor
     */
    protected $sensor = false;

    /**
     * @var integer $zoom
     */
    protected $zoom = 1;

    /**
     * @var boolean $fitToMarkers
     */
    protected $fitToMarkers = false;

    protected static $typeChoices = array(
        self::TYPE_ROADMAP => 'Road map',
        self::TYPE_SATELLITE => 'Satellite',
        self::TYPE_HYBRID => 'Hybrid',
        self::TYPE_TERRAIN => 'Terrain',
    );

    /**
     * @var string $clickCallback
     */
    protected $clickCallback;

    /**
     * Set map type
     *
     * Available types:
     *     - JavascriptMap::TYPE_ROADMAP
     *     - JavascriptMap::TYPE_SATELLITE
     *     - JavascriptMap::TYPE_HYBRID
     *     - JavascriptMap::TYPE_TERRAIN
     *
     * @param  string $type
     * @return void
     */
    public function setType($type)
    {
        $type = (string) $type;
        if (false === $this->isTypeValid($type)) {
            throw new \InvalidArgumentException(sprintf("'%ss' is not a valid Javascript map type.", $type));
        }
        $this->type = $type;
    }

    /**
     * Validate map type
     *
     * @param mixed $type
     * @static
     * @return bool
     */
    public static function isTypeValid($type)
    {
        return array_key_exists($type, static::$typeChoices);
    }

    /**
     * Get map type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set map center
     *
     * @param  mixed $marker
     * @return void
     */
    public function setCenter(Marker $marker)
    {
        $this->center = $marker;
    }

    /**
     * Get map center
     *
     * @return mixed
     */
    public function getCenter()
    {
        if ($this->center) {
            return $this->center;
        }
        if ($this->hasMarkers()) {
            return array_shift($this->getMarkers());
        }
    }

    /**
     * Set sensor
     *
     * @param boolean $sensor
     */
    public function setSensor($sensor)
    {
        $this->sensor = (bool) $sensor;
    }

    /**
     * Get sensor
     *
     * @return boolean
     */
    public function getSensor()
    {
        return $this->sensor;
    }

    /**
     * Set zoom
     *
     * @param integer $zoom
     */
    public function setZoom($zoom)
    {
        $this->zoom = (int) $zoom;
    }

    /**
     * Get zoom
     *
     * @return integer
     */
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
     * Set fit to markers setting (both center and zoom)
     *
     * @param  bool $value
     * @return void
     */
    public function setFitToMarkers($value)
    {
        $this->fitToMarkers = (bool) $value;
    }

    /**
     * Get fit to markers setting
     *
     * @return bool
     */
    public function getFitToMarkers()
    {
        return $this->fitToMarkers;
    }

    /**
     * Render HTML container
     *
     * @return string
     */
    public function renderContainer()
    {
        return sprintf('<div id="%s"></div>', $this->getId());
    }

    /**
     * Render Javascripts settings
     *
     * @return string
     */
    public function renderJavascript()
    {
        $latitude = $this->center ? $this->center->getLatitude() : 0;
        $longitude = $this->center ? $this->center->getLongitude() : 0;

        //Initialize
        $content = sprintf(
            'GoogleBundle.Map("%s").initialize({ zoom: %d, center: new google.maps.LatLng( %F, %F ), MapTypeId: google.maps.MapTypeId.%s }); ',
            $this->id, $this->zoom, $latitude, $longitude, $this->type
        );

        //Add markers
        if ($this->hasMarkers()) {
            foreach ($this->getMarkers() as $marker) {
                $meta = $marker->getMeta();
                $infowindow = isset($meta['infowindow']) ? $meta['infowindow'] : '';
                $content .= sprintf(
                    'GoogleBundle.Map("%s").addMarker(%F, %F, "%s"); ',
                    $this->id, $marker->getLatitude(), $marker->getLongitude(), $infowindow
                );
            }
        }

        if ($this->fitToMarkers) {
            $content .= sprintf('GoogleBundle.Map("%s").fitToMarkers(); ', $this->id);
        }

        if (!empty($this->clickCallback)) {
            $content .= sprintf('GoogleBundle.Map("%s").click(%s); ', $this->id, $this->clickCallback);
        }

        return $content;
    }

    /**
     * Set Javascript click callback
     *
     * @param  string $callback Javascript function with one argument (coordinates).
     * @return void
     */
    public function setClickCallback($callback)
    {
        $this->clickCallback = $callback;
    }

    /**
     * Get HTML script tag with GoogleMaps API library.
     *
     * @return string
     */
    public function getGoogleMapLibrary()
    {
        $request = static::API_ENDPOINT . ($this->getSensor() ? 'sensor=true&' : 'sensor=false&');
        $request = rtrim($request, "& ");

        return $request;
    }

    /**
     * Render map and settings
     *
     * @return string
     */
    public function render()
    {
        $content = $this->renderContainer();
        $content .= '<script type="text/javascript">' . $this->renderJavascript() . '</script>';

        return $content;
    }
}
