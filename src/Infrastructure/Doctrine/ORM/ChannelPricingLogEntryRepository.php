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
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
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

    public function bulkUpdateLowestPricesBeforeDiscount(ChannelInterface $channel): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = sprintf(
            'UPDATE sylius_channel_pricing scp SET scp.lowest_price_before_discount = (%s) WHERE scp.channel_code = :channelCode',
            $this->getLowestPricesBeforeDiscountQuery(),
        );

        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'lowestPriceForDiscountedProductsCheckingPeriod' => $channel->getLowestPriceForDiscountedProductsCheckingPeriod(),
            'channelCode' => $channel->getCode(),
        ]);
    }

    public function findLowestPricesBeforeDiscount(
        ChannelPricingInterface $channelPricing,
        int $lowestPriceForDiscountedProductsCheckingPeriod,
    ): ?int {
        $conn = $this->getEntityManager()->getConnection();

        $sql = $this->getLowestPricesBeforeDiscountQuery($channelPricing->getId());

        $result = $conn
            ->prepare($sql)
            ->executeQuery([
                'lowestPriceForDiscountedProductsCheckingPeriod' => $lowestPriceForDiscountedProductsCheckingPeriod,
                'channelPricingId' => $channelPricing->getId(),
            ])
            ->fetchOne()
        ;

        if (false === $result || null === $result) {
            return null;
        }

        /** @phpstan-ignore-next-line cast mixed to int */
        return (int) $result;
    }

    private function getLowestPricesBeforeDiscountQuery(int $channelPricingId = null): string
    {
        if (null === $channelPricingId) {
            $channelPricingId = 'scp.id';
        }

        $selectLowestPriceBeforeDiscount = sprintf(
            'CASE
                %s
                WHEN lowestPriceSetInPeriod IS NULL THEN latestPriceSetBeyondPeriod
                WHEN latestPriceSetBeyondPeriod IS NULL THEN lowestPriceSetInPeriod
                ELSE LEAST(lowestPriceSetInPeriod, latestPriceSetBeyondPeriod)
            END',
            'scp.id' === $channelPricingId ? 'WHEN scp.original_price IS NULL OR scp.price >= scp.original_price THEN NULL' : '',
        );

        $startDateSql = "
            SUBDATE(
                (
                    SELECT o.logged_at
                    FROM sylius_price_history_channel_pricing_log_entry o
                    WHERE o.channel_pricing_id = $channelPricingId
                    ORDER BY o.id DESC
                    LIMIT 1
                ),
                :lowestPriceForDiscountedProductsCheckingPeriod
            )
        ";

        $lowestPriceSetInPeriod = "
            SELECT query.price
            FROM sylius_price_history_channel_pricing_log_entry query
            WHERE
                query.logged_at >= $startDateSql
                AND query.id != (
                    SELECT subquery.id FROM sylius_price_history_channel_pricing_log_entry subquery
                    WHERE subquery.channel_pricing_id = $channelPricingId
                    ORDER BY subquery.id DESC
                    LIMIT 1
                )
                AND query.channel_pricing_id = $channelPricingId
            ORDER BY query.price ASC
            LIMIT 1
        ";

        $latestPriceSetBeyondPeriod = "
            SELECT query.price
            FROM sylius_price_history_channel_pricing_log_entry query
            WHERE
                query.logged_at < $startDateSql
                AND query.channel_pricing_id = $channelPricingId
            ORDER BY query.id DESC
            LIMIT 1
        ";

        return sprintf(
            'SELECT (%s) price FROM (SELECT (%s) lowestPriceSetInPeriod, (%s) latestPriceSetBeyondPeriod) t',
            $selectLowestPriceBeforeDiscount,
            $lowestPriceSetInPeriod,
            $latestPriceSetBeyondPeriod,
        );
    }
}
