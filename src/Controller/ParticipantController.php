<?php

namespace App\Controller;

use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Event;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;


class ParticipantController extends AbstractController
{
    #[Route('/events/{id<\d+>}/participants/new', name: 'add_participant')]
    public function addParticipant(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $event = $entityManager->getRepository(Event::class)->find($id);
        if (!$event) {
            $this->addFlash('error', 'L\'événement n\'existe pas');
            throw $this->createNotFoundException('L\'événement n\'existe pas');
        }

        $participant = new Participant();
        $participant->setEvent($event);

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $existingParticipant = $entityManager->getRepository(Participant::class)->findOneBy([
                'event' => $event,
                'email' => $participant->getEmail()
            ]);

            if ($existingParticipant) {
                $this->addFlash('error', 'Ce participant est déjà inscrit à cet événement.');
                return $this->render('participant/new.html.twig', [
                    'form' => $form,
                ]);
            }

            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash('success', 'Participant crée avec succès.');
            return $this->redirectToRoute('event_list');
        }
        return $this->render('participant/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/participants/{id<\d+>}', name: 'participant_view')]
    public function viewEvent(ParticipantRepository $repository, int $id): Response
    {
        $participant = $repository->find($id);
        if (!$participant) {
            throw $this->createNotFoundException('Le participant n\'existe pas');
        }
        return $this->render('participant/view.html.twig', [
            'controller_name' => 'ParticipantController',
            'participant' => $participant,
        ]);
    }

    #[Route('/participants/{id<\d+>}/edit', name: 'participant_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);
        if (!$participant) {
            throw $this->createNotFoundException('Le participant n\'existe pas');
        }
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Participant modifié avec succès.');
            return $this->redirectToRoute('event_list');
        }
        return $this->render('participant/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
