<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController
{
    /* @var string */
    private $app_root_dir;

    public function __construct(string $app_root_dir)
    {
        $this->app_root_dir = $app_root_dir;
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        return new Response(file_get_contents($this->app_root_dir . '/public/index.html'));
    }
}
