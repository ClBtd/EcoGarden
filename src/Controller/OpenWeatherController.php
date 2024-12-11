<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherController extends AbstractController
{
    #[Route('api/meteo', name: 'api_currentLocation_weather', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message:'Vous devez être connectés pour accéder à cette page.')]
    public function getWeatherByZipcode(CacheInterface $cache, HttpClientInterface $httpClient): JsonResponse
    {
        $zipcode = 69290;
        $cacheKey = 'weather_' . $zipcode;

        $weatherData = $cache->get($cacheKey, function (ItemInterface $item) use ($httpClient, $zipcode) {
            $item->expiresAfter(3600);

            try {
                $curlResponse = $httpClient->request('GET', 'http://api.openweathermap.org/geo/1.0/zip?zip='. $zipcode . ',FR&appid=d349b955093f2e4e23d1e99a66784187');
                $coordinates = $curlResponse->toArray();
                if (empty($coordinates)) {
                    throw new \Exception('Code postal inexistant.');
                }   
                $response = $httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather?lat='. $coordinates['lat'] .'&lon='. $coordinates['lon'] .'&appid=d349b955093f2e4e23d1e99a66784187');
                return $response->toArray();
            }
            catch (\Exception $e) {
                return [
                    'error' => 'Erreur lors de la récupération des données météo.',
                    'message' => $e->getMessage(),
                ];
            }

        });

        return new JsonResponse($weatherData);
    }

    #[Route('api/meteo/{city}', name: 'api_location_weather', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message:'Vous devez être connectés pour accéder à cette page.')]
    public function getWeatherByCity(string $city, CacheInterface $cache, HttpClientInterface $httpClient): JsonResponse
    {
        $cacheKey = 'weather_' . $city;

        $weatherData = $cache->get($cacheKey, function (ItemInterface $item) use ($httpClient, $city) {
            $item->expiresAfter(3600);

            try {
                $curlResponse = $httpClient->request('GET', 'http://api.openweathermap.org/geo/1.0/direct?q='. $city . ',FR&appid=d349b955093f2e4e23d1e99a66784187');
                $arrayResponse = $curlResponse->toArray();
                if (empty($arrayResponse)) {
                    throw new \Exception('Aucune ville trouvée.');
                }    
                $coordinates = $arrayResponse[0];
                $response = $httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather?lat='. $coordinates['lat'] .'&lon='. $coordinates['lon'] .'&appid=d349b955093f2e4e23d1e99a66784187');
                return $response->toArray();
            } catch (\Exception $e) {
                return [
                    'error' => 'Erreur lors de la récupération des données météo.',
                    'message' => $e->getMessage(),
                ];
            }
        });

        return new JsonResponse($weatherData);
    }
}
