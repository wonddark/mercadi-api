<?php

namespace App\Controller;

use App\Entity\MediaObject;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
final class CreateMediaObjectAction extends AbstractController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $manager
    ): MediaObject {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $offerId = $request->request->get("offer_id");
        if (!$offerId) {
            throw new BadRequestHttpException('"offer_id" is required');
        }
        $offer = $manager->getRepository(Offer::class)->find($offerId);
        if (!$offer) {
            throw new NotFoundHttpException('The referred offer cannot be found');
        }

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;
        $mediaObject->setOffer($offer);

        return $mediaObject;
    }
}
