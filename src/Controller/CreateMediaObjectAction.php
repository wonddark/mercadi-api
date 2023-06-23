<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\MediaObject;
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

        $itemId = $request->request->get("item_id");
        if (!$itemId) {
            throw new BadRequestHttpException('"item_id" is required');
        }
        $item = $manager->getRepository(Item::class)->find($itemId);
        if (!$item) {
            throw new NotFoundHttpException('The referred item cannot be found');
        }

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;
        $mediaObject->setItem($item);

        return $mediaObject;
    }
}
