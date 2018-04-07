<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use App\Component\AttachmentAdder;
use App\Component\FaceLifter;
use App\Component\JsonBodyParser;
use App\Component\TagParser;
use App\Component\UserTracker;
use App\Entity\ApiKey;
use App\Entity\Attachment;
use App\Entity\Tag;
use App\Entity\Thing;
use App\Entity\User;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\Criteria;
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
    private $face_lifter;
    private $attachment_adder;
    private $tag_parser;

    public function __construct(
        UserTracker            $user_tracker,
        JsonBodyParser         $json_parser,
        EntityManagerInterface $entity_manager,
        FaceLifter             $face_lifter,
        AttachmentAdder        $attachment_adder,
        TagParser              $tag_parser,
        TagRepository          $tag_repository
    ) {
        $this->user_tracker     = $user_tracker;
        $this->json_parser      = $json_parser;
        $this->entity_manager   = $entity_manager;
        $this->face_lifter      = $face_lifter;
        $this->attachment_adder = $attachment_adder;
        $this->tag_parser       = $tag_parser;
        $this->tag_repository   = $tag_repository;
    }

    /**
     * @Route("/things", methods={"GET"})
     */
    public function getAllAction(Request $request): JsonResponse
    {
        $key      = $this->user_tracker->findKeyForRequest($request);
        $user     = $key->getUser();
        $archived = false;
        $things   = [];

        $skip  = (int) $request->query->get('skip');
        $limit = (int) $request->query->get('limit');
        
        if ($request->query->get('archived') === 'true') {
            $archived = true;
        }

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('archived', $archived))
            ->setFirstResult($skip)
            ->setMaxResults($limit)
            ->orderBy([
                'sticky'      => Criteria::DESC,
                'modified_at' => Criteria::DESC
            ]);

        if (!empty(($filter = $request->query->get('filter')))) {
            $tag = $this->tag_repository
                ->findOneBy([ 'name' => preg_replace('/#/', '', $filter, 1) ]);
             
            if ($tag instanceof Tag) {
                $things = $tag->getThings()->matching($criteria)->toArray();
            } else {
                $things = [];
            }
        } else {
            $things = $user->getThings()->matching($criteria)->toArray();
        }

        return new JsonResponse(
            [
                'things' => $this->attachment_adder->addAttachmentsToThings($things, $key)
            ]
        );
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
        $thing = new Thing(
            $json['content'],
            $this->face_lifter->liftFace(
                $json['content']
            ),
            $user
        );

        foreach ($json['attachments'] as $attachment_json) {
            $attachment = new Attachment(
                $thing,
                $attachment_json['fileName'],
                $attachment_json['identifier'],
                $attachment_json['type']
            );
            $thing->addAttachment($attachment);
        }

        $thing->setTags(
            $this->mergeNewAndExistingTags(
                $this->tag_parser->parseNewAndExisting($thing->getContent()),
                $thing,
                $user
            )
        );

        $user->addThing($thing);

        $this->entity_manager->persist($user);
        $this->entity_manager->flush();

        return new JsonResponse([ 'thing' => $thing ], 201);
    }

    /**
     * @Route("/things/{thing_id}", methods={"PUT"})
     */
    public function editAction(int $thing_id, Request $request): JsonResponse
    {
        if (($json = $this->json_parser->parse($request)) instanceof JsonResponse) {
            return $json;
        }
        $user     = $this->user_tracker->findUserForRequest($request);
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('id', $thing_id));
        $thing    = $user->getThings()->matching($criteria)->first();

        if ($thing === null) {
            return new JsonResponse(
                [
                    'status'  => '404 Not Found',
                    'message' => sprintf('Thing by id "%d" could not be found.', $thing_id)
                ],
                404
            );
        }

        $thing->setContent($json['content']);
        $thing->setRichContent($this->face_lifter->liftFace($json['content']));
        $thing->setSticky($json['sticky']);
        $thing->setArchived($json['archived']);
        $thing->updateModifiedAt();
        $thing->setPublic($json['public']);

        foreach ($json['attachments'] as $attachment_json) {
            if (!$this->hasAttachment($thing, $attachment_json['identifier'])) {
                $thing->addAttachment(
                    new Attachment(
                        $thing,
                        $attachment_json['fileName'],
                        $attachment_json['identifier'],
                        $attachment_json['type']
                    )
                );
            }
        }

        $thing->setTags(
            $this->mergeNewAndExistingTags(
                $this->tag_parser->parseNewAndExisting($thing->getContent()),
                $thing,
                $user
            )
        );

        $this->entity_manager->persist($thing);
        $this->entity_manager->flush();

        return new JsonResponse([ 'thing' => $thing ], 201);
    }

    /**
     * @Route("/things/{thing}", methods={"DELETE"})
     */
    public function deleteAction(Thing $thing, Request $request): JsonResponse
    {
        if ($thing->getUser() === $this->user_tracker->findUserForRequest($request)) {
            $this->entity_manager->remove($thing);
            $this->entity_manager->flush();

            return new JsonResponse([], 200);
        }

        return new JsonResponse(
            [
                'status'   => 'Unauthorized',
                'messsage' => 'You do not have acces to this resource.'
            ],
            401
        );
    }

    private function hasAttachment(Thing $thing, string $identifier)
    {
        $matching_attachments = array_filter(
            $thing->getAttachments()->toArray(),
            function ($attachment) use ($identifier) {
                return $attachment->getIdentifier() === $identifier;
            }
        );

        return count($matching_attachments) > 0;
    }

    private function mergeNewAndExistingTags(array $tags, Thing $thing, User $user): array
    {
        $merged_tags = [];
        
        foreach ($tags['new'] as $tag) {
            $new_tag       = new Tag($tag, [ $thing ], $user);
            $merged_tags[] = $new_tag;
            $this->entity_manager->persist($new_tag);
        }
        
        foreach ($tags['existing'] as $ex_tag) {
            $merged_tags[] = $ex_tag;
        }

        return $merged_tags;
    }
}
