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

namespace Sylius\PriceHistoryPlugin\Infrastructure\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Webmozart\Assert\Assert;

class ChannelPricingLogEntryRepository extends EntityRepository implements ChannelPricingLogEntryRepositoryInterface
{
    public function createByChannelPricingIdListQueryBuilder(mixed $channelPricingId): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.channelPricing', 'channelPricing')
            ->andWhere('channelPricing = :channelPricingId')
            ->orderBy('o.id', 'DESC')
            ->setParameter('channelPricingId', $channelPricingId)
        ;
    }

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

    public function findLowestPriceBeforeDiscount(
        ChannelPricingInterface $channelPricing,
        int $lowestPriceForDiscountedProductsCheckingPeriod,
    ): ?int {
        $conn = $this->getEntityManager()->getConnection();

        $result = $conn
            ->prepare($this->getLowestPricesBeforeDiscountQuery())
            ->executeQuery([
                'lowestPriceForDiscountedProductsCheckingPeriod' => $lowestPriceForDiscountedProductsCheckingPeriod,
                'channelPricingId' => $channelPricing->getId(),
            ])
            ->fetchOne()
        ;

        if (null === $result || false === $result) {
            return null;
        }

        /** @phpstan-ignore-next-line cast mixed to int */
        return (int) $result;
    }

    private function getLowestPricesBeforeDiscountQuery(): string
    {
        return <<<SQL
SELECT
    (
        CASE
            WHEN lowestPriceSetInPeriod IS NULL THEN latestPriceSetBeyondPeriod
            WHEN latestPriceSetBeyondPeriod IS NULL THEN lowestPriceSetInPeriod
            ELSE LEAST(lowestPriceSetInPeriod, latestPriceSetBeyondPeriod)
        END
    ) price
FROM (
     SELECT
         (
             SELECT query.price
             FROM sylius_price_history_channel_pricing_log_entry query
             WHERE
                query.logged_at >= SUBDATE(
                    (
                        SELECT o.logged_at
                        FROM sylius_price_history_channel_pricing_log_entry o
                        WHERE o.channel_pricing_id = :channelPricingId
                        ORDER BY o.id DESC
                        LIMIT 1
                    ),
                    :lowestPriceForDiscountedProductsCheckingPeriod
                )
                AND query.id != (
                    SELECT subquery.id FROM sylius_price_history_channel_pricing_log_entry subquery
                    WHERE subquery.channel_pricing_id = :channelPricingId
                    ORDER BY subquery.id DESC
                    LIMIT 1
                )
                AND query.channel_pricing_id = :channelPricingId
             ORDER BY query.price ASC
             LIMIT 1
         ) lowestPriceSetInPeriod,
         (
             SELECT query.price
             FROM sylius_price_history_channel_pricing_log_entry query
             WHERE
                query.logged_at < SUBDATE(
                    (
                        SELECT o.logged_at
                        FROM sylius_price_history_channel_pricing_log_entry o
                        WHERE o.channel_pricing_id = :channelPricingId
                        ORDER BY o.id DESC
                        LIMIT 1
                    ),
                    :lowestPriceForDiscountedProductsCheckingPeriod
                )
                AND query.channel_pricing_id = :channelPricingId
             ORDER BY query.id DESC
             LIMIT 1
         ) latestPriceSetBeyondPeriod
) t
SQL;
    }
}
