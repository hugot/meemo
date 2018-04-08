<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use App\Component\AttachmentAdder;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/public")
 */
class PublicThingController
{
    private $user_repository;
    private $attachment_adder;

    public function __construct(
        UserRepository  $user_repository,
        AttachmentAdder $attachment_adder
    ) {
        $this->user_repository  = $user_repository;
        $this->attachment_adder = $attachment_adder;
    }

    /**
     * @Route("/{username}/things")
     */
    public function getAllForUserAction(string $username)
    {
        $user = $this->user_repository->findOneByUsername($username);

        if ($user === null) {
            return new JsonResponse(
                [
                    'status'  => '404 Not found',
                    'message' => 'The requested user could not be found.'
                ],
                404
            );
        }

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('public', true))
            ->orderBy([ 'created_at' => Criteria::DESC ]);
        
        return new JsonResponse([
            'things' => $this->attachment_adder->addAttachmentsToThings(
                $user->getThings()->matching($criteria)->toArray()
            )
        ]);
    }
}
