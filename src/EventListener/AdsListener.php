<?php // src/EventListener/AdsListener.php

namespace App\EventListener;

use App\Entity\Ads;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class AdsListener
{
    // Cette méthode est appelée avant qu'une nouvelle annonce soit insérée en base de données
    public function prePersist(Ads $ads, LifecycleEventArgs $event): void
    {
        // Par exemple, on pourrait vérifier si l'annonce est vérifiée
        if ($ads->isVerified() === null) {
            $ads->setVerified(false);
        }
    }

    // Cette méthode est appelée avant la mise à jour d'une annonce existante
    public function preUpdate(Ads $ads, PreUpdateEventArgs $event): void
    {
        // Exemple de logique : si le titre a changé, on fait une action
        if ($event->hasChangedField('title')) {
            // Par exemple, marquer l'annonce comme non vérifiée si le titre a changé
            $ads->setVerified(false);
        }
    }
}