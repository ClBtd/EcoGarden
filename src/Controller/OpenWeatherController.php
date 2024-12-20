<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherController extends AbstractController
{
    #[Route('api/meteo', name: 'api_currentLocation_weather', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message:'Vous devez être connectés pour accéder à cette page.')]
    public function getWeatherByZipcode(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository, CacheInterface $cache, HttpClientInterface $httpClient): JsonResponse
    {
        // Récupération du code postal de l'utilisateur via le token 
        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());
        $currentUser = $userRepository->getUserByName($decodedJwtToken['username']);
        $zipcode = $currentUser->getZipcode();
        $cacheKey = 'weather_' . $zipcode;

        // Récupération de la clé de l'api externe
        $apiKey = $_ENV['API_KEY'];

        // Mise en cache des données récupérées
        $weatherData = $cache->get($cacheKey, function (ItemInterface $item) use ($httpClient, $zipcode, $apiKey) {
            
            $item->expiresAfter(3600);

            try {
                // Récupération des coordonées géographiques de l'utilisateur
                $curlResponse = $httpClient->request('GET', 'http://api.openweathermap.org/geo/1.0/zip?zip='. $zipcode . ',FR&appid=' . $apiKey);
                $coordinates = $curlResponse->toArray();
                if (empty($coordinates)) {
                    throw new \Exception('Code postal inexistant.');
                }
                // Récupération des données météos de la ville de l'utilisateur   
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

        // Récupération de la clé de l'api externe
        $apiKey = $_ENV['API_KEY'];

        // Mise en cache des données récupérées
        $weatherData = $cache->get($cacheKey, function (ItemInterface $item) use ($httpClient, $city, $apiKey) {
            
            $item->expiresAfter(3600);

            try {
                // Récupération des coordonées géographiques de la ville entrée par l'utilisateur
                $curlResponse = $httpClient->request('GET', 'http://api.openweathermap.org/geo/1.0/direct?q='. $city . ',FR&appid=' . $apiKey);
                $arrayResponse = $curlResponse->toArray();
                if (empty($arrayResponse)) {
                    throw new \Exception('Aucune ville trouvée.');
                }
                // Récupération des données météos de la ville    
                $coordinates = $arrayResponse[0];
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
}
