<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherController extends AbstractController
{
    #[Route('api/meteo', name: 'api_currentLocation_weather', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message:'Vous devez être connectés pour accéder à cette page.')]
    public function getWeatherByZipcode(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository, CacheInterface $cache, HttpClientInterface $httpClient): JsonResponse
    {
        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());
        $currentUser = $userRepository->getUserByName($decodedJwtToken['username']);
        $zipcode = $currentUser->getZipcode();
        $cacheKey = 'weather_' . $zipcode;
        $apiKey = $_ENV['API_KEY'];

        $weatherData = $cache->get($cacheKey, function (ItemInterface $item) use ($httpClient, $zipcode, $apiKey) {
            $item->expiresAfter(3600);

            try {
                $curlResponse = $httpClient->request('GET', 'http://api.openweathermap.org/geo/1.0/zip?zip='. $zipcode . ',FR&appid=' . $apiKey);
                $coordinates = $curlResponse->toArray();
                if (empty($coordinates)) {
                    throw new \Exception('Code postal inexistant.');
                }   
                $response = $httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather?lat='. $coordinates['lat'] .'&lon='. $coordinates['lon'] .'&appid=' . $apiKey);
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
        $apiKey = $_ENV['API_KEY'];

        $weatherData = $cache->get($cacheKey, function (ItemInterface $item) use ($httpClient, $city, $apiKey) {
            $item->expiresAfter(3600);

            try {
                $curlResponse = $httpClient->request('GET', 'http://api.openweathermap.org/geo/1.0/direct?q='. $city . ',FR&appid=' . $apiKey);
                $arrayResponse = $curlResponse->toArray();
                if (empty($arrayResponse)) {
                    throw new \Exception('Aucune ville trouvée.');
                }    
                $coordinates = $arrayResponse[0];
                $response = $httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather?lat='. $coordinates['lat'] .'&lon='. $coordinates['lon'] .'&appid=' . $apiKey);
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
