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

namespace Sylius\PriceHistoryPlugin\Application\MessageHandler;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Message\RemoveTaxonExclusionFromShowingLowestPrice;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

final class RemoveTaxonExclusionFromShowingLowestPriceHandler implements MessageHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private TaxonRepositoryInterface $taxonRepository,
    ) {
    }

    public function __invoke(RemoveTaxonExclusionFromShowingLowestPrice $command): ChannelInterface
    {
        Assert::allNotNull([$command->channelCode, $command->taxonCode]);

        /**
         * @psalm-suppress PossiblyNullArgument
         * @phpstan-ignore-next-line
         * @var ChannelInterface|null $channel
         */
        $channel = $this->channelRepository->findOneByCode($command->channelCode);
        Assert::notNull($channel);

        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $command->taxonCode]);
        Assert::notNull($taxon);

        $channel->removeTaxonExcludedFromShowingLowestPrice($taxon);

        return $channel;
    }
}
