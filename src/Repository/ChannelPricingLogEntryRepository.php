<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\PriceHistoryPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Webmozart\Assert\Assert;

class ChannelPricingLogEntryRepository extends EntityRepository implements ChannelPricingLogEntryRepositoryInterface
{
    public function findOlderThan(\DateTimeInterface $date, ?int $limit = null): array
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->andWhere('o.loggedAt < :date')
            ->setParameter('date', $date)
        ;

        if (null !== $limit) {
            Assert::positiveInteger($limit);
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
