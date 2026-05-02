<?php

namespace App\Controller;

use App\Entity\Site;
use App\Event\SiteCheckExpireDateEvent;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use App\Repository\StatusLogRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;


#[Route('/sites')]
class SiteController extends AbstractController
{
    #[Route('/', name: 'sites_index', methods: ['GET'])]
    public function index(SiteRepository $siteRepository, MessageBusInterface $bus): Response
    {
        return $this->render('site/index.html.twig', [
            'sites' => $siteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'sites_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SiteRepository $siteRepository, EventDispatcherInterface $bus): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entitySite = $siteRepository->save($site, true);
            $bus->dispatch(new SiteCheckExpireDateEvent($entitySite));

            return $this->redirectToRoute('sites_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/new.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'sites_show', methods: ['GET'])]
    public function show(Site $site, StatusLogRepository $statusLogRepository, CacheInterface $cache): Response
    {
        $statusChart = $cache->get(
            "site_status_chart_{$site->getId()}_24h",
            static function (ItemInterface $item) use ($site, $statusLogRepository): array {
                $item->expiresAfter(300);

                $statusLogs = $statusLogRepository->findBySiteSince($site, new DateTimeImmutable('-24 hours'));

                return [
                    'labels' => array_map(
                        static fn ($statusLog): string => $statusLog->getTimestamp()->format('d.m H:i'),
                        $statusLogs
                    ),
                    'data' => array_map(
                        static fn ($statusLog): ?int => $statusLog->getStatus(),
                        $statusLogs
                    ),
                ];
            }
        );

        return $this->render('site/show.html.twig', [
            'site' => $site,
            'statusChartLabels' => $statusChart['labels'],
            'statusChartData' => $statusChart['data'],
        ]);
    }

    #[Route('/{id}/edit', name: 'sites_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Site $site, SiteRepository $siteRepository): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $siteRepository->save($site, true);

            return $this->redirectToRoute('sites_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/edit.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'sites_delete', methods: ['POST'])]
    public function delete(Request $request, Site $site, SiteRepository $siteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$site->getId(), $request->request->get('_token'))) {
            $siteRepository->remove($site, true);
        }

        return $this->redirectToRoute('sites_index', [], Response::HTTP_SEE_OTHER);
    }
}
