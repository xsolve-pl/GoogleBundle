<?php

namespace AntiMattr\GoogleBundle\Maps;

class JavascriptMap extends AbstractMap
{
    const API_ENDPOINT = 'http://maps.googleapis.com/maps/api/js?';

    const TYPE_ROADMAP = 'ROADMAP';
    const TYPE_SATELLITE = 'SATELLITE';
    const TYPE_HYBRID = 'HYBRID';
    const TYPE_TERRAIN = 'TERRAIN';

    protected $center = false;
    protected $type = self::TYPE_ROADMAP;
    protected $fitToMarkers = false;

    static protected $typeChoices = array(
        self::TYPE_ROADMAP => 'Road Map',
        self::TYPE_SATELLITE => 'Satellite',
        self::TYPE_HYBRID => 'Hybrid',
        self::TYPE_TERRAIN => 'Terrain',
    );

    protected $templating;
    protected $clickCallback = null;

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

    public function setCenter($marker)
    {
        $this->center = $marker;
        $this->fitToMarkers = false;
    }

    public function getFitToMarkers()
    {
        return $this->fitToMarkers;
    }

    public function setFitToMarkers($value)
    {
        $this->fitToMarkers = (bool) $value;
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

    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    public function renderContainer()
    {
        return $this->templating->render('GoogleBundle:Maps:container.html.twig', array(
            'id' => $this->getId(),
        ));
    }

    public function renderJavascript()
    {
        return $this->templating->render('GoogleBundle:Maps:javascript.js.twig', array(
            'map' => $this,
            'latitude' => $this->center ? $this->center->getLatitude() : 0,
            'longitude' => $this->center ? $this->center->getLongitude() : 0,
            'zoom' => $this->zoom,
            'type' => $this->type,
            'map_id' => $this->getId(),
            'callback' => $this->clickCallback,
        ));
    }
    
    public function setClickCallback($callback)
    {
        $this->clickCallback = $callback;
    }

    public function getGoogleMapLibrary()
    {
        $request = static::API_ENDPOINT;
        $request .= $this->getSensor() ? 'sensor=true&' : 'sensor=false&';
        $request = rtrim($request, "& ");
        $content = sprintf('<script type="text/javascript" src="%s"></script>', $request);
        return $content;
    }

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
