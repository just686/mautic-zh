<?php

namespace MauticPlugin\MauticRegistrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityOverrideController extends AbstractController
{
    public function guardAction(): RedirectResponse
    {
        return $this->redirectToRoute('mautic_dashboard_index');
    }
}
