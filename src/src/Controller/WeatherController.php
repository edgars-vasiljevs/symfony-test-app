<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Weather\ClientGeoLocation;
use App\Weather\ClientIpAddress;
use App\Weather\Weather;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    /**
     * @var Weather
     */
    protected Weather $weather;

    /**
     * @var ClientGeoLocation
     */
    protected ClientGeoLocation $geo;

    /**
     * @var ClientIpAddress
     */
    protected ClientIpAddress $ip;

    /**
     * @var LocationRepository
     */
    protected LocationRepository $locationRepository;

    /**
     * WeatherController constructor.
     * @param LocationRepository $locationRepository
     * @param ClientGeoLocation $geo
     * @param ClientIpAddress $ip
     * @param Weather $weather
     */
    public function __construct(
        LocationRepository $locationRepository,
        ClientGeoLocation $geo,
        ClientIpAddress $ip,
        Weather $weather)
    {
        $this->locationRepository = $locationRepository;
        $this->geo = $geo;
        $this->ip = $ip;
        $this->weather = $weather;

        $this->weather->setCacheKey($ip->get());
    }

    /**
     * @Route("/weather/{refresh}", name="weather", methods={"GET"})
     */
    public function index(string $refresh = null): Response
    {
        $location = $this->locationRepository
            ->findBy(['ip' => $this->ip->get()]);

        if ($location) {
            $geo = $location[0]->toArray();
        }
        else {
            $geo = $this->geo->getGeoLocation($this->ip->get());

            $location = new Location();
            $location->setIp($this->ip->get());
            $location->setLat($geo[0]);
            $location->setLon($geo[1]);

            $em = $this->getDoctrine()->getManager();
            $em->persist($location);
            $em->flush();
        }

        $forecast = $this->weather->getForecastByGeoLocation($geo, $refresh === 'refresh');

        return $this->json($forecast);
    }

}
