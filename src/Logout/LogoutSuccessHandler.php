<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 28.11.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Logout;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;

/**
 * Class LogoutSuccessHandler
 * @package LdapBundle\Security
 */
class LogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    /**
     * @param HttpUtils $httpUtils
     * @param string    $targetUrl
     */
    public function __construct(HttpUtils $httpUtils, $targetUrl = '/')
    {
        parent::__construct($httpUtils, $targetUrl);
    }

    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        return new JsonResponse(null, 204);
    }
}

