<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use App\Component\JsonBodyParser;
use App\Component\UserTracker;
use App\Entity\Thing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ThingController
{
    private $user_tracker;
    private $json_parser;
    private $entity_manager;

    public function __construct(
        UserTracker            $user_tracker,
        JsonBodyParser         $json_parser,
        EntityManagerInterface $entity_manager
    ) {
        $this->user_tracker   = $user_tracker;
        $this->json_parser    = $json_parser;
        $this->entity_manager = $entity_manager;
    }

    /**
     * @Route("/things", methods={"GET"})
     */
    public function getAllAction(Request $request): JsonResponse
    {
        $user = $this->user_tracker->findUserForRequest($request);

        return new JsonResponse([ 'things' => $user->getThings()->toArray() ]);
    }

    /**
     * @Route("/things", methods={"POST"})
     */
    public function addAction(Request $request): JsonResponse
    {
        if (($json = $this->json_parser->parse($request)) instanceof JsonResponse) {
            return $json;
        }
        $user  = $this->user_tracker->findUserForRequest($request);
        $thing = new Thing($json['content'], $user);
        $user->addThing($thing);

        $this->entity_manager->persist($user);
        $this->entity_manager->flush();

        return new JsonResponse([ 'thing' => $thing ], 201);
    }
}
