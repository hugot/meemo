<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use App\Component\UserTracker;
use App\Enum\ContentType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class FileController
{
    /* @var UserTracker */
    private $user_tracker;

    /* @var string */
    private $attachment_dir;

    public function __construct(
        UserTracker $user_tracker,
        string      $app_root_dir
    ) {
        $this->user_tracker   = $user_tracker;
        $this->attachment_dir = $app_root_dir . '/' . $_ENV['ATTACHMENT_DIR'];
    }

    /**
     * @Route("/files/{username}/{filename}/", name="app-serve-file")
     */
    public function serveAction(string $username, string $filename, Request $request)
    {
        $file_path = $this->attachment_dir . '/' . $username . '/' . $filename;

        if (file_exists($file_path)) {
            return new BinaryFileResponse($file_path);
        }
        
        return new JsonResponse(
            [
                'status'  => '404 Not found',
                'message' => 'The requested resource was not found on this server.'
            ],
            404
        );
    }

    /**
     * Handle uploads. At the time of writing, Symfony's method of handling this
     * seems very verbose, so I decided to do it the PHP way. This does make the
     * method untestable with unit tests, so it might have to be revisited.
     *
     * @Route("/files", methods={"POST"})
     */
    public function uploadAction(Request $request)
    {
        $user                = $this->user_tracker->findUserForRequest($request);
        $user_attachment_dir = $this->attachment_dir . '/' . $user->getUsername();
        $file                = $_FILES['file'];
        $filename            = $file['name'];
        $tmp_file            = $file['tmp_name'];
        $file_type           = ContentType::UNKNOWN;

        $extention = preg_replace('/^[^.]+/', '', $filename);

        if (preg_match('/^image/', $file['type'])) {
            $file_type = ContentType::IMAGE;
        }

        if (!file_exists($this->attachment_dir)) {
            mkdir($this->attachment_dir);
        }

        if (!file_exists($user_attachment_dir)) {
            mkdir($user_attachment_dir);
        }

        do {
            $new_filename = uniqid($user->getUsername()) . $extention;
            $new_location = $user_attachment_dir . '/' . $new_filename;
        } while (file_exists($new_location));

        move_uploaded_file($tmp_file, $new_location);

        return new JsonResponse(
            [
                'identifier' => $new_filename,
                'fileName'   => $filename,
                'type'       => $file_type
            ],
            201
        );
    }
}
