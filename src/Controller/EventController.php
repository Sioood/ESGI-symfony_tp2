<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\EventRepository;

use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use App\Service\DistanceCalculatorService;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Event;
use App\Form\EventType;

class EventController extends AbstractController
{
    
    private $distanceCalculator;

    public function __construct(DistanceCalculatorService $distanceCalculator)
    {
        $this->distanceCalculator = $distanceCalculator;
    }

    #[Route('/events/new', name: 'event_create')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Événement crée avec succès.');
            return $this->redirectToRoute('event_list');
        }
        return $this->render('event/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/events', name: 'event_list')]
    public function listEvents(EventRepository $repository): Response
    {
        $events = $repository->findAll();
        return $this->render('event/list.html.twig', [
            'controller_name' => 'EventController',
            'events' => $events
        ]);
    }

    #[Route('/events/{id<\d+>}', name: 'event_view')]
    public function viewEvent(EventRepository $repository, int $id): Response
    {
        $event = $repository->find($id);
        if (!$event) {
            throw $this->createNotFoundException('L\'événement n\'existe pas');
        }
        return $this->render('event/view.html.twig', [
            'controller_name' => 'EventController',
            'event' => $event,
        ]);
    }

    #[Route('/events/{id<\d+>}/distance', name: 'calculate_distance_to_event', methods: ['GET'])]
    public function calculateDistanceToEvent(EventRepository $repository, int $id, #[MapQueryParameter] float $lat, #[MapQueryParameter] float $lon): Response
    {

        $event = $repository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        $eventData = [
            'id' => $event->getId(),
            'name' => $event->getName(),
            'latitude' => $event->getLatitude(),
            'longitude' => $event->getLongitude(),
            'date' => $event->getDate(),
        ];

        $distance = $this->distanceCalculator->calculateDistance(
            $lat,
            $lon,
            $event->getLatitude(),
            $event->getLongitude()
        );

        return $this->json([
            'event_id' => $id,
            'event' => $eventData,
            'distance' => $distance,
            'unit' => 'km'
        ]);
    }
}
