<?php

namespace spec\Sylius\PriceHistoryPlugin\Infrastructure\EventListener;

use ApiPlatform\Symfony\Validator\Exception\ConstraintViolationListAwareExceptionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Channel\Factory\ChannelFactoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BeforeDenormalizationChannelValidationListenerSpec extends ObjectBehavior
{
    private const VALIDATION_GROUPS = ['sylius'];

    function let(ValidatorInterface $validator, ChannelFactoryInterface $channelFactory): void
    {
        $this->beConstructedWith($validator, $channelFactory, self::VALIDATION_GROUPS);
    }

    function it_does_nothing_when_its_not_an_api_platform_request(
        ChannelFactoryInterface $channelFactory,
        ValidatorInterface $validator,
        RequestEvent $event,
        Request $request,
        ParameterBag $attributes,
    ): void {
        $attributes->all()->willReturn([]);
        $request->attributes = $attributes;

        $event->getRequest()->willReturn($request);

        $channelFactory->createNew()->shouldNotBeCalled();
        $validator->validatePropertyValue(Argument::cetera())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_when_request_is_safe(
        ChannelFactoryInterface $channelFactory,
        ValidatorInterface $validator,
        RequestEvent $event,
        Request $request,
        ParameterBag $attributes,
        ChannelInterface $channel,
    ): void {
        $attributes->all()->willReturn(['_api_resource_class' => Channel::class, 'data' => $channel]);
        $request->attributes = $attributes;
        $request->isMethodSafe()->willReturn(true);

        $event->getRequest()->willReturn($request);

        $channelFactory->createNew()->shouldNotBeCalled();
        $validator->validatePropertyValue(Argument::cetera())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_when_request_is_for_deletion(
        ChannelFactoryInterface $channelFactory,
        ValidatorInterface $validator,
        RequestEvent $event,
        Request $request,
        ParameterBag $attributes,
        ChannelInterface $channel,
    ): void {
        $attributes->all()->willReturn(['_api_resource_class' => Channel::class, 'data' => $channel]);
        $request->attributes = $attributes;
        $request->isMethodSafe()->willReturn(false);
        $request->isMethod(Request::METHOD_DELETE)->willReturn(true);

        $event->getRequest()->willReturn($request);

        $channelFactory->createNew()->shouldNotBeCalled();
        $validator->validatePropertyValue(Argument::cetera())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_when_request_content_is_empty(
        ChannelFactoryInterface $channelFactory,
        ValidatorInterface $validator,
        ContextualValidatorInterface $context,
        ConstraintViolationListInterface $violationsList,
        RequestEvent $event,
        Request $request,
        ParameterBag $attributes,
        ChannelInterface $channel,
    ): void {
        $attributes->all()->willReturn(['_api_resource_class' => Channel::class]);
        $request->attributes = $attributes;
        $request->isMethodSafe()->willReturn(false);
        $request->isMethod(Request::METHOD_DELETE)->willReturn(false);
        $request->getContent(false)->willReturn('{}');

        $event->getRequest()->willReturn($request);

        $channelFactory->createNew()->willReturn($channel);

        $validator->startContext()->willReturn($context);
        $context->getViolations()->willReturn($violationsList);

        $validator->validatePropertyValue(Argument::cetera())->shouldNotBeCalled();
        $violationsList->count()->willReturn(0);
        $violationsList->addAll(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_not_throw_validation_exception_when_request_content_causes_no_violations(
        ChannelFactoryInterface $channelFactory,
        ValidatorInterface $validator,
        ContextualValidatorInterface $context,
        ConstraintViolationListInterface $mainViolationsList,
        ConstraintViolationListInterface $codeViolationsList,
        RequestEvent $event,
        Request $request,
        ParameterBag $attributes,
        ChannelInterface $channel,
    ): void {
        $attributes->all()->willReturn(['_api_resource_class' => Channel::class]);
        $request->attributes = $attributes;
        $request->isMethodSafe()->willReturn(false);
        $request->isMethod(Request::METHOD_DELETE)->willReturn(false);
        $request->getContent(false)->willReturn('{"code": "WEB"}');

        $event->getRequest()->willReturn($request);

        $channelFactory->createNew()->willReturn($channel);

        $validator->startContext()->willReturn($context);
        $context->getViolations()->willReturn($mainViolationsList);

        $validator
            ->validatePropertyValue($channel, 'code', 'WEB', self::VALIDATION_GROUPS)
            ->willReturn($codeViolationsList)
        ;

        $codeViolationsList->count()->willReturn(0);
        $mainViolationsList->addAll(Argument::any())->shouldNotBeCalled();
        $mainViolationsList->count()->willReturn(0);

        $this->onKernelRequest($event);
    }

    function it_throws_violation_exception_when_at_least_one_property_is_not_valid(
        ChannelFactoryInterface $channelFactory,
        ValidatorInterface $validator,
        ContextualValidatorInterface $context,
        ConstraintViolationListInterface $mainViolationsList,
        ConstraintViolationListInterface $codeViolationsList,
        ConstraintViolationListInterface $nameViolationsList,
        RequestEvent $event,
        Request $request,
        ParameterBag $attributes,
        ChannelInterface $channel,
    ): void {
        $attributes->all()->willReturn(['_api_resource_class' => Channel::class]);
        $request->attributes = $attributes;
        $request->isMethodSafe()->willReturn(false);
        $request->isMethod(Request::METHOD_DELETE)->willReturn(false);
        $request->getContent(false)->willReturn('{"code": null, "name": "Name"}');

        $event->getRequest()->willReturn($request);

        $channelFactory->createNew()->willReturn($channel);

        $validator->startContext()->willReturn($context);
        $context->getViolations()->willReturn($mainViolationsList);

        $validator
            ->validatePropertyValue($channel, 'code', null, self::VALIDATION_GROUPS)
            ->shouldBeCalled()
            ->willReturn($codeViolationsList)
        ;
        $validator
            ->validatePropertyValue($channel, 'name', "Name", self::VALIDATION_GROUPS)
            ->shouldBeCalled()
            ->willReturn($nameViolationsList)
        ;

        $codeViolationsList->count()->willReturn(1);
        $nameViolationsList->count()->willReturn(0);

        $mainViolationsList->addAll($codeViolationsList)->shouldBeCalled();
        $mainViolationsList->addAll($nameViolationsList)->shouldNotBeCalled();
        $mainViolationsList->count()->willReturn(1);

        // The 2 calls below are here to silence ValidatorException::__toString
        $mainViolationsList->valid()->willReturn(false);
        $mainViolationsList->rewind()->shouldBeCalled();

        $this->shouldThrow(ConstraintViolationListAwareExceptionInterface::class)->during('onKernelRequest', [$event]);
    }

    function it_throws_violation_exception_when_all_properties_are_not_valid(
        ChannelFactoryInterface $channelFactory,
        ValidatorInterface $validator,
        ContextualValidatorInterface $context,
        ConstraintViolationListInterface $mainViolationsList,
        ConstraintViolationListInterface $codeViolationsList,
        ConstraintViolationListInterface $nameViolationsList,
        RequestEvent $event,
        Request $request,
        ParameterBag $attributes,
        ChannelInterface $channel,
    ): void {
        $attributes->all()->willReturn(['_api_resource_class' => Channel::class]);
        $request->attributes = $attributes;
        $request->isMethodSafe()->willReturn(false);
        $request->isMethod(Request::METHOD_DELETE)->willReturn(false);
        $request->getContent(false)->willReturn('{"code": null, "name": 12}');

        $event->getRequest()->willReturn($request);

        $channelFactory->createNew()->willReturn($channel);

        $validator->startContext()->willReturn($context);
        $context->getViolations()->willReturn($mainViolationsList);

        $validator
            ->validatePropertyValue($channel, 'code', null, self::VALIDATION_GROUPS)
            ->shouldBeCalledTimes(1)
            ->willReturn($codeViolationsList)
        ;
        $validator
            ->validatePropertyValue($channel, 'name', 12, self::VALIDATION_GROUPS)
            ->shouldBeCalledTimes(1)
            ->willReturn($nameViolationsList)
        ;

        $codeViolationsList->count()->willReturn(1);
        $nameViolationsList->count()->willReturn(1);

        $mainViolationsList->addAll($codeViolationsList)->shouldBeCalledTimes(1);
        $mainViolationsList->addAll($nameViolationsList)->shouldBeCalledTimes(1);
        $mainViolationsList->count()->willReturn(2);

        // The 2 calls below are here to silence ValidatorException::__toString
        $mainViolationsList->valid()->willReturn(false);
        $mainViolationsList->rewind()->shouldBeCalled();

        $this->shouldThrow(ConstraintViolationListAwareExceptionInterface::class)->during('onKernelRequest', [$event]);
    }
}
