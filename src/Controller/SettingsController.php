<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use App\Component\JsonBodyParser;
use App\Component\UserTracker;
use App\Entity\ApiKey;
use App\Entity\Setting;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class SettingsController
{
    /* @var UserTracker */
    private $user_tracker;

    /* @var EntityManagerInterface */
    private $entity_manager;

    /* @var JsonBodyParser */
    private $json_parser;

    public function __construct(
        UserTracker            $user_tracker,
        EntityManagerInterface $entity_manager,
        JsonBodyParser         $json_parser
    ) {
        $this->user_tracker   = $user_tracker;
        $this->entity_manager = $entity_manager;
        $this->json_parser    = $json_parser;
    }

    /**
     * @Route("/settings", methods={"GET"})
     */
    public function getAllAction(Request $request): JsonResponse
    {
        $user     = $this->user_tracker->findUserForRequest($request);
        $settings = $user->getSettings();

        if (count($settings) <= null) {
            $this->insertDefaults($user);
            $settings = $user->getSettings();
        }

        $settings_json = [];

        foreach ($settings as $setting) {
            $value = $setting->getValue();

            if ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            }

            $settings_json[$setting->getName()] = $value;
        }

        return new JsonResponse([ 'settings' => $settings_json ]);
    }

    /**
     * @Route("/settings", methods={"POST"})
     */
    public function saveAllAction(Request $request): JsonResponse
    {
        if (($json = $this->json_parser->parse($request)) instanceof JsonResponse) {
            return $json;
        }

        $user           = $this->user_tracker->findUserForRequest($request);
        $settings       = $user->getSettings();
        $assoc_settings = [];
        foreach ($settings as $setting) {
            $assoc_settings[$setting->getName()] = $setting;
        }

        
        foreach ($json['settings'] as $name => $value) {
            $assoc_settings[$name]->setValue($value);
        }

        $user->setSettings(array_values($assoc_settings));
        $this->entity_manager->persist($user);
        $this->entity_manager->flush();

        return new JsonResponse([], 202);
    }

    private function insertDefaults(User $user)
    {
        $settings = [
            new Setting('title', $_SERVER['HTTP_HOST'], $user),
            new Setting('backgroundImageDataUrl', '', $user),
            new Setting('wide', 'false', $user),
            new Setting('wideNavbar', 'true', $user),
            new Setting('keepPositionAfterEdit', 'false', $user),
            new Setting('publicBackground', 'false', $user),
            new Setting('showTagSidebar', 'false', $user)
        ];

        $user->setSettings($settings);
        $this->entity_manager->persist($user);

        $this->entity_manager->flush();
    }
}
