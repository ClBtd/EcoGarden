<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Récupère les exceptions pour les transformer en réponse JSON.
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        // Récupération de l'exception qui a declenché l'évènement
        $exception = $event->getThrowable();

        // On vérifie si c'est une exception HTTP et on en retourne le code et le message
        if ($exception instanceof HttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data));
        } 
        // Sinon on retourne une erreur de serveur 500
        else {
            $data = [
                'status' => 500, 
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data));
      }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
