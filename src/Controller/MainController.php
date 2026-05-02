<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Repository\StatusLogRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class MainController extends AbstractController
{
    #[Route('/', name: 'main_index', methods: ['GET'])]
    public function index(SiteRepository $siteRepository, StatusLogRepository $statusLogRepository): Response
    {
        $sites = $siteRepository->findAll();
        $expiresBefore = new DateTimeImmutable('+14 days');

        $problemSites = array_values(array_filter(
            $sites,
            static fn (Site $site): bool => $site->getStatus() !== null && $site->getStatus() !== 200
        ));
        $unknownSites = array_values(array_filter(
            $sites,
            static fn (Site $site): bool => $site->getStatus() === null
        ));
        $expiringSites = array_values(array_filter(
            $sites,
            static function (Site $site) use ($expiresBefore): bool {
                $expireDate = $site->getExpireDate();

                return $expireDate
                    && (
                        ($expireDate->getDomain() && $expireDate->getDomain() <= $expiresBefore)
                        || ($expireDate->getSsl() && $expireDate->getSsl() <= $expiresBefore)
                    );
            }
        ));
        $attentionSites = [];
        foreach ([$problemSites, $unknownSites, $expiringSites] as $siteGroup) {
            foreach ($siteGroup as $site) {
                $siteId = $site->getId();
                if ($siteId !== null) {
                    $attentionSites[$siteId] = $site;
                }
            }
        }

        return $this->render('index.html.twig', [
            'sites' => $sites,
            'problemSites' => $problemSites,
            'unknownSites' => $unknownSites,
            'expiringSites' => $expiringSites,
            'attentionSites' => array_values($attentionSites),
            'expiresBefore' => $expiresBefore,
            'latestStatusLogs' => $statusLogRepository->findBy([], ['timestamp' => 'DESC'], 8),
        ]);
    }
}
